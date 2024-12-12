<?php

namespace App\Filament\Widgets;

use App\Models\Receipt;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MonthlyRevenueReport extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'successfulMonthsChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Most Successful Months';

    /**
     * Chart options
     * @return array
     */
    protected function getOptions(): array
    {
        // Calculate monthly totals
        $monthlyTotals = Receipt::selectRaw('MONTH(created_at) as month, SUM(total) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Map the data to months and totals
        $data = collect(range(1, 12))->map(function ($month) use ($monthlyTotals) {
            return $monthlyTotals->firstWhere('month', $month)->total ?? 0;
        });

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Total Revenue',
                    'data' => $data->toArray(),
                ],
            ],
            'xaxis' => [
                'categories' => [
                    'January',
                    'February',
                    'March',
                    'April',
                    'May',
                    'June',
                    'July',
                    'August',
                    'September',
                    'October',
                    'November',
                    'December',
                ],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#6366f1'], // Customize color
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 5,
                    'horizontal' => false,
                ],
            ],
            'dataLabels' => [
                'enabled' => false, // Disable data labels by default
            ],
            'tooltip' => [
                'enabled' => true, // Enable tooltip on hover
                'shared' => true,
                'followCursor' => true,
                'intersect' => false,
            ],
        ];
    }
}
