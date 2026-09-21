<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Tax;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;



class OrderInvoiceController extends Controller
{
    public function download(Order $order)
    {
        $order->load(['orderDetails.product', 'Address', 'user']);
        $company = [
            'name'    => 'Chumpay',
            'address' => 'Textile Complex, Tiruppur, Tamil Nadu 641601',
            'phone'   => '+91 74490 78888',
            'gstin'   => 'XXAAAAA0000A1Z5',
            'state'   => 'Tamil Nadu',
            'email'   => 'support@chumpay.com',
        ];

        $address   = $order->Address;
        $userState = $address?->state ?? '';
        $pincode   = $address?->pincode ?? null;

        // ----- Same-state check: decides CGST+SGST vs IGST -----
        $isSameState = strtolower(trim($company['state'])) === strtolower(trim($userState));
        $taxPercentage = 0;
        // if ($pincode) {
            $taxRow = Tax::first();
            $taxPercentage = (float) ($taxRow->percent ?? 0);
        // }
        $items   = [];
        $taxRows = [];
        $totalQty      = 0;
        $totalTaxable  = 0;
        $totalCgst     = 0;
        $totalSgst     = 0;
        $totalIgst     = 0;
        $totalTax      = 0;

        foreach ($order->orderDetails ?? [] as $item) {
            $qty      = (float) $item->quantity;
            $lineBase = (float) $item->net_amount;
            $totalQty += $qty;
            if ($taxPercentage > 0) {
                $taxable = $lineBase;
                $tax     = $lineBase * ($taxPercentage / 100);
            } else {
                $taxable = $lineBase;
                $tax     = 0;
            }

            $lineAmount = $taxable + $tax;
            $unitPrice  = $lineBase;

            $items[] = [
                'name'        => $item->product_name,
                'qty'         => $qty,
                'unit_price'  => $unitPrice,
                'gst_rate'    => $taxPercentage,
                'gst_amount'  => $tax,
                'line_amount' => $lineAmount,
            ];

            if ($isSameState) {
                $cgstRate = $taxPercentage / 2;
                $sgstRate = $taxPercentage / 2;
                $cgstAmt  = $tax / 2;
                $sgstAmt  = $tax / 2;

                $taxRows[] = [
                    'taxable'    => $taxable,
                    'cgst_rate'  => $cgstRate,
                    'cgst_amt'   => $cgstAmt,
                    'sgst_rate'  => $sgstRate,
                    'sgst_amt'   => $sgstAmt,
                    'tax'        => $tax,
                ];

                $totalCgst += $cgstAmt;
                $totalSgst += $sgstAmt;
            } else {
                $taxRows[] = [
                    'taxable'   => $taxable,
                    'igst_rate' => $taxPercentage,
                    'igst_amt'  => $tax,
                    'tax'       => $tax,
                ];

                $totalIgst += $tax;
            }

            $totalTaxable += $taxable;
            $totalTax     += $tax;
        }

        // ----- Bill-to block -----
        $billTo = [
            'name'    => $address?->name ?? '',
            'line1'   => $address?->address ?? '',
            'city'    => $address?->city ?? '',
            'state'   => $userState,
            'pincode' => $address?->pincode ?? '',
            'country' => $address?->country ?? 'India',
            'phone'   => $address?->phone_number ?? '',
            'gstin'   => $order->user?->gst_no ?? '',
        ];

        // ----- Order-level amounts -----
        $coupon        = (float) ($order->coupon_amount ?? 0);
        $shipping      = (float) ($order->shipping_amount ?? 0);
        $platformFee    = (float) ($taxRow->platform_fee ?? 0);
        $grossAmount   = (float) ($order->gross_amount ?? 0);
        $receivedAmount = (float) ($order->received_amount ?? 0);
        $balance       = $grossAmount - $receivedAmount;
        $subTotal      = $totalTaxable + $totalTax;
        $data = [
            'order'          => $order,
            'company'        => $company,
            'billTo'         => $billTo,
            'items'          => $items,
            'taxRows'        => $taxRows,
            'isSameState'    => $isSameState,
            'taxPercentage'  => $taxPercentage,
            'totalQty'       => $totalQty,
            'totalTaxable'   => $totalTaxable,
            'totalCgst'      => $totalCgst,
            'totalSgst'      => $totalSgst,
            'totalIgst'      => $totalIgst,
            'totalTax'       => $totalTax,
            'subTotal'       => $subTotal,
            'coupon'         => $coupon,
            'platformFee'    => $platformFee,
            'shipping'       => $shipping,
            'grossAmount'    => $grossAmount,
            'receivedAmount' => $receivedAmount,
            'balance'        => $balance,
            'amountInWords'  => $this->numberToWordsINR($grossAmount),
        ];

        $pdf = Pdf::loadView('invoices.order_invoice', $data)->setPaper('a4');

        return $pdf->download('invoice-' . $order->order_id . '.pdf');
    }

    private function numberToWordsINR(float $amount): string
    {
        $rupees = (int) floor($amount);
        $paise  = (int) round(($amount - $rupees) * 100);

        $words = ($rupees > 0 ? $this->convertNumberToWords($rupees) : 'Zero') . ' Rupees';

        if ($paise > 0) {
            $words .= ' and ' . $this->convertNumberToWords($paise) . ' Paise';
        }

        return $words . ' Only';
    }

    private function convertNumberToWords(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $ones = [
            '',
            'One',
            'Two',
            'Three',
            'Four',
            'Five',
            'Six',
            'Seven',
            'Eight',
            'Nine',
            'Ten',
            'Eleven',
            'Twelve',
            'Thirteen',
            'Fourteen',
            'Fifteen',
            'Sixteen',
            'Seventeen',
            'Eighteen',
            'Nineteen'
        ];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $convertBelowThousand = function (int $n) use ($ones, $tens, &$convertBelowThousand) {
            if ($n === 0) return '';
            if ($n < 20) return $ones[$n] . ' ';
            if ($n < 100) return $tens[intdiv($n, 10)] . ' ' . $convertBelowThousand($n % 10);
            return $ones[intdiv($n, 100)] . ' Hundred ' . $convertBelowThousand($n % 100);
        };

        $result   = '';
        $crore    = intdiv($number, 10000000);
        $number %= 10000000;
        $lakh     = intdiv($number, 100000);
        $number %= 100000;
        $thousand = intdiv($number, 1000);
        $number %= 1000;
        $hundred  = $number;

        if ($crore)    $result .= $convertBelowThousand($crore) . 'Crore ';
        if ($lakh)     $result .= $convertBelowThousand($lakh) . 'Lakh ';
        if ($thousand) $result .= $convertBelowThousand($thousand) . 'Thousand ';
        if ($hundred)  $result .= $convertBelowThousand($hundred);

        return trim(preg_replace('/\s+/', ' ', $result));
    }
}
