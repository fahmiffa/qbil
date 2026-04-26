<!DOCTYPE html>
<html>
<head>
    <title>Print Invoice #{{ $invoice->invoice_number }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page { 
            size: 58mm auto; 
            margin: 0; 
        }
        body { 
            width: 54mm; /* Slightly smaller than 58mm to account for margins */
            margin: 0 auto; 
            padding: 2mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.2;
            color: #000;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .border-top { border-top: 1px dashed #000; margin-top: 4px; padding-top: 4px; }
        .border-bottom { border-bottom: 1px dashed #000; margin-bottom: 4px; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 4px; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border: 1px solid #000;
            font-size: 10px;
            margin-top: 4px;
        }
        .footer { font-size: 9px; margin-top: 10px; }
    </style>
</head>
<body onload="window.print(); setTimeout(window.close, 500);">
    <div class="center bold mb-1" style="font-size: 14px;">
        {{ $invoice->customer->user->name }}
    </div>
    @if($invoice->customer->user->appSetting->address)
    <div class="center mb-2" style="font-size: 9px;">
        {{ $invoice->customer->user->appSetting->address }}
    </div>
    @endif

    <div class="border-top">
        ID Pel : {{ $invoice->customer->id_pelanggan }}<br>
        Nama   : {{ $invoice->customer->name }}<br>
        Invoice: {{ $invoice->invoice_number }}<br>
        Tanggal: {{ $invoice->created_at->format('d/m/Y H:i') }}
    </div>

    <table class="border-top">
        <thead>
            <tr>
                <th class="left" style="text-align: left;">LAYANAN</th>
                <th class="right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ $invoice->package->name ?? 'Internet' }}
                    @if($invoice->package && ($invoice->package->speed_download || $invoice->package->speed_upload))
                    <br><span style="font-size:9px;">{{ $invoice->package->speed_download ?? '-' }}/{{ $invoice->package->speed_upload ?? '-' }}</span>
                    @endif
                </td>
                <td class="right">{{ number_format($invoice->amount, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->unique_code > 0)
            <tr>
                <td>Kode Unik</td>
                <td class="right">+{{ number_format($invoice->unique_code, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="bold border-top">
                <td>TOTAL</td>
                <td class="right">{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="center border-top">
        <div class="status-badge bold">
            {{ $invoice->status === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}
        </div>
    </div>

    @if($invoice->status !== 'paid')
    <div class="footer center">
        Silakan lakukan pembayaran sesuai<br>dengan nominal tertera.<br>
    </div>
    @endif

    <div class="footer center border-top">
        Terima Kasih Atas Kepercayaan Anda
    </div>
    
    <div style="height: 10mm;"></div> <!-- Padding bottom for manual tear -->
</body>
</html>
