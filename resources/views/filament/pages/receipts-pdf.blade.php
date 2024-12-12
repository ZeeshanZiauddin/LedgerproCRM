<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }


        thead,
        tfoot {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            font-size: 14px
        }

        th,
        td {
            padding: 2px;
            text-align: left;
            font-size: 14px
        }

        h1 {
            text-align: center;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>Outlook travel</h2>
        <h3>Customer Ledger Report</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Name</th>
                <th>Ticket No</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receipts as $receipt)
                <tr>
                    <td>{{ $receipt['date'] }}</td>
                    <td>{{ $receipt['name'] }}</td>
                    {{-- <td>
                        @foreach ($receipt['ticket_nos'] as $ticket)
                            <small style="font-size: 8px ">{{ $ticket['t1'] }}{{ $ticket['t2'] }}</small>,<br>
                        @endforeach
                    </td> --}}
                    <td>
                        @if (isset($receipt['ticket_nos'][0]))
                            <small style="font-size: 12px">
                                {{ $receipt['ticket_nos'][0]['t1'] }}{{ $receipt['ticket_nos'][0]['t2'] }}
                            </small>
                        @endif
                    </td>

                    <td>£{{ $receipt['amount'] }}</td>

                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th></th>
                <th></th>
                <th align="end">Sum</th>
                <th>£{{ $receipt['total'] }}</th>
            </tr>
        </tfoot>
    </table>
</body>

</html>
