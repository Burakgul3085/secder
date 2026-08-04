<?php

namespace App\Support\Crm;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\Common\Entity\Sheet;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Kurumsal Excel raporları için ortak stil ve yapı yardımcıları.
 */
abstract class CorporateSpreadsheet
{
    protected const FONT = 'Calibri';

    protected const BRAND_DARK = '333C55';

    protected const BRAND = '4D5C83';

    protected const BRAND_ACCENT = '5F6F9B';

    protected const BAND_LIGHT = 'E8EDF6';

    protected const BAND_SOFT = 'F5F7FB';

    protected const ZEBRA = 'F1F5F9';

    protected const TOTALS_BG = 'D1DBEC';

    protected const BORDER = 'AEBFDA';

    protected const TEXT = '1E293B';

    protected const MUTED = '475569';

    /**
     * Üst başlık bloğunu (dernek adı, iletişim, rapor başlığı, meta) üretir.
     *
     * @return array<int, array{0: string, 1: Style}>
     */
    protected static function titleBands(Setting $setting, string $reportTitle, string $metaLine): array
    {
        $orgName = $setting->site_title ?: 'SECDER';

        $contactParts = array_filter([
            $setting->address,
            $setting->phone ? 'Tel: ' . $setting->phone : null,
            $setting->email,
        ]);

        $bands = [[mb_strtoupper($orgName), self::titleStyle()]];

        if ($contactParts !== []) {
            $bands[] = [implode('   •   ', $contactParts), self::contactStyle()];
        }

        $bands[] = [$reportTitle, self::subtitleStyle()];
        $bands[] = [$metaLine, self::metaStyle()];

        $preparedBy = self::preparedByLine();
        if ($preparedBy !== '') {
            $bands[] = [$preparedBy, self::metaStyle()];
        }

        return $bands;
    }

    protected static function preparedByLine(): string
    {
        $user = Auth::guard('crm')->user() ?? Auth::user();
        $name = trim((string) ($user?->name ?? ''));

        if ($name === '') {
            return 'Oluşturma: ' . now()->format('d.m.Y H:i') . '  •  SECDER Bağış Yönetim Paneli';
        }

        return 'Hazırlayan: ' . $name . '  •  Oluşturma: ' . now()->format('d.m.Y H:i') . '  •  SECDER Bağış Yönetim Paneli';
    }

    /**
     * Tek değerli, tüm sütunları kaplayan (birleştirilecek) bir bant satırı oluşturur.
     */
    protected static function bandRow(string $value, int $lastColumn, Style $style): Row
    {
        $cells = [Cell::fromValue($value, $style)];

        for ($i = 1; $i <= $lastColumn; $i++) {
            $cells[] = Cell::fromValue('', $style);
        }

        return new Row($cells);
    }

    protected static function spacer(Writer $writer, int &$rowNum): void
    {
        $writer->addRow(new Row([Cell::fromValue('', self::spacerStyle())]));
        $rowNum++;
    }

    protected static function sectionTitle(Writer $writer, int &$rowNum, string $title, int $lastColumn): void
    {
        $writer->addRow(self::bandRow($title, $lastColumn, self::sectionStyle()));
        $rowNum++;
    }

    protected static function footerNote(Writer $writer, int &$rowNum, int $lastColumn, string $note = ''): void
    {
        $text = $note !== ''
            ? $note
            : 'Bu belge SECDER bağış yönetim sistemi tarafından otomatik üretilmiştir. Gizli / kurum içi kullanıma mahsustur.';

        self::spacer($writer, $rowNum);
        $writer->addRow(self::bandRow($text, $lastColumn, self::footerStyle()));
        $rowNum++;
    }

    /**
     * @param  array<int, float>  $widths
     */
    protected static function applyWidths(Writer $writer, array $widths): void
    {
        $sheet = $writer->getCurrentSheet();

        foreach ($widths as $i => $width) {
            $sheet->setColumnWidth($width, $i + 1);
        }
    }

    protected static function freezeAfterHeader(Sheet $sheet, int $headerRow): void
    {
        $freezeAt = max(2, $headerRow + 1);
        $sheet->setSheetView(
            (new SheetView())
                ->setFreezeRow($freezeAt)
                ->setShowGridLines(false)
        );
    }

    protected static function money(float $amount): string
    {
        return number_format($amount, 2, ',', '.');
    }

    protected static function border(): Border
    {
        return new Border(
            new BorderPart(Border::TOP, self::BORDER, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::BOTTOM, self::BORDER, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT, self::BORDER, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT, self::BORDER, Border::WIDTH_THIN, Border::STYLE_SOLID),
        );
    }

    protected static function titleStyle(): Style
    {
        return (new Style())
            ->setFontName(self::FONT)
            ->setFontBold()
            ->setFontSize(18)
            ->setFontColor('FFFFFF')
            ->setBackgroundColor(self::BRAND_DARK)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }

    protected static function contactStyle(): Style
    {
        return (new Style())
            ->setFontName(self::FONT)
            ->setFontSize(10)
            ->setFontColor('FFFFFF')
            ->setBackgroundColor(self::BRAND)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }

    protected static function subtitleStyle(): Style
    {
        return (new Style())
            ->setFontName(self::FONT)
            ->setFontBold()
            ->setFontSize(13)
            ->setFontColor(self::BRAND_DARK)
            ->setBackgroundColor(self::BAND_LIGHT)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText();
    }

    protected static function metaStyle(): Style
    {
        return (new Style())
            ->setFontName(self::FONT)
            ->setFontSize(10)
            ->setFontColor(self::MUTED)
            ->setBackgroundColor(self::BAND_SOFT)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText();
    }

    protected static function sectionStyle(): Style
    {
        return (new Style())
            ->setFontName(self::FONT)
            ->setFontBold()
            ->setFontSize(11)
            ->setFontColor('FFFFFF')
            ->setBackgroundColor(self::BRAND_ACCENT)
            ->setCellAlignment(CellAlignment::LEFT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText();
    }

    protected static function kpiLabelStyle(): Style
    {
        return (new Style())
            ->setFontName(self::FONT)
            ->setFontBold()
            ->setFontSize(10)
            ->setFontColor(self::MUTED)
            ->setBackgroundColor(self::BAND_LIGHT)
            ->setCellAlignment(CellAlignment::LEFT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder(self::border());
    }

    protected static function kpiValueStyle(): Style
    {
        return (new Style())
            ->setFontName(self::FONT)
            ->setFontBold()
            ->setFontSize(11)
            ->setFontColor(self::BRAND_DARK)
            ->setBackgroundColor('FFFFFF')
            ->setCellAlignment(CellAlignment::LEFT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText()
            ->setBorder(self::border());
    }

    protected static function headerStyle(): Style
    {
        return (new Style())
            ->setFontName(self::FONT)
            ->setFontBold()
            ->setFontSize(11)
            ->setFontColor('FFFFFF')
            ->setBackgroundColor(self::BRAND_DARK)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText()
            ->setBorder(self::border());
    }

    protected static function cellStyle(bool $zebra, string $alignment = CellAlignment::LEFT, bool $wrap = false): Style
    {
        $style = (new Style())
            ->setFontName(self::FONT)
            ->setFontSize(10)
            ->setFontColor(self::TEXT)
            ->setCellAlignment($alignment)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder(self::border());

        if ($zebra) {
            $style->setBackgroundColor(self::ZEBRA);
        }

        if ($wrap) {
            $style->setShouldWrapText();
        }

        return $style;
    }

    protected static function wrapCellStyle(bool $zebra, string $alignment = CellAlignment::LEFT): Style
    {
        return self::cellStyle($zebra, $alignment, true);
    }

    protected static function amountStyle(bool $zebra): Style
    {
        $style = (new Style())
            ->setFontName(self::FONT)
            ->setFontSize(10)
            ->setFontColor(self::TEXT)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setFormat('#,##0.00')
            ->setBorder(self::border());

        if ($zebra) {
            $style->setBackgroundColor(self::ZEBRA);
        }

        return $style;
    }

    protected static function totalsLabelStyle(): Style
    {
        return (new Style())
            ->setFontName(self::FONT)
            ->setFontBold()
            ->setFontSize(11)
            ->setFontColor('FFFFFF')
            ->setBackgroundColor(self::BRAND_DARK)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder(self::border());
    }

    protected static function totalsAmountStyle(): Style
    {
        return (new Style())
            ->setFontName(self::FONT)
            ->setFontBold()
            ->setFontSize(11)
            ->setFontColor('FFFFFF')
            ->setBackgroundColor(self::BRAND)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setFormat('#,##0.00')
            ->setBorder(self::border());
    }

    protected static function totalsCellStyle(): Style
    {
        return (new Style())
            ->setFontName(self::FONT)
            ->setFontBold()
            ->setFontSize(10)
            ->setFontColor('FFFFFF')
            ->setBackgroundColor(self::BRAND)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText()
            ->setBorder(self::border());
    }

    protected static function spacerStyle(): Style
    {
        return (new Style())
            ->setFontName(self::FONT)
            ->setFontSize(8)
            ->setBackgroundColor('FFFFFF');
    }

    protected static function footerStyle(): Style
    {
        return (new Style())
            ->setFontName(self::FONT)
            ->setFontItalic()
            ->setFontSize(9)
            ->setFontColor(self::MUTED)
            ->setBackgroundColor(self::BAND_SOFT)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText();
    }

    /**
     * KPI göstergeleri: dikey "Gösterge | Değer" tablosu (dar sütunda metin kesilmez).
     *
     * @param  array<int, array{0: string, 1: string|int|float}>  $items
     */
    protected static function writeKpiRow(
        Writer $writer,
        int &$rowNum,
        array $items,
        int $lastColumn,
        ?Options $options = null,
        int $sheetIndex = 0,
    ): void {
        $headerCells = [
            Cell::fromValue('Gösterge', self::headerStyle()),
            Cell::fromValue('Değer', self::headerStyle()),
        ];
        for ($i = 2; $i <= $lastColumn; $i++) {
            $headerCells[] = Cell::fromValue('', self::headerStyle());
        }
        $writer->addRow((new Row($headerCells))->setHeight(22));
        $rowNum++;
        $options?->mergeCells(1, $rowNum, $lastColumn, $rowNum, $sheetIndex);

        foreach ($items as [$label, $value]) {
            if ($label === '' && ($value === '' || $value === null)) {
                continue;
            }

            $valueText = is_scalar($value) ? (string) $value : '';
            $cells = [
                Cell::fromValue((string) $label, self::kpiLabelStyle()),
                Cell::fromValue($valueText, self::kpiValueStyle()),
            ];
            for ($i = 2; $i <= $lastColumn; $i++) {
                $cells[] = Cell::fromValue('', self::kpiValueStyle());
            }

            // Etiket tek satırda kalsın; sadece uzun değerlerde satır yükselsin.
            $valueLines = max(1, (int) ceil(mb_strlen($valueText) / 55));
            $height = max(24, min(60, 18 + (($valueLines - 1) * 14)));

            $writer->addRow((new Row($cells))->setHeight((float) $height));
            $rowNum++;
            $options?->mergeCells(1, $rowNum, $lastColumn, $rowNum, $sheetIndex);
        }
    }

    /**
     * Uzun metin için satır yüksekliği (wrap ile birlikte).
     */
    protected static function rowHeightForText(string $text, float $columnWidth = 28.0): float
    {
        $charsPerLine = max(12, (int) floor($columnWidth * 1.15));
        $lines = max(1, (int) ceil(mb_strlen(trim($text)) / $charsPerLine));

        return (float) max(18, min(90, 14 + ($lines * 12)));
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array{label: string, count: int, total: float, average?: float}>  $rows
     */
    protected static function writeMetricTable(
        Writer $writer,
        int &$rowNum,
        array $headers,
        array $rows,
        int $totalCount,
        float $totalAmount,
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
            $height = self::rowHeightForText($label, 30);

            $writer->addRow((new Row([
                Cell::fromValue($index, self::cellStyle($zebra, CellAlignment::CENTER)),
                Cell::fromValue($label, self::wrapCellStyle($zebra)),
                Cell::fromValue($row['count'], self::cellStyle($zebra, CellAlignment::CENTER)),
                Cell::fromValue($row['total'], self::amountStyle($zebra)),
                Cell::fromValue($average, self::amountStyle($zebra)),
            ]))->setHeight($height));
        }

        $rowNum++;
        $writer->addRow((new Row([
            Cell::fromValue('TOPLAM', self::totalsLabelStyle()),
            Cell::fromValue('', self::totalsLabelStyle()),
            Cell::fromValue($totalCount, self::totalsCellStyle()),
            Cell::fromValue($totalAmount, self::totalsAmountStyle()),
            Cell::fromValue($totalCount > 0 ? $totalAmount / $totalCount : 0.0, self::totalsAmountStyle()),
        ]))->setHeight(22));
    }
}
