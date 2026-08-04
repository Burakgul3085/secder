<?php

namespace App\Support\Crm;

use App\Models\Donation;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Bağış listesi için kurumsal, çok sayfalı Excel raporu.
 *
 * Sayfa 1 - Özet: KPI kartları + tür / ödeme / para birimi kırılımları
 * Sayfa 2 - Bağış Listesi: filtrelenmiş tüm kayıtların detay dökümü
 */
class DonationSpreadsheetExporter extends CorporateSpreadsheet
{
    private const SHEET_SUMMARY = 0;

    private const SHEET_DETAIL = 1;

    /** @var array<int, string> */
    private const DETAIL_HEADERS = [
        'Sıra',
        'Bağış No',
        'Makbuz No',
        'Ad',
        'Soyad',
        'Telefon',
        'Şehir',
        'Bağış Türü',
        'Ödeme Türü',
        'Tutar',
        'Para Birimi',
        'Bağış Tarihi',
        'Proje / Faaliyet',
        'Açıklama',
        'Not',
        'Kaydeden',
        'Oluşturulma',
    ];

    /** @var array<int, float> */
    private const DETAIL_WIDTHS = [6, 16, 16, 15, 15, 14, 12, 16, 16, 12, 10, 15, 22, 36, 18, 20, 16];

    private const DETAIL_AMOUNT_COL = 9;

    /** @var array<int, string> */
    private const METRIC_HEADERS = ['Sıra', 'Kırılım', 'Bağış Adedi', 'Toplam Tutar', 'Ort. Bağış'];

    /** @var array<int, float> */
    private const SUMMARY_WIDTHS = [28, 36, 14, 16, 14];

    public static function download(Builder $query, ?string $filename = null): StreamedResponse
    {
        $org = Setting::current()->site_title ?: 'SECDER';
        $slug = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $org) ?: 'SECDER';
        $filename ??= $slug . '_Bagis_Raporu_' . now()->format('Y-m-d_His') . '.xlsx';

        return response()->streamDownload(function () use ($query): void {
            self::write($query);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private static function write(Builder $query): void
    {
        $donations = (clone $query)
            ->with(['donor', 'donationType', 'paymentMethod', 'project', 'creator'])
            ->reorder()
            ->orderByDesc('donated_at')
            ->orderByDesc('id')
            ->get();

        $stats = self::buildStats($donations);

        $options = new Options();
        $writer = new Writer($options);
        $writer->openToFile('php://output');

        $writer->getCurrentSheet()->setName('Özet');
        self::applyWidths($writer, self::SUMMARY_WIDTHS);
        self::writeSummarySheet($writer, $options, $stats);

        $writer->addNewSheetAndMakeItCurrent();
        $writer->getCurrentSheet()->setName('Bağış Listesi');
        self::applyWidths($writer, self::DETAIL_WIDTHS);
        self::writeDetailSheet($writer, $options, $donations, $stats);

        $writer->close();
    }

    /**
     * @param  Collection<int, Donation>  $donations
     * @return array{
     *     count: int,
     *     total: float,
     *     average: float,
     *     currencies: array<string, float>,
     *     byType: array<int, array{label: string, count: int, total: float}>,
     *     byPayment: array<int, array{label: string, count: int, total: float}>,
     *     byCurrency: array<int, array{label: string, count: int, total: float}>,
     *     byMonth: array<int, array{label: string, count: int, total: float}>
     * }
     */
    private static function buildStats(Collection $donations): array
    {
        $count = $donations->count();
        $total = (float) $donations->sum('amount');

        $currencies = [];
        foreach ($donations->groupBy(fn (Donation $d) => $d->currency ?: 'TRY') as $currency => $group) {
            $currencies[(string) $currency] = (float) $group->sum('amount');
        }

        return [
            'count' => $count,
            'total' => $total,
            'average' => $count > 0 ? $total / $count : 0.0,
            'currencies' => $currencies,
            'byType' => self::groupMetrics($donations, fn (Donation $d) => $d->donationType?->name ?: 'Belirtilmemiş'),
            'byPayment' => self::groupMetrics($donations, fn (Donation $d) => $d->paymentMethod?->name ?: 'Belirtilmemiş'),
            'byCurrency' => self::groupMetrics($donations, fn (Donation $d) => $d->currency ?: 'TRY'),
            'byMonth' => self::groupMetrics(
                $donations,
                fn (Donation $d) => $d->donated_at?->translatedFormat('F Y') ?: 'Tarihsiz',
            ),
        ];
    }

    /**
     * @param  Collection<int, Donation>  $donations
     * @param  callable(Donation): string  $key
     * @return array<int, array{label: string, count: int, total: float}>
     */
    private static function groupMetrics(Collection $donations, callable $key): array
    {
        return $donations
            ->groupBy($key)
            ->map(fn (Collection $group, $label): array => [
                'label' => (string) $label,
                'count' => $group->count(),
                'total' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    /**
     * @param  array{
     *     count: int,
     *     total: float,
     *     average: float,
     *     currencies: array<string, float>,
     *     byType: array<int, array{label: string, count: int, total: float}>,
     *     byPayment: array<int, array{label: string, count: int, total: float}>,
     *     byCurrency: array<int, array{label: string, count: int, total: float}>,
     *     byMonth: array<int, array{label: string, count: int, total: float}>
     * }  $stats
     */
    private static function writeSummarySheet(Writer $writer, Options $options, array $stats): void
    {
        $lastColumn = count(self::METRIC_HEADERS) - 1;
        $rowNum = 0;

        $currencyParts = [];
        foreach ($stats['currencies'] as $currency => $sum) {
            $currencyParts[] = self::money((float) $sum) . ' ' . $currency;
        }
        $currencySummary = $currencyParts === [] ? '—' : implode('  |  ', $currencyParts);

        $metaLine = sprintf(
            'Rapor Tarihi: %s          Kayıt Sayısı: %d          Toplam: %s TRY',
            now()->format('d.m.Y H:i'),
            $stats['count'],
            self::money($stats['total']),
        );

        foreach (self::titleBands(Setting::current(), 'BAĞIŞ RAPORU — ÖZET', $metaLine) as [$value, $style]) {
            $writer->addRow(self::bandRow($value, $lastColumn, $style));
            $rowNum++;
            $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
        }

        self::spacer($writer, $rowNum);
        self::sectionTitle($writer, $rowNum, 'Genel Göstergeler', $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);

        self::writeKpiRow($writer, $rowNum, [
            ['Bağış Adedi', (string) $stats['count']],
            ['Toplam Tutar (TRY)', self::money($stats['total'])],
            ['Ortalama Bağış', self::money($stats['average'])],
            ['Para Birimi Dağılımı', $currencySummary],
        ], $lastColumn, $options, self::SHEET_SUMMARY);

        self::spacer($writer, $rowNum);
        self::sectionTitle($writer, $rowNum, 'Bağış Türü Kırılımı', $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
        self::writeMetricTable($writer, $rowNum, self::METRIC_HEADERS, $stats['byType'], $stats['count'], $stats['total']);
        $options->mergeCells(0, $rowNum, 1, $rowNum, self::SHEET_SUMMARY);

        self::spacer($writer, $rowNum);
        self::sectionTitle($writer, $rowNum, 'Ödeme Türü Kırılımı', $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
        self::writeMetricTable($writer, $rowNum, self::METRIC_HEADERS, $stats['byPayment'], $stats['count'], $stats['total']);
        $options->mergeCells(0, $rowNum, 1, $rowNum, self::SHEET_SUMMARY);

        self::spacer($writer, $rowNum);
        self::sectionTitle($writer, $rowNum, 'Para Birimi Kırılımı', $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
        self::writeMetricTable($writer, $rowNum, self::METRIC_HEADERS, $stats['byCurrency'], $stats['count'], $stats['total']);
        $options->mergeCells(0, $rowNum, 1, $rowNum, self::SHEET_SUMMARY);

        if (count($stats['byMonth']) > 1) {
            self::spacer($writer, $rowNum);
            self::sectionTitle($writer, $rowNum, 'Aylık Dağılım', $lastColumn);
            $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
            self::writeMetricTable($writer, $rowNum, self::METRIC_HEADERS, $stats['byMonth'], $stats['count'], $stats['total']);
            $options->mergeCells(0, $rowNum, 1, $rowNum, self::SHEET_SUMMARY);
        }

        self::footerNote($writer, $rowNum, $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
    }

    /**
     * @param  Collection<int, Donation>  $donations
     * @param  array{count: int, total: float, currencies: array<string, float>}  $stats
     */
    private static function writeDetailSheet(Writer $writer, Options $options, Collection $donations, array $stats): void
    {
        $lastColumn = count(self::DETAIL_HEADERS) - 1;
        $rowNum = 0;

        $currencyParts = [];
        foreach ($stats['currencies'] as $currency => $sum) {
            $currencyParts[] = self::money((float) $sum) . ' ' . $currency;
        }

        $metaLine = sprintf(
            'Kayıt: %d          Toplam: %s          %s',
            $stats['count'],
            self::money($stats['total']) . ' TRY',
            $currencyParts === [] ? '' : implode('  |  ', $currencyParts),
        );

        foreach (self::titleBands(Setting::current(), 'BAĞIŞ LİSTESİ', $metaLine) as [$value, $style]) {
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
        foreach ($donations as $donation) {
            $index++;
            $rowNum++;
            $zebra = ($index % 2) === 0;
            $text = self::cellStyle($zebra);
            $wrap = self::wrapCellStyle($zebra);
            $center = self::cellStyle($zebra, CellAlignment::CENTER);

            $description = (string) ($donation->description ?? '');
            $notes = (string) ($donation->notes ?? '');
            $creator = (string) ($donation->creator?->name ?? '');
            $project = (string) ($donation->project?->title ?? '');

            $height = max(
                self::rowHeightForText($description, 36),
                self::rowHeightForText($notes, 18),
                self::rowHeightForText($creator, 20),
                self::rowHeightForText($project, 22),
                20,
            );

            $writer->addRow((new Row([
                Cell::fromValue($index, $center),
                Cell::fromValue($donation->donation_number ?? '', $text),
                Cell::fromValue($donation->receipt_number ?? '', $text),
                Cell::fromValue($donation->donor?->first_name ?? '', $text),
                Cell::fromValue($donation->donor?->last_name ?? '', $text),
                Cell::fromValue($donation->donor?->phone ?? '', $text),
                Cell::fromValue($donation->donor?->city ?? '', $text),
                Cell::fromValue($donation->donationType?->name ?? '', $wrap),
                Cell::fromValue($donation->paymentMethod?->name ?? '', $wrap),
                Cell::fromValue((float) $donation->amount, self::amountStyle($zebra)),
                Cell::fromValue($donation->currency ?? 'TRY', $center),
                Cell::fromValue($donation->donated_at?->format('d.m.Y H:i') ?? '', $center),
                Cell::fromValue($project, $wrap),
                Cell::fromValue($description, $wrap),
                Cell::fromValue($notes, $wrap),
                Cell::fromValue($creator, $wrap),
                Cell::fromValue($donation->created_at?->format('d.m.Y H:i') ?? '', $center),
            ]))->setHeight($height));
        }

        $rowNum++;
        self::writeDetailTotals($writer, $stats['total'], $stats['currencies'], $lastColumn);
        $options->mergeCells(0, $rowNum, self::DETAIL_AMOUNT_COL - 1, $rowNum, self::SHEET_DETAIL);

        self::footerNote($writer, $rowNum, $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_DETAIL);
    }

    /**
     * @param  array<string, float>  $currencies
     */
    private static function writeDetailTotals(Writer $writer, float $totalAmount, array $currencies, int $lastColumn): void
    {
        $currencyParts = [];
        foreach ($currencies as $currency => $sum) {
            $currencyParts[] = self::money((float) $sum) . ' ' . $currency;
        }
        $currencySummary = $currencyParts === [] ? 'TRY' : implode('  |  ', $currencyParts);

        $cells = [Cell::fromValue('GENEL TOPLAM', self::totalsLabelStyle())];
        for ($i = 1; $i < self::DETAIL_AMOUNT_COL; $i++) {
            $cells[] = Cell::fromValue('', self::totalsLabelStyle());
        }
        $cells[] = Cell::fromValue($totalAmount, self::totalsAmountStyle());
        $cells[] = Cell::fromValue($currencySummary, self::totalsCellStyle());
        for ($i = self::DETAIL_AMOUNT_COL + 2; $i <= $lastColumn; $i++) {
            $cells[] = Cell::fromValue('', self::totalsCellStyle());
        }

        $writer->addRow(new Row($cells));
    }
}
