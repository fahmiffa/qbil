<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Transaksi</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        @media print {
            body {
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 10px !important;
            }

            .no-print {
                display: none !important;
            }

            .invoice-card {
                border: none !important;
                margin: 0 !important;
                width: 100% !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            .bg-slate-50\/50,
            .bg-slate-50\/30,
            .bg-indigo-500,
            .bg-emerald-500,
            .bg-amber-500,
            .bg-blue-600,
            .bg-white {
                background: white !important;
                color: black !important;
                box-shadow: none !important;
            }

            .text-white {
                color: black !important;
            }

            * {
                box-shadow: none !important;
            }
        }

        body {
            font-family: 'Figtree', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-slate-900 antialiased p-4 sm:p-10">
    <div class="max-w-4xl mx-auto print:max-w-none print:p-0">
        <!-- Action Bar -->
        <div class="no-print flex justify-between items-center mb-6 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
            <button onclick="window.close()" class="text-xs font-bold text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Tutup</button>
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest hover:scale-105 transition-all shadow-lg shadow-blue-600/20">Cetak Laporan</button>
        </div>

        <div class="invoice-card bg-white dark:bg-slate-900 rounded-[2rem] overflow-hidden border border-slate-100 dark:border-slate-800 shadow-xl print:shadow-none transition-all">
            <!-- Header -->
            <div class="p-8 border-b border-slate-50 dark:border-slate-800">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        @if($user && $user->photo)
                        <img src="{{ Storage::url($user->photo) }}" class="h-16 w-16 rounded-2xl object-cover shadow-lg border border-slate-100 dark:border-slate-800">
                        @else
                        <div class="h-16 w-16 rounded-2xl bg-indigo-500 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                            <span class="text-2xl font-black text-white uppercase">{{ substr($appSetting->nama_aplikasi ?? 'M', 0, 1) }}</span>
                        </div>
                        @endif
                        <div class="space-y-1">
                            <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tighter">{{ $appSetting->nama_aplikasi ?? 'LAPORAN TRANSAKSI' }}</h1>
                            <p class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">Laporan {{ $type == 'income' ? 'Pemasukan' : 'Pengeluaran' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="p-8 grid grid-cols-2 gap-8 text-sm border-b border-slate-50 dark:border-slate-800">
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Periode Laporan:</h3>
                    <p class="font-bold text-slate-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} -
                        {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
                    </p>
                </div>
                <div class="text-right">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tipe Data:</h3>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase">{{ $type == 'income' ? 'Pemasukan' : 'Pengeluaran' }}</p>
                </div>
            </div>

            <!-- Items Table -->
            <div class="px-8 py-4">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b-2 border-slate-100 dark:border-slate-800">
                            <th class="py-4 text-left font-black text-slate-400 uppercase tracking-widest">Tanggal</th>
                            <th class="py-4 text-left font-black text-slate-400 uppercase tracking-widest">Keterangan</th>
                            <th class="py-4 text-left font-black text-slate-400 uppercase tracking-widest">Kategori</th>
                            <th class="py-4 text-left font-black text-slate-400 uppercase tracking-widest">Metode / Layanan</th>
                            <th class="py-4 text-right font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                        @foreach($transactions as $trx)
                        <tr>
                            <td class="py-3 font-medium text-slate-700 dark:text-slate-300">
                                {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d/m/Y') }}
                            </td>
                            <td class="py-3 font-bold text-slate-800 dark:text-slate-200">
                                {{ $trx->description ?: '-' }}
                            </td>
                            <td class="py-3 text-slate-600 dark:text-slate-400">
                                {{ $trx->category }}
                            </td>
                            <td class="py-3 text-slate-600 dark:text-slate-400">
                                {{ $trx->payment_method ?: '-' }} <br>
                                <span class="text-[10px] text-slate-400">{{ $trx->service_type ?: '' }}</span>
                            </td>
                            <td class="py-3 text-right font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                        @if($transactions->isEmpty())
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 italic">Tidak ada transaksi pada periode ini.</td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                            <td colspan="4" class="py-6 pl-4 font-black text-slate-900 dark:text-white uppercase tracking-widest text-left">Total Keseluruhan</td>
                            <td class="py-6 pr-4 text-right whitespace-nowrap">
                                <span class="text-lg font-black text-blue-600 dark:text-blue-400 font-mono tracking-tight">
                                    Rp {{ number_format($totalAmount, 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Footer -->
            <div class="p-8 bg-slate-50/30 dark:bg-slate-800/20 border-t border-slate-50 dark:border-slate-800 mt-4">
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-[11px] leading-relaxed text-slate-600 dark:text-slate-400 italic">
                            Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i:s') }}
                        </p>
                    </div>
                    <div class="text-center w-40">
                        <p class="text-[11px] mb-12">Mengetahui,</p>
                        <p class="text-[11px] font-bold border-b border-slate-400 pb-1">{{ $user->name ?? 'Admin' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Auto print on load
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500);
        };
    </script>
</body>

</html>