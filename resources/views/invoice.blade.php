<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice CRN1899362399</title>
    <style>
        /* Import DejaVu Sans - most reliable for PDF rupee symbol */
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('https://cdnjs.cloudflare.com/ajax/libs/dejavu-fonts/4.0.0/ttf/DejaVuSans.ttf') format('truetype');
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }

        .company-info {
            margin-bottom: 30px;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .customer-info,
        .invoice-meta {
            width: 48%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f5f5f5;
        }

        .total-section {
            margin-top: 30px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }

        .amount-due {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .rupee {
            font-family: 'DejaVu Sans', Arial, sans-serif;
        }

        .company-details {
            flex: 1;
            text-align: center
        }
    </style>
</head>

<body>
    @if ($tripDetails)
        <div class="company-details">
            <h1>Next-Stop</h1>
            <p>Phone: 8080871135 | Email: nextstopkolhapur@gmail.com</p>
        </div>
        <hr>
        <div class="invoice-title" style="text-align:center">INVOICE</div>

        <div class="invoice-details">
            <div class="customer-info">
                <p><strong>Customer Name : </strong>{{ $tripDetails->customer->customer_first_name ?? '' }}
                    {{ $tripDetails->customer->customer_last_name ?? '' }}</p>
                <p><strong>Phone Number : </strong>{{ $tripDetails->customer->customer_phone ?? '' }}</p>
                <p><strong>Email : </strong>{{ $tripDetails->customer->customer_email ?? '' }}</p>
            </div>
        </div>

        <table>
            <tbody>
                <tr>
                    <td>Trip No</td>
                    <td><span class="rupee"></span></span> {{ $tripDetails->trip_id }}</td>
                </tr>
                <tr>
                    <td>TXN NO</td>
                    <td><span class="rupee"></span> {{ $tripDetails->trip_unique_id }}</td>
                </tr>
                <tr>
                    <td>Trip Fare Amount</td>
                    <td><span class="rupee">&#x20B9;</span> {{ $tripDetails->trip_fair_amount }}</td>
                </tr>
                <tr>
                    <td>Discount</td>
                    <td><span class="rupee">&#x20B9;</span> {{ $tripDetails->trip_discount }}</td>
                </tr>
                <tr>
                    <td>Net Fare Amount</td>
                    <td><span class="rupee">&#x20B9;</span> {{ $tripDetails->trip_netfair_amount }}</td>
                </tr>
                <hr>
                <tr>
                    <td>CGST Amount</td>
                    <td>{{ $tripDetails->trip_cgst_rate }} %</td>
                </tr>

                <tr>
                    <td>SGST Amount</td>
                    <td>{{ $tripDetails->trip_sgst_rate }} %</td>
                </tr>
            </tbody>
        </table>

        <div class="total-section">
            <div class="amount-due">
                <p>Payable Amount: <span class="rupee">&#x20B9;</span> {{ $tripDetails->trip_total_amount }}</p>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
        </div>
    @endif
</body>

</html>
