<?php

namespace App\Support\Crm;

use App\Models\Donor;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Bağışçı listesi için kurumsal, çok sayfalı Excel raporu.
 *
 * Sayfa 1 - Özet: KPI + şehir / ülke kırılımları + en yüksek bağışçılar
 * Sayfa 2 - Bağışçı Listesi: filtrelenmiş tüm bağışçıların detay dökümü
 */
class DonorSpreadsheetExporter extends CorporateSpreadsheet
{
    private const SHEET_SUMMARY = 0;

    private const SHEET_DETAIL = 1;

    /** @var array<int, string> */
    private const DETAIL_HEADERS = [
        'Sıra',
        'Ad',
        'Soyad',
        'Telefon',
        'E-posta',
        'Şehir',
        'Ülke',
        'Adres',
        'Bağış Sayısı',
        'Toplam Tutar',
        'İlk Bağış',
        'Son Bağış',
        'Kayıt Tarihi',
    ];

    /** @var array<int, float> */
    private const DETAIL_WIDTHS = [6, 16, 16, 15, 28, 14, 12, 34, 12, 14, 13, 13, 13];

    private const COUNT_COLUMN_INDEX = 8;

    private const AMOUNT_COLUMN_INDEX = 9;

    /** @var array<int, string> */
    private const METRIC_HEADERS = ['Sıra', 'Kırılım', 'Bağışçı Sayısı', 'Toplam Tutar', 'Ort. Tutar'];

    /** @var array<int, string> */
    private const TOP_HEADERS = ['Sıra', 'Bağışçı', 'Telefon', 'Bağış Adedi', 'Toplam Tutar'];

    /** @var array<int, float> */
    private const SUMMARY_WIDTHS = [28, 36, 16, 14, 16];

    public static function download(Builder $query, ?string $filename = null): StreamedResponse
    {
        $org = Setting::current()->site_title ?: 'SECDER';
        $slug = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $org) ?: 'SECDER';
        $filename ??= $slug . '_Bagisci_Raporu_' . now()->format('Y-m-d_His') . '.xlsx';

        return response()->streamDownload(function () use ($query): void {
            self::write($query);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private static function write(Builder $query): void
    {
        $donors = (clone $query)
            ->with('donations')
            ->reorder()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $rows = $donors->map(fn (Donor $donor): array => self::mapDonorRow($donor))->values();
        $stats = self::buildStats($rows);

        $options = new Options();
        $writer = new Writer($options);
        $writer->openToFile('php://output');

        $writer->getCurrentSheet()->setName('Özet');
        self::applyWidths($writer, self::SUMMARY_WIDTHS);
        self::writeSummarySheet($writer, $options, $rows, $stats);

        $writer->addNewSheetAndMakeItCurrent();
        $writer->getCurrentSheet()->setName('Bağışçı Listesi');
        self::applyWidths($writer, self::DETAIL_WIDTHS);
        self::writeDetailSheet($writer, $options, $rows, $stats);

        $writer->close();
    }

    /**
     * @return array{
     *     first_name: string,
     *     last_name: string,
     *     full_name: string,
     *     phone: string,
     *     email: string,
     *     city: string,
     *     country: string,
     *     address: string,
     *     count: int,
     *     total: float,
     *     first_donation: string,
     *     last_donation: string,
     *     created_at: string
     * }
     */
    private static function mapDonorRow(Donor $donor): array
    {
        $donations = $donor->donations;
        $first = $donations->min('donated_at');
        $last = $donations->max('donated_at');

        return [
            'first_name' => (string) ($donor->first_name ?? ''),
            'last_name' => (string) ($donor->last_name ?? ''),
            'full_name' => trim(($donor->first_name ?? '') . ' ' . ($donor->last_name ?? '')) ?: 'İsimsiz',
            'phone' => (string) ($donor->phone ?? ''),
            'email' => (string) ($donor->email ?? ''),
            'city' => (string) ($donor->city ?? '') ?: 'Belirtilmemiş',
            'country' => (string) ($donor->country ?? '') ?: 'Belirtilmemiş',
            'address' => (string) ($donor->address ?? ''),
            'count' => $donations->count(),
            'total' => (float) $donations->sum('amount'),
            'first_donation' => self::formatDate($first),
            'last_donation' => self::formatDate($last),
            'created_at' => $donor->created_at?->format('d.m.Y') ?? '',
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array{
     *     donor_count: int,
     *     donation_count: int,
     *     total: float,
     *     average: float,
     *     active_donors: int,
     *     byCity: array<int, array{label: string, count: int, total: float}>,
     *     byCountry: array<int, array{label: string, count: int, total: float}>,
     *     topDonors: array<int, array<string, mixed>>
     * }
     */
    private static function buildStats(Collection $rows): array
    {
        $donorCount = $rows->count();
        $donationCount = (int) $rows->sum('count');
        $total = (float) $rows->sum('total');
        $active = $rows->where('count', '>', 0)->count();

        return [
            'donor_count' => $donorCount,
            'donation_count' => $donationCount,
            'total' => $total,
            'average' => $donorCount > 0 ? $total / $donorCount : 0.0,
            'active_donors' => $active,
            'byCity' => self::groupDonorMetrics($rows, 'city'),
            'byCountry' => self::groupDonorMetrics($rows, 'country'),
            'topDonors' => $rows
                ->sortByDesc('total')
                ->take(15)
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<int, array{label: string, count: int, total: float}>
     */
    private static function groupDonorMetrics(Collection $rows, string $field): array
    {
        return $rows
            ->groupBy($field)
            ->map(fn (Collection $group, $label): array => [
                'label' => (string) $label,
                'count' => $group->count(),
                'total' => (float) $group->sum('total'),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array{
     *     donor_count: int,
     *     donation_count: int,
     *     total: float,
     *     average: float,
     *     active_donors: int,
     *     byCity: array<int, array{label: string, count: int, total: float}>,
     *     byCountry: array<int, array{label: string, count: int, total: float}>,
     *     topDonors: array<int, array<string, mixed>>
     * }  $stats
     */
    private static function writeSummarySheet(Writer $writer, Options $options, Collection $rows, array $stats): void
    {
        $lastColumn = count(self::METRIC_HEADERS) - 1;
        $rowNum = 0;

        $metaLine = sprintf(
            'Rapor Tarihi: %s          Bağışçı: %d          Toplam Bağış: %d          Tutar: %s TRY',
            now()->format('d.m.Y H:i'),
            $stats['donor_count'],
            $stats['donation_count'],
            self::money($stats['total']),
        );

        foreach (self::titleBands(Setting::current(), 'BAĞIŞÇI RAPORU — ÖZET', $metaLine) as [$value, $style]) {
            $writer->addRow(self::bandRow($value, $lastColumn, $style));
            $rowNum++;
            $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
        }

        self::spacer($writer, $rowNum);
        self::sectionTitle($writer, $rowNum, 'Genel Göstergeler', $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);

        self::writeKpiRow($writer, $rowNum, [
            ['Bağışçı Sayısı', (string) $stats['donor_count']],
            ['Aktif Bağışçı', (string) $stats['active_donors']],
            ['Toplam Bağış Adedi', (string) $stats['donation_count']],
            ['Toplam Tutar (TRY)', self::money($stats['total'])],
            ['Ort. Bağışçı Tutarı', self::money($stats['average'])],
        ], $lastColumn, $options, self::SHEET_SUMMARY);

        self::spacer($writer, $rowNum);
        self::sectionTitle($writer, $rowNum, 'En Yüksek Bağışçılar', $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
        self::writeTopDonorsTable($writer, $rowNum, $stats['topDonors']);

        self::spacer($writer, $rowNum);
        self::sectionTitle($writer, $rowNum, 'Şehir Kırılımı', $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
        self::writeMetricTable(
            $writer,
            $rowNum,
            self::METRIC_HEADERS,
            $stats['byCity'],
            $stats['donor_count'],
            $stats['total'],
        );
        $options->mergeCells(0, $rowNum, 1, $rowNum, self::SHEET_SUMMARY);

        self::spacer($writer, $rowNum);
        self::sectionTitle($writer, $rowNum, 'Ülke Kırılımı', $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
        self::writeMetricTable(
            $writer,
            $rowNum,
            self::METRIC_HEADERS,
            $stats['byCountry'],
            $stats['donor_count'],
            $stats['total'],
        );
        $options->mergeCells(0, $rowNum, 1, $rowNum, self::SHEET_SUMMARY);

        self::footerNote($writer, $rowNum, $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
    }

    /**
     * @param  array<int, array<string, mixed>>  $topDonors
     */
    private static function writeTopDonorsTable(Writer $writer, int &$rowNum, array $topDonors): void
    {
        $headerCells = [];
        foreach (self::TOP_HEADERS as $header) {
            $headerCells[] = Cell::fromValue($header, self::headerStyle());
        }
        $writer->addRow(new Row($headerCells));
        $rowNum++;

        $index = 0;
        foreach ($topDonors as $donor) {
            $index++;
            $rowNum++;
            $zebra = ($index % 2) === 0;

            $writer->addRow(new Row([
                Cell::fromValue($index, self::cellStyle($zebra, CellAlignment::CENTER)),
                Cell::fromValue($donor['full_name'], self::cellStyle($zebra)),
                Cell::fromValue($donor['phone'], self::cellStyle($zebra)),
                Cell::fromValue($donor['count'], self::cellStyle($zebra, CellAlignment::CENTER)),
                Cell::fromValue($donor['total'], self::amountStyle($zebra)),
            ]));
        }

        if ($topDonors === []) {
            $rowNum++;
            $writer->addRow(new Row([
                Cell::fromValue('—', self::cellStyle(false, CellAlignment::CENTER)),
                Cell::fromValue('Listelenecek bağışçı bulunamadı', self::cellStyle(false)),
                Cell::fromValue('', self::cellStyle(false)),
                Cell::fromValue('', self::cellStyle(false)),
                Cell::fromValue('', self::cellStyle(false)),
            ]));
        }
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array{donor_count: int, donation_count: int, total: float}  $stats
     */
    private static function writeDetailSheet(Writer $writer, Options $options, Collection $rows, array $stats): void
    {
        $lastColumn = count(self::DETAIL_HEADERS) - 1;
        $rowNum = 0;

        $metaLine = sprintf(
            'Bağışçı: %d          Bağış: %d          Toplam: %s TRY',
            $stats['donor_count'],
            $stats['donation_count'],
            self::money($stats['total']),
        );

        foreach (self::titleBands(Setting::current(), 'BAĞIŞÇI LİSTESİ', $metaLine) as [$value, $style]) {
            $writer->addRow(self::bandRow($value, $lastColumn, $style));
            $rowNum++;
            $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_DETAIL);
        }

        $headerRow = $rowNum + 1;
        $headerCells = [];
        foreach (self::DETAIL_HEADERS as $header) {
            $headerCells[] = Cell::fromValue($header, self::headerStyle());
        }
        $writer->addRow(new Row($headerCells));
        $rowNum++;

        self::freezeAfterHeader($writer->getCurrentSheet(), $headerRow);

        $index = 0;
        foreach ($rows as $donor) {
            $index++;
            $rowNum++;
            $zebra = ($index % 2) === 0;
            $text = self::cellStyle($zebra);
            $wrap = self::wrapCellStyle($zebra);
            $center = self::cellStyle($zebra, CellAlignment::CENTER);

            $address = (string) $donor['address'];
            $email = (string) $donor['email'];
            $height = max(
                self::rowHeightForText($address, 34),
                self::rowHeightForText($email, 28),
                20,
            );

            $writer->addRow((new Row([
                Cell::fromValue($index, $center),
                Cell::fromValue($donor['first_name'], $text),
                Cell::fromValue($donor['last_name'], $text),
                Cell::fromValue($donor['phone'], $text),
                Cell::fromValue($email, $wrap),
                Cell::fromValue($donor['city'] === 'Belirtilmemiş' ? '' : $donor['city'], $text),
                Cell::fromValue($donor['country'] === 'Belirtilmemiş' ? '' : $donor['country'], $text),
                Cell::fromValue($address, $wrap),
                Cell::fromValue($donor['count'], $center),
                Cell::fromValue($donor['total'], self::amountStyle($zebra)),
                Cell::fromValue($donor['first_donation'], $center),
                Cell::fromValue($donor['last_donation'], $center),
                Cell::fromValue($donor['created_at'], $center),
            ]))->setHeight($height));
        }

        $rowNum++;
        self::writeDetailTotals($writer, $stats['donation_count'], $stats['total'], $lastColumn);
        $options->mergeCells(0, $rowNum, self::COUNT_COLUMN_INDEX - 1, $rowNum, self::SHEET_DETAIL);

        self::footerNote($writer, $rowNum, $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_DETAIL);
    }

    private static function writeDetailTotals(Writer $writer, int $donationCount, float $totalAmount, int $lastColumn): void
    {
        $cells = [Cell::fromValue('GENEL TOPLAM', self::totalsLabelStyle())];
        for ($i = 1; $i < self::COUNT_COLUMN_INDEX; $i++) {
            $cells[] = Cell::fromValue('', self::totalsLabelStyle());
        }
        $cells[] = Cell::fromValue($donationCount, self::totalsCellStyle());
        $cells[] = Cell::fromValue($totalAmount, self::totalsAmountStyle());
        for ($i = self::AMOUNT_COLUMN_INDEX + 1; $i <= $lastColumn; $i++) {
            $cells[] = Cell::fromValue('', self::totalsCellStyle());
        }

        $writer->addRow(new Row($cells));
    }

    private static function formatDate(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('d.m.Y');
        }

        if (is_string($value) && $value !== '') {
            return Carbon::parse($value)->format('d.m.Y');
        }

        return '';
    }
}
