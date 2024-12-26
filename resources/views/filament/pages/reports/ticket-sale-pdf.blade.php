<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipts Report</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif
        }

        h1,
        h2,
        h3,
        h4,
        p {
            text-align: center;
            margin: 0px;
            margin-bottom: 8px;
            padding: 0px;
            font-weight: 400;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 2px;
            text-align: left;
            font-size: 14px
        }

        thead,
        tfoot {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }
    </style>
</head>

<body>
    <h2 style="font-weight: 600">Outlook Travel</h2>
    <h4>Ticket Sales Report</h4>
    <h4 style="margin-bottom:16px">{{ $receipts[0]['date_range'] }}</h4>
    @if ($receipts && $receipts->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Card Name</th>
                    <th>Rec #</th>
                    <th>Ticket</th>
                    <th>Sale</th>
                    <th>Cost</th>
                    <th>Tax</th>
                    <th>GP</th>
                </tr>
            </thead>
            <tbody>

                <?php $profit = 0; ?>
                @foreach ($receipts as $receipt)
                    <?php $profit += $receipt['margin']; ?>
                    <tr>
                        <td>{{ $receipt['date'] }}</td>
                        <td>{{ $receipt['card_name'] }}</td>
                        <td>{{ $receipt['record_number'] }}</td>
                        <td>{{ $receipt['ticket'] }}</td>
                        <td><small>{!! $receipt['sale'] !!}</small></td>
                        <td><small>{!! $receipt['cost'] !!}</small></td>
                        <td><small>{!! $receipt['tax'] !!}</small></td>
                        <td><small>{!! $receipt['margin'] !!}</small></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <th>Total Profit :</th>
                <td>{{ $profit }}</td>

            </tfoot>
        </table>
    @else
        <p>No records found for the selected filters.</p>
    @endif
</body>

</html>
