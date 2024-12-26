<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ItineraryController extends Controller
{
    public function updateItinerary(Request $request)
    {
        $data = $request->validate([
            'itinerary' => 'required|string',
        ]);

        $lines = explode("\n", $data['itinerary']);
        $passengers = [];
        $flights = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Parse passengers
            if (preg_match_all('/\d+\.\d+([A-Z\/ ]+ [A-Z]{2,4})/', $line, $matches)) {
                foreach ($matches[1] as $name) {
                    $passengers[] = ['name' => trim($name)];
                }
            }

            // Parse flights
            if (preg_match('/^\d+\s*\.\s+([A-Z]{2})\s+(\d+)\s+([A-Z])\s+(\d{2}[A-Z]{3})\s+([A-Z]{3})([A-Z]{3})\s+HK\d+\s+(\d{4})\s+(#?)(\d{4})\s+O\*/', $line, $flightMatches)) {
                $departureTime = substr($flightMatches[7], 0, 2) . ':' . substr($flightMatches[7], 2, 2);
                $arrivalTime = substr($flightMatches[9], 0, 2) . ':' . substr($flightMatches[9], 2, 2);

                $flights[] = [
                    'airline' => $flightMatches[1],
                    'flight_no' => $flightMatches[2],
                    'class' => $flightMatches[3],
                    'date' => $flightMatches[4],
                    'from' => $flightMatches[5],
                    'to' => $flightMatches[6],
                    'departure_time' => $departureTime,
                    'arrival_time' => $arrivalTime,
                ];
            }
        }

        return response()->json([
            'passengers' => $passengers,
            'flights' => $flights,
        ]);
    }
}
