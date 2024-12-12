<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use App\Models\Customer;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerReport extends Page
{
    public $customer_id = null;
    public $date_range = '7_days'; // Default value
    public $receipts;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Reports';

    protected static string $view = 'filament.pages.customer-report';

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
                        ->preload(),

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
        $query = Receipt::query();

        if ($data['customer_id']) {
            $query->where('customer_id', $data['customer_id']);
        }

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

        return $query->with('customer:id,name')->get();
    }

    public function generatePDF($data)
    {
        $this->receipts = $this->updateReceipts($data);

        if ($this->receipts->isEmpty()) {
            Notification::make()
                ->title('Records not found!!')
                ->body('No receipts found for the selected filters.')
                ->danger()
                ->send();

            return;
        }

        // Map and convert the collection into an array
        $receipts = $this->receipts->map(function ($receipt, $i) use (&$total) {
            $i++;
            $name = '';
            $ticketNos = [];
            $amount = $receipt->total;

            $total += $amount;

            if ($receipt->card_id) {
                $card = $receipt->card;
                $cardName = $card->card_name;

                $relatedReceipts = Receipt::where('card_id', $receipt->card_id)
                    ->where('customer_id', $receipt->customer_id)
                    ->get();

                $receiptCount = $relatedReceipts->count();
                $name = ($receiptCount > 1) ? "{$cardName}-" . ($relatedReceipts->search($receipt) + $i) : $cardName;

                $cardPassengers = $card->passengers;
                foreach ($cardPassengers as $cardPassenger) {
                    $ticketNos[] = [
                        't1' => $cardPassenger->ticket_1 ?? 'N/A',
                        't2' => $cardPassenger->ticket_2 ?? 'N/A',
                    ];
                }
            } else {
                $name = $receipt->name;
            }

            $customerName = $receipt->customer->name ?? 'N/A';

            return [
                'date' => $receipt->created_at->format('d/m/Y'),
                'customer_name' => $customerName,
                'name' => $name,
                'ticket_nos' => $ticketNos,
                'amount' => $amount,
                'total' => $total,
            ];
        })->toArray(); // Convert the collection to an array
        // dd($receipts[0]['customer_name']);
        // Now you have the $receipts as a plain array
        $pdf = Pdf::loadView('filament.pages.receipts-pdf', compact('receipts'))
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(fn() => print ($pdf->output()), 'receipts_report.pdf');
    }

    public function handleGeneratePDF(array $data)
    {
        $this->customer_id = $data['customer_id'] ?? null;
        $this->date_range = $data['date_range'] ?? '7_days';
        $this->generatePDF($data);
    }
}
