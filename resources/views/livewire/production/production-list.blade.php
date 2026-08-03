<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black tracking-wide text-zinc-100">Audit Trail & Riwayat Produksi Tembakau</h2>
            <p class="text-xs text-zinc-400 mt-1">Traceability log proses produksi, status kunci data, dan unduh sertifikat mutu</p>
        </div>
        @if(!auth()->user()->isWarehouse())
        <a href="{{ route('production.create') }}" class="px-5 py-2.5 min-h-[44px] inline-flex items-center justify-center font-bold text-xs rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white hover:from-amber-500 shadow-md">
            + Input Produksi Baru
        </a>
        @endif
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="w-full md:w-1/3">
            <input type="text" wire:model.live.debounce.300ms="search" class="w-full px-4 py-2.5 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none" placeholder="Cari Kode Produksi, MRL, Batch, Asal...">
        </div>
        <div class="w-full md:w-auto flex flex-wrap items-center gap-3">
            <div>
                <select wire:model.live="shiftFilter" class="px-3 py-2 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                    <option value="">Semua Shift</option>
                    <option value="shift_1">Shift 1</option>
                    <option value="shift_2">Shift 2</option>
                </select>
            </div>
            <div>
                <select wire:model.live="groupFilter" class="px-3 py-2 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                    <option value="">Semua Group</option>
                    <option value="group_a">Group A</option>
                    <option value="group_b">Group B</option>
                    <option value="group_c">Group C</option>
                </select>
            </div>
            <div>
                <select wire:model.live="statusFilter" class="px-3 py-2 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="running">Running / Draft</option>
                    <option value="locked">Completed / Locked</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Production Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
            <table class="w-full text-left text-xs text-zinc-300">
                <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase tracking-wider sticky top-0 z-10 border-b border-zinc-800">
                    <tr>
                        <th class="px-4 py-3.5">Kode Produksi</th>
                        <th class="px-4 py-3.5">MRL & Batch</th>
                        <th class="px-4 py-3.5">Group & Shift</th>
                        <th class="px-4 py-3.5 text-right">Product (kg)</th>
                        <th class="px-4 py-3.5 text-right">Yield (%)</th>
                        <th class="px-4 py-3.5 text-right">Capacity (kg/h)</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Sertifikat & Export</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80">
                    @forelse($productions as $prd)
                        <tr class="hover:bg-zinc-800/50 transition-colors">
                            <td class="px-4 py-3.5 font-mono font-bold text-amber-400 whitespace-nowrap">
                                {{ $prd->production_code }}
                                <div class="text-[10px] text-zinc-500 font-normal">
                                    {{ $prd->start_time ? $prd->start_time->format('Y-m-d H:i') : '-' }}
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-zinc-200">{{ $prd->mrl->mrl_number ?? '-' }}</div>
                                <div class="text-[11px] text-zinc-500 font-mono">Batch: {{ $prd->mrl->batch_number ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-bold text-zinc-200">{{ strtoupper(str_replace('_', ' ', $prd->group_name)) }}</div>
                                <div class="text-[11px] text-amber-400 font-semibold">{{ strtoupper(str_replace('_', ' ', $prd->shift)) }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-emerald-400 text-sm">
                                {{ number_format($prd->product_weight, 2) }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-emerald-400">
                                {{ number_format($prd->product_yield_pct, 2) }}%
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-zinc-300">
                                {{ number_format($prd->capacity_kg_hr, 1) }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($prd->isLocked())
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-950 text-blue-400 border border-blue-800">
                                        🔒 Locked
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-950 text-emerald-400 border border-emerald-800 animate-pulse">
                                        🟢 Running
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($prd->certificate)
                                    <div class="flex items-center justify-center space-x-1">
                                        <a href="{{ route('certificate.show', $prd->certificate->id) }}" target="_blank" class="px-2.5 py-1 min-h-[44px] inline-flex items-center text-[11px] font-bold rounded-lg bg-zinc-800 text-amber-400 hover:bg-zinc-700">
                                            📄 Lihat
                                        </a>
                                        <a href="{{ route('certificate.pdf', $prd->certificate->id) }}" class="px-2 py-1 min-h-[44px] inline-flex items-center text-[11px] font-bold rounded-lg bg-red-950 text-red-300 border border-red-800 hover:bg-red-900" title="Export PDF">
                                            PDF
                                        </a>
                                        <a href="{{ route('certificate.excel', $prd->certificate->id) }}" class="px-2 py-1 min-h-[44px] inline-flex items-center text-[11px] font-bold rounded-lg bg-emerald-950 text-emerald-300 border border-emerald-800 hover:bg-emerald-900" title="Export Excel">
                                            XLSX
                                        </a>
                                    </div>
                                @else
                                    <span class="text-zinc-600 text-[11px]">Belum terbit</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap space-x-1">
                                <a href="{{ route('production.edit', $prd->id) }}" class="px-2.5 py-1.5 min-h-[44px] inline-flex items-center text-xs font-semibold rounded-lg bg-zinc-800 text-zinc-200 hover:bg-zinc-700">
                                    {{ $prd->isLocked() ? 'Detail' : 'Edit' }}
                                </a>
                                @if($prd->isLocked() && (auth()->user()->isAdmin() || auth()->user()->isSupervisor()))
                                    <button wire:click="unlock({{ $prd->id }})" class="px-2.5 py-1.5 min-h-[44px] inline-flex items-center text-xs font-bold rounded-lg bg-red-950 text-red-300 border border-red-800 hover:bg-red-900" title="Reopen / Unlock">
                                        🔓 Unlock
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-zinc-500 text-sm">
                                Tidak ada data produksi yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-zinc-800">
            {{ $productions->links() }}
        </div>
    </div>
</div>
