<?php
namespace App\Http\Controllers;

use App\Models\CardPassenger;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TicketSaleDetailPDFController extends Controller
{
    public function preview(Request $request)
    {
        $customer_id = $request->get('customer_id');
        $date_range = $request->get('date_range', '7_days');

        $query = CardPassenger::query();

        // Apply date range filter if specified
        if ($date_range) {
            $date = Carbon::now();

            switch ($date_range) {
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

        // Prepare the data structure
        $receipts = $passengers->map(function ($passenger) {
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

        if ($receipts->isEmpty()) {
            return response('No receipts found for the selected filters.', 404);
        }

        // Generate the PDF
        $pdf = Pdf::loadView('filament.pages.ticket-sale-details', compact('receipts'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('ticket-sale-details-' . Carbon::now()->format('YmdHis') . '.pdf');
    }
}
