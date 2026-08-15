<div class="space-y-6 pb-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-zinc-900 border border-zinc-800 p-6 rounded-3xl shadow-xl">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-zinc-100 tracking-tight">MANAJEMEN STOCK PRODUK</h1>
                    <p class="text-xs sm:text-sm text-zinc-400 mt-0.5">Monitoring sisa stock barang jadi di gudang & rekapitulasi pengiriman DN Surat Jalan</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.dn-shipments') }}" class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-amber-950/40 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Surat Jalan (DN)</span>
            </a>
            <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold text-xs flex items-center gap-2 border border-zinc-700 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak / PDF Rekap</span>
            </button>
        </div>
    </div>

    <!-- 4 KPI SUMMARY CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Sisa Stock Siap Kirim (Gudang) -->
        <div class="bg-zinc-900 border border-emerald-500/30 p-5 rounded-2xl relative overflow-hidden shadow-lg group hover:border-emerald-500/50 transition-all">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Sisa Stock Gudang</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-black text-emerald-400 font-mono tracking-tight">
                    {{ number_format($globalStats['total_remaining_netto_kg'], 2, ',', '.') }} <span class="text-xs text-emerald-400 font-normal">kg</span>
                </div>
                <div class="text-xs text-zinc-400 mt-1 flex items-center gap-2">
                    <span class="font-bold text-cyan-400">{{ number_format($globalStats['total_remaining_sacks'], 0, ',', '.') }} Karung / Bale</span>
                    <span>•</span>
                    <span class="text-emerald-300 font-semibold">{{ $globalStats['available_batches_count'] }} Batch Tersedia</span>
                </div>
            </div>
        </div>

        <!-- 2. Total Produksi Selesai (Kumulatif Output) -->
        <div class="bg-zinc-900 border border-zinc-800 p-5 rounded-2xl relative overflow-hidden shadow-lg group hover:border-zinc-700 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Total Hasil Produksi</span>
                <span class="p-2 rounded-xl bg-zinc-800 text-zinc-300 border border-zinc-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-black text-zinc-100 font-mono tracking-tight">
                    {{ number_format($globalStats['total_produced_netto_kg'], 2, ',', '.') }} <span class="text-xs text-zinc-400 font-normal">kg</span>
                </div>
                <div class="text-xs text-zinc-400 mt-1 flex items-center gap-2">
                    <span class="font-bold text-zinc-300">{{ number_format($globalStats['total_produced_sacks'], 0, ',', '.') }} Karung / Bale</span>
                    <span>•</span>
                    <span>Total Output Masuk</span>
                </div>
            </div>
        </div>

        <!-- 3. Total Stock Terkirim (via DN Shipment) -->
        <div class="bg-zinc-900 border border-amber-500/30 p-5 rounded-2xl relative overflow-hidden shadow-lg group hover:border-amber-500/50 transition-all">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-500/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">Total Sudah Terkirim</span>
                <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl sm:text-3xl font-black text-amber-400 font-mono tracking-tight">
                    {{ number_format($globalStats['total_shipped_netto_kg'], 2, ',', '.') }} <span class="text-xs text-amber-400 font-normal">kg</span>
                </div>
                <div class="text-xs text-zinc-400 mt-1 flex items-center gap-2">
                    <span class="font-bold text-cyan-400">{{ number_format($globalStats['total_shipped_sacks'], 0, ',', '.') }} Karung / Bale</span>
                    <span>•</span>
                    <span class="text-amber-300 font-semibold">{{ $globalStats['partial_batches_count'] + $globalStats['depleted_batches_count'] }} Batch Terkirim</span>
                </div>
            </div>
        </div>

        <!-- 4. Status Rasio Distribusi Gudang -->
        <div class="bg-zinc-900 border border-zinc-800 p-5 rounded-2xl relative overflow-hidden shadow-lg">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider">Rasio Terdistribusi</span>
                <span class="p-2 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </span>
            </div>
            <div class="mt-3">
                @php
                    $ratio = $globalStats['total_produced_netto_kg'] > 0 
                        ? round(($globalStats['total_shipped_netto_kg'] / $globalStats['total_produced_netto_kg']) * 100, 1) 
                        : 0;
                @endphp
                <div class="text-2xl sm:text-3xl font-black text-cyan-400 font-mono tracking-tight">
                    {{ $ratio }}% <span class="text-xs text-zinc-400 font-normal">Terkirim</span>
                </div>
                <!-- Progress Bar -->
                <div class="w-full bg-zinc-800 rounded-full h-2 mt-2.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ min(100, $ratio) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER & SEARCH BAR -->
    <div class="bg-zinc-900 border border-zinc-800 p-4 sm:p-5 rounded-2xl shadow-lg space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2 relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari Kode Batch, Pelanggan, Asal..." class="w-full pl-9 pr-4 py-2.5 bg-zinc-950 border border-zinc-700 rounded-xl text-xs text-zinc-200 placeholder-zinc-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">
                <svg class="w-4 h-4 text-zinc-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <!-- Filter Customer -->
            <div>
                <select wire:model.live="filterCustomerId" class="w-full px-3 py-2.5 bg-zinc-950 border border-zinc-700 rounded-xl text-xs text-zinc-200 focus:border-amber-500 outline-none">
                    <option value="">Semua Pelanggan</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Origin -->
            <div>
                <select wire:model.live="filterOrigin" class="w-full px-3 py-2.5 bg-zinc-950 border border-zinc-700 rounded-xl text-xs text-zinc-200 focus:border-amber-500 outline-none">
                    <option value="">Semua Asal (Origin)</option>
                    @foreach($origins as $org)
                        <option value="{{ $org->region_name }}">{{ $org->region_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Stock Status -->
            <div>
                <select wire:model.live="filterStockStatus" class="w-full px-3 py-2.5 bg-zinc-950 border border-zinc-700 rounded-xl text-xs text-zinc-200 focus:border-amber-500 outline-none">
                    <option value="all">Semua Status Stock</option>
                    <option value="available">🟢 Tersedia Utuh</option>
                    <option value="partial">🟡 Terkirim Sebagian</option>
                    <option value="depleted">⚪ Habis Terkirim</option>
                </select>
            </div>
        </div>

        <!-- Active Filter Indicator & Reset -->
        @if($search || $filterCustomerId || $filterOrigin || $filterStockStatus !== 'all')
        <div class="flex items-center justify-between pt-2 border-t border-zinc-800/80 text-xs">
            <span class="text-zinc-400">Filter aktif diterapkan</span>
            <button type="button" wire:click="resetFilters" class="text-amber-400 hover:text-amber-300 font-bold flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Reset Filter</span>
            </button>
        </div>
        @endif
    </div>

    <!-- MAIN STOCK TABLE -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-zinc-800 bg-zinc-950/80 text-zinc-400 uppercase font-black tracking-wider text-[11px]">
                        <th class="px-3.5 py-3.5 text-center w-10">No</th>
                        <th class="px-3.5 py-3.5 cursor-pointer hover:text-amber-400" wire:click="sortBy('batch_code')">
                            <div class="flex items-center gap-1">
                                <span>Batch & Pelanggan</span>
                                @if($sortField === 'batch_code') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </div>
                        </th>
                        <th class="px-3.5 py-3.5">Asal & Kemasan</th>
                        <th class="px-3.5 py-3.5 text-center">Standar Berat / Sak</th>
                        <th class="px-3.5 py-3.5 text-right cursor-pointer hover:text-amber-400" wire:click="sortBy('produced_netto_kg')">
                            <div class="flex items-center justify-end gap-1">
                                <span>Total Produksi</span>
                                @if($sortField === 'produced_netto_kg') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </div>
                        </th>
                        <th class="px-3.5 py-3.5 text-right cursor-pointer hover:text-amber-400" wire:click="sortBy('shipped_netto_kg')">
                            <div class="flex items-center justify-end gap-1">
                                <span>Terkirim (DN)</span>
                                @if($sortField === 'shipped_netto_kg') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </div>
                        </th>
                        <th class="px-3.5 py-3.5 text-right cursor-pointer hover:text-amber-400" wire:click="sortBy('remaining_netto_kg')">
                            <div class="flex items-center justify-end gap-1">
                                <span>Sisa di Gudang</span>
                                @if($sortField === 'remaining_netto_kg') <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span> @endif
                            </div>
                        </th>
                        <th class="px-3.5 py-3.5 text-center">Status</th>
                        <th class="px-3.5 py-3.5 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60 font-sans">
                    @forelse($stockItems as $idx => $st)
                    <tr class="hover:bg-zinc-800/40 transition-colors group">
                        <!-- 1. Index -->
                        <td class="px-3.5 py-3.5 text-center font-mono text-zinc-500 font-bold">
                            {{ $stockItems->firstItem() + $idx }}
                        </td>

                        <!-- 2. Batch Code & Customer -->
                        <td class="px-3.5 py-3.5">
                            <div class="font-mono font-black text-amber-400 text-sm tracking-wide">
                                {{ $st['batch_code'] }}
                            </div>
                            <div class="text-[11px] text-zinc-300 font-medium truncate max-w-[160px] mt-0.5" title="{{ $st['customer_name'] }}">
                                {{ $st['customer_name'] }}
                            </div>
                        </td>

                        <!-- 3. Origin, Origin Code & Material Type -->
                        <td class="px-3.5 py-3.5">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="px-2 py-0.5 rounded-lg bg-zinc-800 text-zinc-200 font-semibold text-[11px] border border-zinc-700">
                                    {{ $st['origin'] }}
                                </span>
                                @if($st['origin_code'] && $st['origin_code'] !== '-')
                                <span class="px-2 py-0.5 rounded-lg bg-amber-500/10 text-amber-400 font-mono text-[10px] border border-amber-500/20">
                                    {{ $st['origin_code'] }}
                                </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-zinc-400 mt-1 flex items-center gap-1.5">
                                <span>{{ $st['material_code'] }}</span>
                                <span>•</span>
                                <span class="text-cyan-400 font-mono">{{ $st['pack_type'] }}</span>
                            </div>
                        </td>

                        <!-- 4. Standar Berat Per Sak (Konfigurasi Batch) -->
                        <td class="px-3.5 py-3.5 text-center font-mono">
                            <div class="inline-block bg-zinc-950 px-2.5 py-1 rounded-xl border border-zinc-800 text-[11px]">
                                <div class="font-bold text-amber-400">
                                    {{ number_format($st['std_gross_per_sack'], 2, ',', '.') }} <span class="text-[9px] text-zinc-400">kg Gross/Sak</span>
                                </div>
                                <div class="text-[10px] text-zinc-400 mt-0.5">
                                    Net: <span class="text-emerald-400 font-semibold">{{ number_format($st['std_netto_per_sack'], 2, ',', '.') }} kg</span> • Tar: {{ number_format($st['std_tare_per_sack'], 2, ',', '.') }} kg
                                </div>
                            </div>
                        </td>

                        <!-- 5. Total Output Produksi -->
                        <td class="px-3.5 py-3.5 text-right font-mono">
                            <div class="font-bold text-zinc-200">
                                {{ number_format($st['produced_netto_kg'], 2, ',', '.') }} kg <span class="text-[10px] text-zinc-400">Net</span>
                            </div>
                            <div class="text-[11px] font-bold text-cyan-400">
                                {{ $st['produced_sacks'] }} {{ $st['pack_type'] }}
                            </div>
                            <div class="text-[9px] text-zinc-500">
                                Gross: {{ number_format($st['produced_gross_kg'], 2, ',', '.') }} kg
                            </div>
                        </td>

                        <!-- 6. Total Terkirim via DN -->
                        <td class="px-3.5 py-3.5 text-right font-mono">
                            <div class="font-bold {{ $st['shipped_netto_kg'] > 0 ? 'text-amber-400' : 'text-zinc-500' }}">
                                {{ number_format($st['shipped_netto_kg'], 2, ',', '.') }} kg
                            </div>
                            <div class="text-[11px] text-zinc-400">
                                {{ $st['shipped_sacks'] }} {{ $st['pack_type'] }}
                                @if($st['dn_count'] > 0)
                                <span class="text-[9px] px-1.5 py-0.2 rounded bg-zinc-800 text-zinc-300 ml-0.5 border border-zinc-700">
                                    {{ $st['dn_count'] }} DN
                                </span>
                                @endif
                            </div>
                        </td>

                        <!-- 7. Sisa Stock di Gudang -->
                        <td class="px-3.5 py-3.5 text-right font-mono">
                            <div class="text-sm font-black {{ $st['remaining_netto_kg'] > 0 ? 'text-emerald-400' : 'text-zinc-500' }}">
                                {{ number_format($st['remaining_netto_kg'], 2, ',', '.') }} kg
                            </div>
                            <div class="text-[11px] font-black text-cyan-400">
                                {{ $st['remaining_sacks'] }} {{ $st['pack_type'] }}
                            </div>
                            <div class="text-[9px] text-zinc-500">
                                Gross: {{ number_format($st['remaining_gross_kg'], 2, ',', '.') }} kg
                            </div>
                        </td>

                        <!-- 8. Status Stock Badge -->
                        <td class="px-3.5 py-3.5 text-center">
                            @if($st['status'] === 'available')
                                <span class="px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-bold text-[10px] inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>Tersedia</span>
                                </span>
                            @elseif($st['status'] === 'partial')
                                <span class="px-2 py-0.5 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/30 font-bold text-[10px] inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                    <span>Sisa Sebagian</span>
                                </span>
                            @elseif($st['status'] === 'depleted')
                                <span class="px-2 py-0.5 rounded-lg bg-zinc-800 text-zinc-400 border border-zinc-700 font-semibold text-[10px] inline-flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-zinc-500"></span>
                                    <span>Habis</span>
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-lg bg-zinc-900 text-zinc-500 border border-zinc-800 text-[9px]">
                                    Belum Output
                                </span>
                            @endif
                        </td>

                        <!-- 9. Actions -->
                        <td class="px-3.5 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button type="button" wire:click="showBatchStockDetail({{ $st['batch_id'] }})" title="Lihat Rincian Riwayat Stock" class="p-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-300 border border-zinc-700 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                
                                @if($st['remaining_sacks'] > 0)
                                <a href="{{ route('admin.dn-shipments') }}" title="Buat DN untuk Batch Ini" class="p-1.5 rounded-lg bg-amber-600/20 hover:bg-amber-600 text-amber-400 hover:text-white border border-amber-500/30 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-zinc-500">
                            <svg class="w-12 h-12 mx-auto mb-3 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            <p class="text-sm font-semibold text-zinc-400">Tidak ada data stock produk yang cocok</p>
                            <p class="text-xs text-zinc-500 mt-1">Coba sesuaikan kata kunci pencarian atau reset filter di atas</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($stockItems->hasPages())
        <div class="p-4 border-t border-zinc-800 bg-zinc-950/60">
            {{ $stockItems->links() }}
        </div>
        @endif
    </div>

    <!-- DETAIL STOCK & RIWAYAT DN MODAL -->
    @if($showDetailModal && $selectedBatchStock)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl max-w-2xl w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Modal Header -->
            <div class="p-5 border-b border-zinc-800 flex items-center justify-between bg-zinc-950">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-zinc-100 font-mono tracking-wide">Rincian Stock Batch {{ $selectedBatchStock['batch_code'] }}</h3>
                        <p class="text-xs text-zinc-400">{{ $selectedBatchStock['customer_name'] }} • {{ $selectedBatchStock['origin'] }} ({{ $selectedBatchStock['origin_code'] }})</p>
                    </div>
                </div>
                <button type="button" wire:click="closeDetailModal" class="p-2 text-zinc-400 hover:text-white rounded-xl hover:bg-zinc-800 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 overflow-y-auto space-y-5 flex-1">
                <!-- Konfigurasi Standar Berat per Sak (Basis Konversi) -->
                <div class="bg-gradient-to-r from-amber-950/40 via-zinc-950 to-emerald-950/40 border border-amber-500/30 rounded-2xl p-4 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase text-amber-400 tracking-wider">Standar Berat Per Sak (Konfigurasi Batch)</span>
                        <span class="text-[10px] text-zinc-400 font-mono">Basis Hitungan Jumlah Sak</span>
                    </div>
                    <div class="grid grid-cols-3 gap-2.5 font-mono text-xs">
                        <div class="bg-zinc-900/90 p-2.5 rounded-xl border border-zinc-800">
                            <span class="text-[10px] text-amber-400 font-bold block uppercase">Gross Per Sak</span>
                            <strong class="text-zinc-100 text-sm block mt-0.5">{{ number_format($selectedBatchStock['std_gross_per_sack'], 2, ',', '.') }} kg</strong>
                            <span class="text-[9px] text-zinc-400">Berat Gross / Sak</span>
                        </div>
                        <div class="bg-zinc-900/90 p-2.5 rounded-xl border border-zinc-800">
                            <span class="text-[10px] text-zinc-400 font-bold block uppercase">Tare Per Sak</span>
                            <strong class="text-zinc-300 text-sm block mt-0.5">{{ number_format($selectedBatchStock['std_tare_per_sack'], 2, ',', '.') }} kg</strong>
                            <span class="text-[9px] text-zinc-500">Tara Pembungkus</span>
                        </div>
                        <div class="bg-zinc-900/90 p-2.5 rounded-xl border border-zinc-800">
                            <span class="text-[10px] text-emerald-400 font-bold block uppercase">Netto Per Sak</span>
                            <strong class="text-emerald-400 text-sm block mt-0.5">{{ number_format($selectedBatchStock['std_netto_per_sack'], 2, ',', '.') }} kg</strong>
                            <span class="text-[9px] text-zinc-400">Netto Bersih Standar</span>
                        </div>
                    </div>
                    <div class="text-[11px] text-zinc-300 pt-1.5 flex flex-wrap items-center justify-between gap-1 border-t border-zinc-800/80 font-mono">
                        <span>Konversi: <strong>{{ $selectedBatchStock['produced_std_sacks'] }} Sak Standar</strong> (@ {{ number_format($selectedBatchStock['std_gross_per_sack'], 2) }} kg Gross)</span>
                        @if($selectedBatchStock['has_remnant'])
                        <span class="text-amber-300 font-bold">+ 1 Sak Remnant ({{ number_format($selectedBatchStock['remnant_netto_kg'], 2) }} kg Net)</span>
                        @endif
                    </div>
                </div>

                <!-- Stock Summary Grid -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="p-3.5 rounded-2xl bg-zinc-950 border border-zinc-800 font-mono">
                        <span class="text-[10px] text-zinc-400 uppercase font-bold block">Hasil Produksi</span>
                        <span class="text-base font-black text-zinc-100 block mt-1">{{ number_format($selectedBatchStock['produced_netto_kg'], 2) }} kg</span>
                        <span class="text-xs text-cyan-400">{{ $selectedBatchStock['produced_sacks'] }} {{ $selectedBatchStock['pack_type'] }}</span>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-zinc-950 border border-amber-500/20 font-mono">
                        <span class="text-[10px] text-amber-400 uppercase font-bold block">Terkirim (DN)</span>
                        <span class="text-base font-black text-amber-400 block mt-1">{{ number_format($selectedBatchStock['shipped_netto_kg'], 2) }} kg</span>
                        <span class="text-xs text-cyan-400">{{ $selectedBatchStock['shipped_sacks'] }} {{ $selectedBatchStock['pack_type'] }}</span>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-zinc-950 border border-emerald-500/20 font-mono">
                        <span class="text-[10px] text-emerald-400 uppercase font-bold block">Sisa di Gudang</span>
                        <span class="text-base font-black text-emerald-400 block mt-1">{{ number_format($selectedBatchStock['remaining_netto_kg'], 2) }} kg</span>
                        <span class="text-xs text-cyan-400">{{ $selectedBatchStock['remaining_sacks'] }} {{ $selectedBatchStock['pack_type'] }}</span>
                    </div>
                </div>

                <!-- Riwayat Surat Jalan (DN) Terkait -->
                <div>
                    <h4 class="text-xs font-bold uppercase text-zinc-300 tracking-wider mb-2.5 flex items-center justify-between">
                        <span>Riwayat Pengiriman (Surat Jalan DN)</span>
                        <span class="text-[11px] font-normal text-zinc-400">{{ count($selectedBatchStock['linked_dns']) }} Transaksi</span>
                    </h4>

                    @if(count($selectedBatchStock['linked_dns']) > 0)
                    <div class="bg-zinc-950 rounded-2xl border border-zinc-800 overflow-hidden divide-y divide-zinc-800/60 font-mono text-xs">
                        @foreach($selectedBatchStock['linked_dns'] as $ldn)
                        <div class="p-3 flex items-center justify-between gap-3 hover:bg-zinc-900/60 transition-colors">
                            <div class="flex items-center gap-2.5">
                                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </span>
                                <div>
                                    <div class="font-bold text-zinc-200">{{ $ldn['dn_number'] }}</div>
                                    <div class="text-[10px] text-zinc-500">{{ $ldn['shipment_date'] }}</div>
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="font-bold text-amber-400">{{ number_format($ldn['netto_kg'], 2) }} kg</div>
                                <div class="text-[10px] text-cyan-400">{{ $ldn['sacks'] }} Karung</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="p-6 rounded-2xl bg-zinc-950 border border-zinc-800 text-center text-zinc-500 text-xs">
                        Belum ada Surat Jalan (DN) yang diterbitkan untuk batch ini. Seluruh stock masih utuh di gudang.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-zinc-800 bg-zinc-950 flex items-center justify-between">
                <span class="text-xs text-zinc-400">Status: <strong class="text-{{ $selectedBatchStock['status_color'] }}-400">{{ $selectedBatchStock['status_label'] }}</strong></span>
                <button type="button" wire:click="closeDetailModal" class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-white font-bold text-xs transition-all">
                    Tutup
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
