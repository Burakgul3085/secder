<?php

namespace App\Filament\Crm\Widgets;

use App\Filament\Crm\Widgets\Concerns\InteractsWithCrmDashboardFilters;
use App\Support\BrandPalette;
use App\Support\Crm\DonationDateFilter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\ChartWidget;

class MonthlyDonationsChart extends ChartWidget
{
    use InteractsWithCrmDashboardFilters;

    protected static ?int $sort = 2;

    protected ?string $heading = 'Bağış Grafiği';

    protected ?string $maxHeight = '300px';

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'lg' => 2,
    ];

    protected function getType(): string
    {
        return 'line';
    }

    public function getDescription(): ?string
    {
        $filters = $this->dashboardFilters();
        $period = (string) ($filters['period'] ?? 'this_month');
        $range = DonationDateFilter::resolveRange($period, $filters) ?? [
            now()->copy()->subMonths(11)->startOfMonth(),
            now(),
        ];

        $days = $range[0]->diffInDays($range[1]);

        if ($period === 'all_time') {
            return 'Son 12 ay · ' . DonationDateFilter::projectLabel($filters);
        }

        if ($days <= 31) {
            return 'Günlük · ' . DonationDateFilter::dashboardPeriodLabel($filters);
        }

        return 'Aylık · ' . DonationDateFilter::dashboardPeriodLabel($filters);
    }

    protected function getData(): array
    {
        $filters = $this->dashboardFilters();
        $period = (string) ($filters['period'] ?? 'this_month');
        $range = DonationDateFilter::resolveRange($period, $filters);

        if ($range === null) {
            $range = [now()->copy()->subMonths(11)->startOfMonth(), now()->endOfMonth()];
        }

        [$from, $to] = $range;
        $days = $from->diffInDays($to);

        if ($days <= 31) {
            return $this->buildDailyDataset($from, $to);
        }

        return $this->buildMonthlyDataset($from, $to);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDailyDataset(Carbon $from, Carbon $to): array
    {
        $labels = [];
        $data = [];

        $start = $from->copy()->startOfDay();
        $end = $to->copy()->endOfDay();

        if ($start->diffInDays($end) > 90) {
            $start = $end->copy()->subDays(90)->startOfDay();
        }

        foreach (CarbonPeriod::create($start, '1 day', $end) as $day) {
            $labels[] = $day->locale('tr')->translatedFormat('d M');
            $data[] = (float) $this->filteredDonationsQuery()
                ->whereDate('donated_at', $day->toDateString())
                ->sum('amount');
        }

        return $this->chartPayload($labels, $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMonthlyDataset(Carbon $from, Carbon $to): array
    {
        $labels = [];
        $data = [];
        $cursor = $from->copy()->startOfMonth();
        $end = $to->copy()->endOfMonth();

        while ($cursor <= $end) {
            $labels[] = $cursor->locale('tr')->translatedFormat('M Y');
            $data[] = (float) $this->filteredDonationsQuery()
                ->whereYear('donated_at', $cursor->year)
                ->whereMonth('donated_at', $cursor->month)
                ->sum('amount');
            $cursor->addMonth();
        }

        return $this->chartPayload($labels, $data);
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, float>  $data
     * @return array<string, mixed>
     */
    private function chartPayload(array $labels, array $data): array
    {
        if ($labels === []) {
            $labels[] = now()->locale('tr')->translatedFormat('d M Y');
            $data[] = 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Bağış (TRY)',
                    'data' => $data,
                    'fill' => true,
                    'tension' => 0.3,
                    'borderColor' => BrandPalette::PRIMARY,
                    'backgroundColor' => 'rgba(77, 92, 131, 0.12)',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
