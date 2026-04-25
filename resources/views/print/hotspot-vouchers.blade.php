<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Voucher Hotspot</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --print-width: 210mm;
            --print-height: 297mm;
            --voucher-cols: 5;
            --voucher-rows: 8;
            --gap: 2mm;
            --padding: 10mm;
        }

        @page {
            size: A4;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page {
            width: var(--print-width);
            height: var(--print-height);
            padding: var(--padding);
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin: 10mm auto;
            box-sizing: border-box;
            display: grid;
            grid-template-columns: repeat(var(--voucher-cols), 1fr);
            grid-template-rows: repeat(var(--voucher-rows), 1fr);
            gap: var(--gap);
            page-break-after: always;
            position: relative;
        }

        @media print {
            body { background-color: white; }
            .page { 
                margin: 0; 
                box-shadow: none;
            }
            .no-print { display: none !important; }
        }

        .voucher {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 6px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            overflow: hidden;
            position: relative;
            background: #fff;
            transition: all 0.2s;
        }

        .voucher::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            border-radius: 6px 6px 0 0;
        }

        .voucher-header {
            font-size: 8px;
            font-weight: 800;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .voucher-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .voucher-code-label {
            font-size: 7px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .voucher-code {
            font-size: 16px;
            font-weight: 800;
            color: #111827;
            font-family: 'JetBrains Mono', monospace;
            padding: 4px 8px;
            background-color: #f9fafb;
            border: 1px solid #f3f4f6;
            border-radius: 4px;
            width: 80%;
        }

        .voucher-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            border-top: 1px dashed #e5e7eb;
            padding-top: 5px;
            margin-top: 4px;
        }

        .voucher-duration {
            font-size: 9px;
            font-weight: 700;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .voucher-price {
            font-size: 10px;
            font-weight: 800;
            color: #059669;
        }

        /* Floating buttons */
        .no-print {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 99px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            text-decoration: none;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-secondary {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="javascript:window.history.back()" class="btn btn-secondary">Kembali</a>
        <button onclick="window.print()" class="btn btn-primary">Cetak Sekarang</button>
    </div>

    @php 
        $chunks = $vouchers->chunk(40); 
    @endphp

    @forelse($chunks as $pageVouchers)
    <div class="page">
        @foreach($pageVouchers as $v)
        <div class="voucher">
            <div class="voucher-header">{{ $v->user->name ?? 'Voucher Wi-Fi' }}</div>
            
            <div class="voucher-body">
                <div class="voucher-code-label">KODE AKSES</div>
                <div class="voucher-code">{{ $v->username }}</div>
                @if($v->username !== $v->password && $v->password)
                    <div class="voucher-code-label" style="margin-top: 4px;">PASSWORD</div>
                    <div class="voucher-code" style="font-size: 12px;">{{ $v->password }}</div>
                @endif
            </div>

            <div class="voucher-footer">
                <div class="voucher-duration">
                    <svg style="width: 10px; height: 10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $v->package->session_timeout ?? 'Active' }}
                </div>
                <div class="voucher-price">{{ $v->package->name ?? '-' }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @empty
    <div class="page" style="display: flex; justify-content: center; align-items: center;">
        <p>Tidak ada voucher terpilih.</p>
    </div>
    @endforelse
</body>
</html>
