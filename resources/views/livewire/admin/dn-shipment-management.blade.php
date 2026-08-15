<div class="space-y-6">

    <!-- PAGE HEADER -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-2xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center shrink-0 shadow-lg shadow-amber-500/10">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-black text-zinc-100 tracking-wide uppercase">DN SHIPMENT (SURAT JALAN PENGIRIMAN)</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-950 text-amber-300 border border-amber-800">
                            LOGISTIK KELUAR
                        </span>
                    </div>
                    <p class="text-xs text-zinc-400 mt-0.5">Pembuatan Surat Jalan Pengiriman Produk Jadi & Byproduct Tembakau dengan Rincian Karung Otomatis & Multi-Lot</p>
                </div>
            </div>

            <!-- ACTION BUTTON -->
            <button wire:click="openCreateModal" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-black text-xs sm:text-sm tracking-wide transition-all shadow-xl shadow-amber-950/60 flex items-center justify-center gap-2 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>➕ Buat DN Pengiriman Baru</span>
            </button>
        </div>
    </div>

    <!-- 3 TOP AGGREGATE KPI CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- 1. Total Dokumen DN -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider block">Total Surat Jalan</span>
                <span class="font-mono font-black text-2xl text-amber-400 mt-1 block">{{ number_format($totalShipmentsCount) }}</span>
                <span class="text-[11px] text-zinc-500 mt-0.5 block">Dokumen Pengiriman Terbit</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-950/60 border border-amber-800/60 flex items-center justify-center text-amber-400 text-xl shadow-inner">
                📑
            </div>
        </div>

        <!-- 2. Total Karung Terkirim -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider block">Total Karung Terkirim</span>
                <span class="font-mono font-black text-2xl text-cyan-400 mt-1 block">{{ number_format($totalSacksShipped) }} <span class="text-xs text-zinc-400 font-sans font-bold">Krg</span></span>
                <span class="text-[11px] text-zinc-500 mt-0.5 block">Termasuk Karung Remnant</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-cyan-950/60 border border-cyan-800/60 flex items-center justify-center text-cyan-400 text-xl shadow-inner">
                📦
            </div>
        </div>

        <!-- 3. Total Netto Pengiriman (kg) -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-zinc-400 uppercase tracking-wider block">Total Berat Netto</span>
                <span class="font-mono font-black text-2xl text-emerald-400 mt-1 block">{{ number_format($totalNettoShipped, 2, ',', '.') }} <span class="text-xs text-zinc-400 font-sans font-bold">kg</span></span>
                <span class="text-[11px] text-zinc-500 mt-0.5 block">Hasil Produksi Terkirim</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-950/60 border border-emerald-800/60 flex items-center justify-center text-emerald-400 text-xl shadow-inner">
                ⚖️
            </div>
        </div>
    </div>

    <!-- FILTER & SEARCH BAR -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-4 sm:p-5 shadow-xl grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 items-end">
        <!-- Search -->
        <div class="sm:col-span-2">
            <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Cari No DN / Pelanggan / Sopir / Origin</label>
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Ketik No DN, Customer, Plat No, Origin..." class="w-full pl-9 pr-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                <svg class="w-4 h-4 text-zinc-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>

        <!-- Filter Customer -->
        <div>
            <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Pelanggan</label>
            <select wire:model.live="filterCustomerId" class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                <option value="">Semua Pelanggan</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filter Status -->
        <div>
            <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Status</label>
            <select wire:model.live="filterStatus" class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                <option value="">Semua Status</option>
                <option value="Shipped">Shipped</option>
                <option value="Delivered">Delivered</option>
                <option value="Approved">Approved</option>
                <option value="Draft">Draft</option>
            </select>
        </div>

        <!-- Filter Date From -->
        <div>
            <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Dari Tanggal</label>
            <input type="date" wire:model.live="filterDateFrom" onclick="if(this.showPicker) this.showPicker()" style="color-scheme: dark;" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none cursor-pointer">
        </div>

        <!-- Reset Button -->
        <div class="flex items-center">
            <button type="button" wire:click="resetFilters" class="w-full py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-bold transition-all flex items-center justify-center gap-1 shadow">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                <span>Reset Filter</span>
            </button>
        </div>
    </div>

    <!-- DATA TABLE OF DN SHIPMENTS -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-800 pb-3">
            <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">Daftar Surat Jalan Pengiriman (DN Shipments)</h3>
            <span class="text-xs font-mono font-bold text-zinc-400">Menampilkan {{ $shipments->total() }} Data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-zinc-300 font-sans">
                <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800 text-[11px]">
                    <tr>
                        <th class="px-4 py-3.5">No. Surat Jalan</th>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Pelanggan / Tujuan</th>
                        <th class="px-4 py-3.5">Kendaraan & Sopir</th>
                        <th class="px-4 py-3.5">Rincian Lot / Origin</th>
                        <th class="px-4 py-3.5 text-center">Total Karung</th>
                        <th class="px-4 py-3.5 text-right">Gross (kg)</th>
                        <th class="px-4 py-3.5 text-right">Netto (kg)</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80">
                    @forelse($shipments as $shp)
                        <tr class="hover:bg-zinc-800/40 transition-colors">
                            <!-- No DN -->
                            <td class="px-4 py-3.5 font-mono font-black text-amber-400 whitespace-nowrap">
                                {{ $shp->dn_number }}
                            </td>

                            <!-- Tanggal -->
                            <td class="px-4 py-3.5 font-mono text-zinc-300 whitespace-nowrap">
                                {{ $shp->shipment_date ? $shp->shipment_date->format('d/m/Y') : '-' }}
                            </td>

                            <!-- Customer & Tujuan -->
                            <td class="px-4 py-3.5">
                                <span class="font-bold text-zinc-100 block">{{ $shp->customer->name ?? 'PT Falih Nur Gemilang' }}</span>
                                @if($shp->destination)
                                    <span class="text-[10px] text-zinc-400 block truncate max-w-xs">{{ $shp->destination }}</span>
                                @endif
                            </td>

                            <!-- Kendaraan & Sopir -->
                            <td class="px-4 py-3.5 font-mono text-zinc-300">
                                @if($shp->vehicle_number)
                                    <span class="px-2 py-0.5 rounded bg-zinc-950 border border-zinc-800 font-bold text-amber-300 text-[11px] block w-fit">{{ $shp->vehicle_number }}</span>
                                @endif
                                <span class="text-[11px] text-zinc-400 block mt-0.5">{{ $shp->driver_name ?: '-' }}</span>
                            </td>

                            <!-- Lot / Origin Summary Pills -->
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap gap-1 max-w-sm">
                                    @foreach($shp->items as $it)
                                        <span class="px-2 py-0.5 rounded-lg bg-zinc-950 border border-zinc-800 text-[10px] font-mono text-zinc-300 flex items-center gap-1">
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold {{ ($it->material_type ?? 'Product') === 'Product' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800/80' : (($it->material_type ?? '') === 'Bits / Stem' ? 'bg-amber-950 text-amber-300 border border-amber-800/80' : 'bg-zinc-800 text-cyan-300 border border-zinc-700') }}">
                                                {{ ($it->material_type ?? 'Product') === 'Product' ? '🍃 Produk' : (($it->material_type ?? '') === 'Bits / Stem' ? '🌿 Bits/Stem' : '💨 Dust') }}
                                            </span>
                                            <strong class="text-amber-400">{{ $it->origin }}</strong> ({{ $it->origin_code }}): 
                                            <span class="text-emerald-400 font-bold">{{ $it->total_sacks }} Krg</span>
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Total Karung -->
                            <td class="px-4 py-3.5 text-center font-mono font-bold text-cyan-400 text-sm">
                                {{ $shp->total_sacks }} Krg
                            </td>

                            <!-- Gross kg -->
                            <td class="px-4 py-3.5 text-right font-mono text-zinc-400">
                                {{ number_format($shp->total_gross_kg, 2, ',', '.') }}
                            </td>

                            <!-- Netto kg -->
                            <td class="px-4 py-3.5 text-right font-mono font-black text-emerald-400 text-sm">
                                {{ number_format($shp->total_netto_kg, 2, ',', '.') }} kg
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3.5 text-center">
                                @if($shp->isApprovedByCustomer())
                                    <div class="inline-flex flex-col items-center">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-950 text-emerald-300 border border-emerald-600/80 shadow">
                                            ✅ Approved
                                        </span>
                                        @if($shp->customer_approved_at)
                                            <span class="text-[9px] text-zinc-400 font-mono mt-0.5">{{ $shp->customer_approved_at->format('d/m/y H:i') }}</span>
                                        @endif
                                    </div>
                                @elseif($shp->status === 'Shipped')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-950 text-amber-300 border border-amber-600/80 shadow">
                                        🚚 Shipped
                                    </span>
                                @elseif($shp->status === 'Delivered')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-950 text-blue-300 border border-blue-600/80 shadow">
                                        📦 Delivered
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-zinc-800 text-zinc-300 border border-zinc-700">
                                        {{ $shp->status }}
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap space-x-1.5">
                                <!-- Preview -->
                                <button type="button" wire:click="openPreviewModal({{ $shp->id }})" title="Pratinjau Surat Jalan" class="p-2 rounded-xl bg-zinc-950 border border-zinc-800 text-amber-400 hover:bg-amber-950 hover:text-amber-200 transition-all">
                                    👁️
                                </button>
                                <!-- Download PDF -->
                                <a href="{{ route('dn-shipments.pdf', $shp->id) }}" target="_blank" title="Unduh PDF Surat Jalan" class="p-2 inline-flex items-center rounded-xl bg-zinc-950 border border-zinc-800 text-emerald-400 hover:bg-emerald-950 hover:text-emerald-200 transition-all">
                                    📥
                                </a>
                                <!-- Edit -->
                                <button type="button" wire:click="openEditModal({{ $shp->id }})" title="Edit DN" class="p-2 rounded-xl bg-zinc-950 border border-zinc-800 text-blue-400 hover:bg-blue-950 hover:text-blue-200 transition-all">
                                    ✏️
                                </button>
                                <!-- Delete -->
                                <button type="button" wire:click="confirmDelete({{ $shp->id }})" title="Hapus DN" class="p-2 rounded-xl bg-zinc-950 border border-zinc-800 text-red-400 hover:bg-red-950 hover:text-red-200 transition-all">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-zinc-500 font-sans">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <span class="text-3xl">📦</span>
                                    <p class="font-bold text-zinc-400 text-sm">Belum ada dokumen Surat Jalan Pengiriman (DN Shipment).</p>
                                    <p class="text-xs text-zinc-500">Klik tombol "➕ Buat DN Pengiriman Baru" di atas untuk membuat surat jalan baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-3 border-t border-zinc-800/80">
            {{ $shipments->links() }}
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL CREATE & EDIT DN SHIPMENT -->
    <!-- ========================================================================= -->
    @if($showCreateModal || $showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm overflow-y-auto">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl w-full max-w-5xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden my-6">
            
            <!-- Modal Header -->
            <div class="p-5 border-b border-zinc-800 flex items-center justify-between bg-zinc-950 shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 text-lg">
                        📄
                    </div>
                    <div>
                        <h3 class="text-base font-black text-amber-400 uppercase tracking-wide">
                            {{ $showEditModal ? 'Edit Surat Jalan Pengiriman (DN Shipment)' : 'Buat Surat Jalan Pengiriman Baru (DN Shipment)' }}
                        </h3>
                        <p class="text-xs text-zinc-400">Pengisian nomor surat jalan, data kendaraan, dan multi-lot origin dengan karung otomatis</p>
                    </div>
                </div>
                <button type="button" wire:click="$set('showCreateModal', false); $set('showEditModal', false)" class="p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-800 text-lg">&times;</button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="flex-1 p-5 sm:p-7 overflow-y-auto space-y-6 bg-zinc-900">
                
                <!-- BAGIAN 1: HEADER SURAT JALAN -->
                <div class="bg-zinc-950/80 border border-zinc-800 rounded-2xl p-4 sm:p-5 space-y-4">
                    <h4 class="text-xs font-black text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                        <span>1. Informasi Header Surat Jalan</span>
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- No Surat Jalan -->
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-300 uppercase mb-1">No. Surat Jalan (DN) <span class="text-zinc-500 font-normal text-[10px]">(Opsional)</span></label>
                            <input type="text" wire:model="dn_number" placeholder="Contoh: 001/SJ/2026 atau kosongkan" class="w-full px-3 py-2.5 rounded-xl bg-zinc-900 border border-zinc-700 text-amber-300 font-mono font-bold text-xs focus:border-amber-500 outline-none">
                            @error('dn_number') <span class="text-red-400 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tanggal Kirim -->
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-300 uppercase mb-1">Tanggal Pengiriman <span class="text-red-400">*</span></label>
                            <input type="date" wire:model="shipment_date" onclick="if(this.showPicker) this.showPicker()" style="color-scheme: dark;" class="w-full px-3 py-2 rounded-xl bg-zinc-900 border border-zinc-700 text-zinc-200 text-xs focus:border-amber-500 outline-none cursor-pointer">
                            @error('shipment_date') <span class="text-red-400 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Customer -->
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-300 uppercase mb-1">Pelanggan / Customer</label>
                            <select wire:model.live="customer_id" class="w-full px-3 py-2.5 rounded-xl bg-zinc-900 border border-zinc-700 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                                <option value="">Pilih Pelanggan</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Jenis Produk -->
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-300 uppercase mb-1">Jenis Produk</label>
                            <select wire:model="product_type_id" class="w-full px-3 py-2.5 rounded-xl bg-zinc-900 border border-zinc-700 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                                <option value="">Pilih Jenis Produk</option>
                                @foreach($productTypes as $pt)
                                    <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- No Kendaraan -->
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-300 uppercase mb-1">No. Kendaraan (Plat Polisi)</label>
                            <input type="text" wire:model="vehicle_number" placeholder="Contoh: N 8123 AB" class="w-full px-3 py-2.5 rounded-xl bg-zinc-900 border border-zinc-700 text-zinc-200 font-mono text-xs focus:border-amber-500 outline-none">
                        </div>

                        <!-- Nama Sopir -->
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-300 uppercase mb-1">Nama Pengemudi / Sopir</label>
                            <input type="text" wire:model="driver_name" placeholder="Nama sopir..." class="w-full px-3 py-2.5 rounded-xl bg-zinc-900 border border-zinc-700 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                        </div>

                        <!-- Alamat Tujuan -->
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold text-zinc-300 uppercase mb-1">Alamat Tujuan Pengiriman</label>
                            <input type="text" wire:model="destination" placeholder="Alamat pabrik / gudang customer..." class="w-full px-3 py-2.5 rounded-xl bg-zinc-900 border border-zinc-700 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 2: RINCIAN LOT / ORIGIN PENGIRIMAN (MULTI-ORIGIN / LOTS) -->
                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h4 class="text-xs font-black text-amber-400 uppercase tracking-wider flex items-center gap-2">
                                <span>2. Rincian Lot / Origin Pengiriman (Multi-Origin / Lots)</span>
                            </h4>
                            <p class="text-[11px] text-zinc-400">Panggil dari No. Batch (otomatis menampilkan 1, 2, atau 3 lot sesuai data batch) atau pilih Asal Utama & Kode Material via dropdown</p>
                        </div>

                        <button type="button" wire:click="addItem" class="px-3.5 py-2 rounded-xl bg-amber-950 text-amber-300 border border-amber-800 hover:bg-amber-900 text-xs font-bold transition-all flex items-center gap-1.5 shadow self-start sm:self-auto">
                            <span>➕ Tambah Lot / Origin</span>
                        </button>
                    </div>

                    <!-- Items Container -->
                    <div class="space-y-4">
                        @foreach($items as $index => $item)
                            @php
                                $currentOrigin = $item['origin'] ?? 'Lombok';
                                $codesList = $originCodesMap[$currentOrigin] ?? [];
                                if (!empty($item['origin_code']) && !in_array($item['origin_code'], $codesList)) {
                                    $codesList[] = $item['origin_code'];
                                }
                            @endphp
                            <div class="bg-zinc-950 border border-zinc-800 hover:border-zinc-700 rounded-2xl p-4 sm:p-5 space-y-4 relative shadow-lg">
                                <!-- Row Top Bar -->
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-zinc-800 pb-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-amber-600 text-white shadow">
                                            Lot #{{ $item['item_no'] }}
                                        </span>
                                        <span class="text-xs font-bold text-zinc-200">
                                            {{ $item['origin'] ?: 'Pilih Asal Utama' }} — <span class="font-mono text-cyan-400">{{ $item['origin_code'] ?: '-' }}</span>
                                        </span>
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider
                                            {{ ($item['material_type'] ?? 'Product') === 'Product' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' :
                                               (($item['material_type'] ?? '') === 'Bits / Stem' ? 'bg-amber-950 text-amber-300 border border-amber-800' : 'bg-zinc-800 text-cyan-300 border border-zinc-700') }}">
                                            {{ ($item['material_type'] ?? 'Product') === 'Product' ? '🍃 Produk' : (($item['material_type'] ?? '') === 'Bits / Stem' ? '🌿 Bits/Stem' : '💨 Dust') }}
                                        </span>
                                        @if(!empty($item['batch_code']))
                                            <span class="px-2.5 py-0.5 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-400 font-mono text-[10px] font-bold flex items-center gap-1">
                                                <span>🔗 Batch: {{ $item['batch_code'] }}</span>
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <!-- Manggil Batch Sumber Selector -->
                                        <div class="flex items-center gap-1.5">
                                            <label class="text-[10px] font-bold uppercase text-amber-400 whitespace-nowrap">Kode Batch / No. Batch:</label>
                                            <select wire:change="selectBatchForLot({{ $index }}, $event.target.value)" class="px-2.5 py-1.5 rounded-xl bg-zinc-900 border border-amber-500/40 text-amber-300 font-mono text-xs focus:border-amber-400 outline-none max-w-[240px]">
                                                <option value="">-- Panggil Batch (Otomatis) --</option>
                                                @foreach($availableBatches as $ab)
                                                    <option value="{{ $ab->id }}" {{ ($item['batch_id'] ?? null) == $ab->id ? 'selected' : '' }}>
                                                        {{ $ab->batch_code }} ({{ $ab->origin->region_name ?? '-' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @if(count($items) > 1)
                                            <button type="button" wire:click="removeItem({{ $index }})" class="p-1.5 px-2.5 rounded-xl bg-red-950/80 text-red-400 border border-red-800/80 hover:bg-red-900 text-xs font-bold transition-all flex items-center gap-1" title="Hapus Lot">
                                                <span>✕ Hapus</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Inputs Grid -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                                    <!-- 1. Origin Dropdown -->
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-amber-400 mb-1">1. Asal Utama (Origin)</label>
                                        <select wire:change="selectOriginForLot({{ $index }}, $event.target.value)" class="w-full px-3 py-2 rounded-xl bg-zinc-900 border border-zinc-700 text-amber-300 font-bold text-xs focus:border-amber-500 outline-none">
                                            @foreach($distinctOrigins as $do)
                                                <option value="{{ $do }}" {{ ($item['origin'] ?? '') === $do ? 'selected' : '' }}>{{ $do }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- 2. Origin Code Dropdown (Cascading) -->
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-cyan-400 mb-1">2. Kode Material</label>
                                        <select wire:model.live="items.{{ $index }}.origin_code" class="w-full px-3 py-2 rounded-xl bg-zinc-900 border border-zinc-700 text-cyan-300 font-mono font-bold text-xs focus:border-cyan-500 outline-none">
                                            @foreach($codesList as $code)
                                                <option value="{{ $code }}">{{ $code }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- 3. Jenis Muatan / Material Type Dropdown -->
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-purple-400 mb-1">3. Jenis Muatan</label>
                                        <select wire:change="selectMaterialTypeForLot({{ $index }}, $event.target.value)" class="w-full px-3 py-2 rounded-xl bg-zinc-900 border border-purple-500/50 text-purple-300 font-bold text-xs focus:border-purple-400 outline-none">
                                            @foreach($availableMaterialTypes as $amt)
                                                <option value="{{ $amt->code }}" {{ ($item['material_type'] ?? 'Product') === $amt->code ? 'selected' : '' }}>
                                                    @if($amt->code === 'Product') 🍃 @elseif($amt->code === 'Bits / Stem') 🌿 @elseif($amt->code === 'Dust') 💨 @else 📦 @endif {{ $amt->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- 4. Standard Sack Count -->
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-emerald-400 mb-1">4. Karung (Jml Utuh)</label>
                                        <input type="number" min="1" step="1" wire:model.live="items.{{ $index }}.standard_sack_count" class="w-full px-3 py-2 rounded-xl bg-zinc-900 border border-emerald-500/50 text-emerald-300 font-mono font-bold text-xs focus:border-emerald-500 outline-none">
                                    </div>

                                    <!-- 5. Gross per Sack -->
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Gross / Krg (kg)</label>
                                        <input type="number" step="0.01" wire:model.live="items.{{ $index }}.standard_gross_per_sack" class="w-full px-3 py-2 rounded-xl bg-zinc-900 border border-zinc-700 text-zinc-200 font-mono text-xs focus:border-amber-500 outline-none">
                                    </div>

                                    <!-- 6. Tare per Sack -->
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Tare / Krg (kg)</label>
                                        <input type="number" step="0.01" wire:model.live="items.{{ $index }}.standard_tare_per_sack" class="w-full px-3 py-2 rounded-xl bg-zinc-900 border border-zinc-700 text-zinc-200 font-mono text-xs focus:border-amber-500 outline-none">
                                    </div>

                                    <!-- 7. Calculated Netto per Sack (Readonly) -->
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Netto / Krg (kg)</label>
                                        <div class="px-3 py-2 rounded-xl bg-zinc-900/60 border border-zinc-800 text-emerald-400 font-mono font-black text-xs">
                                            {{ number_format($item['standard_netto_per_sack'] ?? 50.0, 2) }} kg
                                        </div>
                                    </div>
                                </div>

                                <!-- Remnant Sack Option (Karung Sisa) -->
                                <div class="bg-zinc-900/80 border border-zinc-800/80 rounded-xl p-3 space-y-2">
                                    <label class="flex items-center space-x-2 cursor-pointer select-none">
                                        <input type="checkbox" wire:model.live="items.{{ $index }}.has_remnant" class="rounded border-zinc-700 text-amber-600 focus:ring-amber-500 w-4 h-4 bg-zinc-950">
                                        <span class="text-xs font-bold text-amber-300">Tambahkan Karung Remnant (Sisa) untuk Lot ini</span>
                                    </label>

                                    @if(!empty($item['has_remnant']))
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-zinc-800">
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Remnant Gross (kg)</label>
                                                <input type="number" step="0.01" wire:model.live="items.{{ $index }}.remnant_gross_kg" placeholder="Contoh: 24.50" class="w-full px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-700 text-amber-300 font-mono font-bold text-xs focus:border-amber-500 outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Remnant Tare (kg)</label>
                                                <input type="number" step="0.01" wire:model.live="items.{{ $index }}.remnant_tare_kg" placeholder="Contoh: 0.70" class="w-full px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-700 text-zinc-300 font-mono text-xs focus:border-amber-500 outline-none">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Remnant Netto (kg)</label>
                                                <div class="px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-800 text-emerald-400 font-mono font-bold text-xs">
                                                    {{ number_format($item['remnant_netto_kg'] ?? 0.0, 2) }} kg
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Real-time Lot Subtotal & Stock Status Badge -->
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs bg-zinc-900 px-3.5 py-2.5 rounded-xl border border-zinc-800">
                                        <div class="flex items-center gap-3 font-mono text-[11px] text-zinc-400">
                                            <span>Total Karung: <strong class="text-cyan-400">{{ $item['total_sacks'] }} Krg</strong></span>
                                            <span>•</span>
                                            <span>Gross: <strong class="text-zinc-200">{{ number_format($item['total_gross_kg'], 2) }} kg</strong></span>
                                            <span>•</span>
                                            <span>Tare: <strong class="text-zinc-200">{{ number_format($item['total_tare_kg'], 2) }} kg</strong></span>
                                        </div>
                                        <div class="font-mono text-xs">
                                            <span class="text-zinc-400">Subtotal Netto:</span>
                                            <strong class="text-emerald-400 font-black text-sm ml-1">{{ number_format($item['total_netto_kg'], 2) }} kg</strong>
                                        </div>
                                    </div>

                                    <!-- Stock Info for Selected Batch -->
                                    @php
                                        $lotStock = !empty($item['batch_id']) ? $this->getLotStockInfo((int)$item['batch_id'], (int)($item['total_sacks'] ?? 0), (float)($item['total_netto_kg'] ?? 0)) : null;
                                    @endphp
                                    @if($lotStock)
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 px-3.5 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-[11px] font-mono shadow-inner">
                                        <div class="flex items-center justify-between sm:justify-start gap-2 text-zinc-400">
                                            <span class="text-[10px] text-zinc-500 uppercase font-bold">Jumlah di Gudang:</span>
                                            <span class="text-zinc-200 font-bold">{{ $lotStock['produced_sacks'] }} Karung ({{ number_format($lotStock['produced_netto_kg'], 2) }} kg)</span>
                                        </div>
                                        <div class="flex items-center justify-between sm:justify-start gap-2 text-zinc-400">
                                            <span class="text-[10px] text-amber-500/80 uppercase font-bold">Terkirim Sblmnya:</span>
                                            <span class="text-amber-400 font-bold">{{ $lotStock['shipped_sacks'] }} Karung ({{ number_format($lotStock['shipped_netto_kg'], 2) }} kg)</span>
                                        </div>
                                        <div class="flex items-center justify-between sm:justify-start gap-2 text-zinc-400">
                                            <span class="text-[10px] text-emerald-500/80 uppercase font-bold">Sisa Gudang:</span>
                                            <span class="font-black {{ $lotStock['remaining_sacks_after'] > 0 ? 'text-emerald-400' : ($lotStock['remaining_sacks_after'] == 0 ? 'text-amber-300' : 'text-red-400') }}">
                                                {{ $lotStock['remaining_sacks_after'] }} Karung ({{ number_format($lotStock['remaining_netto_after'], 2) }} kg)
                                            </span>
                                        </div>
                                    </div>
                                    @else
                                    <div class="px-3.5 py-2 rounded-xl bg-zinc-950/60 border border-zinc-800/60 text-[11px] text-zinc-500 italic flex items-center gap-2">
                                        <span>ℹ️ Pilih <strong>Kode Batch / No. Batch</strong> di pojok kanan atas lot ini untuk memuat rincian stok gudang otomatis (Jumlah di Gudang, Terkirim Sblmnya, dan Sisa Gudang).</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- GRAND TOTAL SHIPMENT BAR -->
                <div class="bg-gradient-to-r from-amber-950/40 via-zinc-950 to-emerald-950/40 border border-amber-500/40 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
                    <div>
                        <span class="text-xs font-black uppercase text-amber-400 tracking-wider block">Ringkasan Total Pengiriman (Grand Total)</span>
                        <span class="text-[11px] text-zinc-400 mt-0.5 block">Akumulasi seluruh {{ count($items) }} lot pengiriman di atas</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 font-mono text-right">
                        <div>
                            <span class="text-[10px] text-zinc-400 uppercase block">Total Karung</span>
                            <span class="text-base font-black text-cyan-400">{{ $this->grandTotalSacks }} Krg</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-zinc-400 uppercase block">Total Gross</span>
                            <span class="text-base font-bold text-zinc-200">{{ number_format($this->grandTotalGross, 2) }} kg</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-zinc-400 uppercase block">Total Tare</span>
                            <span class="text-base font-bold text-zinc-400">{{ number_format($this->grandTotalTare, 2) }} kg</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-emerald-400 uppercase block">Total Netto</span>
                            <span class="text-lg font-black text-emerald-400">{{ number_format($this->grandTotalNetto, 2) }} kg</span>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 3: CATATAN TAMBAHAN & STATUS -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold text-zinc-300 uppercase mb-1">Catatan Pengiriman (Remarks)</label>
                        <textarea wire:model="notes" rows="2" placeholder="Catatan tambahan untuk surat jalan pengiriman..." class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-700 text-zinc-200 text-xs focus:border-amber-500 outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-zinc-300 uppercase mb-1">Status Dokumen</label>
                        <select wire:model="status" class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-700 text-amber-300 font-bold text-xs focus:border-amber-500 outline-none">
                            <option value="Shipped">Shipped (Terkirim)</option>
                            <option value="Delivered">Delivered (Telah Diterima)</option>
                            <option value="Approved">Approved (Disetujui)</option>
                            <option value="Draft">Draft (Konsep)</option>
                        </select>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 sm:p-5 border-t border-zinc-800 bg-zinc-950 flex items-center justify-between shrink-0">
                <button type="button" wire:click="$set('showCreateModal', false); $set('showEditModal', false)" class="px-5 py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-bold transition-all">
                    Batal
                </button>

                <button type="button" wire:click="{{ $showEditModal ? 'updateShipment' : 'saveShipment' }}" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-white font-black text-xs tracking-wide transition-all shadow-lg shadow-amber-950/50 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ $showEditModal ? 'Perbarui Surat Jalan' : 'Simpan & Terbitkan Surat Jalan' }}</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- PREVIEW SURAT JALAN MODAL -->
    <!-- ========================================================================= -->
    @if($showPreviewModal && $previewShipmentId)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl w-full max-w-5xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-zinc-800 flex items-center justify-between bg-zinc-950">
                <div class="flex items-center space-x-2">
                    <span class="text-xl">📄</span>
                    <h3 class="text-base font-black text-amber-400">Pratinjau Surat Jalan Pengiriman (DN Shipment)</h3>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('dn-shipments.pdf', $previewShipmentId) }}" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-emerald-950 text-emerald-300 border border-emerald-800 hover:bg-emerald-900 text-xs font-bold transition-all flex items-center gap-1 shadow">
                        <span>📥 Download PDF</span>
                    </a>
                    <button type="button" wire:click="$set('showPreviewModal', false)" class="p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-800 text-lg">&times;</button>
                </div>
            </div>

            <div class="flex-1 p-3 sm:p-5 overflow-y-auto bg-zinc-950/80 flex justify-center items-start">
                <div class="w-full bg-white rounded-2xl overflow-hidden shadow-2xl border border-zinc-700">
                    <iframe src="{{ route('dn-shipments.preview', $previewShipmentId) }}" class="w-full h-[680px] bg-white border-0"></iframe>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- DELETE CONFIRMATION MODAL -->
    <!-- ========================================================================= -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl w-full max-w-md p-6 shadow-2xl space-y-4">
            <div class="flex items-center space-x-3 text-red-400">
                <div class="w-10 h-10 rounded-2xl bg-red-950 border border-red-800 flex items-center justify-center text-lg">
                    ⚠️
                </div>
                <h3 class="text-base font-black text-zinc-100">Konfirmasi Hapus DN</h3>
            </div>
            
            <p class="text-xs text-zinc-300">
                Apakah Anda yakin ingin menghapus data Surat Jalan Pengiriman ini? Tindakan ini tidak dapat dibatalkan.
            </p>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" wire:click="$set('showDeleteModal', false)" class="px-4 py-2 rounded-xl bg-zinc-800 text-zinc-300 text-xs font-bold hover:bg-zinc-700 transition-all">
                    Batal
                </button>
                <button type="button" wire:click="deleteShipment" class="px-4 py-2 rounded-xl bg-red-600 text-white text-xs font-bold hover:bg-red-500 transition-all">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
