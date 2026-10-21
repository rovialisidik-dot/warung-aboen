<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $sale->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
            size: auto;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 12px;
            max-width: 320px;
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #444;
            margin: 8px 0;
        }
        .double-divider {
            border-top: 2px dashed #000;
            margin: 8px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 2px 0;
            vertical-align: top;
        }
        .no-print {
            margin-bottom: 12px;
            text-align: center;
        }
        .btn-print {
            background: #059669;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-family: sans-serif;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk Sekarang</button>
    </div>

    <div class="text-center">
        <div class="bold" style="font-size: 15px; margin-bottom: 2px;">{{ app_name() }}</div>
        <div>{{ app_setting('store_address', 'Warung Sembako Digital') }}</div>
        <div>Telp: {{ app_setting('store_phone', '-') }}</div>
    </div>

    <div class="divider"></div>

    <table>
        <tr>
            <td>No. Nota</td>
            <td>:</td>
            <td class="bold">{{ $sale->invoice_number }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td>{{ $sale->user->name ?? 'Kasir' }}</td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td>:</td>
            <td>{{ $sale->customer_name }}</td>
        </tr>
        @if($sale->status === 'cancelled')
        <tr>
            <td colspan="3" class="text-center bold" style="color: red; padding: 4px 0;">*** TRANSAKSI DIBATALKAN ***</td>
        </tr>
        @endif
    </table>

    <div class="divider"></div>

    <table>
        @foreach($sale->items as $item)
        <tr>
            <td colspan="2" class="bold">{{ $item->product_name }}</td>
        </tr>
        <tr>
            <td>{{ $item->quantity }} x {{ format_rupiah($item->unit_price, false) }}</td>
            <td class="text-right">{{ format_rupiah($item->subtotal, false) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td>Subtotal</td>
            <td class="text-right">{{ format_rupiah($sale->subtotal, false) }}</td>
        </tr>
        @if($sale->discount > 0)
        <tr>
            <td>Diskon</td>
            <td class="text-right">-{{ format_rupiah($sale->discount, false) }}</td>
        </tr>
        @endif
        <tr class="bold" style="font-size: 13px;">
            <td>TOTAL</td>
            <td class="text-right">{{ format_rupiah($sale->total_amount) }}</td>
        </tr>
        <tr>
            <td>Bayar ({{ strtoupper($sale->payment_method) }})</td>
            <td class="text-right">{{ format_rupiah($sale->payment_amount, false) }}</td>
        </tr>
        <tr class="bold">
            <td>Kembalian</td>
            <td class="text-right">{{ format_rupiah($sale->change_amount, false) }}</td>
        </tr>
    </table>

    <div class="double-divider"></div>

    <div class="text-center" style="font-size: 11px;">
        {{ app_setting('receipt_footer', 'Terima kasih atas kunjungan Anda!') }}
    </div>

    <script>
        window.onload = function() {
            // Optional: Auto print if opened from POS
            // window.print();
        }
    </script>
</body>
</html>
