<div class="space-y-6">
    <!-- Header & Filter Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black tracking-wide text-zinc-100 flex items-center">
                Executive Management KPI Dashboard
                <span class="ml-3 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-950 text-amber-400 border border-amber-800">
                    Real-time Monitor
                </span>
            </h2>
            <p class="text-xs text-zinc-400 mt-1">Pemantauan KPI performa pabrik tembakau, perbandingan regu, dan efisiensi mesin</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 p-3 rounded-2xl flex flex-wrap items-center gap-3">
            <div>
                <label class="text-[10px] uppercase font-bold text-zinc-400 block mb-1">Tanggal Mulai</label>
                <input type="date" wire:model.live="startDate" class="px-3 py-1.5 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs outline-none focus:border-amber-500">
            </div>
            <div>
                <label class="text-[10px] uppercase font-bold text-zinc-400 block mb-1">Tanggal Akhir</label>
                <input type="date" wire:model.live="endDate" class="px-3 py-1.5 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs outline-none focus:border-amber-500">
            </div>
            <div>
                <label class="text-[10px] uppercase font-bold text-zinc-400 block mb-1">Filter Asal Daerah</label>
                <input type="text" wire:model.live.debounce.300ms="originFilter" placeholder="Misal: Jember" class="px-3 py-1.5 min-h-[44px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs outline-none focus:border-amber-500">
            </div>
        </div>
    </div>

    <!-- 1. LIVE MACHINE STATUS INDICATORS PER GROUP -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($liveStatus as $key => $st)
            <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-5 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-black tracking-wider text-zinc-200 uppercase">{{ $st['name'] }}</span>
                    @if($st['is_running'])
                        <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase bg-emerald-950 text-emerald-400 border border-emerald-700 flex items-center shadow-lg shadow-emerald-950/60">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 mr-1.5 animate-ping"></span>
                            RUNNING
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase bg-red-950 text-red-400 border border-red-800 flex items-center">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 mr-1.5"></span>
                            STOPPED / IDLE
                        </span>
                    @endif
                </div>
                <div class="mt-4 flex items-center justify-between text-xs text-zinc-400 font-mono">
                    <span>Batch Terakhir: <strong class="text-zinc-200">{{ $st['latest_code'] }}</strong></span>
                    <span>Shift: <strong class="text-amber-400">{{ $st['shift'] }}</strong></span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- 2. EXECUTIVE KPI OVERVIEW SUMMARY CARDS -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Input Net Weight -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4">
            <p class="text-[11px] uppercase font-bold text-zinc-400">Total Net Bahan Mentah</p>
            <p class="text-xl font-black text-amber-400 font-mono mt-1">{{ number_format($totalInputNetKg, 0) }} <span class="text-xs text-zinc-500 font-normal">kg</span></p>
        </div>

        <!-- Product Output -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4">
            <p class="text-[11px] uppercase font-bold text-zinc-400">Total Produk Rajangan</p>
            <p class="text-xl font-black text-emerald-400 font-mono mt-1">{{ number_format($totalProductKg, 0) }} <span class="text-xs text-zinc-500 font-normal">kg</span></p>
        </div>

        <!-- Average Yield % -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4">
            <p class="text-[11px] uppercase font-bold text-zinc-400">Rata-rata Yield %</p>
            <p class="text-xl font-black text-emerald-400 font-mono mt-1">{{ $avgYield }}%</p>
        </div>

        <!-- Average Capacity -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4">
            <p class="text-[11px] uppercase font-bold text-zinc-400">Rata-rata Kapasitas</p>
            <p class="text-xl font-black text-teal-400 font-mono mt-1">{{ $avgCapacity }} <span class="text-xs text-zinc-500 font-normal">kg/jam</span></p>
        </div>

        <!-- Average Uptime % -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4">
            <p class="text-[11px] uppercase font-bold text-zinc-400">Rata-rata Uptime %</p>
            <p class="text-xl font-black text-blue-400 font-mono mt-1">{{ $avgUptime }}%</p>
        </div>

        <!-- Average Performance % -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-4">
            <p class="text-[11px] uppercase font-bold text-zinc-400">Perf. vs Target ({{ $targetCapacity }}kg/h)</p>
            <p class="text-xl font-black text-purple-400 font-mono mt-1">{{ $avgPerformance }}%</p>
        </div>
    </div>

    <!-- 3. TARGET VS ACTUAL COMPARATIVE APEXCHARTS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Target vs Actual Bar Chart -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 space-y-4">
            <h3 class="text-sm font-black uppercase tracking-wider text-amber-400">Target vs Actual KPI Overview</h3>
            <div id="chart-target-actual" class="w-full h-72"></div>
        </div>

        <!-- Group Performance Comparison Chart -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 space-y-4">
            <h3 class="text-sm font-black uppercase tracking-wider text-amber-400">Perbandingan Performa Regu (Group A vs B vs C)</h3>
            <div id="chart-group-comparison" class="w-full h-72"></div>
        </div>
    </div>

    <!-- 4. DETAILED GROUP & SHIFT RANKINGS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Group Rankings Table -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 space-y-4">
            <h3 class="text-sm font-bold uppercase text-zinc-200">Ranking Performa Group</h3>
            <div class="space-y-3">
                @foreach($groupStats as $gName => $gData)
                    <div class="bg-zinc-950 p-4 rounded-xl border border-zinc-800 flex items-center justify-between">
                        <div>
                            <span class="font-black text-zinc-100 text-sm">{{ $gName }}</span>
                            <p class="text-[11px] text-zinc-500 font-mono">{{ $gData['count'] }} Batch Produksi</p>
                        </div>
                        <div class="flex items-center space-x-4 text-xs font-mono">
                            <div class="text-right">
                                <span class="text-zinc-500 text-[10px] block">Yield</span>
                                <span class="font-bold text-emerald-400">{{ $gData['yield_pct'] }}%</span>
                            </div>
                            <div class="text-right">
                                <span class="text-zinc-500 text-[10px] block">Kapasitas</span>
                                <span class="font-bold text-teal-400">{{ $gData['capacity_kg_hr'] }} kg/h</span>
                            </div>
                            <div class="text-right">
                                <span class="text-zinc-500 text-[10px] block">Performance</span>
                                <span class="font-bold text-purple-400">{{ $gData['performance_pct'] }}%</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Shift Comparison Table -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 space-y-4">
            <h3 class="text-sm font-bold uppercase text-zinc-200">Perbandingan Performa Shift (Shift 1 vs Shift 2)</h3>
            <div class="space-y-3">
                @foreach($shiftStats as $sName => $sData)
                    <div class="bg-zinc-950 p-4 rounded-xl border border-zinc-800 flex items-center justify-between">
                        <div>
                            <span class="font-black text-amber-400 text-sm">{{ $sName }}</span>
                            <p class="text-[11px] text-zinc-500 font-mono">{{ $sData['count'] }} Batch Produksi</p>
                        </div>
                        <div class="flex items-center space-x-4 text-xs font-mono">
                            <div class="text-right">
                                <span class="text-zinc-500 text-[10px] block">Yield</span>
                                <span class="font-bold text-emerald-400">{{ $sData['yield_pct'] }}%</span>
                            </div>
                            <div class="text-right">
                                <span class="text-zinc-500 text-[10px] block">Kapasitas</span>
                                <span class="font-bold text-teal-400">{{ $sData['capacity_kg_hr'] }} kg/h</span>
                            </div>
                            <div class="text-right">
                                <span class="text-zinc-500 text-[10px] block">Performance</span>
                                <span class="font-bold text-purple-400">{{ $sData['performance_pct'] }}%</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- APEXCHARTS SCRIPT INITIALIZATION -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Target vs Actual Chart
            var optionsTarget = {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, background: 'transparent' },
                theme: { mode: 'dark' },
                series: [{
                    name: 'Actual % / Value',
                    data: [{{ $avgYield }}, {{ $avgCapacity }}, {{ $avgUptime }}, {{ $avgPerformance }}]
                }],
                xaxis: {
                    categories: ['Yield %', 'Kapasitas (kg/h)', 'Uptime %', 'Performance %'],
                    labels: { style: { colors: '#a1a1aa' } }
                },
                colors: ['#d97706'],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '45%' } },
                grid: { borderColor: '#27272a' }
            };
            var chartTarget = new ApexCharts(document.querySelector("#chart-target-actual"), optionsTarget);
            chartTarget.render();

            // 2. Group Comparison Chart
            var groupNames = @json(array_keys($groupStats));
            var groupYields = @json(array_column($groupStats, 'yield_pct'));
            var groupCaps = @json(array_column($groupStats, 'capacity_kg_hr'));

            var optionsGroup = {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, background: 'transparent' },
                theme: { mode: 'dark' },
                series: [
                    { name: 'Yield %', data: groupYields },
                    { name: 'Capacity (kg/h)', data: groupCaps }
                ],
                xaxis: { categories: groupNames, labels: { style: { colors: '#a1a1aa' } } },
                colors: ['#10b981', '#14b8a6'],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
                grid: { borderColor: '#27272a' }
            };
            var chartGroup = new ApexCharts(document.querySelector("#chart-group-comparison"), optionsGroup);
            chartGroup.render();
        });
    </script>
</div>
