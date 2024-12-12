<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PDFController extends Controller
{
    public function preview(Request $request)
    {
        $customer_id = $request->get('customer_id');
        $date_range = $request->get('date_range', '7_days');

        $query = Receipt::query();

        if ($customer_id) {
            $query->where('customer_id', $customer_id);
        }

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

        $result = $query->get();

        if ($result->isEmpty()) {
            return response('No receipts found', 404);
        }
        $receipts = $result->map(function ($receipt, $i) use (&$total) {
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

            return [
                'date' => $receipt->created_at->format('d/m/Y'),
                'customer' => $receipt->customer(),
                'name' => $name,
                'ticket_nos' => $ticketNos,
                'amount' => $amount,
                'total' => $total,
            ];
        });


        $pdf = Pdf::loadView('filament.pages.receipts-pdf', compact('receipts'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('preview.pdf');
    }
}
