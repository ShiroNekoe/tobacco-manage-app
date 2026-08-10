<div x-data="{
    activeTab: @entangle('activeTab'),
    showPreviewModal: @entangle('showPreviewModal')
}"
x-init="$watch('activeTab', value => $dispatch('customer-tab-changed', value))"
x-on:switch-customer-tab.window="activeTab = $event.detail; $wire.setTab($event.detail)"
class="space-y-6">

    <!-- TOP PORTAL HEADER -->
    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-4 sm:p-6 shadow-2xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-11 h-11 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center shrink-0 shadow-lg shadow-amber-500/10">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black text-zinc-100 tracking-wide">TOBACCO SEPARATION</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-950 text-amber-300 border border-amber-800">
                            CUSTOMER PORTAL
                        </span>
                    </div>
                    <p class="text-xs text-zinc-400 mt-0.5">Dashboard Produksi, Rekonsiliasi Penerimaan, Analitik Separasi & Kalkulator Biaya</p>
                </div>
            </div>

            <!-- ACTIVE MODULE BADGE -->
            <div class="flex items-center gap-2 bg-zinc-950 px-4 py-2 rounded-2xl border border-zinc-800 self-start sm:self-auto shadow-inner">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                <span class="text-xs font-mono font-bold text-amber-400" x-text="
                    activeTab === 'batch_overview' ? 'Batch Overview' :
                    activeTab === 'historical_analytics' ? 'Historical Analytics' :
                    activeTab === 'yield_calculator' ? 'Yield Cost Calculator' :
                    activeTab === 'reconciliation' ? 'Receiving Reconciliation' :
                    activeTab === 'traceability' ? 'Batch Traceability' :
                    activeTab === 'certificates' ? 'Certificates' : 'Batch Overview'
                ">Batch Overview</span>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 1: BATCH OVERVIEW & RECONCILIATION -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'batch_overview' || activeTab === 'reconciliation' || activeTab === 'traceability'" class="space-y-6">

        <!-- HEADER & BATCH NAVIGATOR -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-zinc-100 tracking-wide">Batch Overview & Reconciliation</h2>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <span class="text-xs text-zinc-400">Customer: <strong class="text-zinc-200 font-semibold">{{ $batchOverviewData['customerName'] ?? 'PT Falih Nur Gemilang' }}</strong></span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-950 text-emerald-400 border border-emerald-800/80 flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ $batchOverviewData['reportingLabel'] ?? 'Receiving Control Improvement • Implemented from Batch 23' }}
                    </span>
                </div>
            </div>

            <!-- BATCH PAGINATION / NAVIGATOR -->
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <button wire:click="previousBatch" class="p-2.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white hover:bg-zinc-800 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <div class="px-4 py-2.5 rounded-xl bg-zinc-900 border border-zinc-800 font-mono font-bold text-xs text-amber-400">
                    {{ $batchOverviewData['batchPosition'] ?? 'Batch 25 of 25' }}
                </div>
                <button wire:click="nextBatch" class="p-2.5 rounded-xl bg-zinc-900 border border-zinc-800 text-zinc-300 hover:text-white hover:bg-zinc-800 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>

        <!-- FILTER CONTROLS BAR -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-4 sm:p-5 shadow-xl grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 items-end">
            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Batch</label>
                <select wire:model.live="selectedBatchId" class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-amber-400 font-mono font-bold text-xs focus:border-amber-500 outline-none">
                    @foreach($allApprovedBatches as $ab)
                        <option value="{{ $ab->id }}">{{ $ab->batch_code }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Delivery Note</label>
                <input type="text" value="{{ $batchOverviewData['deliveryNote'] ?? '-' }}" readonly class="w-full px-3 py-2.5 rounded-xl bg-zinc-950/60 border border-zinc-800 text-zinc-300 font-mono text-xs outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Receipt Date</label>
                <div class="flex items-center px-3 py-2.5 rounded-xl bg-zinc-950/60 border border-zinc-800 text-zinc-300 font-mono text-xs">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ $batchOverviewData['receiptDate'] ?? '-' }}
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Origin</label>
                <select class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                    <option value="">All Origins</option>
                    @foreach($origins as $org)
                        <option value="{{ $org->id }}">{{ $org->region_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Origin Code</label>
                <select class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                    <option value="">All Codes</option>
                    <option value="P10T5">P10T5</option>
                    <option value="P9K5">P9K5</option>
                    <option value="FN504">FN504</option>
                    <option value="FN602">FN602</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Certificate</label>
                <div class="px-3 py-2.5 rounded-xl bg-zinc-950/60 border border-zinc-800 text-emerald-400 font-bold text-xs flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ $batchOverviewData['certificateStatus'] ?? 'Released' }}
                </div>
            </div>

            <div class="flex items-center gap-1.5">
                <button wire:click="resetBatchOverviewFilters" class="w-1/2 py-2.5 rounded-xl bg-zinc-800 text-zinc-300 hover:bg-zinc-700 text-xs font-bold transition-all">
                    Reset
                </button>
                <button class="w-1/2 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-black transition-all shadow-md shadow-amber-900/30">
                    Apply
                </button>
            </div>
        </div>

        <!-- 8 TOP KPI CARDS (MATCHING MOCKUP 2 EXACTLY) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
            <!-- 1. DN Gross -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between text-[11px] font-bold text-zinc-400 uppercase">
                    <span>DN Gross</span>
                    <span class="text-amber-500">🛍️</span>
                </div>
                <div class="font-mono font-black text-base lg:text-lg text-amber-400">
                    {{ number_format($batchOverviewData['dnGross'] ?? 3247.60, 2, '.', ',') }} <span class="text-[11px] font-normal text-zinc-400">kg</span>
                </div>
            </div>

            <!-- 2. MRL Gross -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between text-[11px] font-bold text-zinc-400 uppercase">
                    <span>MRL Gross</span>
                    <span class="text-amber-500">🛍️</span>
                </div>
                <div class="font-mono font-black text-base lg:text-lg text-amber-400">
                    {{ number_format($batchOverviewData['mrlGross'] ?? 3251.90, 2, '.', ',') }} <span class="text-[11px] font-normal text-zinc-400">kg</span>
                </div>
            </div>

            <!-- 3. Receiving Difference -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between text-[11px] font-bold text-zinc-400 uppercase">
                    <span>Receiving Diff</span>
                    <span class="text-cyan-400">⚖️</span>
                </div>
                <div class="font-mono font-black text-base lg:text-lg text-cyan-300">
                    {{ ($batchOverviewData['diffKg'] ?? 4.30) >= 0 ? '+' : '' }}{{ number_format($batchOverviewData['diffKg'] ?? 4.30, 2, '.', ',') }} <span class="text-[11px] font-normal text-zinc-400">kg</span>
                </div>
            </div>

            <!-- 4. MRL Netto -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between text-[11px] font-bold text-zinc-400 uppercase">
                    <span>MRL Netto</span>
                    <span class="text-amber-500">🛍️</span>
                </div>
                <div class="font-mono font-black text-base lg:text-lg text-amber-300">
                    {{ number_format($batchOverviewData['mrlNetto'] ?? 3173.80, 2, '.', ',') }} <span class="text-[11px] font-normal text-zinc-400">kg</span>
                </div>
            </div>

            <!-- 5. Processed Input -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between text-[11px] font-bold text-zinc-400 uppercase">
                    <span>Processed Input</span>
                    <span class="text-amber-400">📥</span>
                </div>
                <div class="font-mono font-black text-base lg:text-lg text-amber-400">
                    {{ number_format($batchOverviewData['processedInput'] ?? 3173.70, 2, '.', ',') }} <span class="text-[11px] font-normal text-zinc-400">kg</span>
                </div>
            </div>

            <!-- 6. Product Output -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between text-[11px] font-bold text-zinc-400 uppercase">
                    <span>Product Output</span>
                    <span class="text-amber-400">📦</span>
                </div>
                <div class="font-mono font-black text-base lg:text-lg text-amber-400">
                    {{ number_format($batchOverviewData['productOutput'] ?? 2442.50, 2, '.', ',') }} <span class="text-[11px] font-normal text-zinc-400">kg</span>
                </div>
            </div>

            <!-- 7. Weighted Product Yield -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between text-[11px] font-bold text-zinc-400 uppercase">
                    <span>Product Yield</span>
                    <span class="text-emerald-400">📈</span>
                </div>
                <div class="font-mono font-black text-base lg:text-lg text-emerald-400">
                    {{ number_format($batchOverviewData['weightedProductYield'] ?? 76.96, 2, '.', ',') }}%
                </div>
            </div>

            <!-- 8. Process Material Balance -->
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg relative overflow-hidden">
                <div class="flex items-center justify-between text-[11px] font-bold text-zinc-400 uppercase">
                    <span>Process Balance</span>
                    <span class="text-emerald-400">🟢</span>
                </div>
                <div class="font-mono font-black text-base lg:text-lg text-emerald-400">
                    {{ number_format($batchOverviewData['processMaterialBalance'] ?? 100.00, 2, '.', ',') }}%
                </div>
            </div>
        </div>

        <!-- 2x2 MAIN CONTENT GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- CARD 1: Material Receiving Reconciliation — DN vs MRL -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">
                        Material Receiving Reconciliation — DN vs MRL
                    </h3>
                    <p class="text-xs text-zinc-400 mt-0.5">Rekonsiliasi penimbangan awal surat jalan vs kedatangan fisik gudang</p>

                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-left text-xs text-zinc-300">
                            <thead class="bg-zinc-950/80 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                                <tr>
                                    <th class="px-3 py-2.5">Origin</th>
                                    <th class="px-3 py-2.5 text-center">Packs</th>
                                    <th class="px-3 py-2.5 text-right">DN Gross (kg)</th>
                                    <th class="px-3 py-2.5 text-right">MRL Gross (kg)</th>
                                    <th class="px-3 py-2.5 text-right">Difference</th>
                                    <th class="px-3 py-2.5 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/60 font-mono">
                                @forelse($batchOverviewData['originReconciliation'] ?? [] as $or)
                                    <tr class="hover:bg-zinc-800/30">
                                        <td class="px-3 py-2.5 font-sans font-bold text-zinc-200">{{ $or['name'] }}</td>
                                        <td class="px-3 py-2.5 text-center text-zinc-400">{{ $or['packs'] }}</td>
                                        <td class="px-3 py-2.5 text-right text-zinc-300">{{ number_format($or['dnGross'], 2, '.', ',') }}</td>
                                        <td class="px-3 py-2.5 text-right text-zinc-200 font-bold">{{ number_format($or['mrlGross'], 2, '.', ',') }}</td>
                                        <td class="px-3 py-2.5 text-right font-bold {{ $or['differenceKg'] >= 0 ? 'text-cyan-400' : 'text-amber-400' }}">
                                            {{ $or['differenceKg'] >= 0 ? '+' : '' }}{{ number_format($or['differenceKg'], 2, '.', ',') }}
                                        </td>
                                        <td class="px-3 py-2.5 text-center font-sans">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-800">
                                                {{ $or['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-3 py-2.5 font-sans font-bold text-zinc-200">PAITON P10T5</td>
                                        <td class="px-3 py-2.5 text-center text-zinc-400">37</td>
                                        <td class="px-3 py-2.5 text-right text-zinc-300">1,815.60</td>
                                        <td class="px-3 py-2.5 text-right text-zinc-200 font-bold">1,818.00</td>
                                        <td class="px-3 py-2.5 text-right font-bold text-cyan-400">+2.40</td>
                                        <td class="px-3 py-2.5 text-center font-sans">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-800">Confirmed</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2.5 font-sans font-bold text-zinc-200">LOMBOK P9K5</td>
                                        <td class="px-3 py-2.5 text-center text-zinc-400">28</td>
                                        <td class="px-3 py-2.5 text-right text-zinc-300">1,432.00</td>
                                        <td class="px-3 py-2.5 text-right text-zinc-200 font-bold">1,433.90</td>
                                        <td class="px-3 py-2.5 text-right font-bold text-cyan-400">+1.90</td>
                                        <td class="px-3 py-2.5 text-center font-sans">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-800">Confirmed</span>
                                        </td>
                                    </tr>
                                @endforelse
                                <tr class="bg-zinc-950/90 font-bold border-t-2 border-zinc-700">
                                    <td class="px-3 py-2.5 font-sans uppercase text-zinc-100">TOTAL</td>
                                    <td class="px-3 py-2.5 text-center text-zinc-300">{{ $batchOverviewData['totalPacks'] ?? 65 }}</td>
                                    <td class="px-3 py-2.5 text-right text-zinc-300">{{ number_format($batchOverviewData['dnGross'] ?? 3247.60, 2, '.', ',') }}</td>
                                    <td class="px-3 py-2.5 text-right text-amber-400">{{ number_format($batchOverviewData['mrlGross'] ?? 3251.90, 2, '.', ',') }}</td>
                                    <td class="px-3 py-2.5 text-right font-bold text-cyan-400">
                                        {{ ($batchOverviewData['diffKg'] ?? 4.30) >= 0 ? '+' : '' }}{{ number_format($batchOverviewData['diffKg'] ?? 4.30, 2, '.', ',') }}
                                    </td>
                                    <td class="px-3 py-2.5 text-center font-sans">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-800">Confirmed</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded-2xl bg-emerald-950/40 border border-emerald-800/80 flex items-center text-xs text-emerald-300">
                    <svg class="w-4 h-4 mr-2 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Total difference: <strong>{{ ($batchOverviewData['diffKg'] ?? 4.30) >= 0 ? '+' : '' }}{{ number_format($batchOverviewData['diffKg'] ?? 4.30, 2, '.', ',') }} kg</strong> confirmed within tolerance.</span>
                </div>
            </div>

            <!-- CARD 2: Receiving Confirmation Status (Stepper) -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">
                        Receiving Confirmation Status
                    </h3>
                    <p class="text-xs text-zinc-400 mt-0.5">Audit trail alur tahapan operasional dan verifikasi penerimaan</p>

                    <!-- Horizontal Stepper Flow -->
                    <div class="relative mt-8 mb-4">
                        <!-- Horizontal Connecting Line -->
                        <div class="absolute top-4 left-6 right-6 h-0.5 bg-emerald-800 -z-0"></div>

                        <div class="grid grid-cols-5 gap-2 relative z-10 text-center">
                            @foreach($batchOverviewData['stepper'] ?? [] as $step)
                                <div class="flex flex-col items-center space-y-2">
                                    <div class="w-8 h-8 rounded-full bg-emerald-600 border-2 border-emerald-400 flex items-center justify-center text-white shadow-lg shadow-emerald-900/50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <div>
                                        <div class="text-[11px] font-bold text-zinc-200 leading-tight">{{ $step['title'] }}</div>
                                        <div class="text-[9px] font-mono text-zinc-400 mt-0.5">{{ $step['time'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-2xl bg-zinc-950 border border-zinc-800 flex items-center justify-between text-xs text-zinc-400">
                    <span class="flex items-center">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>
                        Status: <strong class="text-emerald-400 ml-1">Seluruh 5 Tahapan Terverifikasi & ACC</strong>
                    </span>
                    <button wire:click="openPreviewModal({{ $currentBatch->id ?? 0 }})" class="text-amber-400 hover:underline font-bold text-xs flex items-center">
                        Lihat Sertifikat &rarr;
                    </button>
                </div>
            </div>

            <!-- CARD 3: Separation Result by Origin -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-800 pb-3">
                    <div>
                        <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">
                            Separation Result by Origin
                        </h3>
                        <p class="text-xs text-zinc-400 mt-0.5">Komposisi output separasi tembakau per asal asal tembakau</p>
                    </div>
                    <!-- Legend -->
                    <div class="flex items-center flex-wrap gap-2 text-[10px] font-bold">
                        <span class="flex items-center"><span class="w-2.5 h-2.5 rounded bg-emerald-500 mr-1"></span> Product</span>
                        <span class="flex items-center"><span class="w-2.5 h-2.5 rounded bg-amber-500 mr-1"></span> Bits Stem</span>
                        <span class="flex items-center"><span class="w-2.5 h-2.5 rounded bg-blue-500 mr-1"></span> Dust</span>
                        <span class="flex items-center"><span class="w-2.5 h-2.5 rounded bg-zinc-500 mr-1"></span> Process Variance</span>
                    </div>
                </div>

                <!-- Stacked Progress Bars -->
                <div class="space-y-4 pt-2">
                    @forelse($batchOverviewData['originSeparation'] ?? [] as $os)
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-zinc-200">{{ $os['name'] }}</span>
                                <span class="font-mono text-emerald-400">{{ number_format($os['productPct'], 2) }}% Yield</span>
                            </div>
                            <div class="h-6 w-full rounded-xl bg-zinc-950 overflow-hidden flex border border-zinc-800 font-mono text-[10px] font-bold text-zinc-950">
                                <div style="width: {{ $os['productPct'] }}%" class="bg-emerald-500 flex items-center justify-center text-white" title="Product {{ $os['productPct'] }}%">
                                    {{ $os['productPct'] > 15 ? number_format($os['productPct'], 1) . '%' : '' }}
                                </div>
                                <div style="width: {{ $os['bitsStemPct'] }}%" class="bg-amber-500 flex items-center justify-center text-zinc-900" title="Bits Stem {{ $os['bitsStemPct'] }}%">
                                    {{ $os['bitsStemPct'] > 10 ? number_format($os['bitsStemPct'], 1) . '%' : '' }}
                                </div>
                                <div style="width: {{ $os['dustPct'] }}%" class="bg-blue-500 flex items-center justify-center text-white" title="Dust {{ $os['dustPct'] }}%">
                                    {{ $os['dustPct'] > 5 ? number_format($os['dustPct'], 1) . '%' : '' }}
                                </div>
                                <div style="width: {{ $os['variancePct'] }}%" class="bg-zinc-600 flex items-center justify-center text-zinc-300" title="Process Variance {{ $os['variancePct'] }}%">
                                    {{ $os['variancePct'] > 5 ? number_format($os['variancePct'], 1) . '%' : '' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-zinc-200">PAITON P10T5</span>
                                <span class="font-mono text-emerald-400">79.70%</span>
                            </div>
                            <div class="h-6 w-full rounded-xl bg-zinc-950 overflow-hidden flex border border-zinc-800 font-mono text-[10px] font-bold">
                                <div style="width: 79.70%" class="bg-emerald-500 flex items-center justify-center text-white">79.70%</div>
                                <div style="width: 18.00%" class="bg-amber-500 flex items-center justify-center text-zinc-900">18.00%</div>
                                <div style="width: 1.65%" class="bg-blue-500"></div>
                                <div style="width: 0.65%" class="bg-zinc-600"></div>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-zinc-200">LOMBOK P9K5</span>
                                <span class="font-mono text-emerald-400">73.50%</span>
                            </div>
                            <div class="h-6 w-full rounded-xl bg-zinc-950 overflow-hidden flex border border-zinc-800 font-mono text-[10px] font-bold">
                                <div style="width: 73.50%" class="bg-emerald-500 flex items-center justify-center text-white">73.50%</div>
                                <div style="width: 23.67%" class="bg-amber-500 flex items-center justify-center text-zinc-900">23.67%</div>
                                <div style="width: 1.87%" class="bg-blue-500"></div>
                                <div style="width: 0.96%" class="bg-zinc-600"></div>
                            </div>
                        </div>
                    @endforelse

                    <!-- Scale Labels -->
                    <div class="flex justify-between text-[10px] font-mono text-zinc-500 pt-1">
                        <span>0%</span>
                        <span>25%</span>
                        <span>50%</span>
                        <span>75%</span>
                        <span>100%</span>
                    </div>
                </div>
            </div>

            <!-- CARD 4: Process Material Balance -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">
                        Process Material Balance
                    </h3>
                    <p class="text-xs text-zinc-400 mt-0.5">Neraca massa operasional separasi tembakau</p>

                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-left text-xs text-zinc-300">
                            <thead class="bg-zinc-950/80 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                                <tr>
                                    <th class="px-3 py-2.5">Item</th>
                                    <th class="px-3 py-2.5 text-right">Total (kg)</th>
                                    <th class="px-3 py-2.5 text-right">% of Processed Input</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/60 font-mono">
                                <tr>
                                    <td class="px-3 py-2.5 font-sans font-bold text-zinc-200">Processed Input (MRL Netto)</td>
                                    <td class="px-3 py-2.5 text-right font-bold text-zinc-100">{{ number_format($batchOverviewData['balanceItems']['inputKg'] ?? 3173.70, 2, '.', ',') }}</td>
                                    <td class="px-3 py-2.5 text-right text-zinc-300">100.00%</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2.5 font-sans text-emerald-400 font-bold">Product Output</td>
                                    <td class="px-3 py-2.5 text-right text-emerald-400 font-bold">{{ number_format($batchOverviewData['balanceItems']['productKg'] ?? 2442.50, 2, '.', ',') }}</td>
                                    <td class="px-3 py-2.5 text-right text-emerald-400 font-bold">{{ number_format($batchOverviewData['balanceItems']['productPct'] ?? 76.96, 2, '.', ',') }}%</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2.5 font-sans text-amber-300">Bits Stem Output</td>
                                    <td class="px-3 py-2.5 text-right text-amber-300">{{ number_format($batchOverviewData['balanceItems']['stemKg'] ?? 589.22, 2, '.', ',') }}</td>
                                    <td class="px-3 py-2.5 text-right text-amber-300">{{ number_format($batchOverviewData['balanceItems']['stemPct'] ?? 18.56, 2, '.', ',') }}%</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2.5 font-sans text-blue-300">Dust Output</td>
                                    <td class="px-3 py-2.5 text-right text-blue-300">{{ number_format($batchOverviewData['balanceItems']['dustKg'] ?? 58.70, 2, '.', ',') }}</td>
                                    <td class="px-3 py-2.5 text-right text-blue-300">{{ number_format($batchOverviewData['balanceItems']['dustPct'] ?? 1.85, 2, '.', ',') }}%</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2.5 font-sans text-zinc-400">Process Variance</td>
                                    <td class="px-3 py-2.5 text-right text-zinc-400">{{ number_format($batchOverviewData['balanceItems']['varianceKg'] ?? 20.28, 2, '.', ',') }}</td>
                                    <td class="px-3 py-2.5 text-right text-zinc-400">{{ number_format($batchOverviewData['balanceItems']['variancePct'] ?? 0.63, 2, '.', ',') }}%</td>
                                </tr>
                                <tr class="bg-zinc-950/90 font-bold border-t-2 border-zinc-700">
                                    <td class="px-3 py-2.5 font-sans uppercase text-zinc-100">Total Balance</td>
                                    <td class="px-3 py-2.5 text-right text-amber-400">{{ number_format($batchOverviewData['balanceItems']['totalKg'] ?? 3110.70, 2, '.', ',') }}</td>
                                    <td class="px-3 py-2.5 text-right text-emerald-400">{{ number_format($batchOverviewData['balanceItems']['totalPct'] ?? 100.00, 2, '.', ',') }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded-2xl bg-emerald-950/40 border border-emerald-800/80 flex items-center text-xs text-emerald-300">
                    <svg class="w-4 h-4 mr-2 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Material balance within acceptable variance (&plusmn;2.00%).</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 2: HISTORICAL SEPARATION PERFORMANCE (HISTORICAL ANALYTICS) -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'historical_analytics'" class="space-y-6">

        <!-- HEADER & EXPORT ANALYSIS BUTTON -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-zinc-100 tracking-wide">Historical Separation Performance</h2>
                <p class="text-xs text-zinc-400 mt-1">Analyze separation yield, output composition, and process consistency across validated batches.</p>
            </div>

            <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-black text-xs flex items-center shadow-lg shadow-amber-900/30 transition-all self-start sm:self-auto">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export Analysis
            </button>
        </div>

        <!-- FILTER BAR -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-4 sm:p-5 shadow-xl grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3 items-end">
            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Date Range</label>
                <input type="text" placeholder="01 Jun - 31 Jul 2026" class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Batch Range</label>
                <select class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-amber-400 font-mono font-bold text-xs focus:border-amber-500 outline-none">
                    <option value="1-25">1 - 25</option>
                    <option value="1-10">1 - 10</option>
                    <option value="11-20">11 - 20</option>
                    <option value="21-25">21 - 25</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Product Type</label>
                <select class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                    <option value="">Rajangan</option>
                    @foreach($productTypes as $pt)
                        <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Origin</label>
                <select class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                    <option value="">All Origins</option>
                    @foreach($origins as $org)
                        <option value="{{ $org->id }}">{{ $org->region_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Origin Code</label>
                <select class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                    <option value="">All Codes</option>
                    <option value="P10T5">P10T5</option>
                    <option value="P9K5">P9K5</option>
                    <option value="FN504">FN504</option>
                    <option value="FN602">FN602</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Grouping</label>
                <select class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                    <option value="by_batch">By Batch</option>
                    <option value="by_origin">By Origin</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Metric</label>
                <select class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-emerald-400 font-bold text-xs focus:border-amber-500 outline-none">
                    <option value="yield">Yield (%)</option>
                    <option value="kg">Weight (kg)</option>
                </select>
            </div>

            <div class="flex items-center gap-1.5">
                <button wire:click="resetHistoricalFilters" class="w-1/2 py-2.5 rounded-xl bg-zinc-800 text-zinc-300 hover:bg-zinc-700 text-xs font-bold transition-all">
                    Reset
                </button>
                <button class="w-1/2 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-black transition-all shadow-md shadow-amber-900/30">
                    Apply
                </button>
            </div>
        </div>

        <!-- 8 TOP AGGREGATE KPI CARDS -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg">
                <div class="text-[11px] font-bold text-zinc-400 uppercase">Total Batches</div>
                <div class="font-mono font-black text-xl text-amber-400">{{ $historicalData['totalBatches'] ?? 25 }}</div>
            </div>

            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg">
                <div class="text-[11px] font-bold text-zinc-400 uppercase">Processed Input</div>
                <div class="font-mono font-black text-xl text-amber-400">{{ $historicalData['processedInputTon'] ?? 53.8 }} <span class="text-xs font-normal text-zinc-400">t</span></div>
            </div>

            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg">
                <div class="text-[11px] font-bold text-zinc-400 uppercase">Product Output</div>
                <div class="font-mono font-black text-xl text-amber-400">{{ $historicalData['productOutputTon'] ?? 38.9 }} <span class="text-xs font-normal text-zinc-400">t</span></div>
            </div>

            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg">
                <div class="text-[11px] font-bold text-zinc-400 uppercase">Weighted Yield</div>
                <div class="font-mono font-black text-xl text-emerald-400">{{ number_format($historicalData['weightedProductYield'] ?? 72.31, 2) }}%</div>
            </div>

            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg">
                <div class="text-[11px] font-bold text-zinc-400 uppercase">Bits Stem</div>
                <div class="font-mono font-black text-xl text-amber-400">{{ number_format($historicalData['bitsStemPct'] ?? 24.60, 2) }}%</div>
            </div>

            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg">
                <div class="text-[11px] font-bold text-zinc-400 uppercase">Dust</div>
                <div class="font-mono font-black text-xl text-blue-400">{{ number_format($historicalData['dustPct'] ?? 1.78, 2) }}%</div>
            </div>

            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg">
                <div class="text-[11px] font-bold text-zinc-400 uppercase">Variance</div>
                <div class="font-mono font-black text-xl text-zinc-300">{{ number_format($historicalData['variancePct'] ?? 1.31, 2) }}%</div>
            </div>

            <div class="bg-zinc-900 border border-zinc-800/80 rounded-2xl p-3.5 space-y-1 shadow-lg">
                <div class="text-[11px] font-bold text-zinc-400 uppercase">Consistency</div>
                <div class="font-mono font-black text-sm text-amber-400 flex items-center mt-1">
                    <span class="mr-1">🛡️</span> {{ $historicalData['consistency'] ?? 'Moderate' }}
                </div>
            </div>
        </div>

        <!-- UPPER SECTION: HISTORICAL PRODUCT YIELD TREND (FULL-WIDTH ENLARGED CHART) -->
        <div wire:key="historical-line-chart-{{ md5(json_encode($historicalData['chartLabels'] ?? [])) }}"
             x-data="historicalYieldChart({
                 labels: @js($historicalData['chartLabels'] ?? []),
                 yieldSeries: @js($historicalData['yieldSeries'] ?? []),
                 weightedAvg: @js($historicalData['weightedProductYield'] ?? 72.31),
                 outlierPoints: @js($historicalData['outlierPoints'] ?? [])
             })"
             x-init="initChart()"
             class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-7 shadow-2xl space-y-5">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-zinc-800 pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base sm:text-lg font-black text-zinc-100 uppercase tracking-wide">Historical Product Yield Trend</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-950 text-emerald-300 border border-emerald-800">
                            FULL EXPANDED VIEW
                        </span>
                    </div>
                    <p class="text-xs text-zinc-400 mt-1">Tren hasil yield per batch dengan garis rata-rata tertimbang & deteksi outlier</p>
                </div>

                <!-- Legend -->
                <div class="flex items-center flex-wrap gap-2.5 sm:gap-4 text-xs font-bold bg-zinc-950 px-4 py-2.5 rounded-2xl border border-zinc-800">
                    <span class="flex items-center text-emerald-400"><span class="w-3 h-3 rounded-full bg-emerald-500 mr-2 shadow-sm shadow-emerald-500/50"></span> Product Yield (%)</span>
                    <span class="flex items-center text-amber-400"><span class="w-4 h-0.5 border-t-2 border-dashed border-amber-400 mr-2"></span> Weighted Avg ({{ number_format($historicalData['weightedProductYield'] ?? 72.31, 2) }}%)</span>
                    <span class="flex items-center text-red-400"><span class="w-3 h-3 rounded-full bg-red-500 mr-2 shadow-sm shadow-red-500/50"></span> Outlier</span>
                </div>
            </div>

            <!-- Enlarged Full-Width Canvas Container -->
            <div class="relative w-full h-[420px] sm:h-[480px] lg:h-[520px] bg-zinc-950 p-3 sm:p-5 rounded-2xl border border-zinc-800/80 shadow-inner">
                <canvas x-ref="canvas" class="w-full h-full"></canvas>
            </div>

            <!-- Chart Footer Information Strip -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-zinc-400 bg-zinc-950/70 p-3.5 rounded-2xl border border-zinc-800">
                <div class="flex items-center text-zinc-300">
                    <svg class="w-4 h-4 mr-2 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Milestone Batch 23: Implementasi kontrol penerimaan langsung gudang (DN + MRL).</span>
                </div>
                <div class="text-zinc-500 font-mono text-[11px] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Monitoring {{ count($historicalData['chartLabels'] ?? []) }} Approved Batches</span>
                </div>
            </div>
        </div>

        <!-- PERFORMANCE INSIGHTS (4 HIGH-IMPACT METRIC CARDS + ADVISORY NOTE) -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-800 pb-3">
                <div>
                    <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">Performance Insights</h3>
                    <p class="text-xs text-zinc-400 mt-0.5">Ringkasan performa yield & konsistensi separasi</p>
                </div>
                <div class="px-3.5 py-1.5 rounded-xl bg-amber-950/50 border border-amber-800/60 text-xs text-amber-300 flex items-center">
                    <span class="mr-1.5">💡</span>
                    <span>92% batch berada pada rentang deviasi toleransi standar &plusmn;3.5%.</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono">
                <div class="p-4 rounded-2xl bg-zinc-950 border border-zinc-800 flex items-center justify-between shadow-md">
                    <div>
                        <div class="text-xs font-sans text-zinc-400">🏆 Best Batch</div>
                        <div class="font-bold text-emerald-400 text-lg mt-1">{{ $historicalData['bestBatch'] ?? '24 / 75.8%' }}</div>
                    </div>
                    <span class="text-2xl">📈</span>
                </div>

                <div class="p-4 rounded-2xl bg-zinc-950 border border-zinc-800 flex items-center justify-between shadow-md">
                    <div>
                        <div class="text-xs font-sans text-zinc-400">📉 Lowest Batch</div>
                        <div class="font-bold text-amber-400 text-lg mt-1">{{ $historicalData['lowestBatch'] ?? '7 / 67.4%' }}</div>
                    </div>
                    <span class="text-2xl">📉</span>
                </div>

                <div class="p-4 rounded-2xl bg-zinc-950 border border-zinc-800 flex items-center justify-between shadow-md">
                    <div>
                        <div class="text-xs font-sans text-zinc-400">⟷ Stable Range</div>
                        <div class="font-bold text-cyan-300 text-lg mt-1">{{ $historicalData['stableRange'] ?? '71.0 - 74.5%' }}</div>
                    </div>
                    <span class="text-2xl">📊</span>
                </div>

                <div class="p-4 rounded-2xl bg-zinc-950 border border-zinc-800 flex items-center justify-between shadow-md">
                    <div>
                        <div class="text-xs font-sans text-zinc-400">⚠️ Outliers</div>
                        <div class="font-bold text-red-400 text-lg mt-1">{{ $historicalData['outliersCount'] ?? 3 }} batches</div>
                    </div>
                    <span class="text-2xl">🚨</span>
                </div>
            </div>
        </div>

        <!-- MIDDLE SECTION: OUTPUT COMPOSITION, WEIGHTED YIELD BY ORIGIN, ORIGIN CODE MATRIX -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- 1. Output Composition Trend (Stacked Bars) -->
            <div wire:key="composition-chart-{{ md5(json_encode($historicalData['chartLabels'] ?? [])) }}"
                 x-data="historicalCompositionChart({
                     labels: @js($historicalData['chartLabels'] ?? []),
                     prod: @js($historicalData['compProduct'] ?? []),
                     stem: @js($historicalData['compStem'] ?? []),
                     dust: @js($historicalData['compDust'] ?? []),
                     variance: @js($historicalData['compVariance'] ?? [])
                 })"
                 x-init="initChart()"
                 class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
                
                <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">Output Composition Trend</h3>
                <div class="relative w-full h-[220px] bg-zinc-950 p-2 rounded-2xl border border-zinc-800">
                    <canvas x-ref="canvas" class="w-full h-full"></canvas>
                </div>
            </div>

            <!-- 2. Weighted Yield by Origin -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">Weighted Yield by Origin</h3>
                    <span class="text-[10px] font-bold text-zinc-400"># Batches</span>
                </div>

                <div class="space-y-3 pt-2">
                    @foreach($historicalData['originYieldBars'] ?? [] as $oyb)
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-bold">
                                <span class="text-zinc-200">{{ $oyb['origin'] }}</span>
                                <span class="font-mono text-emerald-400">{{ number_format($oyb['yieldPct'], 2) }}% ({{ $oyb['batchCount'] }})</span>
                            </div>
                            <div class="h-3 w-full bg-zinc-950 rounded-full overflow-hidden border border-zinc-800">
                                <div style="width: {{ min(100, max(10, $oyb['yieldPct'])) }}%" class="h-full bg-gradient-to-r from-amber-500 to-emerald-500 rounded-full"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 3. Origin Code Performance Matrix -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-3">
                <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">Origin Code Performance</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-center text-xs font-mono">
                        <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase text-[10px] border-b border-zinc-800">
                            <tr>
                                <th class="px-2 py-2 text-left">Code</th>
                                <th class="px-2 py-2">&lt;68%</th>
                                <th class="px-2 py-2">68-71%</th>
                                <th class="px-2 py-2">71-74%</th>
                                <th class="px-2 py-2">74-77%</th>
                                <th class="px-2 py-2">&ge;77%</th>
                                <th class="px-2 py-2">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/60">
                            @foreach($historicalData['codeMatrix'] ?? [] as $cm)
                                <tr>
                                    <td class="px-2 py-2 text-left font-bold text-zinc-200">{{ $cm['code'] }}</td>
                                    <td class="px-2 py-2 bg-red-950/40 text-red-300 font-bold">{{ $cm['c1'] }}</td>
                                    <td class="px-2 py-2 bg-amber-950/40 text-amber-300 font-bold">{{ $cm['c2'] }}</td>
                                    <td class="px-2 py-2 bg-emerald-950/40 text-emerald-300 font-bold">{{ $cm['c3'] }}</td>
                                    <td class="px-2 py-2 bg-emerald-900/60 text-emerald-200 font-bold">{{ $cm['c4'] }}</td>
                                    <td class="px-2 py-2 bg-emerald-800/80 text-emerald-100 font-bold">{{ $cm['c5'] }}</td>
                                    <td class="px-2 py-2 font-bold text-zinc-200">{{ $cm['total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-[10px] text-zinc-500">⚠️ Interpret carefully when fewer than 3 batches.</div>
            </div>
        </div>

        <!-- LOWER SECTION: HISTORICAL BATCH PERFORMANCE TABLE -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
            <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">Historical Batch Performance</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-300 font-mono">
                    <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800 text-[11px]">
                        <tr>
                            <th class="px-3 py-3">Batch</th>
                            <th class="px-3 py-3">Date</th>
                            <th class="px-3 py-3">Origin</th>
                            <th class="px-3 py-3">Code</th>
                            <th class="px-3 py-3 text-right">Input kg</th>
                            <th class="px-3 py-3 text-right">Product kg</th>
                            <th class="px-3 py-3 text-right text-emerald-400">Yield %</th>
                            <th class="px-3 py-3 text-right">Stem %</th>
                            <th class="px-3 py-3 text-right">Dust %</th>
                            <th class="px-3 py-3 text-right">Variance %</th>
                            <th class="px-3 py-3 text-center">Certificate</th>
                            <th class="px-3 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60">
                        @forelse($historicalData['batchRows'] ?? [] as $row)
                            <tr class="hover:bg-zinc-800/40 transition-colors">
                                <td class="px-3 py-3 font-bold text-amber-400">{{ $row['batchNum'] }}</td>
                                <td class="px-3 py-3 text-zinc-300">{{ $row['date'] }}</td>
                                <td class="px-3 py-3 font-sans text-zinc-200 font-bold">{{ $row['origin'] }}</td>
                                <td class="px-3 py-3 text-zinc-400">{{ $row['originCode'] }}</td>
                                <td class="px-3 py-3 text-right text-zinc-300">{{ number_format($row['inputKg'], 0, '.', ',') }}</td>
                                <td class="px-3 py-3 text-right text-zinc-200 font-bold">{{ number_format($row['productKg'], 0, '.', ',') }}</td>
                                <td class="px-3 py-3 text-right font-black text-emerald-400">{{ number_format($row['yieldPct'], 2) }}%</td>
                                <td class="px-3 py-3 text-right text-amber-400">{{ number_format($row['stemPct'], 2) }}%</td>
                                <td class="px-3 py-3 text-right text-blue-400">{{ number_format($row['dustPct'], 2) }}%</td>
                                <td class="px-3 py-3 text-right text-zinc-400">{{ number_format($row['variancePct'], 2) }}%</td>
                                <td class="px-3 py-3 text-center font-sans">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-800">
                                        ✓ Released
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center font-sans">
                                    <button wire:click="selectBatch({{ $row['id'] }})" class="px-3 py-1 rounded-xl bg-amber-950 text-amber-300 border border-amber-800 hover:bg-amber-900 text-xs font-bold transition-all">
                                        View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-4 py-8 text-center text-zinc-500 font-sans">
                                    Tidak ada data historis batch yang cocok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="text-[11px] text-zinc-500 font-sans">ⓘ Illustrative historical data until connected to the validated production database.</div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 3: PRIVATE YIELD COST CALCULATOR (CLIENT-SIDE ONLY) -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'yield_calculator'"
         x-data="privateYieldCostCalculator({
             operational: {
                 processedInput: {{ $batchOverviewData['processedInput'] ?? 3173.70 }},
                 productYield: {{ $batchOverviewData['weightedProductYield'] ?? 76.96 }},
                 productQty: {{ $batchOverviewData['productOutput'] ?? 2442.50 }},
                 bitsStemQty: {{ $batchOverviewData['balanceItems']['stemKg'] ?? 650.80 }},
                 dustQty: {{ $batchOverviewData['balanceItems']['dustKg'] ?? 55.50 }},
                 processVariance: {{ $batchOverviewData['balanceItems']['varianceKg'] ?? 24.90 }}
             }
         })"
         class="space-y-6">

        <!-- HEADER & BLUE PRIVACY BANNER -->
        <div>
            <h2 class="text-2xl font-black text-zinc-100 tracking-wide">Private Yield Cost Calculator</h2>
            <p class="text-xs text-zinc-400 mt-1">Estimate effective product cost using actual or simulated separation yield</p>
        </div>

        <div class="p-4 rounded-2xl bg-blue-950/80 border border-blue-800/80 text-blue-200 flex items-center space-x-3 shadow-xl">
            <svg class="w-6 h-6 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            <div class="text-xs">
                <strong class="font-black tracking-wide uppercase text-blue-300">PRIVATE CALCULATION — LOCAL ONLY.</strong>
                <span class="text-blue-200/90 ml-1">Price, cost, and simulation results are not stored or sent to the system and are cleared when the page closes or reloads.</span>
            </div>
        </div>

        <!-- SELECTORS BAR -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-4 sm:p-5 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div>
                    <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Mode</label>
                    <div class="flex items-center bg-zinc-950 p-1 rounded-xl border border-zinc-800">
                        <button type="button" @click="calcMode = 'basic'" :class="calcMode === 'basic' ? 'bg-amber-600 text-white font-black' : 'text-zinc-400'" class="px-3 py-1.5 rounded-lg text-xs transition-all">Basic</button>
                        <button type="button" @click="calcMode = 'advanced'" :class="calcMode === 'advanced' ? 'bg-amber-600 text-white font-black' : 'text-zinc-400'" class="px-3 py-1.5 rounded-lg text-xs transition-all">Advanced</button>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Data Source</label>
                    <select class="px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                        <option>Use Actual Batch Data</option>
                        <option>Manual Simulation</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Batch</label>
                    <select wire:model.live="selectedBatchId" class="px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-amber-400 font-mono font-bold text-xs focus:border-amber-500 outline-none">
                        @foreach($allApprovedBatches as $ab)
                            <option value="{{ $ab->id }}">{{ $ab->batch_code }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Origin</label>
                    <select class="px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                        <option>All Origins</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @click="loadBatchData()" class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-black transition-all flex items-center shadow-lg shadow-amber-900/30">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Load Operational Data
                </button>
                <span class="text-[10px] text-zinc-400 hidden lg:inline">ⓘ Operational data only — no prices loaded</span>
            </div>
        </div>

        <!-- 2-COLUMNS CALCULATOR GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- LEFT COLUMN: 1. OPERATIONAL DATA & 2. PRIVATE COST INPUTS -->
            <div class="space-y-6">

                <!-- 1. Operational Data -->
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
                    <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">1. Operational Data</h3>
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block text-zinc-400 text-[11px] mb-1">Processed Input (kg)</label>
                            <input type="number" step="0.01" x-model.number="op.processedInput" @input="recalculate()" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-amber-400 font-mono font-bold outline-none">
                        </div>
                        <div>
                            <label class="block text-zinc-400 text-[11px] mb-1">Product Yield (%)</label>
                            <input type="number" step="0.01" x-model.number="op.productYield" @input="recalculateYield()" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-emerald-400 font-mono font-bold outline-none">
                        </div>
                        <div>
                            <label class="block text-zinc-400 text-[11px] mb-1">Product Qty (kg)</label>
                            <input type="number" step="0.01" x-model.number="op.productQty" @input="recalculate()" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 font-mono font-bold outline-none">
                        </div>
                        <div>
                            <label class="block text-zinc-400 text-[11px] mb-1">Bits Stem Qty (kg)</label>
                            <input type="number" step="0.01" x-model.number="op.bitsStemQty" @input="recalculate()" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-amber-300 font-mono font-bold outline-none">
                        </div>
                        <div>
                            <label class="block text-zinc-400 text-[11px] mb-1">Dust Qty (kg)</label>
                            <input type="number" step="0.01" x-model.number="op.dustQty" @input="recalculate()" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-blue-300 font-mono font-bold outline-none">
                        </div>
                        <div>
                            <label class="block text-zinc-400 text-[11px] mb-1">Process Variance (kg)</label>
                            <input type="number" step="0.01" x-model.number="op.processVariance" @input="recalculate()" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-400 font-mono font-bold outline-none">
                        </div>
                    </div>
                </div>

                <!-- 2. Private Cost Inputs & By-product Recovery -->
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
                    <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">2. Private Cost Inputs & Recovery</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <!-- Cost Inputs -->
                        <div class="space-y-3">
                            <h4 class="text-[11px] font-bold uppercase text-amber-400">Cost Components</h4>
                            <div>
                                <label class="block text-zinc-400 text-[10px] mb-1">Purchase Price/kg (Rp)</label>
                                <input type="number" x-model.number="costs.purchasePrice" @input="recalculate()" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono font-bold outline-none" placeholder="50000">
                            </div>
                            <div>
                                <label class="block text-zinc-400 text-[10px] mb-1">Processing Fee/Input kg (Rp)</label>
                                <input type="number" x-model.number="costs.processingFee" @input="recalculate()" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono font-bold outline-none" placeholder="2500">
                            </div>
                            <div>
                                <label class="block text-zinc-400 text-[10px] mb-1">Transportation (Rp)</label>
                                <input type="number" x-model.number="costs.transport" @input="recalculate()" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono font-bold outline-none" placeholder="3000000">
                            </div>
                            <div>
                                <label class="block text-zinc-400 text-[10px] mb-1">Handling & Storage (Rp)</label>
                                <input type="number" x-model.number="costs.handling" @input="recalculate()" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono font-bold outline-none" placeholder="500000">
                            </div>
                            <div>
                                <label class="block text-zinc-400 text-[10px] mb-1">Other Costs (Rp)</label>
                                <input type="number" x-model.number="costs.other" @input="recalculate()" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono font-bold outline-none" placeholder="0">
                            </div>
                        </div>

                        <!-- By-product Recovery -->
                        <div class="space-y-3">
                            <h4 class="text-[11px] font-bold uppercase text-emerald-400">By-product Recovery</h4>
                            <div>
                                <label class="block text-zinc-400 text-[10px] mb-1">Bits Stem Value/kg (Rp)</label>
                                <input type="number" x-model.number="recovery.bitsStemValue" @input="recalculate()" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono font-bold outline-none" placeholder="8000">
                            </div>
                            <div>
                                <label class="block text-zinc-400 text-[10px] mb-1">Dust Value/kg (Rp)</label>
                                <input type="number" x-model.number="recovery.dustValue" @input="recalculate()" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono font-bold outline-none" placeholder="1000">
                            </div>
                            <div>
                                <label class="block text-zinc-400 text-[10px] mb-1">Other Recovery (Rp)</label>
                                <input type="number" x-model.number="recovery.otherRecovery" @input="recalculate()" class="w-full px-3 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono font-bold outline-none" placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="recalculate()" class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-black shadow-lg shadow-amber-900/30 transition-all">
                            Calculate
                        </button>
                        <button type="button" @click="clearPrivateInputs()" class="px-4 py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-bold transition-all">
                            Clear Private Inputs
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: 3. CALCULATION RESULTS & SENSITIVITY CHART -->
            <div class="space-y-6">

                <!-- 3. Calculation Results Cards -->
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
                    <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">3. Calculation Results (Simulation Only)</h3>
                    
                    <div class="grid grid-cols-2 gap-3 font-mono">
                        <div class="bg-zinc-950 p-3.5 rounded-2xl border border-zinc-800">
                            <div class="text-[10px] font-sans font-bold uppercase text-zinc-400">Product Output</div>
                            <div class="text-base sm:text-lg font-black text-emerald-400 mt-0.5" x-text="formatNumber(res.productQty) + ' kg'"></div>
                        </div>

                        <div class="bg-zinc-950 p-3.5 rounded-2xl border border-zinc-800">
                            <div class="text-[10px] font-sans font-bold uppercase text-zinc-400">Total Cost Before Recovery</div>
                            <div class="text-base sm:text-lg font-black text-amber-400 mt-0.5" x-text="formatRupiah(res.totalBeforeRecovery)"></div>
                        </div>

                        <div class="bg-zinc-950 p-3.5 rounded-2xl border border-zinc-800">
                            <div class="text-[10px] font-sans font-bold uppercase text-zinc-400">By-product Recovery</div>
                            <div class="text-base sm:text-lg font-black text-emerald-400 mt-0.5" x-text="formatRupiah(res.totalRecovery)"></div>
                        </div>

                        <div class="bg-zinc-950 p-3.5 rounded-2xl border border-zinc-800">
                            <div class="text-[10px] font-sans font-bold uppercase text-zinc-400">Net Cost</div>
                            <div class="text-base sm:text-lg font-black text-amber-300 mt-0.5" x-text="formatRupiah(res.netCost)"></div>
                        </div>

                        <div class="bg-zinc-950 p-3.5 rounded-2xl border border-zinc-800">
                            <div class="text-[10px] font-sans font-bold uppercase text-emerald-400">Effective Product Cost/kg</div>
                            <div class="text-base sm:text-lg font-black text-emerald-400 mt-0.5" x-text="formatRupiah(res.effectiveCostPerKg)"></div>
                        </div>

                        <div class="bg-zinc-950 p-3.5 rounded-2xl border border-zinc-800">
                            <div class="text-[10px] font-sans font-bold uppercase text-amber-400">Break-even Price/kg</div>
                            <div class="text-base sm:text-lg font-black text-amber-400 mt-0.5" x-text="formatRupiah(res.breakEvenPrice)"></div>
                        </div>
                    </div>
                </div>

                <!-- Yield Sensitivity Chart -->
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
                    <div class="flex items-center justify-between border-b border-zinc-800 pb-2">
                        <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">Yield Sensitivity — Effective Cost/kg</h3>
                        <span class="text-[10px] font-mono text-emerald-400 font-bold" x-text="'Actual Yield: ' + op.productYield + '% (' + formatRupiah(res.effectiveCostPerKg) + ')'"></span>
                    </div>

                    <div class="relative w-full h-[220px] bg-zinc-950 p-2 rounded-2xl border border-zinc-800">
                        <canvas x-ref="sensitivityCanvas" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- SCENARIO COMPARISON TABLE -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
            <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">Scenario Comparison (Simulation)</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-300 font-mono">
                    <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800 text-[11px]">
                        <tr>
                            <th class="px-4 py-3 font-sans">Scenario</th>
                            <th class="px-4 py-3 text-right">Yield</th>
                            <th class="px-4 py-3 text-right">Product Qty (kg)</th>
                            <th class="px-4 py-3 text-right">Net Cost (Rp)</th>
                            <th class="px-4 py-3 text-right">Effective Cost/kg (Rp)</th>
                            <th class="px-4 py-3 text-right">Diff vs Baseline (Rp/kg)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60">
                        <!-- Baseline 75% -->
                        <tr class="hover:bg-zinc-800/30">
                            <td class="px-4 py-3 font-sans font-bold text-zinc-300">Baseline (75%)</td>
                            <td class="px-4 py-3 text-right text-zinc-300">75.00 %</td>
                            <td class="px-4 py-3 text-right text-zinc-300" x-text="formatNumber(scenarios.baseline.productQty)"></td>
                            <td class="px-4 py-3 text-right text-zinc-300" x-text="formatNumber(scenarios.baseline.netCost)"></td>
                            <td class="px-4 py-3 text-right text-zinc-200 font-bold" x-text="formatNumber(scenarios.baseline.effectiveCost)"></td>
                            <td class="px-4 py-3 text-right text-zinc-500">—</td>
                        </tr>

                        <!-- Actual Batch (Highlighted Green) -->
                        <tr class="bg-emerald-950/30 border-y border-emerald-800/50">
                            <td class="px-4 py-3 font-sans font-black text-emerald-400" x-text="'Actual Batch (' + op.productYield + '%)'"></td>
                            <td class="px-4 py-3 text-right font-black text-emerald-400" x-text="op.productYield + ' %'"></td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-300" x-text="formatNumber(res.productQty)"></td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-300" x-text="formatNumber(res.netCost)"></td>
                            <td class="px-4 py-3 text-right font-black text-emerald-400" x-text="formatNumber(res.effectiveCostPerKg)"></td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-400" x-text="formatNumber(res.effectiveCostPerKg - scenarios.baseline.effectiveCost)"></td>
                        </tr>

                        <!-- Target Scenario 80% -->
                        <tr class="hover:bg-zinc-800/30">
                            <td class="px-4 py-3 font-sans font-bold text-zinc-300">Target Scenario (80%)</td>
                            <td class="px-4 py-3 text-right text-zinc-300">80.00 %</td>
                            <td class="px-4 py-3 text-right text-zinc-300" x-text="formatNumber(scenarios.target.productQty)"></td>
                            <td class="px-4 py-3 text-right text-zinc-300" x-text="formatNumber(scenarios.target.netCost)"></td>
                            <td class="px-4 py-3 text-right text-amber-400 font-bold" x-text="formatNumber(scenarios.target.effectiveCost)"></td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-400" x-text="formatNumber(scenarios.target.effectiveCost - scenarios.baseline.effectiveCost)"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FOOTER PRIVACY BADGES & LOCAL PRINT BUTTON -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl bg-zinc-950 border border-zinc-800 text-xs">
            <div class="flex flex-wrap items-center gap-3 text-zinc-400">
                <span class="text-amber-400">⚠️ Simulation only — not part of Process Certificate, invoice, or commercial agreement.</span>
                <span class="px-2.5 py-1 rounded-lg bg-zinc-900 border border-zinc-800 text-emerald-400 font-bold">🛡️ No Database</span>
                <span class="px-2.5 py-1 rounded-lg bg-zinc-900 border border-zinc-800 text-emerald-400 font-bold">🛡️ No Analytics Tracking</span>
                <span class="px-2.5 py-1 rounded-lg bg-zinc-900 border border-zinc-800 text-emerald-400 font-bold">🛡️ Cleared on Refresh</span>
            </div>

            <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-100 font-bold text-xs flex items-center transition-all">
                🖨️ Print Locally
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 4: CERTIFICATES & DOWNLOADS -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'certificates'" class="space-y-6">
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
            <div class="border-b border-zinc-800 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-black text-amber-400 uppercase tracking-wide">Daftar Sertifikat Produk Resmi (ACC Supervisor)</h3>
                    <p class="text-xs text-zinc-400">Pilih 'Preview Certificate' untuk melihat dokumen pratinjau sebelum mengunduh file sertifikat resmi PDF</p>
                </div>
                <span class="text-xs font-mono font-bold text-zinc-400">Total: {{ $approvedBatches->total() }} Sertifikat</span>
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
                            <th class="px-4 py-3 text-center">Aksi / Dokumen</th>
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
                                    <button wire:click="openPreviewModal({{ $b->id }})" class="px-3.5 py-2 min-h-[38px] inline-flex items-center text-xs font-black rounded-xl bg-amber-950 text-amber-300 border border-amber-800 hover:bg-amber-900 shadow">
                                        👁️ Preview
                                    </button>
                                    <a href="{{ route('certificate.pdf', $b->id) }}" target="_blank" class="px-3.5 py-2 min-h-[38px] inline-flex items-center text-xs font-black rounded-xl bg-emerald-950 text-emerald-300 border border-emerald-800 hover:bg-emerald-900 shadow">
                                        📥 Download PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-zinc-500">Belum ada Sertifikat Produk yang disetujui.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-3 border-t border-zinc-800/80">
                {{ $approvedBatches->links() }}
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- PREVIEW CERTIFICATE MODAL -->
    <!-- ========================================================================= -->
    <div x-show="showPreviewModal"
         style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        
        <div @click.away="showPreviewModal = false" class="bg-zinc-900 border border-zinc-700 rounded-3xl w-full max-w-4xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-zinc-800 flex items-center justify-between bg-zinc-950">
                <div class="flex items-center space-x-2">
                    <span class="text-xl">📄</span>
                    <h3 class="text-base font-black text-amber-400">Pratinjau Sertifikat Pemisahan Produk Tembakau</h3>
                </div>
                <button @click="showPreviewModal = false" class="p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-800 text-lg">&times;</button>
            </div>

            <div class="flex-1 p-4 overflow-y-auto bg-zinc-950/60">
                @if($previewBatchId)
                    <iframe src="{{ route('certificate.show', $previewBatchId) }}" class="w-full h-[600px] rounded-2xl border border-zinc-800 shadow-inner"></iframe>
                @else
                    <div class="h-64 flex items-center justify-center text-zinc-500 text-xs">Memuat dokumen sertifikat...</div>
                @endif
            </div>

            <div class="p-4 border-t border-zinc-800 bg-zinc-950 flex items-center justify-between">
                <button @click="showPreviewModal = false" class="px-4 py-2.5 rounded-xl bg-zinc-800 text-zinc-300 font-bold text-xs">Tutup</button>
                @if($previewBatchId)
                    <a href="{{ route('certificate.pdf', $previewBatchId) }}" target="_blank" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs flex items-center shadow-lg shadow-emerald-900/30">
                        📥 Download PDF Resmi
                    </a>
                @endif
            </div>
        </div>
    </div>

</div>

<!-- JAVASCRIPT & ALPINE COMPONENTS FOR CHARTS & LOCAL CALCULATOR -->
<script>
function historicalYieldChart(data) {
    return {
        chart: null,
        initChart() {
            this.$nextTick(() => {
                const ctx = this.$refs.canvas.getContext('2d');
                if (!ctx) return;

                if (this.chart) this.chart.destroy();

                const avgArray = new Array(data.labels.length).fill(data.weightedAvg);

                // Dynamically compute safe bounds so both top and bottom are completely visible with zero clipping
                const values = (data.yieldSeries || []).filter(v => v !== null && v !== undefined && !isNaN(v));
                const minVal = values.length > 0 ? Math.min(...values, data.weightedAvg) : 0;
                const maxVal = values.length > 0 ? Math.max(...values, data.weightedAvg) : 100;
                const hasZero = values.some(v => v <= 5);

                // Auto-scale min/max with comfortable margin so no points or peaks clip
                let yMin = hasZero ? 0 : Math.max(0, Math.floor((minVal - 10) / 10) * 10);
                let yMax = Math.min(100, Math.ceil((maxVal + 8) / 10) * 10);
                if (maxVal > 85 || yMax < 100) {
                    if (maxVal > 85) yMax = 100;
                }
                if (yMin >= yMax) { yMin = 0; yMax = 100; }

                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [
                            {
                                label: 'Product Yield (%)',
                                data: data.yieldSeries,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.12)',
                                pointBackgroundColor: '#10b981',
                                pointBorderColor: '#064e3b',
                                pointRadius: 5,
                                pointHoverRadius: 8,
                                tension: 0.25,
                                borderWidth: 3,
                                fill: true
                            },
                            {
                                label: 'Weighted Average (' + data.weightedAvg + '%)',
                                data: avgArray,
                                borderColor: '#f59e0b',
                                borderDash: [8, 6],
                                pointRadius: 0,
                                fill: false,
                                borderWidth: 2.5
                            },
                            {
                                label: 'Outlier',
                                data: data.outlierPoints,
                                borderColor: '#ffffff',
                                borderWidth: 2,
                                backgroundColor: '#ef4444',
                                pointBackgroundColor: '#ef4444',
                                pointRadius: 7,
                                pointHoverRadius: 10,
                                pointStyle: 'circle',
                                showLine: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                top: 20,
                                bottom: 15,
                                left: 10,
                                right: 15
                            }
                        },
                        interaction: { intersect: false, mode: 'index' },
                        scales: {
                            y: {
                                min: yMin,
                                max: yMax,
                                grid: { color: 'rgba(255, 255, 255, 0.08)' },
                                ticks: { 
                                    color: '#a1a1aa', 
                                    font: { family: 'ui-monospace, SFMono-Regular, monospace', size: 12, weight: '600' },
                                    stepSize: 10,
                                    callback: v => v + '%' 
                                }
                            },
                            x: {
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { 
                                    color: '#a1a1aa', 
                                    font: { family: 'ui-monospace, SFMono-Regular, monospace', size: 11, weight: '600' },
                                    maxRotation: 0,
                                    autoSkip: false
                                }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#18181b',
                                titleColor: '#f59e0b',
                                titleFont: { size: 13, weight: 'bold' },
                                bodyColor: '#ffffff',
                                bodyFont: { size: 12 },
                                borderColor: '#3f3f46',
                                borderWidth: 1,
                                padding: 12,
                                boxPadding: 6,
                                usePointStyle: true,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed.y !== null) {
                                            label += context.parsed.y + '%';
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        }
    };
}

function historicalCompositionChart(data) {
    return {
        chart: null,
        initChart() {
            this.$nextTick(() => {
                const ctx = this.$refs.canvas.getContext('2d');
                if (!ctx) return;

                if (this.chart) this.chart.destroy();

                this.chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [
                            { label: 'Product', data: data.prod, backgroundColor: '#10b981' },
                            { label: 'Bits Stem', data: data.stem, backgroundColor: '#f59e0b' },
                            { label: 'Dust', data: data.dust, backgroundColor: '#3b82f6' },
                            { label: 'Variance', data: data.variance, backgroundColor: '#6b7280' }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { stacked: true, ticks: { color: '#9ca3af', font: { size: 9 } } },
                            y: { stacked: true, max: 100, ticks: { color: '#9ca3af', callback: v => v + '%' } }
                        },
                        plugins: { legend: { display: false } }
                    }
                });
            });
        }
    };
}

function privateYieldCostCalculator(initial) {
    return {
        calcMode: 'basic',
        op: {
            processedInput: initial.operational.processedInput || 3173.70,
            productYield: initial.operational.productYield || 76.96,
            productQty: initial.operational.productQty || 2442.50,
            bitsStemQty: initial.operational.bitsStemQty || 650.80,
            dustQty: initial.operational.dustQty || 55.50,
            processVariance: initial.operational.processVariance || 24.90
        },
        costs: {
            purchasePrice: 50000,
            processingFee: 2500,
            transport: 3000000,
            handling: 500000,
            other: 0
        },
        recovery: {
            bitsStemValue: 8000,
            dustValue: 1000,
            otherRecovery: 0
        },
        res: {
            productQty: 2442.50,
            totalBeforeRecovery: 170619250,
            totalRecovery: 5261900,
            netCost: 165357350,
            effectiveCostPerKg: 67700,
            breakEvenPrice: 67700
        },
        scenarios: {
            baseline: { productQty: 2380.28, netCost: 165249650, effectiveCost: 69389 },
            target: { productQty: 2538.96, netCost: 165851750, effectiveCost: 65310 }
        },
        sensChart: null,

        init() {
            this.recalculate();
            this.$nextTick(() => this.initSensitivityChart());
        },

        loadBatchData() {
            this.recalculate();
            this.updateSensitivityChart();
        },

        recalculateYield() {
            if (this.op.processedInput > 0 && this.op.productYield > 0) {
                this.op.productQty = parseFloat(((this.op.processedInput * this.op.productYield) / 100).toFixed(2));
            }
            this.recalculate();
        },

        recalculate() {
            const input = parseFloat(this.op.processedInput) || 0;
            const yieldPct = parseFloat(this.op.productYield) || 0;
            const prodQty = parseFloat(this.op.productQty) || (input * (yieldPct / 100));

            const pPrice = parseFloat(this.costs.purchasePrice) || 0;
            const pFee = parseFloat(this.costs.processingFee) || 0;
            const trans = parseFloat(this.costs.transport) || 0;
            const hand = parseFloat(this.costs.handling) || 0;
            const otherC = parseFloat(this.costs.other) || 0;

            const stemVal = parseFloat(this.recovery.bitsStemValue) || 0;
            const dustVal = parseFloat(this.recovery.dustValue) || 0;
            const otherR = parseFloat(this.recovery.otherRecovery) || 0;

            const totalBefore = (pPrice * input) + (pFee * input) + trans + hand + otherC;
            const totalRec = (parseFloat(this.op.bitsStemQty) * stemVal) + (parseFloat(this.op.dustQty) * dustVal) + otherR;
            const net = Math.max(0, totalBefore - totalRec);
            const effCost = prodQty > 0 ? Math.round(net / prodQty) : 0;

            this.res.productQty = prodQty;
            this.res.totalBeforeRecovery = totalBefore;
            this.res.totalRecovery = totalRec;
            this.res.netCost = net;
            this.res.effectiveCostPerKg = effCost;
            this.res.breakEvenPrice = effCost;

            // Compute Baseline (75%)
            const baseProd = input * 0.75;
            const baseStem = input * 0.22;
            const baseDust = input * 0.02;
            const baseRec = (baseStem * stemVal) + (baseDust * dustVal) + otherR;
            const baseNet = Math.max(0, totalBefore - baseRec);
            this.scenarios.baseline = {
                productQty: baseProd,
                netCost: baseNet,
                effectiveCost: baseProd > 0 ? Math.round(baseNet / baseProd) : 0
            };

            // Compute Target (80%)
            const targetProd = input * 0.80;
            const targetStem = input * 0.17;
            const targetDust = input * 0.02;
            const targetRec = (targetStem * stemVal) + (targetDust * dustVal) + otherR;
            const targetNet = Math.max(0, totalBefore - targetRec);
            this.scenarios.target = {
                productQty: targetProd,
                netCost: targetNet,
                effectiveCost: targetProd > 0 ? Math.round(targetNet / targetProd) : 0
            };

            this.updateSensitivityChart();
        },

        clearPrivateInputs() {
            this.costs = { purchasePrice: 0, processingFee: 0, transport: 0, handling: 0, other: 0 };
            this.recovery = { bitsStemValue: 0, dustValue: 0, otherRecovery: 0 };
            this.recalculate();
        },

        initSensitivityChart() {
            const canvas = this.$refs.sensitivityCanvas;
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            const yieldPoints = [70, 72.5, this.op.productYield, 80, 82.5];
            const costPoints = yieldPoints.map(y => {
                const q = this.op.processedInput * (y / 100);
                return q > 0 ? Math.round(this.res.netCost / q) : 0;
            });

            this.sensChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: yieldPoints.map(y => y + '%'),
                    datasets: [{
                        label: 'Cost/kg (Rp)',
                        data: costPoints,
                        borderColor: '#f59e0b',
                        backgroundColor: '#10b981',
                        pointBackgroundColor: ['#10b981', '#10b981', '#10b981', '#f59e0b', '#f97316'],
                        pointRadius: 5,
                        tension: 0.2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { ticks: { color: '#9ca3af', font: { size: 9 }, callback: v => 'Rp ' + (v / 1000) + 'k' } },
                        x: { ticks: { color: '#9ca3af', font: { size: 9 } } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        },

        updateSensitivityChart() {
            if (!this.sensChart) {
                this.initSensitivityChart();
                return;
            }
            const yieldPoints = [70, 72.5, this.op.productYield, 80, 82.5];
            const costPoints = yieldPoints.map(y => {
                const q = this.op.processedInput * (y / 100);
                return q > 0 ? Math.round(this.res.netCost / q) : 0;
            });
            this.sensChart.data.labels = yieldPoints.map(y => y + '%');
            this.sensChart.data.datasets[0].data = costPoints;
            this.sensChart.update();
        },

        formatRupiah(num) {
            if (num === null || isNaN(num)) return 'Rp 0';
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
        },

        formatNumber(num) {
            if (num === null || isNaN(num)) return '0';
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
        }
    };
}
</script>
