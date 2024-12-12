<?php

namespace App\Filament\Pages;

use App\Models\CardPassenger;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use App\Models\Customer;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketSaleDetails extends Page
{
    public $customer_id = null;
    public $date_range = '7_days'; // Default value
    public $receipts;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Reports';

    protected static string $view = 'filament.pages.ticket-sale-details'; // Specify the custom view

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
                        ->reactive()
                        ->searchable()
                        ->preload()
                        ->default(Customer::first()->id), // Set the default value to the ID of the first customer

                    Select::make('date_range')
                        ->label('Date Range')
                        ->options([
                            '7_days' => 'Last 7 Days',
                            '3_months' => 'Last 3 Months',
                            '6_months' => 'Last 6 Months',
                            'this_month' => 'This Month',
                            'last_year' => 'Last Year',
                            'all' => 'All Time', // Optional: no filter
                        ])
                        ->default('7_days')
                        ->reactive(),
                ])
                ->action(fn(array $data) => $this->handleGeneratePDF($data)),
        ];
    }

    public function updateReceipts($data)
    {
        $query = CardPassenger::query();

        // dd($data['date_range']);
        // Apply date range filter if specified
        if ($data['date_range']) {
            $date = Carbon::now();

            switch ($data['date_range']) {
                case 'last_year':
                    $query->where('created_at', '>=', $date->subYear());
                    break;
                case '6_months':
                    $query->where('created_at', '>=', $date->subMonths(6));
                    break;
                case '3_months':
                    $query->where('created_at', '>=', $date->subMonths(3));
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', $date->month);
                    break;
                case '7_days':
                    $query->whereBetween('created_at', [$date->subDays(7)->startOfDay(), now()]);
                    break;
            }
        }

        // Eager load the 'card' relationship and the related 'receipts'
        $passengers = $query->with('card.receipts')->get();
        // dd($passengers);
        // Prepare the data structure
        $formattedData = $passengers->map(function ($passenger) {
            // Combine ticket_1 and ticket_2
            $ticket = $passenger->ticket_1 . $passenger->ticket_2;

            // Extract sale, cost, tax, margin from the passenger
            $sale = $passenger->sale;
            $cost = $passenger->cost;
            $tax = $passenger->tax;
            $margin = $passenger->margin;

            // Sum the total field from all related receipts (make sure the total is numeric)
            $total = $passenger->card->receipts->sum(function ($receipt) {
                return (float) $receipt->total; // Convert the total from string to float
            });

            // Return the formatted data for each passenger/card
            return [
                'created_at' => $passenger->created_at,
                'ticket' => $ticket,
                'card_name' => $passenger->card->card_name, // Card name
                'sale' => $sale,
                'cost' => $cost,
                'tax' => $tax,
                'margin' => $margin,
                'total' => $total // Sum of all receipts' total
            ];
        });

        // Optionally, calculate the overall total sum for all passengers (if needed)
        $totalSum = $passengers->sum(function ($passenger) {
            return $passenger->card->receipts->sum(function ($receipt) {
                return (float) $receipt->total; // Convert to float for summing
            });
        });

        // Dump the results to inspect
        // dd($formattedData, $totalSum);
        return $formattedData;
    }



    public function generatePDF($data)
    {
        $this->customer_id = $data['customer_id'];
        $this->date_range = $data['date_range'];
        $receipts = $this->updateReceipts($data);
        // dd($receipts);

        // if ($receipts->isEmpty()) {
        //     Notification::make()
        //         ->title('Records not found!!')
        //         ->body('No receipts found for the selected filters.')
        //         ->danger()
        //         ->send();

        //     return;
        // }
        // Now you have the $receipts array with card, passenger, and receipt details
        $pdf = Pdf::loadView('filament.pages.ticket-sale-details', compact('receipts'))
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            print ($pdf->output());
        }, 'ticket-sale-details-' . Carbon::now() . '.pdf');
    }

    public function handleGeneratePDF(array $data)
    {
        $this->customer_id = $data['customer_id'] ?? null;
        $this->date_range = $data['date_range'] ?? '7_days';
        $this->generatePDF($data);
    }
}
