<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black tracking-wide text-zinc-100">Daftar Material Receipt List (MRL)</h2>
            <p class="text-xs text-zinc-400 mt-1">Daftar penimbangan penerimaan bahan baku tembakau mentah dari supplier</p>
        </div>
        @if(!auth()->user()->isOperator())
        <a href="{{ route('mrl.create') }}" class="px-5 py-2.5 min-h-[44px] inline-flex items-center justify-center font-bold text-xs rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white hover:from-amber-500 hover:to-amber-600 shadow-md shadow-amber-900/30">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            + Input MRL Baru
        </a>
        @endif
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="w-full md:w-1/2">
            <input type="text" wire:model.live.debounce.300ms="search" class="w-full px-4 py-2.5 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-sm focus:border-amber-500 outline-none" placeholder="Cari MRL, Batch, Grade, atau Asal Daerah...">
        </div>
        <div class="w-full md:w-auto flex items-center space-x-3">
            <label class="text-xs text-zinc-400 font-semibold uppercase">Status:</label>
            <select wire:model.live="statusFilter" class="px-3 py-2 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                <option value="">Semua Status</option>
                <option value="ready_for_production">Ready for Production</option>
                <option value="in_production">In Production</option>
                <option value="completed">Completed</option>
                <option value="on_hold">On Hold</option>
            </select>
        </div>
    </div>

    <!-- MRL Data Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
            <table class="w-full text-left text-xs text-zinc-300">
                <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase tracking-wider sticky top-0 z-10 border-b border-zinc-800">
                    <tr>
                        <th class="px-4 py-3.5">No MRL</th>
                        <th class="px-4 py-3.5">Supplier / Delivery Note</th>
                        <th class="px-4 py-3.5">Asal / Batch</th>
                        <th class="px-4 py-3.5">Grade</th>
                        <th class="px-4 py-3.5 text-right">Gross (kg)</th>
                        <th class="px-4 py-3.5 text-right">Tare (kg)</th>
                        <th class="px-4 py-3.5 text-right">Net Weight (kg)</th>
                        <th class="px-4 py-3.5 text-center">Packs</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80">
                    @forelse($mrls as $mrl)
                        <tr class="hover:bg-zinc-800/50 transition-colors">
                            <td class="px-4 py-3.5 font-mono font-bold text-amber-400 whitespace-nowrap">
                                {{ $mrl->mrl_number }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-zinc-200">{{ $mrl->supplier->name ?? '-' }}</div>
                                <div class="text-[11px] text-zinc-500 font-mono">DN: {{ $mrl->deliveryNote->dn_number ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="text-zinc-200">{{ $mrl->origin_region }}</div>
                                <div class="text-[11px] text-zinc-400 font-mono">{{ $mrl->batch_number }}</div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-1 rounded bg-zinc-800 text-zinc-300 font-medium text-[11px]">
                                    {{ $mrl->tobacco_grade }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-zinc-300">{{ number_format($mrl->gross_weight, 2) }}</td>
                            <td class="px-4 py-3.5 text-right font-mono text-zinc-400">{{ number_format($mrl->tare_weight, 2) }}</td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-emerald-400 text-sm">
                                {{ number_format($mrl->net_weight, 2) }}
                            </td>
                            <td class="px-4 py-3.5 text-center font-mono font-semibold">{{ $mrl->total_pack }}</td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if($mrl->status === 'ready_for_production')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-950 text-emerald-400 border border-emerald-800">
                                        Ready Production
                                    </span>
                                @elseif($mrl->status === 'in_production')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-950 text-amber-400 border border-amber-800 animate-pulse">
                                        In Production
                                    </span>
                                @elseif($mrl->status === 'completed')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-950 text-blue-400 border border-blue-800">
                                        Completed
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-red-950 text-red-400 border border-red-800">
                                        On Hold
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap space-x-2">
                                @if($mrl->status === 'ready_for_production' && !auth()->user()->isWarehouse())
                                    <a href="{{ route('production.create', ['mrl_id' => $mrl->id]) }}" class="px-3 py-1.5 min-h-[44px] inline-flex items-center text-xs font-bold rounded-lg bg-emerald-700 hover:bg-emerald-600 text-white shadow">
                                        Mulai Produksi
                                    </a>
                                @endif
                                @if(!auth()->user()->isOperator())
                                    <a href="{{ route('mrl.edit', $mrl->id) }}" class="px-2.5 py-1.5 min-h-[44px] inline-flex items-center text-xs font-semibold rounded-lg bg-zinc-800 text-zinc-300 hover:bg-zinc-700">
                                        Edit
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-zinc-500 text-sm">
                                Tidak ada data MRL yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-zinc-800">
            {{ $mrls->links() }}
        </div>
    </div>
</div>
