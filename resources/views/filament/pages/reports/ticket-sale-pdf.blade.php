<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipts Report</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>Receipts Report</h2>

    @if ($receipts && $receipts->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Card Name</th>
                    <th>Passenger Name</th>
                    <th>Airline</th>
                    <th>PNR</th>
                    <th>Ticket Numbers</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($receipts as $receipt)
                    <tr>
                        <td>{{ $receipt['created_at'] }}</td>
                        <td>{{ $receipt['ticket'] }}</td>
                        <td>{{ $receipt['card_name'] }}</td>
                        <td>{{ $receipt['sale'] }}</td>
                        <td>{{ $receipt['cost'] }}</td>
                        <td>{{ $receipt['tax'] }}</td>
                        <td>{{ $receipt['margin'] }}</td>
                        <td>{{ number_format($receipt['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No records found for the selected filters.</p>
    @endif
</body>

</html>
