@extends('layouts.invoice-layout')
@section('title', "Pembayaran Multi Bulan {$invoice_number} - {$customer->user->name}")

@push('meta')
    <meta property="og:title" content="Pembayaran Multi Bulan - {{ $customer->user->name }}">
    <meta property="og:description" content="Pembayaran Bulk #{{ $invoice_number }}">
    @if($customer->user->photo)
        <meta property="og:image" content="{{ url(Storage::url($customer->user->photo)) }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->fullUrl() }}">
    <meta name="twitter:card" content="summary_large_image">
@endpush

@section('content')

<div class="py-4 sm:py-10 px-4 max-w-2xl mx-auto print:max-w-none print:p-0">

    <!-- Action Bar (Hidden on Print) -->
    <div class="no-print flex justify-between items-center mb-6 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
        <div class="flex items-center gap-3">
            @if($customer->user->photo)
                <img src="{{ Storage::url($customer->user->photo) }}" class="w-8 h-8 rounded-lg object-cover">
            @else
                <x-application-logo class="w-6 h-6 text-blue-600" />
            @endif
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ $customer->user->name }}</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="flex items-center gap-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest hover:scale-105 transition-transform">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4h10z" />
                </svg>
                Print PDF / A5
            </button>
        </div>
    </div>

    <!-- Main Invoice Card -->
    <div class="invoice-card bg-white dark:bg-slate-900 rounded-[2rem] print:rounded-none overflow-hidden border border-slate-100 dark:border-slate-800 shadow-xl print:shadow-none transition-all">

        <!-- Header -->
        <div class="p-8 sm:p-10 border-b border-slate-50 dark:border-slate-800">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    @if($customer->user->photo)
                        <img src="{{ Storage::url($customer->user->photo) }}" class="h-16 w-16 rounded-2xl object-cover shadow-lg border border-slate-100 dark:border-slate-800">
                    @else
                        <div class="h-16 w-16 rounded-2xl bg-indigo-500 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                            <span class="text-2xl font-black text-white uppercase">{{ substr($customer->user->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div class="space-y-1">
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight uppercase tracking-tighter">INVOICE BULK</h1>
                        <p class="text-xs font-mono text-slate-400 dark:text-slate-500 uppercase tracking-widest">#{{ $invoice_number }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="px-4 py-1.5 rounded-lg bg-emerald-500 text-white text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-emerald-500/20">LUNAS</div>
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="p-8 sm:p-10 grid grid-cols-2 gap-8 text-sm">
            <div class="space-y-3">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pelanggan:</h3>
                <div class="space-y-0.5">
                    <p class="font-bold text-slate-900 dark:text-white">{{ $customer->name }}</p>
                    <p class="text-slate-500 dark:text-slate-500 text-xs leading-relaxed">{{ $customer->address ?? '-' }}</p>
                    <p class="text-slate-500 dark:text-slate-500 text-xs font-medium">{{ $customer->phone }}</p>
                </div>
            </div>
            <div class="space-y-3 text-right">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Detail:</h3>
                <div class="space-y-1 text-xs">
                    <div class="flex justify-end gap-2 text-slate-500 italic">
                        <span>Layanan:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 non-italic">{{ $customer->package->name ?? '-' }}</span>
                    </div>
                    @if($customer->package)
                    <div class="flex justify-end gap-2 text-slate-500 italic">
                        <span>Speed:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 non-italic">{{ $customer->package->speed_download ?? '-' }} / {{ $customer->package->speed_upload ?? '-' }}</span>
                    </div>
                    @endif
                    <div class="flex justify-end gap-2 text-slate-500 italic">
                        <span>Total Invoice:</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200 non-italic">{{ count($invoices) }} Bulan</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="px-8 sm:px-10 pb-8">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b-2 border-slate-100 dark:border-slate-800">
                        <th class="py-4 text-left font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Periode Bulan</th>
                        <th class="py-4 text-right font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Harga</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                    @foreach($invoices as $inv)
                    <tr>
                        <td class="py-6">
                            <div class="font-bold text-slate-800 dark:text-slate-200">
                                {{ \Carbon\Carbon::parse($inv->billing_period)->translatedFormat('F Y') }}
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest font-mono">
                                #{{ $inv->invoice_number }}
                            </div>
                        </td>
                        <td class="py-6 text-right font-mono font-bold text-slate-900 dark:text-white">
                            {{ number_format($inv->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                        <td class="py-6 pl-4 font-black text-slate-900 dark:text-white uppercase tracking-widest">Total Keseluruhan</td>
                        <td class="py-6 pr-4 text-right">
                            <span class="text-xl font-black text-blue-600 dark:text-blue-400 font-mono tracking-tight">
                                Rp {{ number_format($totalAmount, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer / Instructions -->
        <div class="p-8 sm:p-10 bg-slate-50/30 dark:bg-slate-800/20 border-t border-slate-50 dark:border-slate-800">
            <div class="space-y-4">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Info Pembayaran:</h3>
                <div class="text-[11px] leading-relaxed text-slate-600 dark:text-slate-400">
                    Pembayaran ini merangkum total dari beberapa bulan sekaligus dan telah diverifikasi LUNAS.
                </div>
                <p class="text-center pt-6 text-[9px] font-bold text-slate-300 dark:text-slate-700 uppercase tracking-[0.3em]">
                    Terima kasih telah berlangganan
                </p>
            </div>
        </div>
    </div>

    <!-- Print Optimization for A5 -->
    <style>
        @page {
            size: A5 landscape;
            margin: 0;
        }

        @media print {
            body {
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 9px !important;
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
                padding: 5mm !important;
            }

            /* Header Section */
            .p-8.sm\:p-10.border-b {
                padding: 0 0 5px 0 !important;
                margin-bottom: 5px !important;
            }

            /* Main Content: Info and Table Side-by-Side */
            .p-8.sm\:p-10.grid.grid-cols-2 {
                padding: 0 !important;
                gap: 10px !important;
                display: flex !important;
                justify-content: space-between !important;
                align-items: flex-start !important;
            }

            .p-8.sm\:p-10.grid.grid-cols-2 > div {
                flex: 1 !important;
            }

            /* Table Section */
            .px-8.sm\:px-10.pb-8 {
                padding: 0 !important;
                margin-top: 5px !important;
            }

            table th, table td {
                padding: 3px 0 !important;
            }

            .py-6, .py-4 {
                padding-top: 2px !important;
                padding-bottom: 2px !important;
            }

            /* Footer Section */
            .p-8.sm\:p-10.bg-slate-50\/30 {
                padding: 5px 0 !important;
                border-top: 1px dashed #eee !important;
                margin-top: 5px !important;
            }

            .h-16 {
                height: 30px !important;
                width: 30px !important;
            }

            .text-2xl {
                font-size: 14px !important;
            }

            /* Remove Backgrounds and Gradients */
            .bg-slate-50\/50,
            .bg-slate-50\/30,
            .bg-indigo-500,
            .bg-emerald-500,
            .bg-amber-500,
            .bg-blue-600,
            .bg-slate-900,
            .bg-white {
                background: white !important;
                background-color: white !important;
                color: black !important;
            }

            .invoice-card, div, section, table, img, button {
                background-image: none !important;
                background-color: white !important;
                border-color: #eee !important;
                box-shadow: none !important;
            }

            .text-white {
                color: black !important;
            }
            
            .status-badge, .px-4.py-1\.5 {
                border: 1px solid #000 !important;
                background: none !important;
                color: #000 !important;
            }

            .px-8, .px-10, .p-8, .p-10, .pb-8, .pb-10 {
                padding-left: 0 !important;
                padding-right: 0 !important;
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }
        }
    </style>
</div>
@endsection
