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
            width: 58mm;
            margin: 0; 
            padding: 3mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.1;
            color: #000;
            background-color: #fff;
            -webkit-print-color-adjust: exact;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .left { text-align: left; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        /* High contrast solid borders for thermal sharpness */
        .border-top { border-top: 1px solid #000; margin-top: 6px; padding-top: 4px; }
        .border-bottom { border-bottom: 1px solid #000; margin-bottom: 6px; padding-bottom: 4px; }
        .border-double { border-top: 3px double #000; margin-top: 6px; padding-top: 4px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 4px; }
        th { font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px; }
        td { padding: 2px 0; vertical-align: top; }
        
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border: 2px solid #000;
            font-size: 12px;
            margin-top: 8px;
            font-weight: 900;
        }
        .footer { font-size: 9px; margin-top: 12px; line-height: 1.3; }
        .logo-text { font-size: 16px; letter-spacing: 1px; }
    </style>
</head>
<body onload="window.print(); setTimeout(window.close, 500);">
    <div class="center bold mb-1 logo-text uppercase">
        {{ $invoice->customer->user->name }}
    </div>
    @if($invoice->customer->user->appSetting->address)
    <div class="center mb-2" style="font-size: 9px;">
        {{ $invoice->customer->user->appSetting->address }}
    </div>
    @endif

    <div class="border-top uppercase" style="font-size: 10px;">
        ID Pel : {{ $invoice->customer->id_pelanggan }}<br>
        Nama   : {{ $invoice->customer->name }}<br>
        Inv    : {{ $invoice->invoice_number }}<br>
        Tgl    : {{ $invoice->created_at->format('d/m/y H:i') }}
    </div>

    <table class="border-top">
        <thead>
            <tr>
                <th class="left uppercase">Layanan</th>
                <th class="right uppercase">Total</th>
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
        <div class="status-badge uppercase">
            {{ $invoice->status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
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
