<?php

namespace App\Filament\Crm\Widgets;

use App\Filament\Crm\Widgets\Concerns\InteractsWithCrmDashboardFilters;
use App\Models\Donation;
use App\Support\BrandPalette;
use App\Support\Crm\DonationDateFilter;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\View\View;

class YearlyComparisonChart extends ChartWidget
{
    use InteractsWithCrmDashboardFilters;

    protected static ?int $sort = 4;

    protected ?string $heading = 'Yıllık Karşılaştırma';

    protected ?string $maxHeight = '280px';

    protected int | string | array $columnSpan = 'full';

    public function render(): View
    {
        if (! $this->yearlyComparisonIsRelevant()) {
            return view('filament.crm.widgets.hidden-widget');
        }

        return parent::render();
    }

    protected function yearlyComparisonIsRelevant(): bool
    {
        $period = (string) ($this->dashboardFilters()['period'] ?? 'this_month');

        return in_array($period, ['this_year', 'last_year', 'all_time'], true);
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getDescription(): ?string
    {
        $filters = $this->dashboardFilters();
        $period = (string) ($filters['period'] ?? 'this_month');

        if (in_array($period, ['this_year', 'last_year', 'all_time'], true)) {
            return 'Bu yıl ve geçen yıl (aylık)';
        }

        return 'Yıllık karşılaştırma için "Bu yıl" veya "Tüm zamanlar" seçin';
    }

    protected function getData(): array
    {
        $filters = $this->dashboardFilters();
        $period = (string) ($filters['period'] ?? 'this_month');

        if (! in_array($period, ['this_year', 'last_year', 'all_time'], true)) {
            return [
                'datasets' => [
                    [
                        'label' => 'Bağış (TRY)',
                        'data' => array_fill(0, 12, 0),
                        'backgroundColor' => '#cbd5e1',
                    ],
                ],
                'labels' => collect(range(1, 12))
                    ->map(fn (int $month): string => now()->month($month)->locale('tr')->translatedFormat('M'))
                    ->all(),
            ];
        }

        $labels = [];
        $thisYear = [];
        $lastYear = [];

        $currentYear = now()->year;
        $previousYear = $currentYear - 1;

        for ($month = 1; $month <= 12; $month++) {
            $labels[] = now()->month($month)->locale('tr')->translatedFormat('M');

            $thisYearQuery = $this->filteredDonationsQuery()
                ->whereYear('donated_at', $currentYear)
                ->whereMonth('donated_at', $month);

            $lastYearQuery = Donation::query();

            if (filled($filters['project_id'] ?? null)) {
                $lastYearQuery->where('project_id', $filters['project_id']);
            }

            $thisYear[] = (float) $thisYearQuery->sum('amount');
            $lastYear[] = (float) $lastYearQuery
                ->whereYear('donated_at', $previousYear)
                ->whereMonth('donated_at', $month)
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => (string) $currentYear,
                    'data' => $thisYear,
                    'backgroundColor' => BrandPalette::PRIMARY,
                ],
                [
                    'label' => (string) $previousYear,
                    'data' => $lastYear,
                    'backgroundColor' => '#94a3b8',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
