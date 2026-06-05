<!DOCTYPE html>
<html>
<head>
    <title>Print Invoice #{{ $invoice->invoice_number }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @page { 
            size: 80mm auto; 
            margin: 0; 
        }
        body { 
            width: 80mm;
            margin: 0; 
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.2;
            color: #000;
            background-color: #fff;
            -webkit-print-color-adjust: exact;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .left { text-align: left; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        /* Kembali ke solid border tapi tipis agar rapi dan tidak putus-putus */
        .border-solid { border-top: 1px solid #000; margin: 6px 0; padding-top: 4px; }
        .border-double { border-top: 3px double #000; margin: 6px 0; padding-top: 4px; }
        
        table { width: 100%; border-collapse: collapse; margin: 4px 0; }
        th { font-weight: bold; text-align: left; border-bottom: 1px solid #000; padding: 2px 0; }
        td { padding: 3px 0; vertical-align: top; font-weight: 500; }
        
        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border: 1px solid #000;
            font-size: 14px;
            margin: 8px 0;
            font-weight: 900;
        }
        .footer { font-size: 10px; margin-top: 10px; line-height: 1.3; }
        .logo-text { font-size: 17px; font-weight: 900; }
        
        #printable-area {
            padding: 4mm 2mm;
            background: #fff;
            margin: 60px auto 0 auto;
            width: 80mm;
            box-sizing: border-box;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Action Bar (Hidden on Print) -->
    <div class="no-print" style="position: fixed; top: 0; left: 0; right: 0; background: #fff; border-bottom: 1px solid #ddd; padding: 10px; display: flex; gap: 10px; justify-content: center; items-center; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <div style="display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 11px; font-weight: bold; color: #64748b;">Kertas:</label>
            <select id="paperSize" style="padding: 5px; border-radius: 5px; border: 1px solid #ddd; font-size: 11px;">
                <option value="80" selected>80mm</option>
                <option value="58">58mm</option>
            </select>
        </div>
        <div style="display: flex; align-items: center; gap: 5px;">
            <label style="font-size: 11px; font-weight: bold; color: #64748b;">Printer:</label>
            <select id="printerList" style="padding: 5px; border-radius: 5px; border: 1px solid #ddd; font-size: 11px; min-width: 150px;">
                <option value="">Mencari Printer...</option>
            </select>
        </div>
        <button onclick="printWithGo()" id="btnQZ" class="btn-qz" style="background: #2563eb; color: #fff; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold;">
            Cetak (Thermal)
        </button>
        <button onclick="window.print()" style="background: #64748b; color: #fff; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">
            Cetak Manual
        </button>
        <button onclick="window.close()" style="background: #ef4444; color: #fff; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">
            Tutup
        </button>
    </div>

    <div id="printable-area">
        <div class="center bold logo-text uppercase">
            {{ $invoice->customer->user->name }}
        </div>
        @if($invoice->customer->user->appSetting->address)
        <div class="center" style="font-size: 10px; margin-bottom: 5px;">
            {{ $invoice->customer->user->appSetting->address }}
        </div>
        @endif

        <div class="border-solid uppercase" style="font-size: 11px;">
            ID Pel : {{ $invoice->customer->id_pelanggan }}<br>
            Nama   : {{ $invoice->customer->name }}<br>
            Inv    : {{ $invoice->invoice_number }}<br>
            Tgl    : {{ $invoice->created_at->translatedFormat('d/m/y H:i') }}
        </div>

        <table class="border-solid">
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
                        <br><span style="font-size:10px;">{{ $invoice->package->speed_download ?? '-' }}/{{ $invoice->package->speed_upload ?? '-' }}</span>
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
                <tr class="bold border-solid">
                    <td>TOTAL</td>
                    <td class="right">{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="center border-solid">
            <div class="status-badge uppercase">
                {{ $invoice->status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}
            </div>
        </div>

        @if($invoice->status !== 'paid')
        <div class="footer center">
            Silakan lakukan pembayaran sesuai<br>dengan nominal tertera.<br>
        </div>
        @endif

        <div class="footer center border-solid">
            Terima Kasih Atas kerjasamanya, pemasangan dan perbaikan hubungi nomor {{ $invoice->customer->user->phone }}
            <br>
           {{ $invoice->customer->user->appSetting->payment_instruction }}
        </div>
        
        <div style="height: 10mm;"></div>
    </div>

    <!-- Go Print Service Dependencies -->
    <script>
        const printerSelect = document.getElementById('printerList');
        const paperSelect = document.getElementById('paperSize');
        const STORAGE_KEY = 'qz_preferred_printer'; // Kita tetap pakai key yang sama agar user tidak perlu pilih ulang jika sudah ada
        const PAPER_KEY = 'qz_preferred_paper';
        const SERVICE_URL = 'http://localhost:9000';

        function updatePreviewWidth(size) {
            const width = size + 'mm';
            document.body.style.width = width;
            document.getElementById('printable-area').style.width = width;
            
            let style = document.getElementById('dynamic-page-style');
            if (!style) {
                style = document.createElement('style');
                style.id = 'dynamic-page-style';
                document.head.appendChild(style);
            }
            style.innerHTML = `@page { size: ${width} auto; }`;
        }

        async function initPrintService() {
            const savedPaper = localStorage.getItem(PAPER_KEY);
            if (savedPaper) paperSelect.value = savedPaper;

            updatePreviewWidth(paperSelect.value);

            paperSelect.onchange = function() {
                localStorage.setItem(PAPER_KEY, this.value);
                updatePreviewWidth(this.value);
            };
            try {
                // Ambil daftar printer dari Go Service
                const response = await fetch(`${SERVICE_URL}/printers`);
                const printers = await response.json();
                
                printerSelect.innerHTML = '<option value="">-- Pilih Printer --</option>';
                
                printers.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p;
                    opt.innerText = p;
                    printerSelect.appendChild(opt);
                });

                const saved = localStorage.getItem(STORAGE_KEY);
                if (saved && printers.includes(saved)) {
                    printerSelect.value = saved;
                }

            } catch (err) {
                console.error(err);
                printerSelect.innerHTML = '<option value="">Service Belum Jalan</option>';
                alert("Print Service belum aktif. Pastikan Anda sudah menjalankannya.");
            }
        }

        printerSelect.onchange = function() {
            if (this.value) {
                localStorage.setItem(STORAGE_KEY, this.value);
            }
        };

        // Helper untuk merapikan kolom (padding) - menyesuaikan ukuran
        function formatRow(label, value, totalWidth) {
            const labelWidth = totalWidth - value.length;
            let finalLabel = label;
            if (label.length > labelWidth) {
                finalLabel = label.substring(0, labelWidth - 1);
            }
            return finalLabel.padEnd(labelWidth) + value;
        }

        // Helper untuk membungkus teks panjang
        function wrapText(text, limit) {
            if (!text) return "";
            const words = text.split(' ');
            let lines = [];
            let currentLine = '';

            words.forEach(word => {
                if ((currentLine + word).length > limit) {
                    lines.push(currentLine.trim());
                    currentLine = word + ' ';
                } else {
                    currentLine += word + ' ';
                }
            });
            lines.push(currentLine.trim());
            return lines.join('\n');
        }

        async function printWithGo() {
            const printerName = printerSelect.value;
            const btn = document.getElementById('btnQZ');
            
            const paperWidthChars = paperSelect.value === '80' ? 48 : 32;
            const lineStr = '-'.repeat(paperWidthChars) + '\n';

            if (!printerName) {
                alert("Silakan pilih printer thermal terlebih dahulu.");
                return;
            }

            try {
                btn.innerText = 'Printing...';
                btn.disabled = true;

                // Susun Raw ESC/POS
                let rawData = 
                    '\x1B' + '\x40' +          // Initialize
                    '\x1B' + '\x61' + '\x01' + // Center
                    '\x1B' + '\x21' + '\x30' + // Double Height + Bold
                    '{{ $invoice->customer->user->name }}\n' +
                    '\x1B' + '\x21' + '\x00' + // Reset
                    lineStr +
                    '\x1B' + '\x61' + '\x00' + // Left
                    'ID PEL : {{ $invoice->customer->id_pelanggan }}\n' +
                    'NAMA   : {{ $invoice->customer->name }}\n' +
                    'INV    : {{ $invoice->invoice_number }}\n' +
                    'TGL    : {{ $invoice->created_at->translatedFormat("d/m/y H:i") }}\n' +
                    lineStr +
                    formatRow('LAYANAN', 'TOTAL', paperWidthChars) + '\n' +
                    lineStr +
                    formatRow('{{ $invoice->package->name ?? "Internet" }}', '{{ number_format($invoice->amount, 0, ",", ".") }}', paperWidthChars) + '\n';

                @if($invoice->package && ($invoice->package->speed_download || $invoice->package->speed_upload))
                    rawData += '{{ $invoice->package->speed_download ?? "-" }}/{{ $invoice->package->speed_upload ?? "-" }}\n';
                @endif

                rawData += '\n';

                @if($invoice->unique_code > 0)
                    rawData += formatRow('Kode Unik', '+{{ number_format($invoice->unique_code, 0, ",", ".") }}', paperWidthChars) + '\n';
                @endif

                rawData += 
                    lineStr +
                    '\x1B' + '\x45' + '\x01' + // Bold ON
                    formatRow('TOTAL', '{{ number_format($invoice->total_amount, 0, ",", ".") }}', paperWidthChars) + '\n' +
                    '\x1B' + '\x45' + '\x00' + // Bold OFF
                    lineStr +
                    '\n' +
                    '\x1B' + '\x61' + '\x01' + // Center
                    '\x1B' + '\x21' + '\x08' + // Bold Font
                    '[ {{ $invoice->status === "paid" ? "LUNAS" : "BELUM LUNAS" }} ]\n' +
                    '\x1B' + '\x21' + '\x00' + // Reset
                    '\n' +
                    lineStr +
                    '\x1B' + '\x61' + '\x01' + // Center Footer
                    wrapText('Terima Kasih Atas kerjasamanya, pemasangan dan perbaikan hubungi nomor {{ $invoice->customer->user->phone }}', paperWidthChars) + '\n' +
                    '\n' +
                    wrapText('{{ str_replace(["\r\n", "\r", "\n"], " ", $invoice->customer->user->appSetting->payment_instruction) }}', paperWidthChars) + '\n' +
                    '\n\n\n\n' +
                    '\x1B' + '\x69';           // Cut

                // Kirim ke Go Service
                const response = await fetch(`${SERVICE_URL}/print`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        printer: printerName,
                        content: rawData
                    })
                });

                if (response.ok) {
                    btn.innerText = 'Selesai!';
                    setTimeout(() => window.close(), 1500);
                } else {
                    throw new Error(await response.text());
                }

            } catch (err) {
                console.error(err);
                alert("Gagal Cetak via Go Service: " + err.message);
                btn.innerText = 'Cetak (Go Service)';
                btn.disabled = false;
            }
        }

        window.onload = initPrintService;
    </script>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; margin: 0 !important; padding: 0 !important; }
            #printable-area { margin: 0 !important; border: none !important; }
        }
    </style>
</body>
</html>
