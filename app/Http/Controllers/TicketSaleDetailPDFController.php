<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CardPassenger;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TicketSaleDetailPDFController extends Controller
{
    public $customer_id;
    public $date_range_data;

    public function preview(Request $request)
    {
        $customer_id = $request->get('customer_id');
        $date_range = $request->get('date_range', '7_days');

        $query = CardPassenger::query();

        if ($date_range) {
            $date = Carbon::now();

            switch ($date_range) {
                case 'last_year':
                    $this->date_range_data = 'Records of Last year';
                    $query->where('created_at', '>=', $date->subYear());
                    break;
                case '6_months':
                    $this->date_range_data = 'Records of Last 6 Months';
                    $query->where('created_at', '>=', $date->subMonths(6));
                    break;
                case '3_months':
                    $this->date_range_data = 'Records of Last 3 months';

                    $query->where('created_at', '>=', $date->subMonths(3));
                    break;
                case 'this_month':
                    $this->date_range_data = 'Records of This month';

                    $query->whereMonth('created_at', $date->month);
                    break;
                case '7_days':
                    $this->date_range_data = 'Records of last 7 days';

                    $query->whereBetween('created_at', [$date->subDays(7)->startOfDay(), now()]);
                    break;
            }
        }

        $passengers = $query->with('card.receipts')->get();

        // Group passengers by card and assign record numbers
        $groupedPassengers = $passengers->groupBy('card_id');

        $receipts = $groupedPassengers->flatMap(function ($group) {
            return $group->map(function ($passenger, $index) {
                $ticket = $passenger->ticket_1 . $passenger->ticket_2;

                return [
                    'record_number' => $index + 1, // Assign sequential numbers
                    'date' => $passenger->created_at->format('dMY'),
                    'ticket' => $ticket ?? 'N/A',
                    'card_name' => $passenger->card->card_name,
                    'sale' => $passenger->sale,
                    'cost' => $passenger->cost,
                    'tax' => $passenger->tax,
                    'margin' => ($passenger->sale - ($passenger->cost + $passenger->tax)),
                    'date_range' => $this->date_range_data,
                ];
            });
        });

        if ($receipts->isEmpty()) {
            return response('No receipts found for the selected filters.', 404);
        }

        $pdf = Pdf::loadView('filament.pages.reports.ticket-sale-pdf', compact('receipts'))
            ->setOption('font', 'Serif')
            ->setPaper('a4', 'portrait');

        return $pdf->stream('ticket-sale-details-' . Carbon::now()->format('YmdHis') . '.pdf');
    }


}
