<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 20px;
        }

        .doc-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .outer {
            border: 1px solid #000;
        }

        .outer td {
            vertical-align: top;
        }

        .header-table td {
            padding: 10px 12px;
            border: none;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 4px 0;
        }

        .header-table .lbl {
            font-weight: bold;
        }

        .band-head td {
            background: #f2f2f2;
            font-weight: bold;
            padding: 5px 8px;
            border: 1px solid #000;
        }

        .band-body td {
            padding: 8px;
            border: 1px solid #000;
            vertical-align: top;
        }

        .items th,
        .items td {
            border: 1px solid #000;
            padding: 6px;
        }

        .items th {
            background: #f2f2f2;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .summary-wrap td {
            vertical-align: top;
            border: 1px solid #000;
            padding: 0;
        }

        .tax-table th,
        .tax-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 10px;
        }

        .tax-table th {
            background: #f2f2f2;
            text-align: center;
        }

        .totals-row {
            padding: 5px 10px;
            border-bottom: 1px solid #000;
        }

        .totals-row .l {
            float: left;
        }

        .totals-row .r {
            float: right;
        }

        .clear {
            clear: both;
        }

        .foot-head td {
            background: #f2f2f2;
            font-weight: bold;
            padding: 5px 8px;
            border: 1px solid #000;
        }

        .foot-body td {
            border: 1px solid #000;
            padding: 10px;
            vertical-align: top;
        }
    </style>
</head>

<body>

    <div class="doc-title">Tax Invoice</div>

    <table class="outer">

        <!-- ===================== HEADER ===================== -->
        <tr>
            <td style="padding:0;">
                <table class="header-table">
                    <tr>
                        <td style="width:18%; text-align:center; vertical-align:middle;">
                            @if (file_exists(public_path('images/chumpay.png')))
                                <img src="{{ public_path('images/chumpay.png') }}" width="90">
                            @endif
                        </td>
                        <td style="width:52%;">
                            <p class="company-name">{{ $company['name'] }}</p>
                            {{ $company['address'] }}<br>
                            Phone: <span class="bold">{{ $company['phone'] }}</span><br>
                            GSTIN: <span class="bold">{{ $company['gstin'] }}</span>
                        </td>
                        <td style="width:30%; vertical-align:bottom;">
                            Email: <span class="bold">{{ $company['email'] }}</span><br>
                            State: <span class="bold">{{ $company['state'] }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- ===================== BILL / INVOICE ===================== -->
        <tr>
            <td style="padding:0;">
                <table>
                    <tr class="band-head">
                        <td style="width:60%;">Bill To:</td>
                        <td style="width:40%;">Invoice Details:</td>
                    </tr>
                    <tr class="band-body">
                        <td>
                            <span class="bold">{{ $billTo['name'] }}</span><br>
                            {{ $billTo['line1'] }}<br>
                            {{ $billTo['city'] }} – {{ $billTo['state'] }} – {{ $billTo['pincode'] }} –
                            {{ $billTo['country'] }}<br>
                            Contact No: <span class="bold">{{ $billTo['phone'] }}</span><br>
                            State: <span class="bold">{{ $billTo['state'] }}</span><br>
                            @if (!empty($billTo['gstin']))
                                GSTIN Number: <span class="bold">{{ $billTo['gstin'] }}</span>
                            @endif
                        </td>
                        <td>
                            No: <span class="bold">{{ $order->order_id ?? '' }}</span><br>
                            Date: <span class="bold">{{ $order->created_at?->format('d-m-Y') ?? '' }}</span><br>
                            Time: <span class="bold">{{ $order->created_at?->format('h:i A') ?? '' }}</span><br>
                            Place of Supply: <span class="bold">{{ $billTo['state'] }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- ===================== ITEMS ===================== -->
        <tr>
            <td style="padding:0;">
                <table class="items">
                    <thead>
                        <tr>
                            <th style="width:4%;">#</th>
                            <th style="width:40%;">Item Name</th>
                            <th style="width:10%;" class="right">Quantity</th>
                            <th style="width:12%;" class="right">Price/ Unit (₹)</th>
                            <th style="width:14%;" class="right">GST (₹)</th>
                            <th style="width:12%;" class="right">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $i => $item)
                            <tr>
                                <td class="center">{{ $i + 1 }}</td>
                                <td>{{ $item['name'] }}</td>
                                <td class="right">{{ $item['qty'] }}</td>
                                <td class="right" style="white-space: nowrap;">₹
                                    {{ number_format($item['unit_price'], 2) }}</td>
                                <td class="right" style="white-space: nowrap;">
                                    ₹ {{ number_format($item['gst_amount'], 2) }}
                                    ({{ number_format($item['gst_rate'], 1) }}%)
                                </td>
                                <td class="right" style="white-space: nowrap;">₹
                                    {{ number_format($item['line_amount'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="bold">
                            <td></td>
                            <td>Total</td>
                            <td class="right">{{ $totalQty }}</td>
                            <td></td>
                            <td class="right">₹ {{ number_format($totalTax, 2) }}</td>
                            <td class="right" style="white-space: nowrap;">₹ {{ number_format($subTotal, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>

        <!-- ===================== TAX SUMMARY + TOTALS ===================== -->
        <tr>
            <td style="padding:0;">
                <table class="summary-wrap">
                    <tr>

                        <!-- LEFT : TAX SUMMARY -->
                        <td style="width:60%;">
                            <div
                                style="padding:5px 8px;font-weight:bold;background:#f2f2f2;border-bottom:1px solid #000;">
                                Tax Summary
                            </div>

                            <table class="tax-table" style="border:none;">
                                @if ($isSameState)
                                    <thead>
                                        <tr>
                                            <th rowspan="2">Taxable Amount</th>
                                            <th colspan="2">CGST</th>
                                            <th colspan="2">SGST</th>
                                            <th rowspan="2">Total Tax</th>
                                        </tr>
                                        <tr>
                                            <th>Rate %</th>
                                            <th>Amount</th>
                                            <th>Rate %</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($taxRows as $row)
                                            <tr>
                                                <td class="right">{{ number_format($row['taxable'], 2) }}</td>
                                                <td class="center">{{ number_format($row['cgst_rate'], 2) }}</td>
                                                <td class="right">{{ number_format($row['cgst_amt'], 2) }}</td>
                                                <td class="center">{{ number_format($row['sgst_rate'], 2) }}</td>
                                                <td class="right">{{ number_format($row['sgst_amt'], 2) }}</td>
                                                <td class="right">{{ number_format($row['tax'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bold">
                                            <td class="right">{{ number_format($totalTaxable, 2) }}</td>
                                            <td></td>
                                            <td class="right">{{ number_format($totalCgst, 2) }}</td>
                                            <td></td>
                                            <td class="right">{{ number_format($totalSgst, 2) }}</td>
                                            <td class="right">{{ number_format($totalTax, 2) }}</td>
                                        </tr>
                                    </tbody>
                                @else
                                    <thead>
                                        <tr>
                                            <th>Taxable Amount</th>
                                            <th>IGST Rate %</th>
                                            <th>IGST Amount</th>
                                            <th>Total Tax</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($taxRows as $row)
                                            <tr>
                                                <td class="right">{{ number_format($row['taxable'], 2) }}</td>
                                                <td class="center">{{ number_format($row['igst_rate'], 2) }}</td>
                                                <td class="right">{{ number_format($row['igst_amt'], 2) }}</td>
                                                <td class="right">{{ number_format($row['tax'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bold">
                                            <td class="right">{{ number_format($totalTaxable, 2) }}</td>
                                            <td></td>
                                            <td class="right">{{ number_format($totalIgst, 2) }}</td>
                                            <td class="right">{{ number_format($totalIgst, 2) }}</td>
                                        </tr>
                                    </tbody>
                                @endif
                            </table>
                        </td>

                        <!-- RIGHT : TOTALS -->
                        <td style="width:40%;">
                            <div class="totals-row">
                                <span class="l">Sub Total :</span>
                                <span class="r">₹ {{ number_format($subTotal, 2) }}</span>
                                <div class="clear"></div>
                            </div>
                            @if ($platformFee > 0)
                                <div class="totals-row">
                                    <span class="l">Platform Fee :</span>
                                    <span class="r">₹ {{ number_format($platformFee, 2) }}</span>
                                    <div class="clear"></div>
                                </div>
                            @endif

                            @if ($coupon > 0)
                                <div class="totals-row">
                                    <span class="l">Coupon :</span>
                                    <span class="r">₹ - {{ number_format($coupon, 2) }}</span>
                                    <div class="clear"></div>
                                </div>
                            @endif


                            <div class="totals-row">
                                <span class="l">Shipping :</span>
                                <span class="r">₹ {{ number_format($shipping, 2) }}</span>
                                <div class="clear"></div>
                            </div>

                            <div class="totals-row bold">
                                <span class="l">Total :</span>
                                <span class="r">₹ {{ number_format($grossAmount, 2) }}</span>
                                <div class="clear"></div>
                            </div>

                            <div class="totals-row">
                                <span class="bold">Invoice Amount In Words :</span><br>
                                {{ $amountInWords }}
                            </div>
                            <div class="totals-row" style="border-bottom:none;">
                                <span class="l">Balance :</span>
                                <span class="r">₹ {{ number_format($balance, 2) }}</span>
                                <div class="clear"></div>
                            </div>
                        </td>

                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="width:100%; text-align:right; margin-top:20px;">
        <div style="display:inline-block; text-align:center;">
            @if (file_exists(public_path('images/signature.png')))
                <img src="{{ public_path('images/signature.png') }}" height="50">
            @endif
            <div style="margin-bottom:5px;">Authorized Signatory</div>
        </div>
    </div>

</body>

</html>
