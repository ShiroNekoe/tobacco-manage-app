<div x-data="{ showCreateModal: @entangle('showCreateModal'), showCloseModal: @entangle('showCloseModal'), showPdfModal: @entangle('showPdfRemarksModal') }" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black tracking-wide text-zinc-100 flex items-center">
                Manajemen Batch Production (TPMS v2.0)
                <span class="ml-3 px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-purple-950 text-purple-300 border border-purple-800">
                    Akses Admin & Supervisor
                </span>
            </h2>
            <p class="text-xs text-zinc-400 mt-1">Pre-launch MRL verification per sak/bale, selisih berat DN vs MRL, approval Supervisor, dan live preview Sertifikat PDF</p>
        </div>

        <button @click="showCreateModal = true; $wire.openCreateModal()" class="px-5 py-3 min-h-[48px] inline-flex items-center justify-center font-black text-xs rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white hover:from-amber-500 shadow-xl shadow-amber-950/50">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            + Buat Batch Timbangan Baru
        </button>
    </div>

    <!-- Search & Filters -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="w-full md:w-1/2">
            <input type="text" wire:model.live.debounce.300ms="search" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none" placeholder="Cari Kode Batch (BCH-...), Pelanggan, atau Jenis Produk...">
        </div>
        <div class="w-full md:w-auto flex items-center space-x-3">
            <label class="text-xs text-zinc-400 font-bold uppercase">Status Batch:</label>
            <select wire:model.live="statusFilter" class="px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                <option value="">Semua Status</option>
                <option value="OPEN">⚪ OPEN</option>
                <option value="ACTIVE">🟢 ACTIVE</option>
                <option value="WAITING">⏳ WAITING</option>
                <option value="CLOSED">🔒 CLOSED</option>
            </select>
        </div>
    </div>

    <!-- Batches Data Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
            <table class="w-full text-left text-xs text-zinc-300">
                <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase tracking-wider sticky top-0 z-10 border-b border-zinc-800">
                    <tr>
                        <th class="px-4 py-4">Kode Batch</th>
                        <th class="px-4 py-4">Pelanggan & Surat Jalan (DN)</th>
                        <th class="px-4 py-4">Jenis Produk & Asal</th>
                        <th class="px-4 py-4 text-right">DN Gross (kg)</th>
                        <th class="px-4 py-4 text-right">MRL Gross (kg)</th>
                        <th class="px-4 py-4">Keterangan Selisih Berat (DN vs MRL)</th>
                        <th class="px-4 py-4 text-center">Status ACC Supervisor</th>
                        <th class="px-4 py-4 text-center">Aksi / Gate Control</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @forelse($batches as $b)
                        <tr class="hover:bg-zinc-800/50 transition-colors">
                            <td class="px-4 py-4 font-mono font-bold text-amber-400 whitespace-nowrap">
                                {{ $b->batch_code }}
                                <div class="text-[10px] text-zinc-500 font-normal">
                                    {{ $b->date_of_receipt ? $b->date_of_receipt->format('d/m/Y') : '-' }}
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-zinc-200">{{ $b->customer->name ?? '-' }}</div>
                                <div class="text-[11px] text-zinc-500 font-mono">DN: {{ $b->deliveryNote->dn_number ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-zinc-200">{{ $b->productType->name ?? '-' }}</div>
                                <div class="text-[11px] text-amber-400 font-semibold">{{ $b->origin->region_name ?? '-' }} ({{ $b->pack_type }})</div>
                            </td>
                            <td class="px-4 py-4 text-right font-mono font-bold text-zinc-300">
                                {{ number_format($b->dn_gross_weight, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-right font-mono font-bold text-emerald-400 text-sm">
                                {{ number_format($b->mrl_gross_weight, 2, ',', '.') }}
                            </td>
                            <!-- Dynamic Weight Discrepancy Explanation -->
                            <td class="px-4 py-4">
                                @if((float)$b->discrepancy_dn_vs_mrl_kg != 0.0)
                                    <span class="text-[11px] text-red-400 font-bold block">
                                        🔴 {{ number_format($b->discrepancy_dn_vs_mrl_kg, 2) }} kg - {{ $b->discrepancy_explanation }}
                                    </span>
                                @else
                                    <span class="text-[11px] text-emerald-400 font-bold block">
                                        🟢 0,00 kg - {{ $b->discrepancy_explanation }}
                                    </span>
                                @endif
                            </td>
                            <!-- Supervisor Approval Status -->
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                @if($b->isApprovedBySupervisor())
                                    <div class="inline-flex flex-col items-center">
                                        <span class="px-3 py-1.5 rounded-full text-[10px] font-black uppercase bg-emerald-950 text-emerald-300 border border-emerald-800 flex items-center gap-1 shadow-sm">
                                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            APPROVED BY SUPERVISOR
                                        </span>
                                        @if($b->supervisorApprovedBy)
                                            <span class="text-[9px] text-zinc-400 font-mono mt-1 block">
                                                ACC: {{ $b->supervisorApprovedBy->name }} ({{ $b->supervisor_approved_at ? $b->supervisor_approved_at->format('d/m/y H:i') : '-' }})
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    @if(auth()->user() && (auth()->user()->isSupervisor() || auth()->user()->isAdmin()))
                                        <button type="button" 
                                                wire:click="approveCertificate({{ $b->id }})"
                                                wire:loading.attr="disabled"
                                                title="Klik untuk ACC Batch ini oleh Supervisor"
                                                class="px-3.5 py-1.5 rounded-full text-[11px] font-black uppercase bg-gradient-to-r from-emerald-600 to-emerald-700 text-white hover:from-emerald-500 hover:to-emerald-600 shadow-lg shadow-emerald-950/60 border border-emerald-500/40 transition-all inline-flex items-center gap-1.5 cursor-pointer transform hover:scale-105 active:scale-95">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            ACC SUPERVISOR
                                        </button>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-amber-950 text-amber-300 border border-amber-800 animate-pulse">
                                            ⏳ PENDING SUPERVISOR ACC
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center">
                                <div class="flex flex-wrap items-center justify-center gap-1.5 max-w-[210px] mx-auto">
                                    <a href="{{ route('karyawan.weighing', ['batch_id' => $b->id]) }}" 
                                       title="Buka Lembar Timbangan Lapangan"
                                       class="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-zinc-800 text-zinc-200 hover:bg-zinc-700 border border-zinc-700 transition-colors inline-flex items-center gap-1 shrink-0">
                                        📋 Timbangan
                                    </a>

                                    <button wire:click="openPdfRemarksModal({{ $b->id }})" 
                                            title="Preview Visual & Cetak Certificate PDF"
                                            class="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-red-950/80 text-red-300 border border-red-800/80 hover:bg-red-900 shadow transition-colors inline-flex items-center gap-1 shrink-0">
                                        👁️ Preview PDF
                                    </button>

                                    @if(auth()->user() && (auth()->user()->isSupervisor() || auth()->user()->isAdmin()))
                                        @if(!$b->isApprovedBySupervisor())
                                            <button wire:click="approveCertificate({{ $b->id }})" 
                                                    title="ACC / Setujui Sertifikat Process Certificate"
                                                    class="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-emerald-950/80 text-emerald-300 border border-emerald-800/80 hover:bg-emerald-900 shadow transition-colors inline-flex items-center gap-1 shrink-0">
                                                ✅ ACC
                                            </button>
                                        @else
                                            <button wire:click="revokeCertificateApproval({{ $b->id }})" 
                                                    title="Batalkan ACC Supervisor"
                                                    class="px-2 py-1.5 text-[10px] font-bold rounded-lg bg-zinc-800 text-zinc-400 border border-zinc-700 hover:bg-amber-950 hover:text-amber-300 hover:border-amber-800 shadow transition-colors inline-flex items-center gap-1 shrink-0">
                                                ↩️ Batal ACC
                                            </button>
                                        @endif
                                    @endif

                                    @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isSupervisor()))
                                        <button type="button"
                                            @click="
                                                Swal.fire({
                                                    title: 'Konfirmasi Hapus Batch',
                                                    html: 'Apakah Anda yakin ingin menghapus <b>{{ $b->batch_code }}</b>?<br><span class=\'text-xs text-rose-400 mt-2 block\'>Seluruh data timbangan karung dan sertifikat terkait akan terhapus permanen.</span>',
                                                    icon: 'warning',
                                                    showCancelButton: true,
                                                    confirmButtonColor: '#e11d48',
                                                    cancelButtonColor: '#27272a',
                                                    confirmButtonText: 'Ya, Hapus!',
                                                    cancelButtonText: 'Batal',
                                                    background: '#18181b',
                                                    color: '#f4f4f5',
                                                    heightAuto: false
                                                }).then((result) => {
                                                    if (result.isConfirmed) {
                                                        $wire.deleteBatch({{ $b->id }});
                                                    }
                                                })
                                            "
                                            title="Hapus Data Batch Ini"
                                            class="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-rose-950/80 text-rose-300 border border-rose-800/80 hover:bg-rose-900 shadow transition-colors inline-flex items-center gap-1 shrink-0">
                                            🗑️ Hapus
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-zinc-500 text-sm">
                                Belum ada data batch timbangan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-zinc-800">
            {{ $batches->links() }}
        </div>
    </div>

    <!-- CREATE BATCH MODAL WITH SIMPLIFIED MRL GROSS WEIGHT TABLE -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl max-w-5xl w-full p-6 sm:p-8 shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
                <h3 class="text-lg font-black text-amber-400 uppercase tracking-wider">Buat Batch Timbangan Baru (Input MRL Pre-Launch)</h3>
                <button type="button" @click="showCreateModal = false" class="text-zinc-400 hover:text-white text-2xl font-bold p-2 min-w-[44px] min-h-[44px]">&times;</button>
            </div>

            <form wire:submit.prevent="createBatch" class="space-y-6">
                <!-- Header Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-amber-400 mb-1">Kode Batch / No. Batch <span class="text-red-400">*</span> <span class="text-zinc-500/70 opacity-60 font-normal text-[11px]">(Isi Manual)</span></label>
                        <input type="text" wire:model="batch_code" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-amber-500/80 text-amber-300 font-mono font-bold text-sm outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/50 placeholder-zinc-600/70" placeholder="BCH-20260804-001">
                        @error('batch_code') <span class="text-red-400 font-bold block text-[11px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-amber-400 mb-1">Pelanggan (Customer) <span class="text-red-400">*</span></label>
                        <select wire:model="customer_id" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-amber-500/80 text-amber-300 font-bold text-sm outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/50">
                            <option value="" class="bg-zinc-950 text-zinc-500/70 opacity-60">-- Pilih Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" class="bg-zinc-950 text-zinc-100">{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-red-400 font-bold block text-[11px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-amber-400 mb-1">Nomor Surat Jalan (DN Number) <span class="text-zinc-500/70 opacity-60 font-normal text-[11px]">(Opsional)</span></label>
                        <input type="text" wire:model="dn_number" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-amber-500/80 text-amber-300 font-bold text-sm outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/50 placeholder-zinc-600/70" placeholder="DN-2026-0801 (Otomatis jika dikosongkan)">
                        @error('dn_number') <span class="text-red-400 font-bold block text-[11px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-amber-400 mb-1">DN Total Gross Weight Surat Jalan (kg) <span class="text-zinc-500/70 opacity-60 font-normal text-[11px]">(Opsional)</span></label>
                        <input type="number" step="0.01" wire:model.live.debounce.300ms="dn_gross_weight_input" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-amber-500/80 text-amber-300 font-bold text-sm outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/50 placeholder-zinc-600/70" placeholder="Opsional (Opsional untuk hitung selisih)">
                        <span class="text-[10px] text-zinc-500/70 opacity-60 block mt-1">Pengisian angka setelah 0</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-amber-400 mb-1">Jenis Produk (Product Type) <span class="text-red-400">*</span></label>
                        <select wire:model="product_type_id" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-amber-500/80 text-amber-300 font-bold text-sm outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/50">
                            <option value="" class="bg-zinc-950 text-zinc-500/70 opacity-60">-- Pilih Jenis Produk --</option>
                            @foreach($productTypes as $pt)
                                <option value="{{ $pt->id }}" class="bg-zinc-950 text-zinc-100">{{ $pt->name }}</option>
                            @endforeach
                        </select>
                        @error('product_type_id') <span class="text-red-400 font-bold block text-[11px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-amber-400 mb-1">Asal Utama Tembakau (Primary Origin) <span class="text-red-400">*</span></label>
                        <select wire:model="origin_id" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-amber-500/80 text-amber-300 font-bold text-sm outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/50">
                            <option value="" class="bg-zinc-950 text-zinc-500/70 opacity-60">-- Pilih Asal Utama --</option>
                            @foreach($origins as $org)
                                <option value="{{ $org->id }}" class="bg-zinc-950 text-zinc-100">{{ $org->region_name }}</option>
                            @endforeach
                        </select>
                        @error('origin_id') <span class="text-red-400 font-bold block text-[11px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-amber-400 mb-1">Kode Material / Material Code <span class="text-zinc-500/70 opacity-60 font-normal text-[11px]">(Isi Manual)</span></label>
                        <input type="text" wire:model="material_code" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-amber-500/80 text-amber-300 font-mono font-bold text-sm outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/50 placeholder-zinc-600/70" placeholder="FN602 / MAT-001 / Custom Code">
                        <span class="text-[10px] text-zinc-500/70 opacity-60 block mt-1">Kode material manual untuk dokumen PDF & tracking</span>
                        @error('material_code') <span class="text-red-400 font-bold block text-[11px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-amber-400 mb-1">Jenis Kemasan (Pack Type) <span class="text-red-400">*</span></label>
                        <select wire:model="pack_type" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-amber-500/80 text-amber-300 font-bold text-sm outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/50">
                            <option value="Bale" class="bg-zinc-950 text-zinc-100">Bale</option>
                            <option value="Sack" class="bg-zinc-950 text-zinc-100">Sack (Karung)</option>
                            <option value="Box" class="bg-zinc-950 text-zinc-100">Box</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-amber-400 mb-1">Berat Gross Per Sak Produk Jadi (kg/sak) <span class="text-red-400">*</span></label>
                        <input type="number" step="0.01" wire:model="product_kg_per_sack" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-amber-500/80 text-amber-300 font-bold text-sm outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/50 placeholder-zinc-600/70" placeholder="25.20">
                        <span class="text-[10px] text-zinc-500/70 opacity-60 block mt-1">Gross per sak untuk konversi Produk Jadi</span>
                        @error('product_kg_per_sack') <span class="text-red-400 font-bold block text-[11px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-amber-400 mb-1">Tare Standar Produk Jadi (kg/sak) <span class="text-red-400">*</span></label>
                        <input type="number" step="0.01" wire:model="product_tare_per_sack" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-amber-500/80 text-amber-300 font-bold text-sm outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/50 placeholder-zinc-600/70" placeholder="0.20">
                        <span class="text-[10px] text-zinc-500/70 opacity-60 block mt-1">Tare standar awal per sak di Laporan Pemisahan</span>
                        @error('product_tare_per_sack') <span class="text-red-400 font-bold block text-[11px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-amber-400 mb-1">Tanggal Penerimaan <span class="text-red-400">*</span></label>
                        <input type="date" wire:model="date_of_receipt" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-amber-500/80 text-amber-300 font-bold text-sm outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/50">
                    </div>
                </div>

                <!-- SIMPLIFIED MRL RECEIVING ITEMS TABLE (ONLY MRL GROSS WEIGHT PER SACK) -->
                <div class="border-t border-zinc-800 pt-6 space-y-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-zinc-950 p-4 rounded-2xl border border-zinc-800">
                        <div>
                            <h4 class="text-sm font-black uppercase text-amber-400 tracking-wider flex items-center gap-2">
                                📦 Input Wajib MRL Pre-Launch Penerimaan Gudang
                            </h4>
                            <p class="text-xs text-zinc-400 mt-0.5">Ketik jumlah sak/bale di samping untuk membuat daftar baris berat Gross secara otomatis.</p>
                        </div>

                        <!-- Numeric Sack Count Generator Input -->
                        <div class="flex items-center space-x-2.5 shrink-0 bg-zinc-900/80 px-3.5 py-2 rounded-xl border border-zinc-800">
                            <label class="text-xs font-bold uppercase text-amber-400 whitespace-nowrap">
                                Jumlah Sak / Bale:
                            </label>
                            <input type="number" min="1" max="500" 
                                wire:model.live.debounce.300ms="target_sack_count"
                                class="w-20 px-3 py-1.5 text-center rounded-lg bg-zinc-950 border border-amber-500/80 text-amber-400 font-mono font-black text-base outline-none focus:border-amber-400"
                                placeholder="32">
                            <span class="text-xs text-zinc-300 font-bold">Unit</span>
                        </div>
                    </div>

                    <!-- Responsive Multi-Column Grid Layout for MRL Items (Arranged Side-by-Side) -->
                    <div class="bg-zinc-950/80 p-4 rounded-2xl border border-zinc-800/80 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                            <div class="flex items-center space-x-2">
                                <span class="text-xs font-black uppercase text-amber-400 tracking-wider">
                                    Daftar Baris MRL ({{ count($mrl_items) }} Sak / Bale)
                                </span>
                                <span class="text-[10px] text-amber-300 font-bold bg-amber-950/80 px-2 py-0.5 rounded-md border border-amber-800/60">
                                    Gross Weight
                                </span>
                            </div>
                            <span class="text-[10px] text-zinc-400 font-bold bg-zinc-900 px-2.5 py-1 rounded-lg border border-zinc-800">
                                📊 Layout Kolom Menyamping
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 max-h-[420px] overflow-y-auto p-1 pr-2">
                            @foreach($mrl_items as $index => $item)
                                <div class="bg-zinc-900/90 border border-zinc-800 hover:border-amber-500/50 rounded-2xl p-3.5 flex items-center justify-between gap-3 shadow-sm transition-all group">
                                    <!-- Sack Number Badge -->
                                    <div class="flex items-center space-x-2.5 shrink-0">
                                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/40 text-amber-400 font-mono font-black text-sm flex items-center justify-center shadow-inner group-hover:border-amber-400 transition-colors">
                                            #{{ $item['sack_number'] }}
                                        </div>
                                        <div class="hidden sm:block">
                                            <span class="text-[11px] font-black uppercase tracking-wider text-zinc-300 block">Sak #{{ $item['sack_number'] }}</span>
                                            <span class="text-[9px] text-amber-400/80 font-bold uppercase">Gross Wt</span>
                                        </div>
                                    </div>

                                    <!-- MRL Gross Weight Input -->
                                    <div class="flex-1 min-w-[120px]">
                                        <div class="relative flex items-center">
                                            <input type="number" step="0.01" inputmode="decimal" 
                                                wire:model.live.debounce.300ms="mrl_items.{{ $index }}.mrl_gross_weight" 
                                                class="w-full pl-3 pr-9 py-2 rounded-xl bg-zinc-950 border border-zinc-700 hover:border-zinc-600 focus:border-amber-400 text-amber-300 font-mono font-black text-base outline-none focus:ring-2 focus:ring-amber-400/30 placeholder-zinc-700 transition-all" 
                                                placeholder="0.00">
                                            <span class="absolute right-3 text-xs font-bold text-zinc-500 pointer-events-none">kg</span>
                                        </div>
                                    </div>

                                    <!-- Hapus Action Button -->
                                    @if(count($mrl_items) > 1)
                                        <button type="button" wire:click="removeMrlItemRow({{ $index }})" 
                                            title="Hapus Sak #{{ $item['sack_number'] }}"
                                            class="text-zinc-500 hover:text-red-400 text-2xl font-bold px-1.5 py-0.5 leading-none transition-colors shrink-0">
                                            &times;
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- MRL Real-Time Calculated Summary Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-zinc-950 p-4 rounded-2xl border border-zinc-800/80 text-xs">
                        <div>
                            <span class="text-zinc-500 block text-[10px] uppercase font-bold">Total Sak/Bale</span>
                            <strong class="text-amber-400 font-mono text-base block">{{ count($mrl_items) }} Unit</strong>
                        </div>
                        <div>
                            <span class="text-zinc-500 block text-[10px] uppercase font-bold">Total DN Gross</span>
                            <strong class="text-zinc-200 font-mono text-base block">{{ number_format($dn_gross_weight, 2, ',', '.') }} kg</strong>
                        </div>
                        <div>
                            <span class="text-zinc-500 block text-[10px] uppercase font-bold">Total MRL Gross</span>
                            <strong class="text-emerald-400 font-mono text-base block">{{ number_format($mrl_gross_weight, 2, ',', '.') }} kg</strong>
                        </div>
                        <div>
                            <span class="text-zinc-500 block text-[10px] uppercase font-bold">Selisih (DN vs MRL)</span>
                            @php $diff = round($mrl_gross_weight - $dn_gross_weight, 2); @endphp
                            <strong class="{{ $diff != 0 ? 'text-red-400' : 'text-emerald-400' }} font-mono text-base block">
                                {{ number_format($diff, 2, ',', '.') }} kg
                            </strong>
                        </div>
                    </div>
                </div>


                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-zinc-800">
                    <button type="button" @click="showCreateModal = false" class="px-5 py-3 min-h-[48px] rounded-xl bg-zinc-800 text-zinc-300 font-bold text-xs hover:bg-zinc-700">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-3 min-h-[48px] rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-black text-xs hover:from-amber-500 shadow-lg">
                        Simpan MRL & Launch Batch ({{ count($mrl_items) }} Sak)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- LIVE INTERACTIVE PDF PREVIEW & CUSTOM REMARKS MODAL -->
    <div x-show="showPdfModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/85 backdrop-blur-sm">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl max-w-5xl w-full p-5 sm:p-6 space-y-4 shadow-2xl max-h-[95vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                <div>
                    <h3 class="text-lg font-black text-amber-400 flex items-center">
                        👁️ Live Preview Process Certificate PDF
                        <span class="ml-3 px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-950 text-emerald-300 border border-emerald-800">
                            Pratinjau Dokumen Real-Time
                        </span>
                    </h3>
                    <p class="text-xs text-zinc-400">Pratinjau visual tampilan sertifikat sebelum memutuskan untuk mencetak / mengunduh PDF</p>
                </div>
                <button type="button" @click="showPdfModal = false" class="text-zinc-400 hover:text-white text-2xl font-bold p-2 min-w-[44px] min-h-[44px]">&times;</button>
            </div>

            <!-- Split Screen Content: Left Controls & Right Iframe Live Preview -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 flex-1 overflow-y-auto">
                <!-- Left Panel: Optional Custom Remarks Input -->
                <div class="lg:col-span-5 space-y-3 bg-zinc-950 p-4 rounded-2xl border border-zinc-800/80">
                    <div class="flex items-center justify-between border-b border-zinc-800/80 pb-2">
                        <h4 class="text-xs font-bold uppercase text-amber-400">Catatan Khusus Sertifikat</h4>
                    </div>

                    <div class="flex items-center space-x-2 bg-zinc-900 p-3 rounded-xl border border-zinc-800">
                        <input type="checkbox" id="toggleCustomRemarks" wire:model.live="addCustomRemarks" wire:change="saveCustomRemarksAndRefreshPreview" class="rounded bg-zinc-950 border-zinc-700 text-amber-500 focus:ring-amber-500">
                        <label for="toggleCustomRemarks" class="text-xs font-bold text-zinc-200 cursor-pointer">
                            Tambahkan Catatan Khusus PDF
                        </label>
                    </div>

                    @if($addCustomRemarks)
                        <div class="space-y-3 text-xs pt-1">
                            <div>
                                <label class="block font-bold uppercase text-zinc-400 mb-1">Section 1: Delivery Note (DN)</label>
                                <textarea wire:model.live.debounce.400ms="custom_dn_remark" wire:change="saveCustomRemarksAndRefreshPreview" rows="2" class="w-full px-3 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-200 outline-none focus:border-amber-500" placeholder="Ketik catatan khusus DN..."></textarea>
                            </div>

                            <div>
                                <label class="block font-bold uppercase text-zinc-400 mb-1">Section 2: Material Receipt List (MRL)</label>
                                <textarea wire:model.live.debounce.400ms="custom_mrl_remark" wire:change="saveCustomRemarksAndRefreshPreview" rows="2" class="w-full px-3 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-200 outline-none focus:border-amber-500" placeholder="Ketik catatan khusus MRL..."></textarea>
                            </div>

                            <div>
                                <label class="block font-bold uppercase text-zinc-400 mb-1">Section 3: Separation Results</label>
                                <textarea wire:model.live.debounce.400ms="custom_separation_remark" wire:change="saveCustomRemarksAndRefreshPreview" rows="2" class="w-full px-3 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-200 outline-none focus:border-amber-500" placeholder="Ketik catatan khusus Pemisahan..."></textarea>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Panel: Live Document Iframe Preview -->
                <div class="lg:col-span-7 flex flex-col">
                    <span class="text-[11px] font-bold text-zinc-400 mb-1.5 flex items-center justify-between">
                        <span>Pratinjau Visual Dokumen:</span>
                        <span class="text-[10px] text-emerald-400 font-mono">Live Update (Key: #{{ $iframeKey }})</span>
                    </span>
                    @if($pdfBatchId)
                        <iframe key="pdf-iframe-{{ $iframeKey }}" src="/certificate/{{ $pdfBatchId }}?key={{ $iframeKey }}" class="w-full h-[460px] rounded-2xl border border-zinc-800 bg-white shadow-inner"></iframe>
                    @endif
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-zinc-800 pt-3">
                <button type="button" @click="showPdfModal = false" class="w-full sm:w-auto px-5 py-3 min-h-[48px] rounded-xl bg-zinc-800 text-zinc-300 font-bold text-xs hover:bg-zinc-700">
                    ❌ Batal (Tidak Jadi Cetak)
                </button>
                
                @if($pdfBatchId)
                    <a href="{{ route('certificate.pdf', $pdfBatchId) }}" target="_blank" class="w-full sm:w-auto px-6 py-3 min-h-[48px] inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-red-600 to-red-700 text-white font-black text-xs hover:from-red-500 shadow-xl shadow-red-950/50">
                        📥 Download & Cetak PDF Certificate Now
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
