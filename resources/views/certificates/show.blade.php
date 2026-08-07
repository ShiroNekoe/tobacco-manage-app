@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black tracking-wide text-zinc-100">Prinjau Process Certificate</h2>
            <p class="text-xs text-zinc-400 mt-1">Dokumen mutu resmi hasil penimbangan dan pemisahan tembakau</p>
        </div>
        <div>
            <a href="{{ route('certificate.pdf', $batch->id) }}" class="px-5 py-3 min-h-[48px] inline-flex items-center text-xs font-black rounded-xl bg-red-950 text-red-300 border border-red-800 hover:bg-red-900 shadow">
                📄 Export PDF Process Certificate
            </a>
        </div>
    </div>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-8 space-y-6 shadow-2xl">
        <div class="text-center border-b border-zinc-800 pb-4">
            <h1 class="text-xl font-black tracking-wider text-amber-400">PROCESS CERTIFICATE</h1>
            <p class="text-xs text-zinc-400 font-mono mt-1">Kode Batch: <strong class="text-zinc-200">{{ $batch->batch_code }}</strong></p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-zinc-950 p-4 rounded-2xl border border-zinc-800 text-xs">
            <div>
                <span class="text-zinc-500 block uppercase font-bold text-[10px]">Pelanggan</span>
                <strong class="text-zinc-200">{{ $batch->customer->name ?? '-' }}</strong>
            </div>
            <div>
                <span class="text-zinc-500 block uppercase font-bold text-[10px]">Surat Jalan (DN)</span>
                <strong class="text-zinc-200 font-mono">{{ $batch->deliveryNote ? $batch->deliveryNote->formatted_dn_number : '-' }}</strong>
            </div>
            <div>
                <span class="text-zinc-500 block uppercase font-bold text-[10px]">Jenis Produk</span>
                <strong class="text-amber-400">{{ $batch->productType->name ?? '-' }}</strong>
            </div>
            <div>
                <span class="text-zinc-500 block uppercase font-bold text-[10px]">Asal / Kemasan</span>
                <strong class="text-zinc-200">{{ $batch->origin->region_name ?? '-' }} ({{ $batch->pack_type }})</strong>
            </div>
        </div>

        <!-- Section 3 Summary Table -->
        <div class="space-y-2">
            <h3 class="text-xs font-bold uppercase tracking-wider text-amber-500">Ringkasan Hasil Pemisahan (Separation Results)</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="bg-zinc-950 p-4 rounded-2xl border border-emerald-900/60">
                    <span class="text-[10px] uppercase font-bold text-zinc-400 block">Produk Rajangan</span>
                    <span class="text-xl font-black text-emerald-400 font-mono mt-1 block">{{ number_format($batch->separation_product_kg, 2) }} kg</span>
                    <span class="text-xs font-bold text-emerald-400">{{ number_format($batch->yield_product_pct, 2) }}%</span>
                </div>
                <div class="bg-zinc-950 p-4 rounded-2xl border border-amber-900/60">
                    <span class="text-[10px] uppercase font-bold text-zinc-400 block">Bits Stem</span>
                    <span class="text-xl font-black text-amber-400 font-mono mt-1 block">{{ number_format($batch->separation_bits_stem_kg, 2) }} kg</span>
                    <span class="text-xs font-bold text-amber-400">{{ number_format($batch->yield_bits_stem_pct, 2) }}%</span>
                </div>
                <div class="bg-zinc-950 p-4 rounded-2xl border border-orange-900/60">
                    <span class="text-[10px] uppercase font-bold text-zinc-400 block">Dust</span>
                    <span class="text-xl font-black text-orange-400 font-mono mt-1 block">{{ number_format($batch->separation_dust_kg, 2) }} kg</span>
                    <span class="text-xs font-bold text-orange-400">{{ number_format($batch->yield_dust_pct, 2) }}%</span>
                </div>
                <div class="bg-zinc-950 p-4 rounded-2xl border border-zinc-800">
                    <span class="text-[10px] uppercase font-bold text-zinc-400 block">Uncountable Waste</span>
                    <span class="text-xl font-black text-zinc-300 font-mono mt-1 block">{{ number_format($batch->separation_waste_kg, 2) }} kg</span>
                    <span class="text-xs font-bold text-zinc-400">{{ number_format($batch->yield_waste_pct, 2) }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
