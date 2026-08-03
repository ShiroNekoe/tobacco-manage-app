<div class="space-y-6">
    <!-- Header & Search Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black tracking-wide text-zinc-100 flex items-center">
                Live Tracking & Progress Karyawan
                <span class="ml-3 px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-purple-950 text-purple-300 border border-purple-800">
                    Akses Admin & Supervisor
                </span>
            </h2>
            <p class="text-xs text-zinc-400 mt-1">Pantau aktivitas pengisian timbangan per shift/group, waktu simpan terakhir, dan riwayat laporan pemisahan interim</p>
        </div>

        <!-- Search Box by Worker Name or Batch Code -->
        <div class="w-full md:w-80 shrink-0">
            <input type="text" wire:model.live.debounce.300ms="search" class="w-full px-4 py-3 min-h-[48px] rounded-2xl bg-zinc-900 border border-zinc-800 text-zinc-100 text-xs focus:border-amber-500 outline-none shadow-lg" placeholder="Cari Kode Batch (BCH-...) atau Nama Karyawan/Worker...">
        </div>
    </div>

    <!-- Live Batch Progress Cards (Paginated 6 per page) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black uppercase text-amber-400 tracking-wider">Kartu Tracking Batch Produksi Aktif</h3>
            <span class="text-xs font-mono font-bold text-zinc-400">Total: {{ $activeBatches->total() }} Batch</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($activeBatches as $batch)
                @php
                    $totalFilled = $batch->weighingItems->where('gross_kg', '>', 0)->count();
                    $totalRows = $batch->weighingItems->count();
                @endphp
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-xl space-y-4 flex flex-col justify-between hover:border-zinc-700 transition-colors">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                            <div>
                                <span class="text-[10px] font-bold text-amber-500 uppercase font-mono">{{ $batch->batch_code }}</span>
                                <h3 class="text-base font-black text-zinc-100 truncate max-w-[180px]">{{ $batch->customer->name ?? '-' }}</h3>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $batch->status === 'CLOSED' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-amber-950 text-amber-300 border border-amber-800' }}">
                                {{ $batch->status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-2.5 text-xs">
                            <div class="bg-zinc-950 p-3 rounded-2xl border border-zinc-800">
                                <span class="text-zinc-500 text-[10px] uppercase font-bold block">Terakhir Pengisi / Operator</span>
                                <strong class="text-zinc-100 block text-sm font-bold truncate">{{ $batch->lastSavedBy->name ?? 'Belum ada' }}</strong>
                                <span class="text-[10px] text-amber-400 font-mono font-semibold">{{ $batch->lastSavedBy->shift ?? '-' }} ({{ $batch->lastSavedBy->group ?? '-' }})</span>
                            </div>

                            <div class="bg-zinc-950 p-3 rounded-2xl border border-zinc-800">
                                <span class="text-zinc-500 text-[10px] uppercase font-bold block">Progress Karung Terisi</span>
                                <strong class="text-emerald-400 text-base font-mono block">{{ $totalFilled }} / {{ $totalRows }} Karung</strong>
                                <span class="text-[10px] text-zinc-400">Tersisa: {{ max(0, $totalRows - $totalFilled) }} baris</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-[11px] text-zinc-400 flex items-center justify-between border-t border-zinc-800/80 pt-3">
                        <div>Mulai: <strong class="text-zinc-300 font-mono">{{ $batch->start_time ? $batch->start_time->format('d/m H:i') : '-' }}</strong></div>
                        <div>Simpan: <strong class="text-emerald-400 font-mono">{{ $batch->last_saved_at ? $batch->last_saved_at->format('d/m H:i') : '-' }}</strong></div>
                    </div>
                </div>
            @empty
                <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-zinc-900 border border-zinc-800 p-8 rounded-3xl text-center text-zinc-500">
                    Tidak ada batch timbangan yang cocok dengan pencarian.
                </div>
            @endforelse
        </div>

        <!-- Pagination Links -->
        <div class="pt-2">
            {{ $activeBatches->links() }}
        </div>
    </div>

    <!-- Interim Separation Reports Log Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
        <div class="border-b border-zinc-800 pb-3">
            <h3 class="text-base font-black text-amber-400 uppercase tracking-wide">Riwayat Laporan Pemisahan Interim (Shift Stop Log)</h3>
            <p class="text-xs text-zinc-400">Catatan hasil pemisahan yang diinput worker saat jeda / pergantian shift</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-zinc-300">
                <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                    <tr>
                        <th class="px-4 py-3">Waktu Input</th>
                        <th class="px-4 py-3">Kode Batch</th>
                        <th class="px-4 py-3">Worker & Shift</th>
                        <th class="px-4 py-3 text-right">Produk (kg)</th>
                        <th class="px-4 py-3 text-right">Bits Stem (kg)</th>
                        <th class="px-4 py-3 text-right">Dust (kg)</th>
                        <th class="px-4 py-3 text-right">Waste (kg)</th>
                        <th class="px-4 py-3 text-center">Karung Diproses</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80">
                    @forelse($interimReports as $rep)
                        <tr class="hover:bg-zinc-800/40 transition-colors">
                            <td class="px-4 py-3 font-mono text-zinc-400">{{ $rep->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 font-mono font-bold text-amber-400">{{ $rep->batch->batch_code ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <strong class="text-zinc-100 block">{{ $rep->user->name ?? '-' }}</strong>
                                <span class="text-[10px] text-zinc-500 font-mono">{{ $rep->shift }} - {{ $rep->group }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-emerald-400">{{ number_format($rep->separation_product_kg, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono text-amber-400">{{ number_format($rep->separation_bits_stem_kg, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono text-orange-400">{{ number_format($rep->separation_dust_kg, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono text-zinc-400">{{ number_format($rep->separation_waste_kg, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center font-mono font-bold text-zinc-200">{{ $rep->sacks_processed_count }} Pack</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-zinc-500">Belum ada riwayat laporan pemisahan interim.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
