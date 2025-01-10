<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <style>
        @font-face {
            font-family: SourceSansPro;
            src: url(SourceSansPro-Regular.ttf);
        }

        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            color: #67aee5;
            text-decoration: none;
        }

        body {
            position: relative;
            margin: 0 auto;
            color: #555555;
            background: #FFFFFF;
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-family: SourceSansPro;
        }

        header {
            padding: 20px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #AAAAAA;
        }

        #logo {
            float: left;
            margin-top: 8px;
        }

        #logo img {
            height: 70px;
        }

        #company {
            float: right;
            text-align: right;
        }


        #details {
            margin-bottom: 50px;
        }

        #client {
            padding-left: 6px;
            border-left: 6px solid #67aee5;
            float: left;
        }

        #client .to {
            color: #777777;
        }

        h2.name {
            font-size: 1.4em;
            font-weight: normal;
            margin: 0;
        }

        #invoice {
            float: right;
            text-align: right;
        }

        #invoice h1 {
            color: #67aee5;
            font-size: 2em;
            line-height: 1em;
            font-weight: normal;
            margin: 0 0 6px 0;
        }

        #invoice .date {
            font-size: 1.1em;
            color: #777777;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        table th,
        table td {
            padding: 5px 10px;
            background: #EEEEEE;
            text-align: left;
            border-bottom: 1px solid #FFFFFF;
        }

        table th {
            white-space: nowrap;
            font-weight: normal;
        }

        table td {
            text-align: left;
        }

        table td h3 {
            color: #b2c134;
            font-size: 1.2em;
            font-weight: normal;
            margin: 0 0 0.2em 0;
        }

        table .no {
            color: #FFFFFF;
            font-size: 1.2em;
            background: #b2c134;
        }

        table .desc {
            text-align: left;
        }

        table .unit {
            background: #DDDDDD;
        }

        /*
table .qty {
} */

        table .total {
            background: #b2c134;
            color: #FFFFFF;
        }

        table td.unit,
        table td.qty,
        table td.total {
            font-size: 1em;
        }

        table tbody tr:last-child td {
            border: none;
        }

        table tfoot td {
            padding: 5px;
            background: #FFFFFF;
            border-bottom: none;
            font-size: 1em;
            white-space: nowrap;
            border-top: 1px solid #AAAAAA;
        }

        table tfoot tr:first-child td {
            border-top: none;
        }

        table tfoot tr:last-child td {
            color: #67aee5;
            font-size: 1em;
            border-top: 1px solid #67aee5;

        }

        table tfoot tr td:first-child {
            border: none;
        }

        h3.table_header {
            margin-top: 16px;
            margin-bottom: 10px;
            font-weight: 700;
            color: #67aee5
        }

        #thanks {
            font-size: 2em;
            margin-bottom: 50px;
        }

        #notices {
            padding-left: 6px;
            border-left: 6px solid #67aee5;
        }

        #notices .notice {
            font-size: 1em;
        }

        footer {
            color: #777777;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #AAAAAA;
            padding: 8px 0;
        }

        footer tr td {
            padding: 0;
            background: transparent
        }

        footer table tbody tr td:last-child {
            text-align: right
        }
    </style>
</head>

<body>

    <header class="clearfix">
        <div id="logo">
            <img src="outlook-logo.png">
        </div>
        <div id="company">
            <h2 class="name">{{ $company['name'] }}</h2>
            <div>{{ $company['address'] }}</div>
            <div>{{ $company['phone'] }}</div>
            <div><a href="mailto:company@example.com">{{ $company['email'] }}</a></div>
        </div>
        </div>
    </header>
    <main>
        <div id="details" class="clearfix">
            <div id="client">
                <div class="to">Customer</div>
                <h2 class="name">{{ $card['customer']['name'] }}</h2>
                <div class="address">{{ $card['customer']['address'] }}</div>
                <div class="email"><a href="mailto:john@example.com">{{ $card['customer']['email'] }}</a></div>
            </div>
            <div id="invoice">
                <h1>INVOICE {{ $card['name'] }}</h1>
                <div class="date">Date of Invoice: {{ $card['date'] }}</div>
                <div class="date">Due Date: {{ $card['due_date'] }}</div>
                <div class="date">Issued by: {{ $issued_by }}</div>
            </div>
        </div>
        <h3 class="table_header">Passengers</h3>
        <table border="0" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th class="no">#</th>
                    <th class="desc">NAME</th>
                    <th class="unit">TICKET</th>
                    <th class="qty">ISSUE DATE</th>
                    <th class="total">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total = 0;
                @endphp
                @foreach ($passengers as $key => $passenger)
                    <tr>
                        <td class="no">{{ $key }}</td>
                        <td class="desc">
                            <h3>{{ $passenger->name }}</h3>
                        </td>
                        <td class="unit">{{ $card['airline']->code . $passenger->ticket_2 }}</td>
                        <td class="qty">{{ $passenger->issue_date }}</td>
                        <td class="total">{{ $passenger->sale }}</td>
                    </tr>
                    @php
                        $total += $passenger->sale;
                    @endphp
                @endforeach

            </tbody>
        </table>
        <h3 class="table_header">Flight Details</h3>
        <table border="0" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th class="no">#</th>
                    <th class="desc">From</th>
                    <th class="desc">to</th>
                    <th class="desc">Flight</th>
                    <th class="desc">Class</th>
                    <th class="desc">date</th>
                    <th class="desc">Dep</th>
                    <th class="desc">Arr</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($flights as $key => $flight)
                    <tr>
                        <td class="no">{{ $key }}</td>
                        <td>
                            {{ $flight->from }}
                        </td>
                        <td>
                            {{ $flight->to }}
                        </td>
                        <td>
                            {{ $flight->airline . ' ' . $flight->flight }}
                        </td>
                        <td>
                            {{ $flight->class }}
                        </td>
                        <td>
                            {{ $flight->date }}
                        </td>
                        <td>
                            {{ $flight->dep }}
                        </td>
                        <td>
                            {{ $flight->arr }}
                        </td>
                    </tr>
                @endforeach

            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5"></td>
                    <td colspan="2">Total Invoice {{ '(' . $currency . ')' }}</td>
                    <td>{{ $total }}</td>
                </tr>
                <tr>
                    @php
                        $totalReceipt = 0;
                        foreach ($card['receipts'] as $receipt) {
                            $totalReceipt += $receipt->total;
                        }
                    @endphp
                    <td colspan="5"></td>
                    <td colspan="2">Total Receipt {{ '(' . $currency . ')' }}</td>
                    <td>{{ $totalReceipt }}</td>
                </tr>
                <tr>
                    <td colspan="5"></td>
                    <td colspan="2">TOTAL {{ '(' . $currency . ')' }}</td>
                    <td>{{ $total - $totalReceipt }}</td>
                </tr>
            </tfoot>
        </table>
        <div id="thanks">Thank you!</div>
        <div id="notices">
            <div>NOTICE:</div>
            <div class="notice">Customer is advised to Pay the amount under due date</div>
        </div>
    </main>

    <footer>
        <table>
            <tbody>
                <tr>
                    <td>
                        <img src="outlook-logo.png" alt="outlook logo" style="width: 100px">
                    </td>
                    <td>
                        <h4>{{ $company['name'] }}</h4>
                    </td>
                </tr>
            </tbody>

        </table>
    </footer>
</body>

</html>
