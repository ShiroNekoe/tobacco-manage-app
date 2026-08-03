<div class="space-y-8">
    <!-- Executive Dashboard Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-zinc-900 border border-zinc-800 p-6 rounded-3xl shadow-2xl">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-amber-950 text-amber-300 border border-amber-800 tracking-wider">
                    Executive Dashboard Direksi
                </span>
                <span class="text-xs text-zinc-500 font-mono">{{ date('d F Y') }}</span>
            </div>
            <h2 class="text-3xl font-black tracking-wide text-zinc-100">Laporan Produksi Tembakau Executive</h2>
            <p class="text-xs text-zinc-400 mt-1">Ringkasan volume penimbangan, efisiensi yield, analisis waste, dan statistik per wilayah asal tembakau</p>
        </div>

        <div class="flex items-center space-x-3">
            <button onclick="window.print()" class="px-5 py-3 min-h-[48px] inline-flex items-center font-bold text-xs rounded-2xl bg-zinc-800 text-zinc-200 hover:bg-zinc-700 shadow">
                🖨️ Cetak Laporan Executive
            </button>
        </div>
    </div>

    <!-- Executive KPI Metric Cards (4 Summary Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Total Volume Netto -->
        <div class="bg-gradient-to-br from-zinc-900 to-zinc-950 border border-zinc-800 p-5 rounded-3xl shadow-xl space-y-2">
            <span class="text-xs font-bold uppercase text-zinc-400 tracking-wider block">Total Volume Timbangan (Netto)</span>
            <div class="flex items-baseline space-x-2">
                <strong class="text-3xl font-black text-emerald-400 font-mono">{{ number_format($totalNettoKg, 2, ',', '.') }}</strong>
                <span class="text-sm text-zinc-400 font-bold">Kg</span>
            </div>
            <p class="text-[11px] text-zinc-500">Dari total <strong class="text-zinc-300 font-bold">{{ $totalBatches }} Batch</strong> produksi</p>
        </div>

        <!-- Card 2: Average Product Yield -->
        <div class="bg-gradient-to-br from-zinc-900 to-zinc-950 border border-zinc-800 p-5 rounded-3xl shadow-xl space-y-2">
            <span class="text-xs font-bold uppercase text-zinc-400 tracking-wider block">Rata-Rata Product Yield (%)</span>
            <div class="flex items-baseline space-x-2">
                <strong class="text-3xl font-black text-amber-400 font-mono">{{ number_format($avgYieldProductPct, 2) }}%</strong>
            </div>
            <p class="text-[11px] text-emerald-400 font-bold">Target Efisiensi Produksi: &gt; 80.00%</p>
        </div>

        <!-- Card 3: Batch Status & Discrepancies -->
        <div class="bg-gradient-to-br from-zinc-900 to-zinc-950 border border-zinc-800 p-5 rounded-3xl shadow-xl space-y-2">
            <span class="text-xs font-bold uppercase text-zinc-400 tracking-wider block">Status Batch & Selisih DN</span>
            <div class="flex items-baseline space-x-2">
                <strong class="text-3xl font-black text-blue-400 font-mono">{{ $closedBatches }} / {{ $totalBatches }}</strong>
                <span class="text-xs text-zinc-400 font-bold">Selesai (Closed)</span>
            </div>
            <p class="text-[11px] {{ $discrepancyCount > 0 ? 'text-red-400 font-bold' : 'text-zinc-500' }}">
                ⚠️ {{ $discrepancyCount }} Batch Memiliki Selisih Timbangan DN vs MRL
            </p>
        </div>

        <!-- Card 4: Total Waste & Loss -->
        <div class="bg-gradient-to-br from-zinc-900 to-zinc-950 border border-zinc-800 p-5 rounded-3xl shadow-xl space-y-2">
            <span class="text-xs font-bold uppercase text-zinc-400 tracking-wider block">Total Uncountable Waste</span>
            <div class="flex items-baseline space-x-2">
                <strong class="text-3xl font-black text-orange-400 font-mono">{{ number_format($totalWasteKg, 2, ',', '.') }}</strong>
                <span class="text-sm text-zinc-400 font-bold">Kg</span>
            </div>
            <p class="text-[11px] text-zinc-500">Yield Waste: <strong class="text-orange-400 font-bold">{{ number_format($avgYieldWastePct, 2) }}%</strong></p>
        </div>
    </div>

    <!-- Interactive Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart 1: Doughnut Chart Composition -->
        <div class="bg-zinc-900 border border-zinc-800 p-6 rounded-3xl shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                <div>
                    <h3 class="text-base font-black text-zinc-100 uppercase tracking-wide">Komposisi Yield Hasil Pemisahan</h3>
                    <p class="text-xs text-zinc-400">Persentase Produk Jadi, Bits Stem, Dust, dan Waste</p>
                </div>
            </div>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="yieldDoughnutChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Bar Chart Volume per Origin -->
        <div class="bg-zinc-900 border border-zinc-800 p-6 rounded-3xl shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                <div>
                    <h3 class="text-base font-black text-zinc-100 uppercase tracking-wide">Volume Timbangan per Asal Daerah</h3>
                    <p class="text-xs text-zinc-400">Perbandingan Netto Produk (PAITON, LOMBOK, JEMBER)</p>
                </div>
            </div>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="originBarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Executive Production Master Table -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
            <div>
                <h3 class="text-lg font-black text-amber-400 uppercase tracking-wide">Ringkasan Data Batch Timbangan Production</h3>
                <p class="text-xs text-zinc-400">Tabel lengkap batch timbangan, persentase yield, dan sertifikat resmi</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-zinc-300">
                <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase tracking-wider border-b border-zinc-800">
                    <tr>
                        <th class="px-4 py-3">Kode Batch</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Jenis & Asal Tembakau</th>
                        <th class="px-4 py-3 text-right">Netto MRL (Kg)</th>
                        <th class="px-4 py-3 text-right">Product (Kg)</th>
                        <th class="px-4 py-3 text-right">Yield Product (%)</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Dokumen PDF</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80">
                    @forelse($batches as $b)
                        <tr class="hover:bg-zinc-800/40 transition-colors">
                            <td class="px-4 py-3.5 font-mono font-bold text-amber-400">{{ $b->batch_code }}</td>
                            <td class="px-4 py-3.5 font-bold text-zinc-200">{{ $b->customer->name ?? '-' }}</td>
                            <td class="px-4 py-3.5">
                                <span class="font-bold text-zinc-300">{{ $b->productType->name ?? '-' }}</span>
                                <span class="text-amber-500 font-semibold block text-[11px]">{{ $b->origin->region_name ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-emerald-400 text-sm">
                                {{ number_format($b->mrl_netto_weight, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-zinc-200">
                                {{ number_format($b->separation_product_kg, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-emerald-400">
                                {{ number_format($b->yield_product_pct, 2) }}%
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                @if(in_array($b->status, ['CLOSED', 'locked']))
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-blue-950 text-blue-400 border border-blue-800">
                                        🔒 CLOSED
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-950 text-emerald-400 border border-emerald-800">
                                        🟢 {{ $b->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <a href="{{ route('certificate.pdf', $b->id) }}" target="_blank" class="px-3 py-2 min-h-[44px] inline-flex items-center text-xs font-black rounded-xl bg-red-950 text-red-300 border border-red-800 hover:bg-red-900 shadow">
                                    📄 Cetak PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-zinc-500">Belum ada data batch.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart.js Initialization Script -->
    <script>
        document.addEventListener('livewire:navigated', initCharts);
        document.addEventListener('DOMContentLoaded', initCharts);

        function initCharts() {
            if (typeof Chart === 'undefined') return;

            // 1. Doughnut Chart: Yield Breakdown
            const doughnutCtx = document.getElementById('yieldDoughnutChart');
            if (doughnutCtx) {
                new Chart(doughnutCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Produk Jadi (Rajangan)', 'Bits Stem (Gagang)', 'Dust (Debu)', 'Uncountable Waste'],
                        datasets: [{
                            data: [
                                {{ $avgYieldProductPct }},
                                {{ $avgYieldBitsPct }},
                                {{ $avgYieldDustPct }},
                                {{ $avgYieldWastePct }}
                            ],
                            backgroundColor: ['#10b981', '#f59e0b', '#f97316', '#71717a'],
                            borderColor: '#18181b',
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: '#e4e4e7', font: { family: 'Inter', weight: 'bold', size: 11 } }
                            }
                        }
                    }
                });
            }

            // 2. Bar Chart: Production Volume per Origin
            const barCtx = document.getElementById('originBarChart');
            if (barCtx) {
                const originsLabels = {!! json_encode($originsData->pluck('name')) !!};
                const originsTotals = {!! json_encode($originsData->pluck('total_kg')) !!};
                const originsProducts = {!! json_encode($originsData->pluck('product_kg')) !!};

                new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: originsLabels,
                        datasets: [
                            {
                                label: 'Total Netto MRL (Kg)',
                                data: originsTotals,
                                backgroundColor: '#f59e0b',
                                borderRadius: 8
                            },
                            {
                                label: 'Produk Jadi (Kg)',
                                data: originsProducts,
                                backgroundColor: '#10b981',
                                borderRadius: 8
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: '#e4e4e7', font: { family: 'Inter', weight: 'bold', size: 11 } }
                            }
                        },
                        scales: {
                            x: { ticks: { color: '#a1a1aa' }, grid: { display: false } },
                            y: { ticks: { color: '#a1a1aa' }, grid: { color: '#27272a' } }
                        }
                    }
                });
            }
        }
    </script>
</div>
