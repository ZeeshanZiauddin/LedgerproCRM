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
                @foreach ($receipt['ticket_details'] as $ticket)
                    <tr>
                        <td>{{ $receipt['date'] }}</td>
                        <td>{{ $receipt['customer_name'] }}</td>
                        <td>{{ $receipt['card_name'] }}</td>
                        <td>{{ $ticket['name'] }}</td>
                        <td>{{ $ticket['airline'] }}</td>
                        <td>{{ $ticket['pnr'] }}</td>
                        <td>{{ $ticket['ticket_no'] }}</td>
                        <td>{{ number_format($receipt['amount'], 2) }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <h3>Total: {{ number_format($receipts[0]['total'], 2) }}</h3>
</body>

</html>
