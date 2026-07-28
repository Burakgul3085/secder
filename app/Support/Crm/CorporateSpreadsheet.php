<?php

namespace App\Support\Crm;

use App\Models\Setting;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Style;

/**
 * Kurumsal Excel raporları için ortak stil ve yapı yardımcıları.
 */
abstract class CorporateSpreadsheet
{
    /** Kurumsal renk paleti (bkz. App\Support\BrandPalette) */
    protected const BRAND_DARK = '333C55';

    protected const BRAND = '4D5C83';

    protected const BAND_LIGHT = 'E8EDF6';

    protected const ZEBRA = 'F1F5F9';

    protected const TOTALS_BG = 'E2E8F0';

    protected const BORDER = 'CBD5E1';

    /**
     * Üst başlık bloğunu (dernek adı, iletişim, rapor başlığı, meta) üretir.
     *
     * @return array<int, array{0: string, 1: Style}>
     */
    protected static function titleBands(Setting $setting, string $reportTitle, string $metaLine): array
    {
        $orgName = $setting->site_title ?: 'Birlikte Kardeşlik Derneği';

        $contactParts = array_filter([
            $setting->address,
            $setting->phone ? 'Tel: ' . $setting->phone : null,
            $setting->email,
        ]);

        $bands = [[$orgName, self::titleStyle()]];

        if ($contactParts !== []) {
            $bands[] = [implode('   •   ', $contactParts), self::contactStyle()];
        }

        $bands[] = [$reportTitle, self::subtitleStyle()];
        $bands[] = [$metaLine, self::metaStyle()];

        return $bands;
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
            ->setFontSize(10)
            ->setFontColor('FFFFFF')
            ->setBackgroundColor(self::BRAND)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }

    protected static function subtitleStyle(): Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontSize(13)
            ->setFontColor(self::BRAND_DARK)
            ->setBackgroundColor(self::BAND_LIGHT)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }

    protected static function metaStyle(): Style
    {
        return (new Style())
            ->setFontSize(10)
            ->setFontColor('334155')
            ->setBackgroundColor(self::BAND_LIGHT)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }

    protected static function headerStyle(): Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontSize(11)
            ->setFontColor('FFFFFF')
            ->setBackgroundColor(self::BRAND_DARK)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText()
            ->setBorder(self::border());
    }

    protected static function cellStyle(bool $zebra, string $alignment = CellAlignment::LEFT): Style
    {
        $style = (new Style())
            ->setFontSize(10)
            ->setFontColor('1E293B')
            ->setCellAlignment($alignment)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder(self::border());

        if ($zebra) {
            $style->setBackgroundColor(self::ZEBRA);
        }

        return $style;
    }

    protected static function amountStyle(bool $zebra): Style
    {
        $style = (new Style())
            ->setFontSize(10)
            ->setFontColor('1E293B')
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
            ->setFontBold()
            ->setFontSize(11)
            ->setFontColor(self::BRAND_DARK)
            ->setBackgroundColor(self::TOTALS_BG)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder(self::border());
    }

    protected static function totalsAmountStyle(): Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontSize(11)
            ->setFontColor(self::BRAND_DARK)
            ->setBackgroundColor(self::TOTALS_BG)
            ->setCellAlignment(CellAlignment::RIGHT)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setFormat('#,##0.00')
            ->setBorder(self::border());
    }

    protected static function totalsCellStyle(): Style
    {
        return (new Style())
            ->setFontBold()
            ->setFontSize(10)
            ->setFontColor(self::BRAND_DARK)
            ->setBackgroundColor(self::TOTALS_BG)
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder(self::border());
    }
}
