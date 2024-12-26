<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use App\Models\Customer;

class TicketSaleDetails extends Page
{
    public $customer_id = null;
    public $date_range = '7_days';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Reports';
    protected static string $view = 'filament.pages.ticket-sale-details';

    public function getActions(): array
    {
        return [
            Action::make('generateReport')
                ->label('Generate Report')
                ->modalHeading('Filter Receipts')
                ->modalWidth('lg')
                ->form([
                    Select::make('customer_id')
                        ->label('Customer')
                        ->options(Customer::all()->pluck('name', 'id')->toArray())
                        ->placeholder('Select a customer')
                        ->searchable()
                        ->reactive(),
                    Select::make('date_range')
                        ->label('Date Range')
                        ->options([
                            '7_days' => 'Last 7 Days',
                            '3_months' => 'Last 3 Months',
                            '6_months' => 'Last 6 Months',
                            'this_month' => 'This Month',
                            'last_year' => 'Last Year',
                            'all' => 'All Time',
                        ])
                        ->default('7_days')

                ])
                ->action(function (array $data) {
                    $this->customer_id = $data['customer_id'];
                    $this->date_range = $data['date_range'];
                }),
        ];
    }

}
