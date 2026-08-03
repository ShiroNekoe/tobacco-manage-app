<div x-data="{ showFinishModal: false }" class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h2 class="text-2xl font-black tracking-wide text-zinc-100">
                    {{ $productionRunId ? 'Edit / Monitoring Laporan Produksi' : 'Form Eksekusi Produksi Tembakau' }}
                </h2>
                <!-- Machine Status Badge -->
                @if($machine_status === 'running')
                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase bg-emerald-950 text-emerald-400 border border-emerald-700 flex items-center shadow-lg shadow-emerald-900/30">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 mr-2 animate-ping"></span>
                        STATUS MESIN: RUNNING
                    </span>
                @elseif($machine_status === 'completed')
                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase bg-blue-950 text-blue-400 border border-blue-700 flex items-center">
                        STATUS MESIN: SELESAI / LOCKED
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-black uppercase bg-red-950 text-red-400 border border-red-700 flex items-center">
                        STATUS MESIN: STOP / STOPPAGE
                    </span>
                @endif
            </div>
            <p class="text-xs text-zinc-400 mt-1">Sistem pencatatan proses produksi real-time & otomatisasi sertifikat mutu</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('production.list') }}" class="px-4 py-2 min-h-[44px] inline-flex items-center text-xs font-semibold rounded-xl bg-zinc-800 text-zinc-300 hover:bg-zinc-700">
                &larr; Riwayat Produksi
            </a>
            @if($status === 'locked' && (auth()->user()->isAdmin() || auth()->user()->isSupervisor()))
                <button wire:click="unlockRecord" class="px-4 py-2 min-h-[44px] inline-flex items-center text-xs font-bold rounded-xl bg-red-900/80 text-red-200 border border-red-700 hover:bg-red-800">
                    🔓 Reopen / Unlock Data
                </button>
            @endif
        </div>
    </div>

    <!-- Locked Warning Alert -->
    @if($status === 'locked')
        <div class="p-4 rounded-xl bg-amber-950/80 border border-amber-700/80 text-amber-200 flex items-center space-x-3">
            <svg class="w-6 h-6 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            <div>
                <p class="font-bold text-sm">Data Produksi Ini Telah Dikunci (Locked)</p>
                <p class="text-xs text-amber-300/80">Sertifikat telah diterbitkan. Data tidak dapat diubah oleh Operator/Warehouse. Hanya Administrator atau Supervisor yang dapat membuka kunci (Unlock).</p>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="saveDraft" class="space-y-6">
        
        <!-- STAGE 1: Approved MRL Selection -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-amber-400 flex items-center">
                <span class="w-6 h-6 rounded-full bg-amber-950 text-amber-400 border border-amber-800 flex items-center justify-center text-xs mr-2">1</span>
                Pilih Material Receipt List (MRL Approved)
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Nomor MRL <span class="text-red-400">*</span></label>
                    <select wire:model.live="mrl_id" {{ $status === 'locked' ? 'disabled' : '' }} class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none">
                        <option value="">-- Pilih MRL --</option>
                        @foreach($approvedMrls as $mItem)
                            <option value="{{ $mItem->id }}">
                                {{ $mItem->mrl_number }} - Batch: {{ $mItem->batch_number }} (Net: {{ number_format($mItem->net_weight, 2) }} kg - {{ $mItem->origin_region }})
                            </option>
                        @endforeach
                    </select>
                    @error('mrl_id') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Net Weight Input Bahan Mentah (kg)</label>
                    <input type="number" step="0.01" value="{{ $netWeight }}" readonly class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950/80 border border-zinc-800 text-amber-400 font-black text-xl outline-none cursor-not-allowed">
                    <p class="text-[11px] text-zinc-500 mt-1">Bobot bersih dasar acuan penghitungan Yield % dan Waste %</p>
                </div>
            </div>
        </div>

        <!-- STAGE 2: Production Planning & Identity (Shift, Group, Personnel) -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-amber-400 flex items-center">
                <span class="w-6 h-6 rounded-full bg-amber-950 text-amber-400 border border-amber-800 flex items-center justify-center text-xs mr-2">2</span>
                Identitas Regu & Jadwal Shift
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <!-- Shift -->
                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Shift Kerja (12 Jam/Shift) <span class="text-red-400">*</span></label>
                    <select wire:model="shift" {{ $status === 'locked' ? 'disabled' : '' }} class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none">
                        <option value="shift_1">Shift 1 (07:00 - 19:00)</option>
                        <option value="shift_2">Shift 2 (19:00 - 07:00)</option>
                    </select>
                </div>

                <!-- Group -->
                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Group Produksi <span class="text-red-400">*</span></label>
                    <select wire:model="group_name" {{ $status === 'locked' ? 'disabled' : '' }} class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none">
                        <option value="group_a">Group A</option>
                        <option value="group_b">Group B</option>
                        <option value="group_c">Group C</option>
                    </select>
                </div>

                <!-- Start Time -->
                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Waktu Mulai (Start Time) <span class="text-red-400">*</span></label>
                    <input type="datetime-local" wire:model="start_time" {{ $status === 'locked' ? 'disabled' : '' }} class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none">
                </div>

                <!-- Personnel -->
                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Group Leader <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="group_leader_name" {{ $status === 'locked' ? 'disabled' : '' }} class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none" placeholder="Nama Group Leader">
                    @error('group_leader_name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Operator 1 <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="operator_1_name" {{ $status === 'locked' ? 'disabled' : '' }} class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none" placeholder="Nama Operator 1">
                    @error('operator_1_name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Operator 2 <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="operator_2_name" {{ $status === 'locked' ? 'disabled' : '' }} class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none" placeholder="Nama Operator 2">
                    @error('operator_2_name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- STAGE 3: Production Data Entry & Real-time Output Weights -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 space-y-4">
            <h3 class="text-sm font-bold uppercase tracking-wider text-amber-400 flex items-center">
                <span class="w-6 h-6 rounded-full bg-amber-950 text-amber-400 border border-amber-800 flex items-center justify-center text-xs mr-2">3</span>
                Input Data Hasil Timbangan Produksi (Hasil Rajangan & Sampingan)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Product Weight -->
                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Product Weight / Produk Jadi (kg) <span class="text-red-400">*</span></label>
                    <input type="number" step="0.01" inputmode="decimal" wire:model.live.debounce.300ms="product_weight" {{ $status === 'locked' ? 'disabled' : '' }} class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-emerald-400 text-xl font-black focus:border-emerald-500 outline-none" placeholder="0.00">
                    @error('product_weight') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Bits Stem Weight -->
                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Bits Stem / Gagang (kg) <span class="text-red-400">*</span></label>
                    <input type="number" step="0.01" inputmode="decimal" wire:model.live.debounce.300ms="bits_stem_weight" {{ $status === 'locked' ? 'disabled' : '' }} class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-amber-400 text-lg font-bold focus:border-amber-500 outline-none" placeholder="0.00">
                    @error('bits_stem_weight') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Dust Weight -->
                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Dust / Debu (kg) <span class="text-red-400">*</span></label>
                    <input type="number" step="0.01" inputmode="decimal" wire:model.live.debounce.300ms="dust_weight" {{ $status === 'locked' ? 'disabled' : '' }} class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-orange-400 text-lg font-bold focus:border-orange-500 outline-none" placeholder="0.00">
                    @error('dust_weight') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Waste Weight (Auto) -->
                <div>
                    <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Waste Sisa / Hilang (kg) <span class="text-amber-500">(Auto)</span></label>
                    <input type="number" step="0.01" value="{{ $wasteWeight }}" readonly class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-zinc-950/80 border border-zinc-800 text-zinc-400 text-lg font-bold outline-none cursor-not-allowed">
                    <p class="text-[11px] text-zinc-500 mt-1">Net Weight - (Produk + Gagang + Debu)</p>
                </div>
            </div>
        </div>

        <!-- STAGE 4: Downtime Stoppage Events (Multiple Dynamic Rows) -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-amber-400 flex items-center">
                    <span class="w-6 h-6 rounded-full bg-amber-950 text-amber-400 border border-amber-800 flex items-center justify-center text-xs mr-2">4</span>
                    Pencatatan Downtime / Kendala Proses Stoppage
                </h3>
                @if($status !== 'locked')
                    <button type="button" wire:click="addDowntimeRow" class="px-3 py-1.5 min-h-[44px] text-xs font-bold rounded-lg bg-zinc-800 text-amber-400 border border-amber-800/60 hover:bg-zinc-700">
                        + Tambah Baris Downtime
                    </button>
                @endif
            </div>

            <div class="space-y-3">
                @foreach($downtimeEvents as $index => $event)
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center bg-zinc-950 p-3 rounded-xl border border-zinc-800">
                        <div class="sm:col-span-3">
                            <label class="block text-[11px] font-semibold text-zinc-400 mb-1">Durasi Downtime (menit)</label>
                            <input type="number" inputmode="numeric" wire:model.live.debounce.300ms="downtimeEvents.{{ $index }}.minutes" {{ $status === 'locked' ? 'disabled' : '' }} class="w-full px-3 py-2 min-h-[44px] rounded-lg bg-zinc-900 border border-zinc-800 text-red-400 font-bold text-sm outline-none" placeholder="0">
                        </div>
                        <div class="sm:col-span-4">
                            <label class="block text-[11px] font-semibold text-zinc-400 mb-1">Alasan Downtime</label>
                            <select wire:model="downtimeEvents.{{ $index }}.reason" {{ $status === 'locked' ? 'disabled' : '' }} class="w-full px-3 py-2 min-h-[44px] rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-200 text-xs outline-none">
                                <option value="Cleaning / Ganti Mess">Cleaning / Ganti Mess</option>
                                <option value="Black Airlock 2 Stoppage">Black Airlock 2 Stoppage</option>
                                <option value="Macet Pisau Cutter">Macet Pisau Cutter</option>
                                <option value="Perbaikan Feeder / Conveyor">Perbaikan Feeder / Conveyor</option>
                                <option value="Listrik / Utility Shutdown">Listrik / Utility Shutdown</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="sm:col-span-4">
                            <label class="block text-[11px] font-semibold text-zinc-400 mb-1">Keterangan Tambahan</label>
                            <input type="text" wire:model="downtimeEvents.{{ $index }}.remarks" {{ $status === 'locked' ? 'disabled' : '' }} class="w-full px-3 py-2 min-h-[44px] rounded-lg bg-zinc-900 border border-zinc-800 text-zinc-300 text-xs outline-none" placeholder="Detail kendala...">
                        </div>
                        <div class="sm:col-span-1 text-center pt-4 sm:pt-0">
                            @if(count($downtimeEvents) > 1 && $status !== 'locked')
                                <button type="button" wire:click="removeDowntimeRow({{ $index }})" class="p-2 min-w-[44px] min-h-[44px] rounded-lg bg-red-950 text-red-400 hover:bg-red-900">
                                    &times;
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="text-right text-xs text-zinc-400 font-mono">
                Total Downtime: <span class="font-bold text-red-400 text-sm">{{ $totalDowntimeMinutes }} Menit</span>
            </div>
        </div>

        <!-- REAL-TIME KPI ENGINE DASHBOARD CARDS -->
        <div class="bg-gradient-to-br from-zinc-900 to-zinc-950 border border-amber-800/40 rounded-2xl p-6 space-y-4">
            <h3 class="text-sm font-black uppercase tracking-wider text-amber-400 flex items-center justify-between">
                <span>⚡ Real-Time Automatic KPI Calculation Engine</span>
                <span class="text-[11px] font-normal text-zinc-400">Otomatis Terkalkulasi Tanpa Hitung Manual</span>
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
                <!-- 1. Product Yield % -->
                <div class="bg-zinc-950 p-3 rounded-xl border border-emerald-900/60 text-center">
                    <p class="text-[10px] uppercase font-bold text-zinc-400">Product Yield</p>
                    <p class="text-xl font-black text-emerald-400 font-mono mt-1">{{ number_format($productYieldPct, 2) }}%</p>
                </div>

                <!-- 2. Bits Stem % -->
                <div class="bg-zinc-950 p-3 rounded-xl border border-amber-900/60 text-center">
                    <p class="text-[10px] uppercase font-bold text-zinc-400">Bits Stem %</p>
                    <p class="text-xl font-black text-amber-400 font-mono mt-1">{{ number_format($bitsStemPct, 2) }}%</p>
                </div>

                <!-- 3. Dust % -->
                <div class="bg-zinc-950 p-3 rounded-xl border border-orange-900/60 text-center">
                    <p class="text-[10px] uppercase font-bold text-zinc-400">Dust %</p>
                    <p class="text-xl font-black text-orange-400 font-mono mt-1">{{ number_format($dustPct, 2) }}%</p>
                </div>

                <!-- 4. Waste % -->
                <div class="bg-zinc-950 p-3 rounded-xl border border-zinc-800 text-center">
                    <p class="text-[10px] uppercase font-bold text-zinc-400">Waste %</p>
                    <p class="text-xl font-black text-zinc-300 font-mono mt-1">{{ number_format($wastePct, 2) }}%</p>
                </div>

                <!-- 5. Uptime % -->
                <div class="bg-zinc-950 p-3 rounded-xl border border-blue-900/60 text-center">
                    <p class="text-[10px] uppercase font-bold text-zinc-400">Uptime %</p>
                    <p class="text-xl font-black text-blue-400 font-mono mt-1">{{ number_format($uptimePct, 2) }}%</p>
                </div>

                <!-- 6. Capacity kg/hr -->
                <div class="bg-zinc-950 p-3 rounded-xl border border-teal-900/60 text-center">
                    <p class="text-[10px] uppercase font-bold text-zinc-400">Kapasitas</p>
                    <p class="text-lg font-black text-teal-400 font-mono mt-1">{{ number_format($capacityKgHr, 1) }}</p>
                    <p class="text-[9px] text-zinc-500">kg/jam</p>
                </div>

                <!-- 7. Performance % -->
                <div class="bg-zinc-950 p-3 rounded-xl border border-purple-900/60 text-center">
                    <p class="text-[10px] uppercase font-bold text-zinc-400">Performance</p>
                    <p class="text-xl font-black text-purple-400 font-mono mt-1">{{ number_format($performancePct, 2) }}%</p>
                </div>
            </div>
        </div>

        <!-- Remarks Observation -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6">
            <label class="block text-xs font-bold uppercase text-zinc-300 mb-2">Catatan Observasi Proses (Remarks)</label>
            <textarea wire:model="remarks" {{ $status === 'locked' ? 'disabled' : '' }} rows="3" class="w-full p-4 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-sm outline-none focus:border-amber-500" placeholder="Catatan kondisi operasional mesin dan tembakau..."></textarea>
        </div>

        <!-- Action Form Buttons -->
        @if($status !== 'locked')
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-zinc-800">
                <button type="submit" class="w-full sm:w-auto px-6 py-3 min-h-[44px] rounded-xl bg-zinc-800 text-zinc-200 font-bold text-sm hover:bg-zinc-700">
                    💾 Simpan Draft
                </button>
                <button type="button" @click="showFinishModal = true" class="w-full sm:w-auto px-8 py-3 min-h-[44px] rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-black text-sm hover:from-emerald-500 hover:to-emerald-600 shadow-xl shadow-emerald-950/50">
                    ✅ Finish Production / Selesai
                </button>
            </div>
        @endif
    </form>

    <!-- ALPINE.JS CONFIRMATION MODAL ALERT -->
    <div x-show="showFinishModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-zinc-900 border border-zinc-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-6">
            <div class="flex items-center space-x-3 text-amber-400">
                <svg class="w-8 h-8 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <h3 class="text-lg font-black text-zinc-100">Konfirmasi Penyelesaian Produksi</h3>
            </div>

            <!-- MANDATORY ALERT TEXT FROM REQUIREMENTS BRIEF -->
            <p class="text-sm text-zinc-300 leading-relaxed bg-zinc-950 p-4 rounded-xl border border-zinc-800 font-medium">
                "Apakah seluruh data timbangan dan kendala proses sudah benar? Setelah dikonfirmasi, data akan dikunci, sertifikat akan diterbitkan, dan data tidak dapat diubah kembali."
            </p>

            <div class="flex items-center justify-end space-x-3">
                <button type="button" @click="showFinishModal = false" class="px-5 py-2.5 min-h-[44px] rounded-xl bg-zinc-800 text-zinc-300 font-semibold text-xs hover:bg-zinc-700">
                    Batal (Batal)
                </button>
                <button type="button" wire:click="finishProduction" @click="showFinishModal = false" class="px-6 py-2.5 min-h-[44px] rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-bold text-xs hover:from-emerald-500 shadow-lg">
                    Ya, Konfirmasi & Kunci Data
                </button>
            </div>
        </div>
    </div>
</div>
