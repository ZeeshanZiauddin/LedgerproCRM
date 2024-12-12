<?php
namespace App\Filament\Widgets;

use App\Models\Customer;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TopCustomer extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'topCustomer';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Top Customers';

    protected function getFormSchema(): array
    {
        return [
            Select::make('date_filter')
                ->native(false)
                ->label('Date Range')
                ->options([
                    'last_6_days' => 'Last 6 Days',
                    'this_month' => 'This Month',
                    'last_3_months' => 'Last 3 Months',
                    'last_6_months' => 'Last 6 Months',
                    'year' => 'This Year',
                    'all_time' => 'All Time',
                ])
                ->default('this_month'),
        ];
    }

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $dateFilter = $this->filterFormData['date_filter'] ?? 'this_month';

        $query = Customer::withSum('receipts as total_paid', 'total')
            ->orderByDesc('total_paid')
            ->whereHas('receipts') // Ensure the customer has at least one receipt
            ->take(5); // Limit to top 5 customers

        // Apply date filters
        switch ($dateFilter) {
            case 'last_6_days':
                $query->whereHas('receipts', function ($query) {
                    $query->whereBetween('created_at', [
                        Carbon::now()->subDays(6)->startOfDay(),
                        Carbon::now()->endOfDay(),
                    ]);
                });
                break;

            case 'this_month':
                $query->whereHas('receipts', function ($query) {
                    $query->whereBetween('created_at', [
                        Carbon::now()->startOfMonth(),
                        Carbon::now()->endOfMonth(),
                    ]);
                });
                break;

            case 'last_3_months':
                $query->whereHas('receipts', function ($query) {
                    $query->whereBetween('created_at', [
                        Carbon::now()->subMonths(3)->startOfMonth(),
                        Carbon::now()->endOfMonth(),
                    ]);
                });
                break;

            case 'last_6_months':
                $query->whereHas('receipts', function ($query) {
                    $query->whereBetween('created_at', [
                        Carbon::now()->subMonths(6)->startOfMonth(),
                        Carbon::now()->endOfMonth(),
                    ]);
                });
                break;

            case 'year':
                $query->whereHas('receipts', function ($query) {
                    $query->whereYear('created_at', Carbon::now()->year);
                });
                break;

            // No filter for 'all_time', include all records
        }

        $data = $query->get();
        $customerNames = $data->pluck('name')->toArray();
        $totalAmounts = $data->pluck('total_paid')->toArray();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Total Paid Amount',
                    'data' => $totalAmounts,
                ],
            ],
            'xaxis' => [
                'categories' => $customerNames,
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
            'colors' => ['#f59e0b'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => true,
                ],
            ],
        ];
    }
}
