<div x-data="{ showLockModal: false }" 
     x-on:scroll-to-separation-form.window="document.getElementById('separation-results-form')?.scrollIntoView({ behavior: 'smooth', block: 'center' }); const el = document.getElementById('separation_product_sack_input'); if(el) { el.focus(); el.classList.add('ring-4', 'ring-red-500'); setTimeout(() => el.classList.remove('ring-4', 'ring-red-500'), 3000); }" 
     class="max-w-4xl mx-auto space-y-6 pb-28">
    
    <!-- Active Batch Selector & Header -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 space-y-4 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-black uppercase text-amber-500 tracking-wider">Aplikasi Timbangan Lapangan PWA (TPMS v2.0)</span>
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

        <!-- Active Batch Dropdown & Active Shift Switcher -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-end">
            <!-- Active Batch Dropdown (Col 8) -->
            <div class="md:col-span-7">
                <label class="block text-xs font-bold uppercase text-zinc-300 mb-1.5">Pilih Batch Timbangan Aktif:</label>
                <select wire:change="selectBatch($event.target.value)" class="w-full px-4 py-3 min-h-[48px] rounded-2xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm font-bold focus:border-amber-500 outline-none">
                    <option value="">-- Pilih Batch --</option>
                    @foreach($activeBatches as $bItem)
                        <option value="{{ $bItem->id }}" {{ $bItem->id == $batchId ? 'selected' : '' }}>
                            {{ $bItem->batch_code }} - {{ $bItem->customer->name ?? '-' }} ({{ $bItem->productType->name ?? '-' }} - {{ $bItem->origin->region_name ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Active Shift Selection Card (Col 5) -->
            <div class="md:col-span-5 bg-zinc-950/80 p-2.5 rounded-2xl border border-zinc-800 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-1.5 px-1">
                    <span class="text-[11px] font-black uppercase text-amber-400 tracking-wider flex items-center gap-1.5">
                        ⏱️ Shift Kerja Aktif:
                    </span>
                    @if(auth()->user() && auth()->user()->shift && auth()->user()->shift !== $active_shift)
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-950 text-amber-300 border border-amber-800/80" title="Shift default akun: {{ auth()->user()->shift }}">
                            Bantuan (Asal: {{ auth()->user()->shift }})
                        </span>
                    @endif
                </div>

                <!-- Shift Toggle Buttons -->
                <div class="grid grid-cols-2 gap-1.5 bg-zinc-900 p-1 rounded-xl border border-zinc-800">
                    <button type="button" 
                            wire:click="setShift('Shift 1')" 
                            class="py-2 px-2 text-center rounded-lg text-xs font-black uppercase transition-all {{ $active_shift === 'Shift 1' ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-zinc-950 font-black shadow-md' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800' }}">
                        Shift 1
                    </button>
                    <button type="button" 
                            wire:click="setShift('Shift 2')" 
                            class="py-2 px-2 text-center rounded-lg text-xs font-black uppercase transition-all {{ $active_shift === 'Shift 2' ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-zinc-950 font-black shadow-md' : 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800' }}">
                        Shift 2
                    </button>
                </div>
            </div>
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

        <!-- Mobile Sack Cards View (Android & Small Screens < 640px) -->
        <div class="sm:hidden space-y-3">
            @foreach($items as $index => $item)
                <div wire:key="mobile-sack-row-{{ $index }}-{{ $item['id'] ?? 'temp' }}" class="bg-zinc-950/90 border border-zinc-800/90 rounded-2xl p-4 space-y-3 shadow-lg {{ !empty($item['is_locked_for_user']) ? 'opacity-85 border-amber-900/50 bg-zinc-950/70' : '' }}">
                    <!-- Header Bar: Sack No, Worker Status, and Delete Action -->
                    <div class="flex items-center justify-between pb-2 border-b border-zinc-800/80 gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-3 py-1 rounded-xl bg-amber-950 text-amber-400 font-mono font-black text-xs border border-amber-800/80 shadow-sm">
                                Karung #{{ $item['sack_number'] }}
                            </span>
                            @if(!empty($item['is_locked_for_user']))
                                <span class="text-[10px] text-amber-400 font-bold bg-amber-950 px-2 py-0.5 rounded-lg border border-amber-800/80 inline-flex items-center gap-1">
                                    🔒 {{ $item['creator_name'] ?: 'Pekerja' }} ({{ $item['shift'] ?? 'Shift Lampau' }})
                                </span>
                            @elseif(!empty($item['creator_name']) || !empty($item['shift']))
                                <span class="text-[10px] text-emerald-400 font-medium bg-emerald-950 px-2 py-0.5 rounded-lg border border-emerald-800/50 inline-flex items-center gap-1">
                                    👤 {{ $item['creator_name'] ?: 'Pekerja' }} ({{ $item['shift'] ?? '' }})
                                </span>
                            @endif
                        </div>

                        @if(count($items) > 1 && !in_array($status, ['CLOSED', 'locked']) && empty($item['is_locked_for_user']))
                            <button type="button" wire:click="removeSackRow({{ $index }})" class="p-2 min-w-[40px] min-h-[40px] rounded-xl bg-red-950/80 text-red-400 hover:bg-red-900 text-xs font-black flex items-center justify-center border border-red-800/60 shadow">
                                ✕
                            </button>
                        @endif
                    </div>

                    <!-- Input Fields Grid inside Card -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Gross (Kg) -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-zinc-400 mb-1">
                                Gross (Kg)
                                @if(! (auth()->user()?->isAdmin() || auth()->user()?->isSupervisor()))
                                    <span class="text-amber-500 font-normal lowercase">(Admin)</span>
                                @endif
                            </label>
                            <input type="number" step="0.01" inputmode="decimal" 
                                wire:model.live.debounce.300ms="items.{{ $index }}.gross_kg" 
                                {{ in_array($status, ['CLOSED', 'locked']) || !empty($item['is_locked_for_user']) || ! (auth()->user()?->isAdmin() || auth()->user()?->isSupervisor()) ? 'disabled' : '' }}
                                id="mobile-gross-input-{{ $index }}"
                                data-index="{{ $index }}"
                                onkeydown="if(event.key==='Enter'){ event.preventDefault(); const next=document.getElementById('mobile-tare-input-{{ $index }}') || document.getElementById('mobile-gross-input-{{ $index + 1 }}'); if(next){ next.focus(); next.select(); } }"
                                class="w-full px-3 py-2.5 min-h-[48px] rounded-xl bg-zinc-900 border border-zinc-800 text-emerald-400 font-black text-base focus:border-emerald-500 outline-none {{ !empty($item['is_locked_for_user']) || ! (auth()->user()?->isAdmin() || auth()->user()?->isSupervisor()) ? 'cursor-not-allowed text-zinc-500 bg-zinc-950/80' : '' }}" 
                                placeholder="0.00">
                        </div>

                        <!-- Tare (Kg) -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-zinc-400 mb-1">Tare / Wadah (Kg)</label>
                            <input type="number" step="0.01" inputmode="decimal" 
                                wire:model.live.debounce.300ms="items.{{ $index }}.tare_kg" 
                                {{ in_array($status, ['CLOSED', 'locked']) || !empty($item['is_locked_for_user']) ? 'disabled' : '' }}
                                id="mobile-tare-input-{{ $index }}"
                                onkeydown="if(event.key==='Enter'){ event.preventDefault(); const next=document.getElementById('mobile-gross-input-{{ $index + 1 }}'); if(next){ next.focus(); next.select(); } }"
                                class="w-full px-3 py-2.5 min-h-[48px] rounded-xl bg-zinc-900 border border-zinc-800 text-amber-400 font-bold text-base focus:border-amber-500 outline-none {{ !empty($item['is_locked_for_user']) ? 'cursor-not-allowed text-zinc-500' : '' }}" 
                                placeholder="2.00">
                        </div>

                        <!-- Netto (Kg) -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-zinc-400 mb-1">Netto / Bersih (Kg)</label>
                            <input type="number" step="0.01" value="{{ number_format($item['netto_kg'], 2) }}" readonly 
                                class="w-full px-3 py-2.5 min-h-[48px] rounded-xl bg-zinc-900/80 border border-zinc-800 text-amber-400 font-black text-base outline-none cursor-not-allowed">
                        </div>

                        <!-- Remark -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-zinc-400 mb-1">Catatan Shift</label>
                            <select wire:model="items.{{ $index }}.remark" {{ in_array($status, ['CLOSED', 'locked']) || !empty($item['is_locked_for_user']) ? 'disabled' : '' }} class="w-full px-3 py-2.5 min-h-[48px] rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-200 text-xs font-bold outline-none">
                                <option value="Normal">Normal</option>
                                <option value="Remnant">Remnant (Sisa)</option>
                            </select>
                        </div>
                    </div>

                    @if((float)($item['tare_kg'] ?? 0) > (float)($item['gross_kg'] ?? 0) && (float)($item['gross_kg'] ?? 0) > 0)
                        <span class="text-red-400 text-[10px] font-bold block pt-1">⚠️ Berat wadah tidak boleh lebih besar dari berat kotor.</span>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Desktop / Tablet Table Grid (≥ 640px) -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-xs min-w-[650px]">
                <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                    <tr>
                        <th class="px-3 py-3 text-center w-12">No</th>
                        <th class="px-3 py-3">
                            Berat Kotor / Gross (Kg)
                            @if(! (auth()->user()?->isAdmin() || auth()->user()?->isSupervisor()))
                                <span class="text-[10px] text-amber-500 font-normal lowercase">(Admin Only)</span>
                            @endif
                        </th>
                        <th class="px-3 py-3">Berat Wadah / Tare (Kg)</th>
                        <th class="px-3 py-3">Berat Bersih / Netto (Kg)</th>
                        <th class="px-3 py-3">Catatan & Status Shift</th>
                        <th class="px-3 py-3 text-center w-12">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80">
                    @foreach($items as $index => $item)
                        <tr wire:key="desktop-sack-row-{{ $index }}-{{ $item['id'] ?? 'temp' }}" class="hover:bg-zinc-800/40 transition-colors {{ !empty($item['is_locked_for_user']) ? 'bg-zinc-950/60 opacity-80' : '' }}">
                            <!-- No -->
                            <td class="px-3 py-3 text-center font-mono font-bold text-amber-400 text-sm">
                                {{ $item['sack_number'] }}
                            </td>

                            <!-- Gross (Kg) -->
                            <td class="px-3 py-3">
                                <input type="number" step="0.01" inputmode="decimal" 
                                    wire:model.live.debounce.300ms="items.{{ $index }}.gross_kg" 
                                    {{ in_array($status, ['CLOSED', 'locked']) || !empty($item['is_locked_for_user']) || ! (auth()->user()?->isAdmin() || auth()->user()?->isSupervisor()) ? 'disabled' : '' }}
                                    id="gross-input-{{ $index }}"
                                    data-index="{{ $index }}"
                                    onkeydown="if(event.key==='Enter'){ event.preventDefault(); const next=document.getElementById('gross-input-{{ $index + 1 }}'); if(next){ next.focus(); next.select(); } }"
                                    class="w-full px-3 py-2.5 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-emerald-400 font-black text-base focus:border-emerald-500 outline-none {{ !empty($item['is_locked_for_user']) || ! (auth()->user()?->isAdmin() || auth()->user()?->isSupervisor()) ? 'cursor-not-allowed text-zinc-500 bg-zinc-950/80' : '' }}" 
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
                                    <div class="mt-1 text-[10px] text-amber-400 font-bold bg-amber-950/80 px-2 py-0.5 rounded-lg border border-amber-800/80 inline-flex items-center gap-1">
                                        🔒 {{ $item['creator_name'] ?: 'Pekerja' }} ({{ $item['shift'] ?? 'Shift Lampau' }})
                                    </div>
                                @elseif(!empty($item['creator_name']) || !empty($item['shift']))
                                    <div class="mt-1 text-[10px] text-emerald-400 font-medium bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-800/50 inline-flex items-center gap-1">
                                        👤 {{ $item['creator_name'] ?: 'Pekerja' }} ({{ $item['shift'] ?? '' }})
                                    </div>
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
    <div id="separation-results-form" class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 space-y-5 shadow-xl transition-all duration-300 {{ $errors->has('separation_product_sack') ? 'ring-2 ring-red-500 shadow-red-950/50' : '' }}">
        @if ($errors->has('separation_product_sack') || session()->has('error'))
            <div class="bg-red-950/90 border border-red-700 text-red-200 p-4 rounded-2xl text-xs font-bold flex items-center justify-between shadow-xl animate-pulse">
                <span>{{ $errors->first('separation_product_sack') ?: session('error') }}</span>
                <button type="button" @click="document.getElementById('separation_product_sack_input')?.focus()" class="px-3.5 py-1.5 rounded-xl bg-red-800 text-white text-[11px] font-black uppercase hover:bg-red-700 shadow">
                    Isi Sekarang &darr;
                </button>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-800 pb-3">
            <div>
                <h3 class="text-base font-black text-amber-400 uppercase tracking-wide">
                    Laporan Hasil Pemisahan Sesi Kerja Ini (Separation Results)
                </h3>
                <p class="text-[11px] text-zinc-400 mt-0.5">Semua kalkulasi berat kotor, wadah (tare), bersih (Netto), dan persentase yield dihitung secara otomatis.</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs text-amber-400 font-bold bg-amber-950 px-3 py-1.5 rounded-xl border border-amber-800/80">
                    Gross Standard: {{ number_format($product_kg_per_sack, 2) }} kg/sak
                </span>
                <span class="text-xs text-emerald-400 font-bold bg-emerald-950 px-3 py-1.5 rounded-xl border border-emerald-800/80">
                    Tare Standard: {{ number_format($product_tare_per_sack, 2) }} kg/sak
                </span>
            </div>
        </div>

        <!-- PROCESS STAGE SWITCHER TABS -->
        <div class="flex flex-col sm:flex-row items-center justify-between bg-zinc-950 p-2.5 rounded-2xl border border-zinc-800 gap-3">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="button" wire:click="setProcessStage(1)" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-xs font-black uppercase transition-all flex items-center justify-center gap-2 {{ $process_stage === 1 ? 'bg-amber-500 text-zinc-950 shadow-md shadow-amber-500/20' : 'bg-zinc-900 text-zinc-400 hover:text-zinc-200' }}">
                    <span>🟢</span> 
                    <span>Proses 1 (Pemasukan Awal)</span>
                    @if($p1_is_locked)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $process_stage === 1 ? 'bg-zinc-950 text-emerald-400' : 'bg-emerald-950 text-emerald-300 border border-emerald-800' }}">
                            🔒 Terkunci ({{ $p1_product_sack }} Sak)
                        </span>
                    @endif
                </button>
                <button type="button" wire:click="setProcessStage(2)" class="flex-1 sm:flex-none px-4 py-2.5 rounded-xl text-xs font-black uppercase transition-all flex items-center justify-center gap-2 {{ $process_stage === 2 ? 'bg-amber-500 text-zinc-950 shadow-md shadow-amber-500/20' : 'bg-zinc-900 text-zinc-400 hover:text-zinc-200' }}">
                    <span>⚡</span> 
                    <span>Proses 2 (Lanjutan + Bit Stem)</span>
                    @if($p2_product_sack > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $process_stage === 2 ? 'bg-zinc-950 text-amber-400' : 'bg-amber-950 text-amber-300 border border-amber-800' }}">
                            P2: {{ $p2_product_sack }} Sak
                        </span>
                    @endif
                </button>
            </div>
            
            <div class="hidden lg:flex items-center gap-2 bg-zinc-900 px-3.5 py-1.5 rounded-xl border border-zinc-800 text-xs font-bold">
                <span class="text-zinc-400">Total Output (P1 + P2):</span>
                <strong class="text-emerald-400 font-mono">{{ $separation_product_sack }} Sak ({{ number_format($separation_product_kg, 2) }} kg)</strong>
            </div>
        </div>

        <div class="space-y-5">
            @if($process_stage === 1)
                <!-- PROSES 1 FORM: PRODUK JADI PROSES 1 & DEBU PROSES 1 -->
                @if($p1_is_locked)
                    <!-- P1 LOCKED STATUS BANNER -->
                    <div class="bg-emerald-950/80 border border-emerald-500/50 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xl">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center text-2xl shrink-0 border border-emerald-500/30">
                                🔒
                            </div>
                            <div>
                                <h5 class="text-sm font-black text-emerald-300 flex items-center gap-2">
                                    Bagian Proses 1 Telah Selesai & Terkunci
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-emerald-900 text-emerald-200 border border-emerald-700">Terkunci</span>
                                </h5>
                                <p class="text-xs text-zinc-300 mt-1">
                                    Produk P1: <strong class="text-emerald-400 font-mono">{{ $p1_product_sack }} Sak</strong> (<strong class="text-emerald-400 font-mono">{{ number_format($p1_product_kg, 2) }} kg Netto</strong>) • Debu P1: <strong class="text-orange-400 font-mono">{{ number_format($p1_dust_netto_kg, 2) }} kg</strong>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 w-full sm:w-auto">
                            @if(!in_array($status, ['CLOSED', 'locked']))
                                <button type="button" wire:click="unlockProses1" class="flex-1 sm:flex-none px-4 py-2.5 min-h-[44px] rounded-xl bg-zinc-900 hover:bg-zinc-800 text-amber-400 border border-amber-500/50 text-xs font-bold transition-all shadow flex items-center justify-center gap-1.5">
                                    <span>🔓</span> Buka Kunci P1 (Edit)
                                </button>
                            @endif
                            <button type="button" wire:click="setProcessStage(2)" class="flex-1 sm:flex-none px-5 py-2.5 min-h-[44px] rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-500 text-white text-xs font-black transition-all shadow flex items-center justify-center gap-1.5">
                                <span>Lanjut ke Proses 2</span> <span>➔</span>
                            </button>
                        </div>
                    </div>
                @endif

                <div class="bg-zinc-950 p-5 rounded-2xl border {{ $p1_is_locked ? 'border-emerald-800/40 bg-zinc-950/60' : 'border-emerald-900/60' }} space-y-4 shadow">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-800/80 pb-2">
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs font-black uppercase text-emerald-400 tracking-wider">1. Form Produk Jadi (Rajangan) - Proses 1 <span class="text-red-400">*</span></h4>
                            @if($p1_is_locked)
                                <span class="text-[9px] font-black uppercase bg-emerald-950 text-emerald-300 px-2 py-0.5 rounded border border-emerald-800">🔒 Terkunci (Read-Only)</span>
                            @endif
                        </div>
                        <span class="text-[10px] font-bold text-emerald-300 bg-emerald-950 px-2.5 py-1 rounded border border-emerald-800 shrink-0">Pemasukan Awal</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-zinc-300 mb-1">Jumlah Produk Jadi (Sak/Karung) <span class="text-red-400">*</span></label>
                            <input type="number" id="separation_product_sack_input" min="0" step="1" inputmode="numeric" 
                                wire:model.live.debounce.500ms="p1_product_sack" 
                                {{ ($p1_is_locked || in_array($status, ['CLOSED', 'locked'])) ? 'disabled readonly' : '' }} 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-900 border {{ $errors->has('p1_product_sack') ? 'border-red-500 ring-2 ring-red-500/50' : 'border-emerald-500/80' }} text-emerald-400 text-base font-black outline-none focus:border-emerald-500 {{ ($p1_is_locked || in_array($status, ['CLOSED', 'locked'])) ? 'opacity-80 cursor-not-allowed bg-zinc-950' : '' }}" 
                                placeholder="0">
                            @error('p1_product_sack') <span class="text-red-400 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-amber-400 mb-1">Remnant Gross P1 (kg) <span class="text-zinc-500 font-normal">(Sisa Produk per Kg)</span></label>
                            <input type="text" inputmode="decimal" 
                                wire:model.live.debounce.500ms="p1_remnant_gross_kg" 
                                {{ ($p1_is_locked || in_array($status, ['CLOSED', 'locked'])) ? 'disabled readonly' : '' }} 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-900 border border-amber-500/80 text-amber-300 text-sm font-bold outline-none focus:border-amber-500 {{ ($p1_is_locked || in_array($status, ['CLOSED', 'locked'])) ? 'opacity-80 cursor-not-allowed bg-zinc-950' : '' }}" 
                                placeholder="0.00">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-amber-400 mb-1">Remnant Tare P1 (kg)</label>
                            <input type="text" inputmode="decimal" 
                                wire:model.live.debounce.500ms="p1_remnant_tare_kg" 
                                {{ ($p1_is_locked || in_array($status, ['CLOSED', 'locked'])) ? 'disabled readonly' : '' }} 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-900 border border-amber-500/80 text-amber-300 text-sm font-bold outline-none focus:border-amber-500 {{ ($p1_is_locked || in_array($status, ['CLOSED', 'locked'])) ? 'opacity-80 cursor-not-allowed bg-zinc-950' : '' }}" 
                                placeholder="0.00">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-amber-400 mb-1">Tare Produk Per Sak (kg/sak)</label>
                            <input type="text" inputmode="decimal" 
                                wire:model.live.debounce.500ms="product_tare_per_sack" 
                                {{ ($p1_is_locked || in_array($status, ['CLOSED', 'locked'])) ? 'disabled readonly' : '' }} 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-950 border border-amber-500/80 text-amber-300 font-bold text-sm outline-none focus:border-amber-400 {{ ($p1_is_locked || in_array($status, ['CLOSED', 'locked'])) ? 'opacity-80 cursor-not-allowed' : '' }}" 
                                placeholder="0.20">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-zinc-300 mb-1">Hasil Netto Produk P1 (kg)</label>
                            <input type="text" value="{{ number_format($p1_product_kg, 2) }} kg" readonly class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-900 border border-emerald-800/60 text-emerald-400 font-mono text-sm font-bold outline-none cursor-not-allowed">
                        </div>

                        <div class="flex flex-col justify-end">
                            <div class="bg-emerald-950 px-4 py-2.5 rounded-xl border border-emerald-800 flex items-center justify-between">
                                <span class="text-xs font-bold text-emerald-400 uppercase">Remnant Netto P1:</span>
                                <span class="text-emerald-300 text-base font-black font-mono">{{ number_format($p1_remnant_netto_kg, 2) }} kg</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Debu / Dust Multi-Row Slot Table (Proses 1) -->
                <div class="bg-zinc-950 p-5 rounded-2xl border {{ $p1_is_locked ? 'border-orange-900/40 bg-zinc-950/60' : 'border-orange-900/60' }} space-y-4 shadow">
                    <div class="flex items-center justify-between border-b border-zinc-800/80 pb-2">
                        <div class="flex items-center gap-2">
                            <h4 class="text-xs font-black uppercase text-orange-400 tracking-wider">3. Debu / Dust (Multi-Slot Wadah) - Proses 1</h4>
                            @if($p1_is_locked)
                                <span class="text-[9px] font-black uppercase bg-orange-950 text-orange-300 px-2 py-0.5 rounded border border-orange-800">🔒 Terkunci</span>
                            @endif
                        </div>
                        @if(! $p1_is_locked && !in_array($status, ['CLOSED', 'locked']))
                            <button type="button" wire:click="addP1DustRow" class="px-3.5 py-1.5 bg-orange-950 text-orange-300 border border-orange-800 hover:bg-orange-900 rounded-xl text-xs font-bold flex items-center gap-1 shadow">
                                ➕ Tambah Slot Wadah P1
                            </button>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-[10px] text-zinc-400 font-bold uppercase border-b border-zinc-800">
                                    <th class="pb-2 w-12">No</th>
                                    <th class="pb-2">Gross (kg)</th>
                                    <th class="pb-2">Tare (kg)</th>
                                    <th class="pb-2">Netto (kg)</th>
                                    <th class="pb-2 w-24 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-900">
                                @foreach($p1_dust_items as $idx => $dItem)
                                    <tr wire:key="p1-dust-row-{{ $idx }}">
                                        <td class="py-2 text-zinc-400 font-bold">{{ $idx + 1 }}</td>
                                        <td class="py-2 pr-3">
                                            <input type="text" inputmode="decimal" 
                                                wire:model.live.debounce.500ms="p1_dust_items.{{ $idx }}.gross_kg" 
                                                {{ ($p1_is_locked || in_array($status, ['CLOSED', 'locked'])) ? 'disabled readonly' : '' }} 
                                                class="w-full px-3 py-1.5 rounded-lg bg-zinc-900 border border-zinc-800 text-orange-400 font-bold text-xs outline-none focus:border-orange-500 {{ ($p1_is_locked || in_array($status, ['CLOSED', 'locked'])) ? 'opacity-80 cursor-not-allowed' : '' }}" 
                                                placeholder="0.00">
                                        </td>
                                        <td class="py-2 pr-3">
                                            <input type="text" inputmode="decimal" 
                                                wire:model.live.debounce.500ms="p1_dust_items.{{ $idx }}.tare_kg" 
                                                {{ ($p1_is_locked || in_array($status, ['CLOSED', 'locked'])) ? 'disabled readonly' : '' }} 
                                                class="w-full px-3 py-1.5 rounded-lg bg-zinc-900 border border-zinc-800 text-orange-400 font-bold text-xs outline-none focus:border-orange-500 {{ ($p1_is_locked || in_array($status, ['CLOSED', 'locked'])) ? 'opacity-80 cursor-not-allowed' : '' }}" 
                                                placeholder="0.00">
                                        </td>
                                        <td class="py-2 font-mono text-orange-400 font-bold">
                                            {{ number_format((float) str_replace(',', '.', $dItem['netto_kg'] ?? 0), 2) }} kg
                                        </td>
                                        <td class="py-2 text-center">
                                            @if(count($p1_dust_items) > 1 && ! $p1_is_locked && !in_array($status, ['CLOSED', 'locked']))
                                                <button type="button" wire:click="removeP1DustRow({{ $idx }})" class="px-2.5 py-1 bg-red-950 text-red-400 border border-red-800 hover:bg-red-900 rounded-lg text-xs font-bold">✕ Hapus</button>
                                            @elseif($p1_is_locked)
                                                <span class="text-[10px] text-zinc-500">🔒</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-2 border-t border-zinc-800/80 flex items-center justify-between text-xs font-bold">
                        <span class="text-orange-400 font-mono text-sm">Total Debu Netto P1: {{ number_format($p1_dust_netto_kg, 2) }} kg</span>
                    </div>
                </div>

                <!-- BOTTOM ACTION BUTTON TO COMPLETE AND LOCK PROCESS 1 -->
                @if(! $p1_is_locked && !in_array($status, ['CLOSED', 'locked']))
                    <div class="bg-zinc-950 p-4 sm:p-5 rounded-2xl border border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-lg">
                        <div class="text-xs text-zinc-400 text-center sm:text-left">
                            <strong class="text-zinc-200 block mb-0.5">Sudah selesai proses pemisahan tahap pertama?</strong>
                            Kunci bagian Proses 1 untuk mengamankan data dan lanjut mengisi Proses 2.
                        </div>
                        <button type="button" wire:click="lockProses1" class="w-full sm:w-auto px-6 py-3.5 min-h-[48px] rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 text-white font-black text-xs shadow-xl shadow-emerald-950/60 flex items-center justify-center gap-2 transition-all">
                            <span>🔒</span> Selesai & Kunci Proses 1 (Lanjut ke Proses 2)
                        </button>
                    </div>
                @endif

            @else
                <!-- PROSES 2 FORM: PRODUK JADI PROSES 2, BIT STEM, DEBU TERKUNCI (P1) & DEBU PROSES 2 -->
                
                <!-- PROSES 1 BASELINE REFERENCE BANNER -->
                <div class="bg-zinc-900 border border-zinc-700 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">📦</span>
                        <div>
                            <span class="text-[10px] font-black uppercase text-zinc-400 tracking-wider block">Baseline Data Dari Proses 1:</span>
                            <p class="text-xs font-bold text-zinc-200 mt-0.5">
                                Produk P1: <span class="text-emerald-400 font-mono font-black">{{ $p1_product_sack }} Sak</span> (<span class="text-emerald-300 font-mono">{{ number_format($p1_product_kg, 2) }} kg Netto</span>) • Debu P1: <span class="text-orange-400 font-mono">{{ number_format($p1_dust_netto_kg, 2) }} kg</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($p1_is_locked)
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-emerald-950 text-emerald-300 border border-emerald-800">
                                🔒 P1 Terkunci
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-amber-950 text-amber-300 border border-amber-800">
                                ⚠️ P1 Belum Dikunci
                            </span>
                        @endif
                        <button type="button" wire:click="setProcessStage(1)" class="px-3 py-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-[11px] font-bold border border-zinc-700">
                            🔍 Lihat / Ubah P1
                        </button>
                    </div>
                </div>

                <div class="bg-zinc-950 p-5 rounded-2xl border border-emerald-900/60 space-y-4 shadow transition-all animate-in fade-in duration-300">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-800/80 pb-2">
                        <h4 class="text-xs font-black uppercase text-emerald-400 tracking-wider">1. Form Produk Jadi (Rajangan) - Proses 2 <span class="text-red-400">*</span></h4>
                        <span class="text-[10px] font-bold text-emerald-300 bg-emerald-950 px-2.5 py-1 rounded border border-emerald-800 shrink-0">Lanjutan Shift / Proses 2</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-zinc-300 mb-1">Jumlah Produk Jadi P2 (Sak/Karung) <span class="text-red-400">*</span></label>
                            <input type="number" id="p2_product_sack_input" min="0" step="1" inputmode="numeric" 
                                wire:model.live.debounce.500ms="p2_product_sack" 
                                {{ in_array($status, ['CLOSED', 'locked']) ? 'disabled' : '' }} 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-900 border border-emerald-500/80 text-emerald-400 text-base font-black outline-none focus:border-emerald-500" 
                                placeholder="0">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-amber-400 mb-1">Remnant Gross P2 (kg) <span class="text-zinc-500 font-normal">(Sisa Produk per Kg)</span></label>
                            <input type="text" inputmode="decimal" 
                                wire:model.live.debounce.500ms="p2_remnant_gross_kg" 
                                {{ in_array($status, ['CLOSED', 'locked']) ? 'disabled' : '' }} 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-900 border border-amber-500/80 text-amber-300 text-sm font-bold outline-none focus:border-amber-500" 
                                placeholder="0.00">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-amber-400 mb-1">Remnant Tare P2 (kg)</label>
                            <input type="text" inputmode="decimal" 
                                wire:model.live.debounce.500ms="p2_remnant_tare_kg" 
                                {{ in_array($status, ['CLOSED', 'locked']) ? 'disabled' : '' }} 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-900 border border-amber-500/80 text-amber-300 text-sm font-bold outline-none focus:border-amber-500" 
                                placeholder="0.00">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-zinc-300 mb-1">Hasil Netto Produk P2 (kg)</label>
                            <input type="text" value="{{ number_format($p2_product_kg, 2) }} kg" readonly class="w-full px-3.5 py-2.5 rounded-xl bg-zinc-900 border border-emerald-800/60 text-emerald-400 font-mono text-sm font-bold outline-none cursor-not-allowed">
                        </div>

                        <div class="flex flex-col justify-end lg:col-span-2">
                            <div class="bg-emerald-950 px-4 py-2.5 rounded-xl border border-emerald-800 flex items-center justify-between">
                                <span class="text-xs font-bold text-emerald-400 uppercase">Kombinasi Netto Total Produk (P1 + P2):</span>
                                <span class="text-emerald-300 text-base font-black font-mono">{{ $separation_product_sack }} Sak ({{ number_format($separation_product_kg, 2) }} kg)</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2.5 border-t border-zinc-800/60 flex flex-wrap items-center gap-4 text-xs font-bold text-zinc-400">
                        <span>P1 Netto: <strong class="text-zinc-200 font-mono">{{ number_format($p1_product_kg, 2) }} kg ({{ $p1_product_sack }} Sak)</strong></span>
                        <span>•</span>
                        <span>P2 Netto: <strong class="text-zinc-200 font-mono">{{ number_format($p2_product_kg, 2) }} kg ({{ $p2_product_sack }} Sak)</strong></span>
                        <span>•</span>
                        <span>Total Gabungan (PDF & Stock): <strong class="text-emerald-400 font-mono">{{ number_format($separation_product_kg, 2) }} kg ({{ $separation_product_sack }} Sak)</strong></span>
                    </div>
                </div>

                <!-- 2. Bit Stem Multi-Row Slot Table (Proses 2) -->
                <div class="bg-zinc-950 p-5 rounded-2xl border border-amber-900/60 space-y-4 shadow transition-all animate-in fade-in duration-300">
                    <div class="flex items-center justify-between border-b border-zinc-800/80 pb-2">
                        <h4 class="text-xs font-black uppercase text-amber-400 tracking-wider">2. Bit Stem / Gagang (Multi-Slot Wadah)</h4>
                        @if(!in_array($status, ['CLOSED', 'locked']))
                            <button type="button" wire:click="addBitStemRow" class="px-3.5 py-1.5 bg-amber-950 text-amber-300 border border-amber-800 hover:bg-amber-900 rounded-xl text-xs font-bold flex items-center gap-1 shadow">
                                ➕ Tambah Slot Wadah
                            </button>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-[10px] text-zinc-400 font-bold uppercase border-b border-zinc-800">
                                    <th class="pb-2 w-12">No</th>
                                    <th class="pb-2">Gross (kg)</th>
                                    <th class="pb-2">Tare (kg)</th>
                                    <th class="pb-2">Netto (kg)</th>
                                    <th class="pb-2 w-24 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-900">
                                @foreach($bit_stem_items as $idx => $bItem)
                                    <tr wire:key="bit-stem-row-{{ $idx }}">
                                        <td class="py-2 text-zinc-400 font-bold">{{ $idx + 1 }}</td>
                                        <td class="py-2 pr-3">
                                            <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="bit_stem_items.{{ $idx }}.gross_kg" {{ in_array($status, ['CLOSED', 'locked']) ? 'disabled' : '' }} class="w-full px-3 py-1.5 rounded-lg bg-zinc-900 border border-zinc-800 text-amber-400 font-bold text-xs outline-none focus:border-amber-500" placeholder="0.00">
                                        </td>
                                        <td class="py-2 pr-3">
                                            <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="bit_stem_items.{{ $idx }}.tare_kg" {{ in_array($status, ['CLOSED', 'locked']) ? 'disabled' : '' }} class="w-full px-3 py-1.5 rounded-lg bg-zinc-900 border border-zinc-800 text-amber-400 font-bold text-xs outline-none focus:border-amber-500" placeholder="0.00">
                                        </td>
                                        <td class="py-2 font-mono text-amber-400 font-bold">
                                            {{ number_format((float) str_replace(',', '.', $bItem['netto_kg'] ?? 0), 2) }} kg
                                        </td>
                                        <td class="py-2 text-center">
                                            @if(count($bit_stem_items) > 1 && !in_array($status, ['CLOSED', 'locked']))
                                                <button type="button" wire:click="removeBitStemRow({{ $idx }})" class="px-2.5 py-1 bg-red-950 text-red-400 border border-red-800 hover:bg-red-900 rounded-lg text-xs font-bold">✕ Hapus</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-2 border-t border-zinc-800/80 flex items-center justify-between text-xs font-bold">
                        <span class="text-amber-400 font-mono text-sm">Total Bit Stem Netto: {{ number_format($separation_bits_stem_netto_kg, 2) }} kg</span>
                        <span class="px-3 py-1 rounded-xl bg-amber-950 text-amber-300 border border-amber-800 text-xs font-black">{{ number_format($yieldBitsStemPct, 2) }}%</span>
                    </div>
                </div>

                <!-- 3. Debu / Dust Multi-Row Slot Table (Proses 2 - Read Only P1 + Editable P2) -->
                <div class="bg-zinc-950 p-5 rounded-2xl border border-orange-900/60 space-y-4 shadow">
                    <div class="flex items-center justify-between border-b border-zinc-800/80 pb-2">
                        <h4 class="text-xs font-black uppercase text-orange-400 tracking-wider">3. Debu / Dust (Multi-Slot Wadah)</h4>
                        <span class="text-[10px] font-bold text-zinc-400 bg-zinc-900 px-2.5 py-1 rounded border border-zinc-800">
                            🔒 Data P1 Terkunci
                        </span>
                    </div>

                    <!-- DEBU PROSES 1 (READ ONLY / TERKUNCI IN PROSES 2) -->
                    @if(count($p1_dust_items) > 0)
                        <div class="space-y-2">
                            <span class="text-[10px] font-black uppercase text-zinc-400 tracking-wider block">🔒 Debu Dari Proses 1 (Terkunci / Read-Only):</span>
                            <div class="overflow-x-auto opacity-75">
                                <table class="w-full text-left text-xs bg-zinc-900/80 rounded-xl p-2 border border-zinc-800">
                                    <thead>
                                        <tr class="text-[10px] text-zinc-500 font-bold uppercase border-b border-zinc-800">
                                            <th class="py-2 px-3 w-12">No</th>
                                            <th class="py-2 px-3">Gross (kg)</th>
                                            <th class="py-2 px-3">Tare (kg)</th>
                                            <th class="py-2 px-3">Netto (kg)</th>
                                            <th class="py-2 px-3 w-32 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-800/60">
                                        @foreach($p1_dust_items as $idx => $dItem)
                                            <tr wire:key="p1-dust-readonly-row-{{ $idx }}">
                                                <td class="py-2 px-3 text-zinc-500 font-bold">{{ $idx + 1 }}</td>
                                                <td class="py-2 px-3 text-orange-300 font-bold">{{ number_format((float) str_replace(',', '.', $dItem['gross_kg'] ?? 0), 2) }} kg</td>
                                                <td class="py-2 px-3 text-orange-300 font-bold">{{ number_format((float) str_replace(',', '.', $dItem['tare_kg'] ?? 0), 2) }} kg</td>
                                                <td class="py-2 px-3 font-mono text-orange-400 font-bold">{{ number_format((float) str_replace(',', '.', $dItem['netto_kg'] ?? 0), 2) }} kg</td>
                                                <td class="py-2 px-3 text-center">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-800 text-zinc-400 border border-zinc-700">🔒 Terkunci (P1)</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- DEBU PROSES 2 (ADDITIONAL SLOTS IN PROSES 2) -->
                    <div class="space-y-2 pt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase text-orange-400 tracking-wider">Debu Tambahan Proses 2:</span>
                            @if(!in_array($status, ['CLOSED', 'locked']))
                                <button type="button" wire:click="addP2DustRow" class="px-3 py-1 bg-orange-950 text-orange-300 border border-orange-800 hover:bg-orange-900 rounded-xl text-[11px] font-bold">
                                    ➕ Tambah Slot Wadah P2
                                </button>
                            @endif
                        </div>

                        @if(count($p2_dust_items) > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="text-[10px] text-zinc-400 font-bold uppercase border-b border-zinc-800">
                                            <th class="pb-2 w-12">No</th>
                                            <th class="pb-2">Gross (kg)</th>
                                            <th class="pb-2">Tare (kg)</th>
                                            <th class="pb-2">Netto (kg)</th>
                                            <th class="pb-2 w-24 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-900">
                                        @foreach($p2_dust_items as $idx => $dItem)
                                            <tr wire:key="p2-dust-row-{{ $idx }}">
                                                <td class="py-2 text-zinc-400 font-bold">{{ $idx + 1 }}</td>
                                                <td class="py-2 pr-3">
                                                    <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="p2_dust_items.{{ $idx }}.gross_kg" {{ in_array($status, ['CLOSED', 'locked']) ? 'disabled' : '' }} class="w-full px-3 py-1.5 rounded-lg bg-zinc-900 border border-zinc-800 text-orange-400 font-bold text-xs outline-none focus:border-orange-500" placeholder="0.00">
                                                </td>
                                                <td class="py-2 pr-3">
                                                    <input type="text" inputmode="decimal" wire:model.live.debounce.500ms="p2_dust_items.{{ $idx }}.tare_kg" {{ in_array($status, ['CLOSED', 'locked']) ? 'disabled' : '' }} class="w-full px-3 py-1.5 rounded-lg bg-zinc-900 border border-zinc-800 text-orange-400 font-bold text-xs outline-none focus:border-orange-500" placeholder="0.00">
                                                </td>
                                                <td class="py-2 font-mono text-orange-400 font-bold">
                                                    {{ number_format((float) str_replace(',', '.', $dItem['netto_kg'] ?? 0), 2) }} kg
                                                </td>
                                                <td class="py-2 text-center">
                                                    @if(!in_array($status, ['CLOSED', 'locked']))
                                                        <button type="button" wire:click="removeP2DustRow({{ $idx }})" class="px-2.5 py-1 bg-red-950 text-red-400 border border-red-800 hover:bg-red-900 rounded-lg text-xs font-bold">✕ Hapus</button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-[11px] text-zinc-500 italic">Belum ada slot debu tambahan pada Proses 2.</p>
                        @endif
                    </div>

                    <div class="pt-2 border-t border-zinc-800/80 flex items-center justify-between text-xs font-bold">
                        <span class="text-orange-400 font-mono text-sm">Total Debu Netto (P1 + P2): {{ number_format($separation_dust_netto_kg, 2) }} kg</span>
                        <span class="px-3 py-1 rounded-xl bg-orange-950 text-orange-300 border border-orange-800 text-xs font-black">{{ number_format($yieldDustPct, 2) }}%</span>
                    </div>
                </div>
            @endif

            <!-- 4. Uncountable Waste (Kombinasi Total Input Netto - Total Output) -->
            <div class="bg-zinc-950 p-5 rounded-2xl border border-zinc-800 space-y-4 shadow">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-800/80 pb-2">
                    <h4 class="text-xs font-black uppercase text-zinc-300 tracking-wider">4. Uncountable Waste (Sisa Tidak Terhitung)</h4>
                    <span class="text-[10px] text-zinc-400 font-bold">Kalkulasi Otomatis Sisa Berat Pembagian (Input Netto - Total Output)</span>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex-1">
                        <input type="text" value="{{ number_format($separation_waste_kg, 2) }} kg" readonly class="w-full px-4 py-3 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-200 text-xl font-black outline-none cursor-not-allowed">
                    </div>
                    <div class="bg-zinc-900 px-5 py-3 rounded-xl border border-zinc-700 flex items-center justify-between gap-4 shrink-0">
                        <span class="text-xs font-bold text-zinc-400 uppercase">Uncountable Waste:</span>
                        <span class="text-zinc-200 text-base font-black font-mono">{{ number_format($yieldWastePct, 2) }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTION BUTTONS FOR WORKERS -->
    @if(!in_array($status, ['CLOSED', 'locked']))
        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-2">
            <button type="button" wire:click="submitPauseAndInterimReport" class="w-full sm:w-auto px-6 py-3.5 min-h-[48px] rounded-2xl bg-amber-950 text-amber-300 border border-amber-800 font-bold text-sm hover:bg-amber-900 shadow">
                🛑 Selesai Shift (Done Shift)
            </button>
            <button type="button" @click="showLockModal = true" class="w-full sm:w-auto px-8 py-3.5 min-h-[48px] rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-black text-sm hover:from-emerald-500 shadow-xl shadow-emerald-950/50">
                🔒 Selesai & Kunci Data
            </button>
        </div>
    @endif

    <!-- THANK YOU SHIFT COMPLETION MODAL POPUP -->
    @if($showThankYouModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md overflow-y-auto">
        <div class="bg-zinc-900 border border-emerald-500/40 rounded-3xl max-w-md w-full p-6 text-center space-y-5 shadow-2xl animate-in fade-in zoom-in duration-300 my-auto">
            <div class="w-16 h-16 rounded-full bg-emerald-950 border border-emerald-500/50 flex items-center justify-center mx-auto text-emerald-400 text-3xl shadow-lg shadow-emerald-950/80">
                ✓
            </div>
            
            <div class="space-y-2">
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-950 text-emerald-300 border border-emerald-800 tracking-wider">
                    🎉 Selesai Shift Berhasil
                </span>
                <h3 class="text-xl font-black text-zinc-100">Terima Kasih Telah Menyelesaikan Shift!</h3>
                <p class="text-xs text-zinc-400 leading-relaxed">
                    Laporan pemisahan & data timbangan Anda untuk shift ini telah berhasil disimpan dan terdeteksi di <strong>Live Tracking Admin</strong>.
                </p>
            </div>

            <div class="pt-2 border-t border-zinc-800">
                <button type="button" wire:click="$set('showThankYouModal', false)" class="w-full py-3.5 px-6 min-h-[48px] rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-black text-sm shadow-xl shadow-emerald-950/50 transition-all">
                    Siap, Mengerti 👍
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
