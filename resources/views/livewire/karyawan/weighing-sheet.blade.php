<div x-data="{ showLockModal: false }" class="max-w-4xl mx-auto space-y-6 pb-28">
    
    <!-- Active Batch Selector & Header -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 space-y-4 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-black uppercase text-amber-500 tracking-wider">Aplikasi Timbangan Lapangan PWA (MES v2.0)</span>
                <h2 class="text-2xl font-black tracking-wide text-zinc-100">Lembar Penimbangan Karung</h2>
            </div>
            
            <!-- Status Badge -->
            <div>
                @if(in_array($status, ['CLOSED', 'locked']))
                    <span class="px-4 py-2 rounded-2xl text-xs font-black uppercase bg-blue-950 text-blue-300 border border-blue-800 inline-flex items-center shadow">
                        🔒 Sudah Dikunci (CLOSED)
                    </span>
                @elseif($status === 'WAITING')
                    <span class="px-4 py-2 rounded-2xl text-xs font-black uppercase bg-amber-950 text-amber-300 border border-amber-800 inline-flex items-center shadow animate-pulse">
                        ⏳ Menunggu Verifikasi (WAITING)
                    </span>
                @else
                    <span class="px-4 py-2 rounded-2xl text-xs font-black uppercase bg-emerald-950 text-emerald-300 border border-emerald-800 inline-flex items-center shadow animate-pulse">
                        🟢 Sedang Diisi (ACTIVE/OPEN)
                    </span>
                @endif
            </div>
        </div>

        <!-- Active Batch Dropdown -->
        <div>
            <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Pilih Batch Timbangan Aktif:</label>
            <select wire:change="selectBatch($event.target.value)" class="w-full px-4 py-3.5 min-h-[48px] rounded-2xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm font-bold focus:border-amber-500 outline-none">
                <option value="">-- Pilih Batch --</option>
                @foreach($activeBatches as $bItem)
                    <option value="{{ $bItem->id }}" {{ $bItem->id == $batchId ? 'selected' : '' }}>
                        {{ $bItem->batch_code }} - {{ $bItem->customer->name ?? '-' }} ({{ $bItem->productType->name ?? '-' }} - {{ $bItem->origin->region_name ?? '-' }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Batch Info Summary Cards -->
        @if($selectedBatch)
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-zinc-950 p-4 rounded-2xl border border-zinc-800/80 text-xs">
                <div>
                    <span class="text-zinc-500 block text-[10px] uppercase font-bold">Pelanggan</span>
                    <strong class="text-zinc-200 truncate block">{{ $selectedBatch->customer->name ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-zinc-500 block text-[10px] uppercase font-bold">Surat Jalan (DN)</span>
                    <strong class="text-zinc-200 font-mono block">{{ $selectedBatch->deliveryNote->dn_number ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-zinc-500 block text-[10px] uppercase font-bold">Jenis Produk</span>
                    <strong class="text-amber-400 block">{{ $selectedBatch->productType->name ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-zinc-500 block text-[10px] uppercase font-bold">Asal / Kemasan</span>
                    <strong class="text-zinc-200 block">{{ $selectedBatch->origin->region_name ?? '-' }} ({{ $selectedBatch->pack_type }})</strong>
                </div>
            </div>
        @endif
    </div>

    <!-- MAIN WEIGHING SHEET SACK GRID (MATERIAL RECEIPT LIST) -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 space-y-4 shadow-xl">
        <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
            <div>
                <h3 class="text-base font-black text-amber-400 uppercase tracking-wide">Lembar Timbangan Per Karung (Material Receipt List)</h3>
                <p class="text-[11px] text-zinc-400">Baris dari shift sebelumnya otomatis terkunci (read-only). Tekan Enter pada keyboard HP untuk pindah baris.</p>
            </div>
            @if(!in_array($status, ['CLOSED', 'locked']))
                <button type="button" wire:click="addSackRow" class="px-4 py-2.5 min-h-[48px] min-w-[48px] text-xs font-black rounded-xl bg-amber-950 text-amber-300 border border-amber-800 hover:bg-amber-900 shadow">
                    + Tambah Karung
                </button>
            @endif
        </div>

        <!-- Speed Entry Table Grid -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                    <tr>
                        <th class="px-3 py-3 text-center w-12">No</th>
                        <th class="px-3 py-3">Berat Kotor / Gross (Kg)</th>
                        <th class="px-3 py-3">Berat Wadah / Tare (Kg)</th>
                        <th class="px-3 py-3">Berat Bersih / Netto (Kg)</th>
                        <th class="px-3 py-3">Catatan & Status Shift</th>
                        <th class="px-3 py-3 text-center w-12">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80">
                    @foreach($items as $index => $item)
                        <tr class="hover:bg-zinc-800/40 transition-colors {{ !empty($item['is_locked_for_user']) ? 'bg-zinc-950/60 opacity-80' : '' }}">
                            <!-- No -->
                            <td class="px-3 py-3 text-center font-mono font-bold text-amber-400 text-sm">
                                {{ $item['sack_number'] }}
                            </td>

                            <!-- Gross (Kg) -->
                            <td class="px-3 py-3">
                                <input type="number" step="0.01" inputmode="decimal" 
                                    wire:model.live.debounce.300ms="items.{{ $index }}.gross_kg" 
                                    {{ in_array($status, ['CLOSED', 'locked']) || !empty($item['is_locked_for_user']) ? 'disabled' : '' }}
                                    id="gross-input-{{ $index }}"
                                    data-index="{{ $index }}"
                                    onkeydown="if(event.key==='Enter'){ event.preventDefault(); const next=document.getElementById('gross-input-{{ $index + 1 }}'); if(next){ next.focus(); next.select(); } }"
                                    class="w-full px-3 py-2.5 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-emerald-400 font-black text-base focus:border-emerald-500 outline-none {{ !empty($item['is_locked_for_user']) ? 'cursor-not-allowed text-zinc-500' : '' }}" 
                                    placeholder="0.00">

                                @if((float)($item['tare_kg'] ?? 0) > (float)($item['gross_kg'] ?? 0) && (float)($item['gross_kg'] ?? 0) > 0)
                                    <span class="text-red-400 text-[10px] font-bold mt-1 block">⚠️ Berat wadah tidak boleh lebih besar dari berat kotor.</span>
                                @endif
                            </td>

                            <!-- Tare (Kg) -->
                            <td class="px-3 py-3">
                                <input type="number" step="0.01" inputmode="decimal" 
                                    wire:model.live.debounce.300ms="items.{{ $index }}.tare_kg" 
                                    {{ in_array($status, ['CLOSED', 'locked']) || !empty($item['is_locked_for_user']) ? 'disabled' : '' }}
                                    class="w-full px-3 py-2.5 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-amber-400 font-bold text-sm focus:border-amber-500 outline-none {{ !empty($item['is_locked_for_user']) ? 'cursor-not-allowed text-zinc-500' : '' }}" 
                                    placeholder="2.00">
                            </td>

                            <!-- Netto (Kg) (Auto-calculated) -->
                            <td class="px-3 py-3">
                                <input type="number" step="0.01" value="{{ number_format($item['netto_kg'], 2) }}" readonly 
                                    class="w-full px-3 py-2.5 min-h-[48px] rounded-xl bg-zinc-950/80 border border-zinc-800 text-amber-400 font-black text-base outline-none cursor-not-allowed">
                            </td>

                            <!-- Remark & Predecessor Lock Badge -->
                            <td class="px-3 py-3">
                                <select wire:model="items.{{ $index }}.remark" {{ in_array($status, ['CLOSED', 'locked']) || !empty($item['is_locked_for_user']) ? 'disabled' : '' }} class="w-full px-2 py-2.5 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-300 text-xs outline-none">
                                    <option value="Normal">Normal</option>
                                    <option value="Remnant">Remnant (Sisa)</option>
                                </select>

                                @if(!empty($item['is_locked_for_user']))
                                    <span class="text-[10px] text-amber-500 font-bold mt-1 block">🔒 Shift Sebelumnya (Read-Only)</span>
                                @endif
                            </td>

                            <!-- Remove Action -->
                            <td class="px-3 py-3 text-center">
                                @if(count($items) > 1 && !in_array($status, ['CLOSED', 'locked']) && empty($item['is_locked_for_user']))
                                    <button type="button" wire:click="removeSackRow({{ $index }})" class="p-2 min-w-[48px] min-h-[48px] rounded-xl bg-red-950 text-red-400 hover:bg-red-900 font-bold">
                                        &times;
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(!in_array($status, ['CLOSED', 'locked']))
            <div class="pt-2 flex items-center justify-between">
                <button type="button" wire:click="generateDefaultSackRows(10)" class="px-4 py-2.5 min-h-[48px] min-w-[48px] text-xs font-bold rounded-xl bg-zinc-800 text-zinc-300 hover:bg-zinc-700">
                    + Tambah 10 Baris Timbangan
                </button>
                <span class="text-xs text-zinc-400">Total Baris: <strong class="text-amber-400 font-mono text-sm">{{ count($items) }} Karung</strong></span>
            </div>
        @endif
    </div>

    <!-- SEPARATION RESULTS REPORT INPUTS -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 space-y-4 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-800 pb-3">
            <div>
                <h3 class="text-base font-black text-amber-400 uppercase tracking-wide">
                    Laporan Hasil Pemisahan Sesi Kerja Ini (Separation Results)
                </h3>
                <p class="text-[11px] text-zinc-400 mt-0.5">Semua kalkulasi berat kotor, wadah, bersih (Netto), dan persentase yield dihitung secara otomatis.</p>
            </div>
            <span class="text-xs text-amber-400 font-bold bg-amber-950 px-3 py-1.5 rounded-xl border border-amber-800/80 shrink-0">
                Hitungan Per Sak: {{ number_format($product_kg_per_sack, 2) }} kg/sak
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-stretch">
            <!-- 1. Produk Jadi (Persack) -->
            <div class="bg-zinc-950 p-4 rounded-2xl border border-emerald-900/60 flex flex-col justify-between space-y-3">
                <div>
                    <label class="block text-xs font-bold uppercase text-emerald-400 mb-2">1. Produk Jadi (Sak/Karung) <span class="text-red-400">*</span></label>
                    <input type="number" min="0" step="1" inputmode="numeric" wire:model.live.debounce.500ms="separation_product_sack" {{ in_array($status, ['CLOSED', 'locked']) ? 'disabled' : '' }} class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-900 border border-emerald-500/80 text-emerald-400 text-lg font-black outline-none focus:border-emerald-500" placeholder="0">
                </div>
                <div class="pt-2 border-t border-zinc-800/80 flex items-center justify-between text-xs font-bold">
                    <span class="text-emerald-400 font-mono text-sm">= {{ number_format($separation_product_kg, 2) }} kg</span>
                    <span class="px-2 py-0.5 rounded-lg bg-emerald-950 text-emerald-300 border border-emerald-800 text-[11px]">{{ number_format($yieldProductPct, 2) }}%</span>
                </div>
            </div>

            <!-- 2. Bit Stem Gross & Tare -->
            <div class="bg-zinc-950 p-4 rounded-2xl border border-amber-900/60 flex flex-col justify-between space-y-3">
                <div>
                    <label class="block text-xs font-bold uppercase text-amber-400 mb-2">2. Bit Stem / Gagang (Kg)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="block text-[10px] text-zinc-400 font-bold uppercase mb-1">Gross</span>
                            <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="separation_bits_stem_gross_kg" {{ in_array($status, ['CLOSED', 'locked']) ? 'disabled' : '' }} class="w-full px-2.5 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-amber-400 font-bold text-sm outline-none focus:border-amber-500" placeholder="0.00">
                        </div>
                        <div>
                            <span class="block text-[10px] text-zinc-400 font-bold uppercase mb-1">Tare</span>
                            <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="separation_bits_stem_tare_kg" {{ in_array($status, ['CLOSED', 'locked']) ? 'disabled' : '' }} class="w-full px-2.5 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-amber-400 font-bold text-sm outline-none focus:border-amber-500" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="pt-2 border-t border-zinc-800/80 flex items-center justify-between text-xs font-bold">
                    <span class="text-amber-400 font-mono text-sm">Netto: {{ number_format($separation_bits_stem_netto_kg, 2) }} kg</span>
                    <span class="px-2 py-0.5 rounded-lg bg-amber-950 text-amber-300 border border-amber-800 text-[11px]">{{ number_format($yieldBitsStemPct, 2) }}%</span>
                </div>
            </div>

            <!-- 3. Debu Gross & Tare -->
            <div class="bg-zinc-950 p-4 rounded-2xl border border-orange-900/60 flex flex-col justify-between space-y-3">
                <div>
                    <label class="block text-xs font-bold uppercase text-orange-400 mb-2">3. Debu / Dust (Kg)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="block text-[10px] text-zinc-400 font-bold uppercase mb-1">Gross</span>
                            <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="separation_dust_gross_kg" {{ in_array($status, ['CLOSED', 'locked']) ? 'disabled' : '' }} class="w-full px-2.5 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-orange-400 font-bold text-sm outline-none focus:border-orange-500" placeholder="0.00">
                        </div>
                        <div>
                            <span class="block text-[10px] text-zinc-400 font-bold uppercase mb-1">Tare</span>
                            <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="separation_dust_tare_kg" {{ in_array($status, ['CLOSED', 'locked']) ? 'disabled' : '' }} class="w-full px-2.5 py-2 rounded-xl bg-zinc-900 border border-zinc-800 text-orange-400 font-bold text-sm outline-none focus:border-orange-500" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="pt-2 border-t border-zinc-800/80 flex items-center justify-between text-xs font-bold">
                    <span class="text-orange-400 font-mono text-sm">Netto: {{ number_format($separation_dust_netto_kg, 2) }} kg</span>
                    <span class="px-2 py-0.5 rounded-lg bg-orange-950 text-orange-300 border border-orange-800 text-[11px]">{{ number_format($yieldDustPct, 2) }}%</span>
                </div>
            </div>

            <!-- 4. Uncountable Waste -->
            <div class="bg-zinc-950 p-4 rounded-2xl border border-zinc-800 flex flex-col justify-between space-y-3">
                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">4. Uncountable Waste (Kg)</label>
                    <div class="mt-1">
                        <input type="text" value="{{ number_format($separation_waste_kg, 2) }}" readonly class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 text-lg font-black outline-none cursor-not-allowed">
                    </div>
                </div>
                <div class="pt-2 border-t border-zinc-800/80 flex items-center justify-between text-xs font-bold">
                    <span class="text-zinc-400 font-mono text-sm">{{ number_format($separation_waste_kg, 2) }} kg</span>
                    <span class="px-2 py-0.5 rounded-lg bg-zinc-900 text-zinc-300 border border-zinc-700 text-[11px]">{{ number_format($yieldWastePct, 2) }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTION BUTTONS FOR WORKERS -->
    @if(!in_array($status, ['CLOSED', 'locked']))
        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-2">
            <button type="button" wire:click="openPauseModal" class="w-full sm:w-auto px-6 py-3.5 min-h-[48px] rounded-2xl bg-amber-950 text-amber-300 border border-amber-800 font-bold text-sm hover:bg-amber-900 shadow">
                🛑 Hentikan Jeda (Pause Shift)
            </button>
            <button type="button" wire:click="saveDraft" class="w-full sm:w-auto px-6 py-3.5 min-h-[48px] rounded-2xl bg-zinc-800 text-zinc-200 font-bold text-sm hover:bg-zinc-700 shadow">
                💾 Simpan Sementara
            </button>
            <button type="button" @click="showLockModal = true" class="w-full sm:w-auto px-8 py-3.5 min-h-[48px] rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-black text-sm hover:from-emerald-500 shadow-xl shadow-emerald-950/50">
                🔒 Selesai & Kunci Data
            </button>
        </div>
    @endif

    <!-- MANDATORY INTERIM REPORT PAUSE MODAL -->
    <div x-data="{ show: @entangle('showPauseModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
            <h3 class="text-lg font-black text-amber-400 border-b border-zinc-800 pb-3">Laporan Pemisahan Interim Pada Jeda Shift</h3>
            <p class="text-xs text-zinc-300">Wajib memasukkan hasil pemisahan sesi ini sebelum produksi dihentikan sementara (Pause Shift):</p>

            <form wire:submit.prevent="submitPauseAndInterimReport" class="space-y-3 text-xs">
                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Produk Jadi / Rajangan (Sak/Karung) <span class="text-red-400">*</span></label>
                    <input type="number" min="0" step="1" inputmode="numeric" wire:model="separation_product_sack" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-emerald-500/80 text-emerald-400 font-black outline-none focus:border-emerald-500" placeholder="0">
                    <span class="text-xs font-bold text-emerald-400 mt-1 block">= {{ number_format($separation_product_kg, 2) }} kg (Standar: {{ number_format($product_kg_per_sack, 2) }} kg/sak)</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Bits Stem Gross (Kg) <span class="text-red-400">*</span></label>
                        <input type="number" step="0.01" inputmode="decimal" wire:model="separation_bits_stem_gross_kg" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-amber-400 font-bold outline-none" placeholder="0.00">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Bits Stem Tare (Kg) <span class="text-red-400">*</span></label>
                        <input type="number" step="0.01" inputmode="decimal" wire:model="separation_bits_stem_tare_kg" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-amber-400 font-bold outline-none" placeholder="0.00">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Debu Gross (Kg) <span class="text-red-400">*</span></label>
                        <input type="number" step="0.01" inputmode="decimal" wire:model="separation_dust_gross_kg" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-orange-400 font-bold outline-none" placeholder="0.00">
                    </div>
                    <div>
                        <label class="block font-bold uppercase text-zinc-300 mb-1">Debu Tare (Kg) <span class="text-red-400">*</span></label>
                        <input type="number" step="0.01" inputmode="decimal" wire:model="separation_dust_tare_kg" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-orange-400 font-bold outline-none" placeholder="0.00">
                    </div>
                </div>
                <span class="text-xs font-bold text-orange-400 block">Debu Netto = {{ number_format($separation_dust_netto_kg, 2) }} kg (Masuk ke Uncountable Waste)</span>

                <div>
                    <label class="block font-bold uppercase text-zinc-300 mb-1">Catatan Jeda Shift (Opsional)</label>
                    <textarea wire:model="pause_notes" rows="2" class="w-full px-4 py-3 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 outline-none" placeholder="Alasan jeda / pergantian shift..."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-3 border-t border-zinc-800">
                    <button type="button" wire:click="$set('showPauseModal', false)" class="px-5 py-3 min-h-[48px] rounded-xl bg-zinc-800 text-zinc-300 font-bold">Batal</button>
                    <button type="submit" class="px-6 py-3 min-h-[48px] rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-black">Simpan Interim & Jeda Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>
