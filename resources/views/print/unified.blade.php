<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ strtoupper($type) }} #{{ $number }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A5 portrait; margin: 10mm; }
        @media print {
            body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .invoice-card { border: 1px solid #f1f5f9 !important; margin: 0 !important; width: 100% !important; box-shadow: none !important; }
        }
        body { font-family: 'Figtree', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 dark:bg-slate-900 antialiased p-4 sm:p-10">
    <div class="max-w-2xl mx-auto print:max-w-none print:p-0">
        <!-- Action Bar -->
        <div class="no-print flex justify-between items-center mb-6 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <button onclick="window.close()" class="text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Tutup</button>
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest hover:scale-105 transition-all shadow-lg shadow-blue-600/20">Cetak</button>
        </div>

        <div class="invoice-card bg-white dark:bg-slate-900 rounded-[2rem] overflow-hidden border border-slate-100 dark:border-slate-800 shadow-xl print:shadow-none transition-all">
            <!-- Header -->
            <div class="p-8 border-b border-slate-50 dark:border-slate-800">
                <div class="flex justify-between items-start">
                    <div class="space-y-1">
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tighter">{{ $type }}</h1>
                        <p class="text-xs font-mono text-slate-400 uppercase">#{{ $number }}</p>
                    </div>
                    <div class="text-right">
                        @if($status == 'paid')
                            <div class="px-4 py-1.5 rounded-lg bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20">LUNAS / DITERIMA</div>
                        @else
                            <div class="px-4 py-1.5 rounded-lg bg-amber-500 text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-amber-500/20">UNPAID / TERUTANG</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="p-8 grid grid-cols-2 gap-8 text-sm">
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Ditujukan Kepada:</h3>
                    <p class="font-bold text-slate-900 dark:text-white">{{ $customer->name }}</p>
                    <p class="text-[11px] text-slate-500 leading-relaxed mt-1">{{ $customer->address }}</p>
                </div>
                <div class="text-right">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Detail Transaksi:</h3>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Tanggal: {{ \Carbon\Carbon::parse($date)->format('d F Y') }}</p>
                    <p class="text-[10px] text-slate-400 mt-1 uppercase">{{ $customer->id_pelanggan }}</p>
                </div>
            </div>

            <!-- Items Table -->
            <div class="px-8 pb-8">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b-2 border-slate-100 dark:border-slate-800">
                            <th class="py-4 text-left font-black text-slate-400 uppercase tracking-widest">Deskripsi</th>
                            <th class="py-4 text-right font-black text-slate-400 uppercase tracking-widest">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                        @foreach($items as $item)
                        <tr>
                            <td class="py-5 font-bold text-slate-800 dark:text-slate-200">
                                @if(isset($item['is_sub'])) <span class="text-[10px] text-slate-400 block font-normal">— {{ $item['label'] }}</span> @else {{ $item['label'] }} @endif
                            </td>
                            <td class="py-5 text-right font-mono font-bold @if(isset($item['is_code'])) text-emerald-600 @else text-slate-900 dark:text-white @endif">
                                @if($item['value'] !== null)
                                    {{ isset($item['is_code']) ? '+' : '' }} {{ number_format($item['value'], 0, ',', '.') }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                            <td class="py-6 pl-4 font-black text-slate-900 dark:text-white uppercase tracking-widest">Total Keseluruhan</td>
                            <td class="py-6 pr-4 text-right">
                                <span class="text-xl font-black text-blue-600 dark:text-blue-400 font-mono tracking-tight">
                                    Rp {{ number_format($amount, 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Footer -->
            <div class="p-8 bg-slate-50/30 dark:bg-slate-800/20 border-t border-slate-50 dark:border-slate-800">
                <p class="text-[11px] leading-relaxed text-slate-600 dark:text-slate-400 italic text-center">
                    Simpan bukti pembayaran ini sebagai tanda bukti yang sah.
                </p>
                <div class="flex justify-center mt-6">
                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.3em]">Terima Kasih Atas Kerjasamanya</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
