<?php

namespace App\Filament\Crm\Widgets;

use App\Filament\Crm\Widgets\Concerns\InteractsWithCrmDashboardFilters;
use App\Models\DonationType;
use App\Support\BrandPalette;
use Filament\Widgets\ChartWidget;

class DonationTypeChart extends ChartWidget
{
    use InteractsWithCrmDashboardFilters;

    protected static ?int $sort = 3;

    protected ?string $heading = 'Bağış Türlerine Göre Dağılım';

    protected ?string $maxHeight = '260px';

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getDescription(): ?string
    {
        return $this->filteredDonationsQuery()->exists()
            ? 'Seçili filtreye göre'
            : 'Bu filtrede bağış bulunamadı';
    }

    protected function getData(): array
    {
        $types = DonationType::query()
            ->orderBy('sort_order')
            ->get();

        $labels = [];
        $data = [];
        $colors = BrandPalette::chartSeries();

        foreach ($types as $type) {
            $amount = (float) $this->filteredDonationsQuery()
                ->where('donation_type_id', $type->id)
                ->sum('amount');

            if ($amount <= 0) {
                continue;
            }

            $labels[] = $type->name;
            $data[] = $amount;
        }

        if ($labels === []) {
            $labels[] = 'Veri yok';
            $data[] = 1;
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        $hasDonations = $this->filteredDonationsQuery()->exists();

        return [
            'plugins' => [
                'legend' => [
                    'display' => $hasDonations,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
