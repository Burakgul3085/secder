<?php

namespace App\Support\Crm;

use App\Models\Donation;
use App\Models\Setting;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Faaliyet raporu için kapsamlı, çok sayfalı kurumsal Excel üretir.
 *
 * Sayfa 1 - Özet: KPI + proje / bağışçı / tür / ödeme kırılımları
 * Sayfa 2 - Bağış Detayları: filtrelenmiş tüm bağışların satır satır dökümü
 * Sayfa 3 - Proje Bazlı Kırılım: her faaliyet altında kim ne kadar bağış yapmış
 * Sayfa 4 - Bağışçı Bazlı Kırılım: her bağışçının yaptığı tüm bağışların detayı
 */
class ActivitySpreadsheetExporter extends CorporateSpreadsheet
{
    private const SHEET_SUMMARY = 0;

    private const SHEET_DETAIL = 1;

    private const SHEET_BY_PROJECT = 2;

    private const SHEET_BY_DONOR = 3;

    /** @var array<int, string> */
    private const METRIC_HEADERS = ['Sıra', 'Kırılım', 'Bağış Adedi', 'Toplam Tutar', 'Ort. Bağış'];

    /** @var array<int, float> */
    private const SUMMARY_WIDTHS = [28.0, 36.0, 14.0, 18.0, 16.0];

    /** @var array<int, string> */
    private const DETAIL_HEADERS = [
        'Sıra', 'Bağış No', 'Makbuz No', 'Bağışçı', 'Telefon',
        'Proje / Faaliyet', 'Bağış Türü', 'Ödeme Türü', 'Tutar', 'Para Birimi',
        'Bağış Tarihi', 'Açıklama',
    ];

    /** @var array<int, float> */
    private const DETAIL_WIDTHS = [6.0, 16.0, 16.0, 22.0, 14.0, 24.0, 16.0, 15.0, 12.0, 10.0, 15.0, 38.0];

    private const DETAIL_AMOUNT_COL = 8;

    /** @var array<int, string> */
    private const BY_PROJECT_HEADERS = [
        'Sıra', 'Bağışçı', 'Telefon', 'Bağış Türü', 'Ödeme Türü',
        'Tutar', 'Bağış Tarihi', 'Makbuz No', 'Açıklama',
    ];

    /** @var array<int, float> */
    private const BY_PROJECT_WIDTHS = [6.0, 24.0, 14.0, 16.0, 15.0, 12.0, 15.0, 16.0, 36.0];

    private const BY_PROJECT_AMOUNT_COL = 5;

    /** @var array<int, string> */
    private const BY_DONOR_HEADERS = [
        'Sıra', 'Bağış No', 'Makbuz No', 'Proje / Faaliyet', 'Bağış Türü',
        'Ödeme Türü', 'Tutar', 'Bağış Tarihi', 'Açıklama',
    ];

    /** @var array<int, float> */
    private const BY_DONOR_WIDTHS = [6.0, 16.0, 16.0, 26.0, 16.0, 15.0, 12.0, 15.0, 36.0];

    private const BY_DONOR_AMOUNT_COL = 6;

    public static function download(ActivityReportResult $report, ?string $filename = null): StreamedResponse
    {
        $filename ??= self::buildFilename($report);

        return response()->streamDownload(function () use ($report): void {
            self::write($report);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private static function buildFilename(ActivityReportResult $report): string
    {
        $org = Setting::current()->site_title ?: 'SECDER';
        $orgSlug = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $org) ?: 'SECDER';
        $projectSlug = $report->meta['project_slug'] ?? 'tum-faaliyetler';
        $projectSlug = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $projectSlug) ?: 'tum-faaliyetler';

        return $orgSlug . '_Faaliyet_Raporu_' . $projectSlug . '_' . now()->format('Y-m-d_His') . '.xlsx';
    }

    private static function write(ActivityReportResult $report): void
    {
        $options = new Options();
        $writer = new Writer($options);
        $writer->openToFile('php://output');

        $donations = self::resolveDonations($report);
        $extraStats = self::buildExtraStats($donations);

        $writer->getCurrentSheet()->setName('Özet');
        self::applyWidths($writer, self::SUMMARY_WIDTHS);
        self::writeSummarySheet($writer, $report, $options, $donations, $extraStats);

        $writer->addNewSheetAndMakeItCurrent();
        $writer->getCurrentSheet()->setName('Bağış Detayları');
        self::applyWidths($writer, self::DETAIL_WIDTHS);
        self::writeDetailSheet($writer, $report, $options, $donations, $extraStats);

        $writer->addNewSheetAndMakeItCurrent();
        $writer->getCurrentSheet()->setName('Proje Bazlı Kırılım');
        self::applyWidths($writer, self::BY_PROJECT_WIDTHS);
        self::writeByProjectSheet($writer, $report, $options, $donations);

        $writer->addNewSheetAndMakeItCurrent();
        $writer->getCurrentSheet()->setName('Bağışçı Bazlı Kırılım');
        self::applyWidths($writer, self::BY_DONOR_WIDTHS);
        self::writeByDonorSheet($writer, $report, $options, $donations);

        $writer->close();
    }

    /**
     * @return Collection<int, Donation>
     */
    private static function resolveDonations(ActivityReportResult $report): Collection
    {
        $filters = ActivityReportFilterResolver::get();

        if (filled($report->meta['project_id'] ?? null)) {
            $filters['project_id'] = $report->meta['project_id'];
        }

        return app(ActivityReportBuilder::class)->detailDonations($filters);
    }

    /**
     * @param  Collection<int, Donation>  $donations
     * @return array{
     *     average: float,
     *     unique_donors: int,
     *     unique_projects: int,
     *     currencies: array<string, float>,
     *     byPayment: array<int, array{label: string, count: int, total: float}>
     * }
     */
    private static function buildExtraStats(Collection $donations): array
    {
        $count = $donations->count();
        $total = (float) $donations->sum('amount');

        $currencies = [];
        foreach ($donations->groupBy(fn (Donation $d) => $d->currency ?: 'TRY') as $currency => $group) {
            $currencies[(string) $currency] = (float) $group->sum('amount');
        }

        $byPayment = $donations
            ->groupBy(fn (Donation $d) => $d->paymentMethod?->name ?: 'Belirtilmemiş')
            ->map(fn (Collection $group, $label): array => [
                'label' => (string) $label,
                'count' => $group->count(),
                'total' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();

        return [
            'average' => $count > 0 ? $total / $count : 0.0,
            'unique_donors' => $donations->pluck('donor_id')->filter()->unique()->count(),
            'unique_projects' => $donations->pluck('project_id')->filter()->unique()->count(),
            'currencies' => $currencies,
            'byPayment' => $byPayment,
        ];
    }

    // ---------------------------------------------------------------------
    // Sayfa 1 — Özet
    // ---------------------------------------------------------------------

    /**
     * @param  Collection<int, Donation>  $donations
     * @param  array{
     *     average: float,
     *     unique_donors: int,
     *     unique_projects: int,
     *     currencies: array<string, float>,
     *     byPayment: array<int, array{label: string, count: int, total: float}>
     * }  $extraStats
     */
    private static function writeSummarySheet(
        Writer $writer,
        ActivityReportResult $report,
        Options $options,
        Collection $donations,
        array $extraStats,
    ): void {
        $lastColumn = count(self::METRIC_HEADERS) - 1;
        $rowNum = 0;

        $currencyParts = [];
        foreach ($extraStats['currencies'] as $currency => $sum) {
            $currencyParts[] = self::money((float) $sum) . ' ' . $currency;
        }
        $currencySummary = $currencyParts === [] ? '—' : implode('  |  ', $currencyParts);

        $metaLine = sprintf(
            'Dönem: %s  •  Faaliyet: %s  •  Kayıt: %d',
            $report->meta['period_label'],
            $report->meta['project_label'],
            $report->summary['donation_count'],
        );

        foreach (self::titleBands(Setting::current(), 'FAALİYET RAPORU — ÖZET', $metaLine) as [$value, $style]) {
            $writer->addRow(self::bandRow($value, $lastColumn, $style));
            $rowNum++;
            $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
        }

        self::spacer($writer, $rowNum);
        self::sectionTitle($writer, $rowNum, 'Genel Göstergeler', $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);

        self::writeKpiRow($writer, $rowNum, [
            ['Dönem', (string) $report->meta['period_label']],
            ['Faaliyet Filtresi', (string) $report->meta['project_label']],
            ['Bağış Adedi', (string) $report->summary['donation_count']],
            ['Toplam Tutar (TRY)', self::money((float) $report->summary['total_amount'])],
            ['Ortalama Bağış', self::money($extraStats['average'])],
            ['Benzersiz Bağışçı', (string) $extraStats['unique_donors']],
            ['Kapsanan Faaliyet', (string) max($extraStats['unique_projects'], count($report->projectRows))],
            ['Para Birimi Dağılımı', $currencySummary],
        ], $lastColumn, $options, self::SHEET_SUMMARY);

        self::spacer($writer, $rowNum);
        self::sectionTitle($writer, $rowNum, 'Proje / Faaliyet Özeti', $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
        self::metricTable(
            $writer,
            $rowNum,
            $options,
            self::METRIC_HEADERS,
            self::normalizeMetricRows($report->projectRows),
            $report->summary,
            self::SHEET_SUMMARY,
        );

        self::spacer($writer, $rowNum);
        self::sectionTitle($writer, $rowNum, 'Bağışçı Bazlı Özet', $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
        self::metricTable(
            $writer,
            $rowNum,
            $options,
            self::METRIC_HEADERS,
            self::normalizeMetricRows($report->donorRows),
            $report->summary,
            self::SHEET_SUMMARY,
        );

        self::spacer($writer, $rowNum);
        self::sectionTitle($writer, $rowNum, 'Bağış Türü Özeti', $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
        self::metricTable(
            $writer,
            $rowNum,
            $options,
            self::METRIC_HEADERS,
            self::normalizeMetricRows($report->typeRows),
            $report->summary,
            self::SHEET_SUMMARY,
        );

        if ($extraStats['byPayment'] !== []) {
            self::spacer($writer, $rowNum);
            self::sectionTitle($writer, $rowNum, 'Ödeme Türü Özeti', $lastColumn);
            $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
            self::metricTable(
                $writer,
                $rowNum,
                $options,
                self::METRIC_HEADERS,
                $extraStats['byPayment'],
                [
                    'donation_count' => $report->summary['donation_count'],
                    'total_amount' => $report->summary['total_amount'],
                ],
                self::SHEET_SUMMARY,
            );
        }

        self::footerNote(
            $writer,
            $rowNum,
            $lastColumn,
            'Bu faaliyet raporu SECDER bağış yönetim sistemi tarafından üretilmiştir. Filtreler: '
            . $report->meta['period_label'] . ' / ' . $report->meta['project_label']
            . '. Gizli / kurum içi kullanıma mahsustur.',
        );
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_SUMMARY);
    }

    /**
     * @param  array<int, array{label: string, donation_count: int, total_amount: float, average_amount?: float}>  $rows
     * @return array<int, array{label: string, count: int, total: float, average?: float}>
     */
    private static function normalizeMetricRows(array $rows): array
    {
        return array_values(array_map(static function (array $row): array {
            return [
                'label' => (string) $row['label'],
                'count' => (int) $row['donation_count'],
                'total' => (float) $row['total_amount'],
                'average' => (float) ($row['average_amount'] ?? (
                    ($row['donation_count'] ?? 0) > 0
                        ? $row['total_amount'] / $row['donation_count']
                        : 0
                )),
            ];
        }, $rows));
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array{label: string, count: int, total: float, average?: float}>  $rows
     * @param  array{donation_count: int, total_amount: float}  $summary
     */
    private static function metricTable(
        Writer $writer,
        int &$rowNum,
        Options $options,
        array $headers,
        array $rows,
        array $summary,
        int $sheetIndex,
    ): void {
        $headerCells = [];
        foreach ($headers as $header) {
            $headerCells[] = Cell::fromValue($header, self::headerStyle());
        }
        $writer->addRow((new Row($headerCells))->setHeight(24));
        $rowNum++;

        $index = 0;
        foreach ($rows as $row) {
            $index++;
            $rowNum++;
            $zebra = ($index % 2) === 0;
            $average = $row['average'] ?? ($row['count'] > 0 ? $row['total'] / $row['count'] : 0.0);
            $label = (string) $row['label'];
            $height = self::rowHeightForText($label, 40);

            $writer->addRow((new Row([
                Cell::fromValue($index, self::cellStyle($zebra, CellAlignment::CENTER)),
                Cell::fromValue($label, self::wrapCellStyle($zebra)),
                Cell::fromValue($row['count'], self::cellStyle($zebra, CellAlignment::CENTER)),
                Cell::fromValue($row['total'], self::amountStyle($zebra)),
                Cell::fromValue($average, self::amountStyle($zebra)),
            ]))->setHeight($height));
        }

        if ($rows === []) {
            $rowNum++;
            $writer->addRow(new Row([
                Cell::fromValue('—', self::cellStyle(false, CellAlignment::CENTER)),
                Cell::fromValue('Bu kırılımda kayıt bulunamadı', self::wrapCellStyle(false)),
                Cell::fromValue('', self::cellStyle(false)),
                Cell::fromValue('', self::cellStyle(false)),
                Cell::fromValue('', self::cellStyle(false)),
            ]));
        }

        $rowNum++;
        $writer->addRow((new Row([
            Cell::fromValue('GENEL TOPLAM', self::totalsLabelStyle()),
            Cell::fromValue('', self::totalsLabelStyle()),
            Cell::fromValue($summary['donation_count'], self::totalsCellStyle()),
            Cell::fromValue($summary['total_amount'], self::totalsAmountStyle()),
            Cell::fromValue(
                $summary['donation_count'] > 0
                    ? $summary['total_amount'] / $summary['donation_count']
                    : 0.0,
                self::totalsAmountStyle(),
            ),
        ]))->setHeight(22));
        $options->mergeCells(0, $rowNum, 1, $rowNum, $sheetIndex);
    }

    // ---------------------------------------------------------------------
    // Sayfa 2 — Bağış Detayları
    // ---------------------------------------------------------------------

    /**
     * @param  Collection<int, Donation>  $donations
     * @param  array{currencies: array<string, float>}  $extraStats
     */
    private static function writeDetailSheet(
        Writer $writer,
        ActivityReportResult $report,
        Options $options,
        Collection $donations,
        array $extraStats,
    ): void {
        $lastColumn = count(self::DETAIL_HEADERS) - 1;
        $amountColumn = self::DETAIL_AMOUNT_COL;
        $rowNum = 0;

        $metaLine = sprintf(
            'Faaliyet: %s  •  Dönem: %s  •  Toplam kayıt: %d  •  Toplam: %s TRY',
            $report->meta['project_label'],
            $report->meta['period_label'],
            $donations->count(),
            self::money((float) $report->summary['total_amount']),
        );

        foreach (self::titleBands(Setting::current(), 'BAĞIŞ DETAY LİSTESİ', $metaLine) as [$value, $style]) {
            $writer->addRow(self::bandRow($value, $lastColumn, $style));
            $rowNum++;
            $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_DETAIL);
        }

        $headerRow = $rowNum + 1;
        $headerCells = [];
        foreach (self::DETAIL_HEADERS as $header) {
            $headerCells[] = Cell::fromValue($header, self::headerStyle());
        }
        $writer->addRow((new Row($headerCells))->setHeight(24));
        $rowNum++;
        self::freezeAfterHeader($writer->getCurrentSheet(), $headerRow);

        $index = 0;
        $total = 0.0;

        foreach ($donations as $donation) {
            $index++;
            $rowNum++;
            $zebra = ($index % 2) === 0;
            $text = self::cellStyle($zebra);
            $wrap = self::wrapCellStyle($zebra);
            $center = self::cellStyle($zebra, CellAlignment::CENTER);
            $total += (float) $donation->amount;

            $donor = self::donorName($donation);
            $project = $donation->project?->title ?? 'Proje atanmamış';
            $description = (string) ($donation->description ?? '');
            $height = max(
                self::rowHeightForText($description, 38),
                self::rowHeightForText($donor, 22),
                self::rowHeightForText($project, 24),
                20,
            );

            $writer->addRow((new Row([
                Cell::fromValue($index, $center),
                Cell::fromValue($donation->donation_number ?? '', $text),
                Cell::fromValue($donation->receipt_number ?? '', $text),
                Cell::fromValue($donor, $wrap),
                Cell::fromValue($donation->donor?->phone ?? '', $text),
                Cell::fromValue($project, $wrap),
                Cell::fromValue($donation->donationType?->name ?? '', $wrap),
                Cell::fromValue($donation->paymentMethod?->name ?? '', $wrap),
                Cell::fromValue((float) $donation->amount, self::amountStyle($zebra)),
                Cell::fromValue($donation->currency ?? 'TRY', $center),
                Cell::fromValue($donation->donated_at?->format('d.m.Y H:i') ?? '', $center),
                Cell::fromValue($description, $wrap),
            ]))->setHeight($height));
        }

        $currencyParts = [];
        foreach ($extraStats['currencies'] as $currency => $sum) {
            $currencyParts[] = self::money((float) $sum) . ' ' . $currency;
        }

        $rowNum++;
        self::totalsRow(
            $writer,
            $lastColumn,
            $amountColumn,
            'GENEL TOPLAM',
            $total,
            $currencyParts === [] ? 'TRY' : implode('  |  ', $currencyParts),
        );
        $options->mergeCells(0, $rowNum, $amountColumn - 1, $rowNum, self::SHEET_DETAIL);

        self::footerNote($writer, $rowNum, $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, self::SHEET_DETAIL);
    }

    // ---------------------------------------------------------------------
    // Sayfa 3 / 4 — Kırılım sayfaları
    // ---------------------------------------------------------------------

    /**
     * @param  Collection<int, Donation>  $donations
     */
    private static function writeByProjectSheet(Writer $writer, ActivityReportResult $report, Options $options, Collection $donations): void
    {
        self::writeGroupedSheet(
            $writer,
            $options,
            self::SHEET_BY_PROJECT,
            'PROJE / FAALİYET BAZLI KIRILIM',
            sprintf(
                'Her faaliyet altında kimlerin ne kadar bağış yaptığı listelenir.  •  Dönem: %s',
                $report->meta['period_label'],
            ),
            $donations,
            self::BY_PROJECT_HEADERS,
            self::BY_PROJECT_AMOUNT_COL,
            fn (Donation $donation): string => $donation->project?->title ?? 'Proje atanmamış',
            fn (Donation $donation, bool $zebra): array => [
                Cell::fromValue(self::donorName($donation), self::wrapCellStyle($zebra)),
                Cell::fromValue($donation->donor?->phone ?? '', self::cellStyle($zebra)),
                Cell::fromValue($donation->donationType?->name ?? '', self::wrapCellStyle($zebra)),
                Cell::fromValue($donation->paymentMethod?->name ?? '', self::wrapCellStyle($zebra)),
                Cell::fromValue((float) $donation->amount, self::amountStyle($zebra)),
                Cell::fromValue($donation->donated_at?->format('d.m.Y H:i') ?? '', self::cellStyle($zebra, CellAlignment::CENTER)),
                Cell::fromValue($donation->receipt_number ?? '', self::cellStyle($zebra)),
                Cell::fromValue((string) ($donation->description ?? ''), self::wrapCellStyle($zebra)),
            ],
            36.0,
        );
    }

    /**
     * @param  Collection<int, Donation>  $donations
     */
    private static function writeByDonorSheet(Writer $writer, ActivityReportResult $report, Options $options, Collection $donations): void
    {
        self::writeGroupedSheet(
            $writer,
            $options,
            self::SHEET_BY_DONOR,
            'BAĞIŞÇI BAZLI KIRILIM',
            sprintf(
                'Her bağışçının dönem içindeki tüm bağış detayları listelenir.  •  Dönem: %s',
                $report->meta['period_label'],
            ),
            $donations,
            self::BY_DONOR_HEADERS,
            self::BY_DONOR_AMOUNT_COL,
            fn (Donation $donation): string => self::donorName($donation)
                . ($donation->donor?->phone ? ' (' . $donation->donor->phone . ')' : ''),
            fn (Donation $donation, bool $zebra): array => [
                Cell::fromValue($donation->donation_number ?? '', self::cellStyle($zebra)),
                Cell::fromValue($donation->receipt_number ?? '', self::cellStyle($zebra)),
                Cell::fromValue($donation->project?->title ?? 'Proje atanmamış', self::wrapCellStyle($zebra)),
                Cell::fromValue($donation->donationType?->name ?? '', self::wrapCellStyle($zebra)),
                Cell::fromValue($donation->paymentMethod?->name ?? '', self::wrapCellStyle($zebra)),
                Cell::fromValue((float) $donation->amount, self::amountStyle($zebra)),
                Cell::fromValue($donation->donated_at?->format('d.m.Y H:i') ?? '', self::cellStyle($zebra, CellAlignment::CENTER)),
                Cell::fromValue((string) ($donation->description ?? ''), self::wrapCellStyle($zebra)),
            ],
            36.0,
        );
    }

    /**
     * @param  Collection<int, Donation>  $donations
     * @param  array<int, string>  $headers
     * @param  callable(Donation): string  $groupKey
     * @param  callable(Donation, bool): array<int, Cell>  $rowCells
     */
    private static function writeGroupedSheet(
        Writer $writer,
        Options $options,
        int $sheetIndex,
        string $title,
        string $metaLine,
        Collection $donations,
        array $headers,
        int $amountColumn,
        callable $groupKey,
        callable $rowCells,
        float $descriptionWidth = 34.0,
    ): void {
        $lastColumn = count($headers) - 1;
        $rowNum = 0;

        foreach (self::titleBands(Setting::current(), $title, $metaLine) as [$value, $style]) {
            $writer->addRow(self::bandRow($value, $lastColumn, $style));
            $rowNum++;
            $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, $sheetIndex);
        }

        if ($donations->isEmpty()) {
            self::spacer($writer, $rowNum);
            $writer->addRow(self::bandRow('Bu filtre için listelenecek bağış bulunamadı.', $lastColumn, self::metaStyle()));
            $rowNum++;
            $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, $sheetIndex);
            self::footerNote($writer, $rowNum, $lastColumn);
            $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, $sheetIndex);

            return;
        }

        $groups = $donations
            ->groupBy($groupKey)
            ->sortByDesc(fn (Collection $group): float => (float) $group->sum('amount'));

        $grandTotal = 0.0;
        $firstGroup = true;

        foreach ($groups as $groupLabel => $group) {
            /** @var Collection<int, Donation> $group */
            $groupTotal = (float) $group->sum('amount');
            $grandTotal += $groupTotal;

            self::spacer($writer, $rowNum);

            $groupHeader = sprintf(
                '%s  —  %d bağış • %s TRY',
                $groupLabel,
                $group->count(),
                self::money($groupTotal),
            );
            $writer->addRow(self::bandRow($groupHeader, $lastColumn, self::sectionStyle()));
            $rowNum++;
            $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, $sheetIndex);

            $headerCells = [];
            foreach ($headers as $header) {
                $headerCells[] = Cell::fromValue($header, self::headerStyle());
            }
            $writer->addRow((new Row($headerCells))->setHeight(22));
            $rowNum++;

            if ($firstGroup) {
                self::freezeAfterHeader($writer->getCurrentSheet(), $rowNum);
                $firstGroup = false;
            }

            $index = 0;
            foreach ($group->sortByDesc(fn (Donation $donation): float => (float) $donation->amount) as $donation) {
                $index++;
                $rowNum++;
                $zebra = ($index % 2) === 0;
                $description = (string) ($donation->description ?? '');
                $height = max(self::rowHeightForText($description, $descriptionWidth), 20);

                $cells = [Cell::fromValue($index, self::cellStyle($zebra, CellAlignment::CENTER))];
                foreach ($rowCells($donation, $zebra) as $cell) {
                    $cells[] = $cell;
                }

                $writer->addRow((new Row($cells))->setHeight($height));
            }

            $rowNum++;
            self::totalsRow($writer, $lastColumn, $amountColumn, 'ARA TOPLAM', $groupTotal);
            $options->mergeCells(0, $rowNum, $amountColumn - 1, $rowNum, $sheetIndex);
        }

        self::spacer($writer, $rowNum);
        $rowNum++;
        self::totalsRow($writer, $lastColumn, $amountColumn, 'GENEL TOPLAM', $grandTotal);
        $options->mergeCells(0, $rowNum, $amountColumn - 1, $rowNum, $sheetIndex);

        self::footerNote($writer, $rowNum, $lastColumn);
        $options->mergeCells(0, $rowNum, $lastColumn, $rowNum, $sheetIndex);
    }

    private static function totalsRow(
        Writer $writer,
        int $lastColumn,
        int $amountColumn,
        string $label,
        float $amount,
        ?string $extra = null,
    ): void {
        $cells = [Cell::fromValue($label, self::totalsLabelStyle())];

        for ($i = 1; $i < $amountColumn; $i++) {
            $cells[] = Cell::fromValue('', self::totalsLabelStyle());
        }

        $cells[] = Cell::fromValue($amount, self::totalsAmountStyle());

        $next = $amountColumn + 1;
        if ($extra !== null && $next <= $lastColumn) {
            $cells[] = Cell::fromValue($extra, self::totalsCellStyle());
            $next++;
        }

        for ($i = $next; $i <= $lastColumn; $i++) {
            $cells[] = Cell::fromValue('', self::totalsCellStyle());
        }

        $writer->addRow((new Row($cells))->setHeight(22));
    }

    private static function donorName(Donation $donation): string
    {
        $name = trim(($donation->donor?->first_name ?? '') . ' ' . ($donation->donor?->last_name ?? ''));

        return $name !== '' ? $name : 'Bilinmeyen bağışçı';
    }
}
