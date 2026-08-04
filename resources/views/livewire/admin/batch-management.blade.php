<div x-data="{ showCreateModal: @entangle('showCreateModal'), showCloseModal: @entangle('showCloseModal'), showPdfModal: @entangle('showPdfRemarksModal') }" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black tracking-wide text-zinc-100 flex items-center">
                Manajemen Batch Production (MES v2.0)
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
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-950 text-emerald-300 border border-emerald-800">
                                        ✅ APPROVED BY SUPERVISOR
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-amber-950 text-amber-300 border border-amber-800 animate-pulse">
                                        ⏳ PENDING SUPERVISOR ACC
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap space-x-2">
                                <a href="{{ route('karyawan.weighing', ['batch_id' => $b->id]) }}" class="px-3 py-2 min-h-[44px] inline-flex items-center text-xs font-bold rounded-xl bg-zinc-800 text-zinc-200 hover:bg-zinc-700">
                                    📋 Timbangan
                                </a>

                                <!-- Supervisor Approval ACC Button -->
                                @if(auth()->user() && (auth()->user()->isSupervisor() || auth()->user()->isAdmin()) && !$b->isApprovedBySupervisor() && in_array($b->status, ['CLOSED', 'locked', 'WAITING']))
                                    <button wire:click="approveCertificate({{ $b->id }})" class="px-3 py-2 min-h-[44px] inline-flex items-center text-xs font-black rounded-xl bg-emerald-900 text-emerald-100 border border-emerald-700 hover:bg-emerald-800 shadow">
                                        ✅ ACC / Approve
                                    </button>
                                @endif

                                <!-- PDF Live Preview Trigger Button -->
                                <button wire:click="openPdfRemarksModal({{ $b->id }})" class="px-3 py-2 min-h-[44px] inline-flex items-center text-xs font-black rounded-xl bg-red-950 text-red-300 border border-red-800 hover:bg-red-900 shadow">
                                    👁️ Preview & Cetak PDF
                                </button>
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
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl max-w-3xl w-full p-6 sm:p-8 shadow-2xl space-y-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
                <h3 class="text-lg font-black text-amber-400 uppercase tracking-wider">Buat Batch Timbangan Baru (Input MRL Pre-Launch)</h3>
                <button type="button" @click="showCreateModal = false" class="text-zinc-400 hover:text-white text-2xl font-bold p-2 min-w-[44px] min-h-[44px]">&times;</button>
            </div>

            <form wire:submit.prevent="createBatch" class="space-y-6">
                <!-- Header Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-300 mb-1">Pelanggan (Customer) <span class="text-red-400">*</span></label>
                        <select wire:model="customer_id" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm outline-none focus:border-amber-500">
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-red-400 font-bold block text-[11px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-300 mb-1">Nomor Surat Jalan (DN Number) <span class="text-red-400">*</span></label>
                        <input type="text" wire:model="dn_number" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm outline-none focus:border-amber-500" placeholder="DN-2026-0801">
                        @error('dn_number') <span class="text-red-400 font-bold block text-[11px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-300 mb-1">DN Total Gross Weight Surat Jalan (kg)</label>
                        <input type="number" step="0.01" wire:model.live.debounce.300ms="dn_gross_weight_input" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm outline-none focus:border-amber-500" placeholder="Opsional (Opsional untuk hitung selisih)">
                         <span class="text-[10px] text-zinc-400 block mt-1">Pengisian angka setelah 0</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-300 mb-1">Jenis Produk (Product Type) <span class="text-red-400">*</span></label>
                        <select wire:model="product_type_id" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm outline-none focus:border-amber-500">
                            <option value="">-- Pilih Jenis Produk --</option>
                            @foreach($productTypes as $pt)
                                <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                            @endforeach
                        </select>
                        @error('product_type_id') <span class="text-red-400 font-bold block text-[11px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-300 mb-1">Asal Utama Tembakau (Primary Origin) <span class="text-red-400">*</span></label>
                        <select wire:model="origin_id" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm outline-none focus:border-amber-500">
                            <option value="">-- Pilih Asal Utama --</option>
                            @foreach($origins as $org)
                                <option value="{{ $org->id }}">{{ $org->region_name }}</option>
                            @endforeach
                        </select>
                        @error('origin_id') <span class="text-red-400 font-bold block text-[11px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-300 mb-1">Jenis Kemasan (Pack Type) <span class="text-red-400">*</span></label>
                        <select wire:model="pack_type" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm outline-none focus:border-amber-500">
                            <option value="Bale">Bale</option>
                            <option value="Sack">Sack (Karung)</option>
                            <option value="Box">Box</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-amber-400 mb-1">Hitungan Per Sak Produk Jadi (kg/sak) <span class="text-red-400">*</span></label>
                        <input type="number" step="0.01" wire:model="product_kg_per_sack" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-amber-500/80 text-amber-400 font-bold text-sm outline-none focus:border-amber-400" placeholder="20.00">
                        <span class="text-[10px] text-zinc-400 block mt-1">Konversi otomatis saat karyawan mengisi Sak Produk Jadi</span>
                        @error('product_kg_per_sack') <span class="text-red-400 font-bold block text-[11px] mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-300 mb-1">Tanggal Penerimaan <span class="text-red-400">*</span></label>
                        <input type="date" wire:model="date_of_receipt" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm outline-none focus:border-amber-500">
                    </div>
                </div>

                <!-- SIMPLIFIED MRL RECEIVING ITEMS TABLE (ONLY MRL GROSS WEIGHT PER SACK) -->
                <div class="border-t border-zinc-800 pt-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-zinc-950 p-4 rounded-2xl border border-zinc-800">
                        <div>
                            <h4 class="text-sm font-black uppercase text-amber-400 tracking-wider">Input Wajib MRL Pre-Launch Penerimaan Gudang</h4>
                            <p class="text-[11px] text-zinc-400">Ketik jumlah sak/bale (misal: 32) untuk membuat jumlah baris MRL Gross Weight secara otomatis.</p>
                        </div>

                        <!-- Numeric Sack Count Generator Input -->
                        <div class="flex items-center space-x-2 shrink-0">
                            <label class="text-xs font-bold uppercase text-amber-400 whitespace-nowrap">
                                Jumlah Sak / Bale:
                            </label>
                            <input type="number" min="1" max="500" 
                                wire:model.live.debounce.300ms="target_sack_count"
                                class="w-24 px-3 py-2 text-center rounded-xl bg-zinc-900 border border-amber-500/80 text-amber-400 font-black text-base outline-none focus:border-amber-400"
                                placeholder="32">
                            <span class="text-xs text-zinc-300 font-bold">Unit</span>
                        </div>
                    </div>

                    <!-- Clean MRL Table Items: Only No | MRL Gross Weight (kg) | Aksi -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-center w-16">No</th>
                                    <th class="px-4 py-3">MRL Gross Weight (kg) *</th>
                                    <th class="px-4 py-3 text-center w-16">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/80">
                                @foreach($mrl_items as $index => $item)
                                    <tr class="hover:bg-zinc-800/30 transition-colors">
                                        <td class="px-4 py-3 text-center font-mono font-bold text-amber-400 text-sm">
                                            {{ $item['sack_number'] }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number" step="0.01" inputmode="decimal" 
                                                wire:model.live.debounce.300ms="mrl_items.{{ $index }}.mrl_gross_weight" 
                                                class="w-full px-4 py-2.5 min-h-[48px] rounded-xl bg-zinc-950 border border-amber-500/80 text-amber-400 font-black text-base outline-none focus:border-amber-400" 
                                                placeholder="Ketik MRL Gross Weight (kg)...">
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if(count($mrl_items) > 1)
                                                <button type="button" wire:click="removeMrlItemRow({{ $index }})" class="p-2 min-w-[44px] min-h-[44px] rounded-xl bg-red-950 text-red-400 hover:bg-red-900 font-bold">
                                                    &times;
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
