<div x-data="{ showPreviewModal: @entangle('showPreviewModal') }" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black tracking-wide text-zinc-100 flex items-center">
                Portal Pelanggan (Customer Dashboard)
                <span class="ml-3 px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-950 text-emerald-300 border border-emerald-800">
                    Resmi & Terverifikasi
                </span>
            </h2>
            <p class="text-xs text-zinc-400 mt-1">Grafik tren historis pemisahan tembakau & pratinjau / pengunduhan Sertifikat Produk resmi yang telah disetujui (ACC Supervisor)</p>
        </div>

        @if($filter_product_type_id || $filter_origin_id || $filter_base_origin || $search)
            <button wire:click="resetFilters" class="px-4 py-2.5 min-h-[44px] text-xs font-bold rounded-xl bg-zinc-800 text-amber-400 border border-zinc-700 hover:bg-zinc-700 flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                Reset Filter Navigasi
            </button>
        @endif
    </div>

    <!-- Filters Bar -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-4 sm:p-5 grid grid-cols-1 md:grid-cols-3 gap-4 shadow-xl">
        <div>
            <label class="block text-[11px] font-bold uppercase text-zinc-400 mb-1">Cari Kode Batch / Surat Jalan (DN)</label>
            <input type="text" wire:model.live.debounce.300ms="search" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 text-xs focus:border-emerald-500 outline-none" placeholder="Cari DN-2026 / BCH-...">
        </div>

        <div>
            <label class="block text-[11px] font-bold uppercase text-zinc-400 mb-1">Filter Kode Tembakau (Product Code)</label>
            <select wire:model.live="filter_product_type_id" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-emerald-500 outline-none">
                <option value="">Semua Kode Tembakau</option>
                @foreach($productTypes as $pt)
                    <option value="{{ $pt->id }}">{{ $pt->code ? ($pt->code . ' - ' . $pt->name) : $pt->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[11px] font-bold uppercase text-zinc-400 mb-1">Filter Asal Tembakau (Base Origin)</label>
            <select wire:model.live="filter_base_origin" class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-amber-400 font-bold text-xs focus:border-emerald-500 outline-none">
                <option value="">Semua Asal Tembakau</option>
                @foreach($baseOrigins as $bOrg)
                    <option value="{{ $bOrg }}">{{ $bOrg }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- 4-SERIES DYNAMIC FILTERED HISTORICAL TREND LINE CHART -->
    <div wire:key="chart-container-{{ $filter_product_type_id ?? 'all' }}-{{ $filter_base_origin ?: ($filter_origin_id ?? 'all') }}-{{ md5($search) }}"
         x-data="customerTrendChartComponent({
             labels: @js($chartLabels),
             product: @js($seriesProduct),
             bitsStem: @js($seriesBitsStem),
             dust: @js($seriesDust),
             waste: @js($seriesWaste)
         })"
         x-init="initChart()"
         class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-800 pb-3">
            <div>
                <h3 class="text-base font-black text-emerald-400 uppercase tracking-wide flex items-center">
                    Grafik Tren Historis Hasil Pemisahan Tembakau
                    <span class="ml-2 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-zinc-800 text-amber-300 border border-zinc-700">
                        {{ count($chartBatches) }} Batch Terfilter
                    </span>
                </h3>
                <p class="text-xs text-zinc-400 mt-0.5">
                    Perbandingan 4 Kategori (Product Qty, Bits Stem Qty, Dust Qty, Uncountable Waste Qty)
                    @if($selectedProductType || $filter_base_origin || $selectedOrigin)
                        <span class="text-emerald-300 font-bold ml-1">
                            • Filter Aktif: {{ $selectedProductType ? $selectedProductType->name : 'Semua Produk' }} ({{ $filter_base_origin ?: ($selectedOrigin ? $selectedOrigin->region_name : 'Semua Asal') }})
                        </span>
                    @endif
                </p>
            </div>
            
            <div class="flex items-center space-x-2">
                <span class="text-[11px] font-mono text-amber-400 font-bold">Terbaca: {{ count($chartLabels) }} Titik Batch</span>
            </div>
        </div>

        <div class="relative w-full h-[360px] bg-zinc-950 p-4 rounded-2xl border border-zinc-800/80">
            @if(count($chartLabels) > 0)
                <canvas x-ref="canvas" class="w-full h-full"></canvas>
            @else
                <div class="h-full flex flex-col items-center justify-center text-zinc-500 text-xs">
                    <svg class="w-10 h-10 mb-2 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Tidak ada data batch yang cocok dengan filter yang dipilih.
                </div>
            @endif
        </div>
    </div>

    <!-- APPROVED PROCESS CERTIFICATES TABLE WITH PREVIEW ACTION -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
        <div class="border-b border-zinc-800 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-base font-black text-amber-400 uppercase tracking-wide">Daftar Sertifikat Produk Resmi (ACC Supervisor)</h3>
                <p class="text-xs text-zinc-400">Klik 'Preview Certificate' untuk melihat dokumen pratinjau sebelum mengunduh file sertifikat</p>
            </div>
            <span class="text-xs font-mono font-bold text-zinc-400">
                Total: {{ $approvedBatches->total() }} Sertifikat
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-zinc-300">
                <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                    <tr>
                        <th class="px-4 py-3">Kode Batch</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Nomor Surat Jalan (DN)</th>
                        <th class="px-4 py-3">Jenis Produk & Asal</th>
                        <th class="px-4 py-3 text-right">Produk Jadi (kg)</th>
                        <th class="px-4 py-3 text-right">Yield (%)</th>
                        <th class="px-4 py-3 text-center">Tanggal Disetujui</th>
                        <th class="px-4 py-3 text-center">Aksi / Preview & Download</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/80">
                    @forelse($approvedBatches as $b)
                        <tr class="hover:bg-zinc-800/40 transition-colors">
                            <td class="px-4 py-3.5 font-mono font-bold text-amber-400">{{ $b->batch_code }}</td>
                            <td class="px-4 py-3.5 font-bold text-zinc-100">{{ $b->customer->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 font-mono text-zinc-400">{{ $b->deliveryNote->dn_number ?? '-' }}</td>
                            <td class="px-4 py-3.5">
                                <span class="font-bold text-zinc-200 block">{{ $b->productType->name ?? '-' }}</span>
                                <span class="text-[10px] text-amber-400 font-semibold">{{ $b->origin->region_name ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-emerald-400 text-sm">
                                {{ number_format($b->separation_product_kg, 2, ',', '.') }} kg
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-emerald-300">
                                {{ number_format($b->yield_product_pct, 2, ',', '.') }}%
                            </td>
                            <td class="px-4 py-3.5 text-center font-mono text-zinc-400">
                                {{ $b->supervisor_approved_at ? $b->supervisor_approved_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap space-x-2">
                                <button wire:click="openPreviewModal({{ $b->id }})" class="px-3.5 py-2 min-h-[44px] inline-flex items-center text-xs font-black rounded-xl bg-amber-950 text-amber-300 border border-amber-800 hover:bg-amber-900 shadow">
                                    👁️ Preview Certificate
                                </button>

                                <a href="{{ route('certificate.pdf', $b->id) }}" target="_blank" class="px-3.5 py-2 min-h-[44px] inline-flex items-center text-xs font-black rounded-xl bg-emerald-950 text-emerald-300 border border-emerald-800 hover:bg-emerald-900 shadow">
                                    📥 Download PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-zinc-500">
                                Belum ada Sertifikat Produk yang cocok dengan filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-2">
            {{ $approvedBatches->links() }}
        </div>
    </div>

    <!-- LIVE PDF PREVIEW MODAL FOR CUSTOMER PORTAL -->
    <div x-show="showPreviewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/85 backdrop-blur-sm">
        <div class="bg-zinc-900 border border-zinc-700 rounded-3xl max-w-4xl w-full p-5 sm:p-6 space-y-4 shadow-2xl max-h-[95vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                <div>
                    <h3 class="text-lg font-black text-amber-400 flex items-center">
                        👁️ Live Preview Process Certificate
                        <span class="ml-3 px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-950 text-emerald-300 border border-emerald-800">
                            Resmi & Terverifikasi
                        </span>
                    </h3>
                    <p class="text-xs text-zinc-400">Pratinjau dokumen Process Certificate PDF sebelum diunduh ke komputer / HP</p>
                </div>
                <button type="button" @click="showPreviewModal = false" class="text-zinc-400 hover:text-white text-2xl font-bold p-2 min-w-[44px] min-h-[44px]">&times;</button>
            </div>

            <!-- Iframe Live Preview Container -->
            <div class="flex-1 overflow-y-auto min-h-[450px]">
                @if($previewBatchId)
                    <iframe src="/certificate/{{ $previewBatchId }}" class="w-full h-[480px] rounded-2xl border border-zinc-800 bg-white shadow-inner"></iframe>
                @endif
            </div>

            <!-- Footer Action Buttons -->
            <div class="flex items-center justify-between border-t border-zinc-800 pt-3">
                <button type="button" @click="showPreviewModal = false" class="px-5 py-3 min-h-[48px] rounded-xl bg-zinc-800 text-zinc-300 font-bold text-xs hover:bg-zinc-700">
                    ❌ Tutup Preview
                </button>

                @if($previewBatchId)
                    <a href="{{ route('certificate.pdf', $previewBatchId) }}" target="_blank" class="px-6 py-3 min-h-[48px] inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-black text-xs hover:from-emerald-500 shadow-xl shadow-emerald-950/50">
                        📥 Download PDF Certificate Now
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- BULLETPROOF ALPINE CHART COMPONENT -->
    <script>
        function customerTrendChartComponent(chartData) {
            return {
                chartInstance: null,
                initChart() {
                    this.$nextTick(() => {
                        const canvas = this.$refs.canvas;
                        if (!canvas) return;

                        const ctx = canvas.getContext('2d');
                        if (!ctx) return;

                        if (this.chartInstance) {
                            this.chartInstance.destroy();
                        }

                        if (!chartData || !chartData.labels || chartData.labels.length === 0) return;

                        this.chartInstance = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: chartData.labels,
                                datasets: [
                                    {
                                        label: 'Product Qty (Kg)',
                                        data: chartData.product,
                                        borderColor: '#10b981',
                                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                                        borderWidth: 3,
                                        pointRadius: 5,
                                        pointHoverRadius: 7,
                                        tension: 0.3,
                                        fill: true
                                    },
                                    {
                                        label: 'Bits Stem Qty (Kg)',
                                        data: chartData.bitsStem,
                                        borderColor: '#f59e0b',
                                        backgroundColor: 'transparent',
                                        borderWidth: 2.5,
                                        pointRadius: 4,
                                        tension: 0.3
                                    },
                                    {
                                        label: 'Dust Qty (Kg)',
                                        data: chartData.dust,
                                        borderColor: '#64748b',
                                        backgroundColor: 'transparent',
                                        borderWidth: 2.5,
                                        pointRadius: 4,
                                        tension: 0.3
                                    },
                                    {
                                        label: 'Uncountable Waste Qty (Kg)',
                                        data: chartData.waste,
                                        borderColor: '#ef4444',
                                        backgroundColor: 'transparent',
                                        borderWidth: 2.5,
                                        pointRadius: 4,
                                        tension: 0.3
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: {
                                            color: '#e4e4e7',
                                            font: { family: 'Inter', weight: 'bold', size: 11 },
                                            usePointStyle: true,
                                            padding: 15
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: '#18181b',
                                        titleColor: '#fbbf24',
                                        bodyColor: '#f4f4f5',
                                        borderColor: '#3f3f46',
                                        borderWidth: 1.5,
                                        padding: 12,
                                        displayColors: true,
                                        callbacks: {
                                            title: function(tooltipItems) {
                                                if (!tooltipItems || !tooltipItems.length) return '';
                                                return '📦 Batch & Kode Tembakau:\n' + tooltipItems[0].label;
                                            },
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                if (label) {
                                                    label += ': ';
                                                }
                                                if (context.parsed.y !== null) {
                                                    label += new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(context.parsed.y) + ' kg';
                                                }
                                                return label;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        ticks: { color: '#a1a1aa', font: { size: 10 } },
                                        grid: { color: 'rgba(255, 255, 255, 0.05)' }
                                    },
                                    y: {
                                        ticks: { color: '#a1a1aa', font: { size: 10 } },
                                        grid: { color: 'rgba(255, 255, 255, 0.05)' }
                                    }
                                }
                            }
                        });
                    });
                }
            };
        }
    </script>
</div>
