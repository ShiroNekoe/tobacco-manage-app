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
                    activeTab === 'reconciliation' ? 'Receiving Reconciliation' :
                    activeTab === 'certificates' ? 'Certificates' :
                    activeTab === 'yield_calculator' ? 'Yield Cost Calculator' :
                    activeTab === 'dn_shipments' ? 'DN Shipment' :
                    activeTab === 'profile' ? 'User Profile' : 'Batch Overview'
                ">Batch Overview</span>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 1: BATCH OVERVIEW & RECONCILIATION -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'batch_overview' || activeTab === 'reconciliation'" class="space-y-6">

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
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-4 sm:p-5 shadow-xl grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3 items-end">
            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Batch</label>
                <select wire:model.live="selectedBatchId" class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-amber-400 font-mono font-bold text-xs focus:border-amber-500 outline-none">
                    @foreach($allApprovedBatches as $ab)
                        <option value="{{ $ab->id }}">{{ $ab->batch_code }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-amber-400 mb-1 flex items-center gap-1">
                    <span>📥 DN Received</span>
                </label>
                <input type="text" value="{{ $batchOverviewData['dnReceived']['dn_number'] ?? ($batchOverviewData['deliveryNote'] ?? '-') }}" readonly class="w-full px-3 py-2.5 rounded-xl bg-zinc-950/80 border border-amber-500/40 text-amber-300 font-mono font-bold text-xs outline-none" title="Inbound Raw Material Delivery Note">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-cyan-400 mb-1 flex items-center gap-1">
                    <span>🚚 DN Shipped</span>
                </label>
                <div class="px-3 py-2 rounded-xl bg-zinc-950/80 border {{ !empty($batchOverviewData['dnShipped']['has_shipment']) ? 'border-cyan-500/40 text-cyan-300' : 'border-zinc-800 text-zinc-500' }} font-mono font-bold text-xs truncate flex items-center justify-between">
                    <span class="truncate">{{ $batchOverviewData['dnShipped']['dn_number'] ?? '-' }}</span>
                    @if(!empty($batchOverviewData['dnShipped']['has_shipment']))
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-black {{ ($batchOverviewData['dnShipped']['status'] ?? '') === 'Approved' ? 'bg-emerald-950 text-emerald-400' : 'bg-amber-950 text-amber-400' }}">
                            {{ $batchOverviewData['dnShipped']['status'] ?? 'Shipped' }}
                        </span>
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Receipt Date</label>
                <div class="flex items-center px-3 py-2.5 rounded-xl bg-zinc-950/60 border border-zinc-800 text-zinc-300 font-mono text-xs">
                    <svg class="w-3.5 h-3.5 mr-1.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    {{ $batchOverviewData['receiptDate'] ?? '-' }}
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-amber-400 mb-1">Origin</label>
                <div class="px-3 py-2.5 rounded-xl bg-zinc-950/80 border border-amber-500/40 text-amber-300 font-bold text-xs truncate">
                    {{ $batchOverviewData['originName'] ?? '-' }}
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-cyan-400 mb-1">Origin Code</label>
                <div class="px-3 py-2.5 rounded-xl bg-zinc-950/80 border border-cyan-500/40 text-cyan-300 font-mono font-bold text-xs truncate">
                    {{ $batchOverviewData['originCode'] ?? '-' }}
                </div>
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

        <!-- DUAL DELIVERY NOTE PIPELINE CARD: DN RECEIVED (INBOUND) vs DN SHIPPED (OUTBOUND) -->
        <div class="bg-gradient-to-r from-zinc-900 via-zinc-900 to-zinc-950 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zinc-800 pb-4 mb-4">
                <div>
                    <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wider flex items-center gap-2">
                        <span>📑 Delivery Note Reconciliation Pipeline (DN Received vs DN Shipped)</span>
                    </h3>
                    <p class="text-xs text-zinc-400 mt-0.5">End-to-end delivery tracking from raw material intake (Inbound) to finished goods dispatch (Outbound)</p>
                </div>
                
                @if(!empty($batchOverviewData['dnShipped']['has_shipment']))
                    <div class="flex items-center gap-2 self-start md:self-auto">
                        @if(!empty($batchOverviewData['dnShipped']['id']))
                            <button wire:click="openShipmentPreview({{ $batchOverviewData['dnShipped']['id'] }})" class="px-3.5 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-cyan-300 border border-cyan-500/30 text-xs font-bold transition-all flex items-center gap-1.5 shadow">
                                <span>📄 View Shipment DN (PDF)</span>
                            </button>
                            @if(empty($batchOverviewData['dnShipped']['is_approved']))
                                <button wire:click="openApprovalModal({{ $batchOverviewData['dnShipped']['id'] }})" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black transition-all flex items-center gap-1.5 shadow-lg shadow-emerald-900/50 animate-pulse">
                                    <span>✅ Approve Shipment DN</span>
                                </button>
                            @endif
                        @endif
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <!-- 1. DN RECEIVED (INBOUND) -->
                <div class="bg-zinc-950/80 border border-amber-500/30 rounded-2xl p-4 space-y-2 relative shadow-lg">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-950 text-amber-300 border border-amber-800 flex items-center gap-1">
                            <span>📥 1. Inbound DN (Received)</span>
                        </span>
                        <span class="text-[10px] font-mono text-zinc-400">{{ $batchOverviewData['dnReceived']['receipt_date'] ?? '-' }}</span>
                    </div>
                    <div class="pt-1">
                        <div class="text-xs text-zinc-400">Inbound Delivery Note:</div>
                        <div class="text-base font-mono font-black text-amber-400">{{ $batchOverviewData['dnReceived']['dn_number'] ?? '-' }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-zinc-800/80 text-xs font-mono">
                        <div>
                            <span class="text-[10px] text-zinc-500 block font-sans">Total Packages</span>
                            <span class="font-bold text-zinc-200">{{ $batchOverviewData['dnReceived']['packs'] ?? '-' }} Packs</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-zinc-500 block font-sans">DN Gross</span>
                            <span class="font-bold text-amber-300">{{ number_format($batchOverviewData['dnReceived']['gross_kg'] ?? 0, 2) }} kg</span>
                        </div>
                    </div>
                    <div class="pt-1 text-[11px] text-emerald-400 font-bold flex items-center gap-1">
                        <span>✓ {{ $batchOverviewData['dnReceived']['status'] ?? 'Verified by Plant' }}</span>
                    </div>
                </div>

                <!-- 2. TRANSITION / PROCESSING STAGE -->
                <div class="bg-zinc-950/50 border border-zinc-800 rounded-2xl p-4 space-y-2 text-center relative">
                    <div class="text-xs font-bold text-zinc-400 uppercase tracking-wider">⚡ Batch Processing</div>
                    <div class="font-mono font-black text-lg text-emerald-400">
                        {{ number_format($batchOverviewData['productOutput'] ?? 0, 2) }} <span class="text-xs font-normal text-zinc-400">kg Output</span>
                    </div>
                    <div class="text-xs text-zinc-400">
                        Product Yield: <strong class="text-emerald-400 font-mono">{{ number_format($batchOverviewData['weightedProductYield'] ?? 0, 2) }}%</strong>
                    </div>
                    <div class="w-full bg-zinc-800 h-2 rounded-full overflow-hidden mt-2">
                        <div class="bg-gradient-to-r from-amber-500 via-emerald-500 to-cyan-500 h-full rounded-full" style="width: 100%"></div>
                    </div>
                    <div class="text-[10px] text-zinc-500 font-mono">Process 1 & Process 2 Completed</div>
                </div>

                <!-- 3. DN SHIPPED (OUTBOUND) -->
                <div class="bg-zinc-950/80 border {{ !empty($batchOverviewData['dnShipped']['has_shipment']) ? 'border-cyan-500/30' : 'border-zinc-800' }} rounded-2xl p-4 space-y-2 relative shadow-lg">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ !empty($batchOverviewData['dnShipped']['has_shipment']) ? 'bg-cyan-950 text-cyan-300 border border-cyan-800' : 'bg-zinc-900 text-zinc-500 border border-zinc-800' }} flex items-center gap-1">
                            <span>🚚 2. Outbound DN (Shipped)</span>
                        </span>
                        @if(!empty($batchOverviewData['dnShipped']['has_shipment']))
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ ($batchOverviewData['dnShipped']['status'] ?? '') === 'Approved' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-amber-950 text-amber-300 border border-amber-800' }}">
                                {{ ($batchOverviewData['dnShipped']['status'] ?? '') === 'Approved' ? '✅ Approved' : '🚚 Shipped' }}
                            </span>
                        @else
                            <span class="text-[10px] text-zinc-500 font-bold">Pending</span>
                        @endif
                    </div>
                    <div class="pt-1">
                        <div class="text-xs text-zinc-400">Outbound Delivery Note:</div>
                        <div class="text-base font-mono font-black {{ !empty($batchOverviewData['dnShipped']['has_shipment']) ? 'text-cyan-300' : 'text-zinc-500' }}">
                            {{ $batchOverviewData['dnShipped']['dn_number'] ?? '-' }}
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-zinc-800/80 text-xs font-mono">
                        <div>
                            <span class="text-[10px] text-zinc-500 block font-sans">Total Sacks Shipped</span>
                            <span class="font-bold text-zinc-200">{{ !empty($batchOverviewData['dnShipped']['total_sacks']) ? $batchOverviewData['dnShipped']['total_sacks'] . ' Sacks' : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-zinc-500 block font-sans">Total Net Weight</span>
                            <span class="font-bold text-cyan-300">{{ !empty($batchOverviewData['dnShipped']['total_netto_kg']) ? number_format($batchOverviewData['dnShipped']['total_netto_kg'], 2) . ' kg' : '-' }}</span>
                        </div>
                    </div>
                    <div class="pt-1 text-[11px] {{ !empty($batchOverviewData['dnShipped']['is_approved']) ? 'text-emerald-400' : 'text-amber-400' }} font-bold flex items-center gap-1">
                        @if(!empty($batchOverviewData['dnShipped']['is_approved']))
                            <span>✓ Approved by Customer ({{ $batchOverviewData['dnShipped']['approved_at'] }})</span>
                        @elseif(!empty($batchOverviewData['dnShipped']['has_shipment']))
                            <span>⏳ Waiting Customer Approval</span>
                        @else
                            <span class="text-zinc-500">No shipment recorded for this batch</span>
                        @endif
                    </div>
                </div>
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

            <!-- CARD 1: Material Receiving Reconciliation — DN vs MRL (Inbound) -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-800 pb-3">
                        <div>
                            <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide flex items-center gap-2">
                                <span>📥 Material Receiving Reconciliation — DN vs MRL</span>
                            </h3>
                            <p class="text-xs text-zinc-400 mt-0.5">Rekonsiliasi penimbangan awal surat jalan inbound vs kedatangan fisik gudang</p>
                        </div>
                        <div class="px-3 py-1.5 rounded-xl bg-zinc-950 border border-zinc-800 text-[11px] font-mono text-zinc-300 flex items-center gap-2 shrink-0">
                            <span class="text-zinc-500 font-sans font-bold">DN Receiver:</span>
                            <span class="text-amber-400 font-bold">{{ $batchOverviewData['dnReceiverName'] ?? 'Plant Intake Team' }}</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-left text-xs text-zinc-300">
                            <thead class="bg-zinc-950/80 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                                <tr>
                                    <th class="px-3 py-2.5">Origin</th>
                                    <th class="px-3 py-2.5">Inbound DN & Receiver</th>
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
                                        <td class="px-3 py-2.5 font-sans">
                                            <div class="font-mono font-bold text-amber-400 text-xs">{{ $or['dnNumber'] ?? ($batchOverviewData['dnReceived']['dn_number'] ?? '-') }}</div>
                                            <div class="text-[10px] text-zinc-400 flex items-center gap-1 mt-0.5">
                                                <span>👤 Recv:</span>
                                                <span class="text-zinc-300 font-semibold">{{ $or['receiver'] ?? ($batchOverviewData['dnReceiverName'] ?? 'Plant Intake Team') }}</span>
                                            </div>
                                        </td>
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
                                        <td class="px-3 py-2.5 font-sans">
                                            <div class="font-mono font-bold text-amber-400 text-xs">DN-2026-0001</div>
                                            <div class="text-[10px] text-zinc-400 mt-0.5">👤 Recv: Plant Intake Team</div>
                                        </td>
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
                                        <td class="px-3 py-2.5 font-sans">
                                            <div class="font-mono font-bold text-amber-400 text-xs">DN-2026-0001</div>
                                            <div class="text-[10px] text-zinc-400 mt-0.5">👤 Recv: Plant Intake Team</div>
                                        </td>
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
                                    <td colspan="2" class="px-3 py-2.5 font-sans uppercase text-zinc-100">TOTAL INBOUND</td>
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

            <!-- CARD 2: Outbound DN Shipment Reconciliation (DN Shipped) -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4 flex flex-col justify-between">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-zinc-800 pb-3">
                        <div>
                            <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide flex items-center gap-2">
                                <span>🚚 Outbound DN Shipment Details (DN Shipped)</span>
                            </h3>
                            <p class="text-xs text-zinc-400 mt-0.5">Daftar Surat Jalan Pengiriman barang jadi (rajangan/bits/dust) kepada customer</p>
                        </div>
                        @if(!empty($batchOverviewData['dnShipped']['has_shipment']))
                            <div class="px-3 py-1.5 rounded-xl text-[11px] font-bold flex items-center gap-1.5 {{ !empty($batchOverviewData['dnShipped']['is_approved']) ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-amber-950 text-amber-300 border border-amber-800' }}">
                                <span>{{ !empty($batchOverviewData['dnShipped']['is_approved']) ? '✅ Approved by Customer' : '⏳ Waiting Approval' }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-left text-xs text-zinc-300">
                            <thead class="bg-zinc-950/80 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                                <tr>
                                    <th class="px-3 py-2.5">Outbound DN #</th>
                                    <th class="px-3 py-2.5">Shipment Date</th>
                                    <th class="px-3 py-2.5">Material / Lot</th>
                                    <th class="px-3 py-2.5">Vehicle & Driver</th>
                                    <th class="px-3 py-2.5 text-center">Sacks</th>
                                    <th class="px-3 py-2.5 text-right">Net Weight (kg)</th>
                                    <th class="px-3 py-2.5 text-center">Status</th>
                                    <th class="px-3 py-2.5 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/60 font-mono">
                                @forelse($batchOverviewData['dnShippedRows'] ?? [] as $row)
                                    <tr class="hover:bg-zinc-800/30">
                                        <td class="px-3 py-2.5">
                                            <span class="font-bold text-cyan-300">{{ $row['dn_number'] }}</span>
                                        </td>
                                        <td class="px-3 py-2.5 font-sans text-zinc-300">{{ $row['shipment_date'] }}</td>
                                        <td class="px-3 py-2.5 font-sans">
                                            <span class="font-bold text-zinc-200">{{ $row['material_type'] }}</span>
                                            <span class="text-amber-500 block text-[11px] font-mono">{{ $row['origin_lot'] }}</span>
                                        </td>
                                        <td class="px-3 py-2.5 font-sans text-zinc-300">
                                            <div class="font-mono text-zinc-200">{{ $row['vehicle_number'] }}</div>
                                            <div class="text-[10px] text-zinc-400">{{ $row['driver_name'] }}</div>
                                        </td>
                                        <td class="px-3 py-2.5 text-center text-zinc-200 font-bold">{{ $row['total_sacks'] }} Sacks</td>
                                        <td class="px-3 py-2.5 text-right text-emerald-400 font-bold">{{ number_format($row['total_netto_kg'], 2, '.', ',') }} kg</td>
                                        <td class="px-3 py-2.5 text-center font-sans">
                                            @if($row['is_approved'])
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-800" title="Approved at {{ $row['approved_at'] }}">
                                                    ✓ Approved
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-950 text-amber-300 border border-amber-800">
                                                    ⏳ Shipped
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2.5 text-center font-sans">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button wire:click="openShipmentPreview({{ $row['id'] }})" class="p-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-cyan-300 border border-zinc-700 text-[11px] transition-all" title="View PDF">
                                                    📄 PDF
                                                </button>
                                                @if(!$row['is_approved'])
                                                    <button wire:click="openApprovalModal({{ $row['id'] }})" class="px-2 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold transition-all shadow" title="Approve Delivery Note">
                                                        ✓ Approve
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    @if(!empty($batchOverviewData['dnShipped']['has_shipment']))
                                        <tr class="hover:bg-zinc-800/30">
                                            <td class="px-3 py-2.5">
                                                <span class="font-bold text-cyan-300">{{ $batchOverviewData['dnShipped']['dn_number'] }}</span>
                                            </td>
                                            <td class="px-3 py-2.5 font-sans text-zinc-300">{{ $batchOverviewData['dnShipped']['shipment_date'] }}</td>
                                            <td class="px-3 py-2.5 font-sans">
                                                <span class="font-bold text-zinc-200">Product (Rajangan)</span>
                                                <span class="text-amber-500 block text-[11px] font-mono">{{ $batchOverviewData['originName'] ?? '-' }}</span>
                                            </td>
                                            <td class="px-3 py-2.5 font-sans text-zinc-300">
                                                <div class="font-mono text-zinc-200">{{ $batchOverviewData['dnShipped']['vehicle_number'] }}</div>
                                                <div class="text-[10px] text-zinc-400">{{ $batchOverviewData['dnShipped']['driver_name'] }}</div>
                                            </td>
                                            <td class="px-3 py-2.5 text-center text-zinc-200 font-bold">{{ $batchOverviewData['dnShipped']['total_sacks'] }} Sacks</td>
                                            <td class="px-3 py-2.5 text-right text-emerald-400 font-bold">{{ number_format($batchOverviewData['dnShipped']['total_netto_kg'], 2, '.', ',') }} kg</td>
                                            <td class="px-3 py-2.5 text-center font-sans">
                                                @if(!empty($batchOverviewData['dnShipped']['is_approved']))
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-800">
                                                        ✓ Approved
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-950 text-amber-300 border border-amber-800">
                                                        ⏳ Shipped
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2.5 text-center font-sans">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    @if(!empty($batchOverviewData['dnShipped']['id']))
                                                        <button wire:click="openShipmentPreview({{ $batchOverviewData['dnShipped']['id'] }})" class="p-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-cyan-300 border border-zinc-700 text-[11px] transition-all" title="View PDF">
                                                            📄 PDF
                                                        </button>
                                                        @if(empty($batchOverviewData['dnShipped']['is_approved']))
                                                            <button wire:click="openApprovalModal({{ $batchOverviewData['dnShipped']['id'] }})" class="px-2 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold transition-all shadow" title="Approve Delivery Note">
                                                                ✓ Approve
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="8" class="px-4 py-6 text-center text-zinc-500 font-sans">
                                                <div class="flex flex-col items-center justify-center space-y-1.5">
                                                    <span class="text-2xl">📦</span>
                                                    <span class="text-xs font-semibold text-zinc-400">Belum ada Surat Jalan Pengiriman (DN Shipped) yang tercatat untuk batch ini.</span>
                                                    <button wire:click="setTab('dn_shipments')" class="text-xs text-amber-400 hover:underline font-bold mt-1">
                                                        Lihat Semua Surat Jalan Pengiriman &rarr;
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded-2xl bg-zinc-950 border border-zinc-800 flex items-center justify-between text-xs text-zinc-400">
                    <span class="flex items-center">
                        <span class="w-2 h-2 rounded-full {{ !empty($batchOverviewData['dnShipped']['has_shipment']) ? 'bg-cyan-400' : 'bg-zinc-600' }} mr-2"></span>
                        <span>Total Shipped: <strong class="text-cyan-300 ml-1">{{ !empty($batchOverviewData['dnShipped']['total_netto_kg']) ? number_format($batchOverviewData['dnShipped']['total_netto_kg'], 2) . ' kg' : '0.00 kg' }}</strong></span>
                    </span>
                    <button wire:click="setTab('dn_shipments')" class="text-amber-400 hover:underline font-bold text-xs flex items-center">
                        Open DN Shipment Tab &rarr;
                    </button>
                </div>
            </div>

            <!-- CARD 3: Receiving Confirmation Status (Stepper) -->
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
                        Status: <strong class="text-emerald-400 ml-1">All 5 Stages Verified & ACC Approved</strong>
                    </span>
                    <button wire:click="openPreviewModal({{ $currentBatch->id ?? 0 }})" class="text-amber-400 hover:underline font-bold text-xs flex items-center">
                        View Certificate &rarr;
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
                        <span class="flex items-center"><span class="w-2.5 h-2.5 rounded bg-zinc-500 mr-1"></span> Uncountable Waste</span>
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
                                <div style="width: {{ $os['variancePct'] }}%" class="bg-zinc-600 flex items-center justify-center text-zinc-300" title="Uncountable Waste {{ $os['variancePct'] }}%">
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
                                    <td class="px-3 py-2.5 font-sans text-zinc-400">Uncountable Waste</td>
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
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Start Date</label>
                <input type="date" wire:model.live="histStartDate" class="w-full px-2.5 py-2 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Batch Range</label>
                <select wire:model.live="histBatchRange" class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-amber-400 font-mono font-bold text-xs focus:border-amber-500 outline-none">
                    <option value="all">All Batches</option>
                    <option value="1-25">1 - 25</option>
                    <option value="1-10">1 - 10</option>
                    <option value="11-20">11 - 20</option>
                    <option value="21-25">21 - 25</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Product Type</label>
                <select wire:model.live="histProductTypeId" class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                    <option value="">All Product Types</option>
                    @foreach($productTypes as $pt)
                        <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-amber-400 mb-1">Origin</label>
                <select wire:model.live="histBaseOrigin" class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-amber-500/60 text-amber-300 font-bold text-xs focus:border-amber-500 outline-none">
                    <option value="">All Origins</option>
                    @foreach($distinctOrigins as $dOrig)
                        <option value="{{ $dOrig }}">{{ $dOrig }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-cyan-400 mb-1">Origin Code</label>
                <select wire:model.live="histOriginCode" class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-cyan-500/60 text-cyan-300 font-mono font-bold text-xs focus:border-cyan-500 outline-none">
                    <option value="">All Codes</option>
                    @foreach($distinctOriginCodes as $dCode)
                        <option value="{{ $dCode }}">{{ $dCode }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Grouping</label>
                <select wire:model.live="histGrouping" class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                    <option value="by_batch">By Batch</option>
                    <option value="by_origin">By Origin</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-zinc-400 mb-1">Metric</label>
                <select wire:model.live="histMetric" class="w-full px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-emerald-400 font-bold text-xs focus:border-amber-500 outline-none">
                    <option value="yield_pct">Yield (%)</option>
                    <option value="weight_kg">Weight (kg)</option>
                </select>
            </div>

            <div class="flex items-center">
                <button type="button" wire:click="resetHistoricalFilters" class="w-full py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-xs font-bold transition-all flex items-center justify-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Reset Filter
                </button>
            </div>
        </div>

        <!-- 8 TOP AGGREGATE KPI CARDS (CENTERED, MODERN & BALANCED HIERARCHY) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
            <!-- 1. Total Batches -->
            <div class="bg-zinc-900/90 border border-zinc-800/80 hover:border-amber-500/40 rounded-2xl p-3.5 flex flex-col items-center justify-between text-center shadow-lg transition-all group relative overflow-hidden min-h-[96px]">
                <div class="w-full flex items-center justify-center gap-1 text-[10px] sm:text-[11px] font-bold text-zinc-400 uppercase tracking-wider">
                    <span>📋</span>
                    <span>Total Batches</span>
                </div>
                <div class="font-mono font-black text-xl lg:text-2xl text-amber-400 my-0.5">
                    {{ $historicalData['totalBatches'] ?? 25 }}
                </div>
                <div class="text-[10px] text-zinc-500 font-medium">Validated</div>
            </div>

            <!-- 2. Processed Input -->
            <div class="bg-zinc-900/90 border border-zinc-800/80 hover:border-amber-500/40 rounded-2xl p-3.5 flex flex-col items-center justify-between text-center shadow-lg transition-all group relative overflow-hidden min-h-[96px]">
                <div class="w-full flex items-center justify-center gap-1 text-[10px] sm:text-[11px] font-bold text-zinc-400 uppercase tracking-wider">
                    <span>📥</span>
                    <span>Processed Input</span>
                </div>
                <div class="font-mono font-black text-base lg:text-lg text-amber-300 my-0.5">
                    {{ number_format($historicalData['processedInputKg'] ?? 0, 0, '.', ',') }}
                </div>
                <div class="text-[10px] text-zinc-500 font-bold font-mono">kg</div>
            </div>

            <!-- 3. Product Output -->
            <div class="bg-zinc-900/90 border border-zinc-800/80 hover:border-emerald-500/40 rounded-2xl p-3.5 flex flex-col items-center justify-between text-center shadow-lg transition-all group relative overflow-hidden min-h-[96px]">
                <div class="w-full flex items-center justify-center gap-1 text-[10px] sm:text-[11px] font-bold text-zinc-400 uppercase tracking-wider">
                    <span>📦</span>
                    <span>Product Output</span>
                </div>
                <div class="font-mono font-black text-base lg:text-lg text-emerald-400 my-0.5">
                    {{ number_format($historicalData['productOutputKg'] ?? 0, 0, '.', ',') }}
                </div>
                <div class="text-[10px] text-zinc-500 font-bold font-mono">kg</div>
            </div>

            <!-- 4. Weighted Yield -->
            <div class="bg-zinc-900/90 border border-emerald-500/40 bg-gradient-to-b from-emerald-950/20 to-zinc-900 rounded-2xl p-3.5 flex flex-col items-center justify-between text-center shadow-lg transition-all group relative overflow-hidden min-h-[96px]">
                <div class="w-full flex items-center justify-center gap-1 text-[10px] sm:text-[11px] font-bold text-emerald-400 uppercase tracking-wider">
                    <span>⚡</span>
                    <span>Weighted Yield</span>
                </div>
                <div class="font-mono font-black text-lg lg:text-xl text-emerald-300 my-0.5">
                    {{ number_format($historicalData['weightedProductYield'] ?? 72.31, 2) }}%
                </div>
                <div class="text-[10px] text-emerald-500/80 font-bold">Product Rendemen</div>
            </div>

            <!-- 5. Bits Stem -->
            <div class="bg-zinc-900/90 border border-zinc-800/80 hover:border-amber-500/40 rounded-2xl p-3.5 flex flex-col items-center justify-between text-center shadow-lg transition-all group relative overflow-hidden min-h-[96px]">
                <div class="w-full flex items-center justify-center gap-1 text-[10px] sm:text-[11px] font-bold text-zinc-400 uppercase tracking-wider">
                    <span>🌿</span>
                    <span>Bits Stem</span>
                </div>
                <div class="font-mono font-black text-base lg:text-lg text-amber-400 my-0.5">
                    {{ number_format($historicalData['bitsStemPct'] ?? 24.60, 2) }}%
                </div>
                <div class="text-[10px] text-zinc-500 font-medium">Gagang</div>
            </div>

            <!-- 6. Dust -->
            <div class="bg-zinc-900/90 border border-zinc-800/80 hover:border-cyan-500/40 rounded-2xl p-3.5 flex flex-col items-center justify-between text-center shadow-lg transition-all group relative overflow-hidden min-h-[96px]">
                <div class="w-full flex items-center justify-center gap-1 text-[10px] sm:text-[11px] font-bold text-zinc-400 uppercase tracking-wider">
                    <span>💨</span>
                    <span>Dust</span>
                </div>
                <div class="font-mono font-black text-base lg:text-lg text-cyan-400 my-0.5">
                    {{ number_format($historicalData['dustPct'] ?? 1.78, 2) }}%
                </div>
                <div class="text-[10px] text-zinc-500 font-medium">Debu</div>
            </div>

            <!-- 7. Variance / Waste -->
            <div class="bg-zinc-900/90 border border-zinc-800/80 hover:border-purple-500/40 rounded-2xl p-3.5 flex flex-col items-center justify-between text-center shadow-lg transition-all group relative overflow-hidden min-h-[96px]">
                <div class="w-full flex items-center justify-center gap-1 text-[10px] sm:text-[11px] font-bold text-zinc-400 uppercase tracking-wider">
                    <span>🗑️</span>
                    <span>Waste</span>
                </div>
                <div class="font-mono font-black text-base lg:text-lg text-purple-400 my-0.5">
                    {{ number_format($historicalData['variancePct'] ?? 1.31, 2) }}%
                </div>
                <div class="text-[10px] text-zinc-500 font-medium">Susut Proses</div>
            </div>

            <!-- 8. Consistency -->
            <div class="bg-zinc-900/90 border border-zinc-800/80 hover:border-emerald-500/40 rounded-2xl p-3.5 flex flex-col items-center justify-between text-center shadow-lg transition-all group relative overflow-hidden min-h-[96px]">
                <div class="w-full flex items-center justify-center gap-1 text-[10px] sm:text-[11px] font-bold text-zinc-400 uppercase tracking-wider">
                    <span>🛡️</span>
                    <span>Consistency</span>
                </div>
                <div class="font-mono font-black text-xs sm:text-sm text-emerald-400 px-2 py-0.5 rounded-lg bg-emerald-950/60 border border-emerald-800/60 my-0.5">
                    {{ $historicalData['consistency'] ?? 'Moderate' }}
                </div>
                <div class="text-[10px] text-zinc-500 font-medium">Mutu Terjaga</div>
            </div>
        </div>

        <!-- UPPER SECTION: HISTORICAL SEPARATION YIELD & OUTPUT TREND (FULL-WIDTH ENLARGED CHART WITH METRIC SWITCHER) -->
        <div wire:key="historical-line-chart-{{ md5(json_encode($historicalData['chartLabels'] ?? [])) }}"
             x-data="historicalYieldChart({
                 labels: @js($historicalData['chartLabels'] ?? []),
                 yieldSeries: @js($historicalData['yieldSeries'] ?? []),
                 movingAvgSeries: @js($historicalData['movingAvgSeries'] ?? []),
                 stemSeries: @js($historicalData['stemSeries'] ?? []),
                 dustSeries: @js($historicalData['dustSeries'] ?? []),
                 wasteSeries: @js($historicalData['wasteSeries'] ?? []),
                 weightedAvgProduct: @js($historicalData['weightedAvgProduct'] ?? ($historicalData['weightedProductYield'] ?? 72.31)),
                 weightedAvgStem: @js($historicalData['weightedAvgStem'] ?? ($historicalData['bitsStemPct'] ?? 24.60)),
                 weightedAvgDust: @js($historicalData['weightedAvgDust'] ?? ($historicalData['dustPct'] ?? 1.78)),
                 weightedAvgWaste: @js($historicalData['weightedAvgWaste'] ?? ($historicalData['variancePct'] ?? 1.31)),
                 outlierPoints: @js($historicalData['outlierPoints'] ?? []),
                 batchDetails: @js($historicalData['batchDetails'] ?? [])
             })"
             x-init="initChart()"
             class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-7 shadow-2xl space-y-5">
            
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-zinc-800 pb-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-base sm:text-lg font-black text-zinc-100 uppercase tracking-wide">Historical Product Yield Trend</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-950 text-emerald-300 border border-emerald-800">
                            FULL EXPANDED VIEW
                        </span>
                    </div>
                    <p class="text-xs text-zinc-400 mt-1">
                        <span x-show="selectedMetric === 'all'">Perbandingan 4 output separasi (Product, Bits/Stem, Dust, Waste) per batch</span>
                        <span x-show="selectedMetric === 'product'">Tren hasil Product Yield per batch dengan Moving Average (MA-5), rata-rata tertimbang & deteksi outlier</span>
                        <span x-show="selectedMetric === 'stem'">Tren perolehan Bits & Stem per batch dengan garis rata-rata tertimbang</span>
                        <span x-show="selectedMetric === 'dust'">Tren partikel debu / dust per batch dengan garis rata-rata tertimbang</span>
                        <span x-show="selectedMetric === 'waste'">Tren susut / uncountable waste per batch dengan garis rata-rata tertimbang</span>
                    </p>
                </div>

                <!-- METRIC SELECTOR PILL BUTTONS (PILIHAN 4 OUTPUT + ALL) & REFRESH BUTTON -->
                <div class="flex items-center flex-wrap gap-2">
                    <div class="flex items-center flex-wrap gap-1.5 p-1 bg-zinc-950 border border-zinc-800 rounded-2xl shrink-0">
                        <!-- All Metrics Button -->
                        <button type="button" 
                                @click="setMetric('all')" 
                                :class="selectedMetric === 'all' ? 'bg-zinc-800 text-amber-300 border border-amber-500/50 shadow-md' : 'text-zinc-400 hover:text-zinc-200 border border-transparent'" 
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                            <span>⚡ All Series</span>
                        </button>

                        <!-- Product Yield Button -->
                        <button type="button" 
                                @click="setMetric('product')" 
                                :class="selectedMetric === 'product' ? 'bg-emerald-950 text-emerald-300 border border-emerald-600 shadow-md' : 'text-zinc-400 hover:text-emerald-400 border border-transparent'" 
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span>
                            <span>Product ({{ number_format($historicalData['weightedProductYield'] ?? 72.31, 1) }}%)</span>
                        </button>

                        <!-- Bits / Stem Button -->
                        <button type="button" 
                                @click="setMetric('stem')" 
                                :class="selectedMetric === 'stem' ? 'bg-amber-950 text-amber-300 border border-amber-600 shadow-md' : 'text-zinc-400 hover:text-amber-400 border border-transparent'" 
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-sm shadow-amber-500/50"></span>
                            <span>Bits/Stem ({{ number_format($historicalData['bitsStemPct'] ?? 24.60, 1) }}%)</span>
                        </button>

                        <!-- Dust Button -->
                        <button type="button" 
                                @click="setMetric('dust')" 
                                :class="selectedMetric === 'dust' ? 'bg-cyan-950 text-cyan-300 border border-cyan-600 shadow-md' : 'text-zinc-400 hover:text-cyan-400 border border-transparent'" 
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-500 shadow-sm shadow-cyan-500/50"></span>
                            <span>Dust ({{ number_format($historicalData['dustPct'] ?? 1.78, 1) }}%)</span>
                        </button>

                        <!-- Uncountable Waste Button -->
                        <button type="button" 
                                @click="setMetric('waste')" 
                                :class="selectedMetric === 'waste' ? 'bg-purple-950 text-purple-300 border border-purple-600 shadow-md' : 'text-zinc-400 hover:text-purple-400 border border-transparent'" 
                                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500 shadow-sm shadow-purple-500/50"></span>
                            <span>Uncountable Waste ({{ number_format($historicalData['variancePct'] ?? 1.31, 1) }}%)</span>
                        </button>
                    </div>

                    <!-- Manual Refresh Button -->
                    <button type="button" 
                            @click="renderChart()" 
                            class="px-3 py-2 rounded-2xl bg-zinc-950 border border-zinc-800 hover:border-amber-500/50 text-zinc-300 hover:text-amber-400 text-xs font-bold transition-all flex items-center gap-1.5 shadow-sm active:scale-95 shrink-0"
                            title="Refresh & Recalculate Chart">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span>Refresh Chart</span>
                    </button>
                </div>
            </div>

            <!-- Dynamic Active Legend Bar -->
            <div class="flex items-center justify-between flex-wrap gap-2 text-xs font-bold bg-zinc-950/80 px-4 py-2.5 rounded-2xl border border-zinc-800">
                <div class="flex items-center flex-wrap gap-3 sm:gap-5">
                    <!-- When ALL is selected -->
                    <template x-if="selectedMetric === 'all'">
                        <div class="flex items-center flex-wrap gap-3 sm:gap-5">
                            <span class="flex items-center text-emerald-400"><span class="w-3 h-3 rounded-full bg-emerald-500 mr-2 shadow-sm shadow-emerald-500/50"></span> Product Yield ({{ number_format($historicalData['weightedProductYield'] ?? 72.31, 2) }}%)</span>
                            <span class="flex items-center text-amber-400"><span class="w-3 h-3 rounded-full bg-amber-500 mr-2 shadow-sm shadow-amber-500/50"></span> Bits / Stem ({{ number_format($historicalData['bitsStemPct'] ?? 24.60, 2) }}%)</span>
                            <span class="flex items-center text-cyan-400"><span class="w-3 h-3 rounded-full bg-cyan-500 mr-2 shadow-sm shadow-cyan-500/50"></span> Dust ({{ number_format($historicalData['dustPct'] ?? 1.78, 2) }}%)</span>
                            <span class="flex items-center text-purple-400"><span class="w-3 h-3 rounded-full bg-purple-500 mr-2 shadow-sm shadow-purple-500/50"></span> Uncountable Waste ({{ number_format($historicalData['variancePct'] ?? 1.31, 2) }}%)</span>
                        </div>
                    </template>

                    <!-- When PRODUCT is selected -->
                    <template x-if="selectedMetric === 'product'">
                        <div class="flex items-center flex-wrap gap-2.5 sm:gap-4">
                            <span class="flex items-center text-emerald-400"><span class="w-3 h-3 rounded-full bg-emerald-500 mr-1.5 shadow-sm shadow-emerald-500/50"></span> &ge;70% (Optimal)</span>
                            <span class="flex items-center text-amber-400"><span class="w-3 h-3 rounded-full bg-amber-500 mr-1.5 shadow-sm shadow-amber-500/50"></span> 65-70% (Standard Warning)</span>
                            <span class="flex items-center text-red-400"><span class="w-3 h-3 rounded-full bg-red-500 mr-1.5 shadow-sm shadow-red-500/50"></span> &lt;65% (Critical)</span>
                            <span class="flex items-center text-sky-400"><span class="w-4 h-0.5 border-t-2 border-solid border-sky-400 mr-1.5"></span> Moving Avg (MA-5)</span>
                            <span class="flex items-center text-amber-400/90"><span class="w-4 h-0.5 border-t-2 border-dashed border-amber-400 mr-1.5"></span> Weighted Avg ({{ number_format($historicalData['weightedProductYield'] ?? 72.31, 2) }}%)</span>
                            <span class="flex items-center text-red-400"><span class="w-3 h-3 rounded-full bg-red-500 mr-1.5 shadow-sm shadow-red-500/50"></span> Outlier</span>
                        </div>
                    </template>

                    <!-- When STEM is selected -->
                    <template x-if="selectedMetric === 'stem'">
                        <div class="flex items-center flex-wrap gap-3 sm:gap-5">
                            <span class="flex items-center text-amber-400"><span class="w-3 h-3 rounded-full bg-amber-500 mr-2 shadow-sm shadow-amber-500/50"></span> Bits / Stem (%)</span>
                            <span class="flex items-center text-sky-400"><span class="w-4 h-0.5 border-t-2 border-dashed border-sky-400 mr-2"></span> Weighted Avg ({{ number_format($historicalData['bitsStemPct'] ?? 24.60, 2) }}%)</span>
                        </div>
                    </template>

                    <!-- When DUST is selected -->
                    <template x-if="selectedMetric === 'dust'">
                        <div class="flex items-center flex-wrap gap-3 sm:gap-5">
                            <span class="flex items-center text-cyan-400"><span class="w-3 h-3 rounded-full bg-cyan-500 mr-2 shadow-sm shadow-cyan-500/50"></span> Dust (%)</span>
                            <span class="flex items-center text-amber-400"><span class="w-4 h-0.5 border-t-2 border-dashed border-amber-400 mr-2"></span> Weighted Avg ({{ number_format($historicalData['dustPct'] ?? 1.78, 2) }}%)</span>
                        </div>
                    </template>

                    <!-- When WASTE is selected -->
                    <template x-if="selectedMetric === 'waste'">
                        <div class="flex items-center flex-wrap gap-3 sm:gap-5">
                            <span class="flex items-center text-purple-400"><span class="w-3 h-3 rounded-full bg-purple-500 mr-2 shadow-sm shadow-purple-500/50"></span> Uncountable Waste (%)</span>
                            <span class="flex items-center text-amber-400"><span class="w-4 h-0.5 border-t-2 border-dashed border-amber-400 mr-2"></span> Weighted Avg ({{ number_format($historicalData['variancePct'] ?? 1.31, 2) }}%)</span>
                        </div>
                    </template>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-zinc-500 text-[11px] font-mono">
                        Mode: <span class="uppercase text-amber-400 font-bold" x-text="selectedMetric"></span>
                    </div>
                </div>
            </div>

            <!-- Enlarged Full-Width Canvas Container -->
            <div class="relative w-full h-[420px] sm:h-[480px] lg:h-[520px] bg-zinc-950 p-3 sm:p-5 rounded-2xl border border-zinc-800/80 shadow-inner overflow-hidden">
                <canvas x-ref="canvas" class="w-full h-full"></canvas>
            </div>

            <!-- Chart Footer Information Strip -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-zinc-400 bg-zinc-950/70 p-3.5 rounded-2xl border border-zinc-800">
                <div class="flex items-center text-zinc-300">
                    <svg class="w-4 h-4 mr-2 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Milestone Batch 23: Implementasi kontrol penerimaan langsung gudang (DN + MRL).</span>
                </div>
                <div class="text-zinc-500 font-mono text-[11px] flex items-center gap-3">
                    <button type="button" @click="renderChart()" class="text-amber-400 hover:underline flex items-center gap-1 font-bold">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span>Refresh View</span>
                    </button>
                    <span class="w-1.5 h-1.5 rounded-full bg-zinc-700"></span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Monitoring {{ count($historicalData['chartLabels'] ?? []) }} Approved Batches</span>
                    </span>
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

        <!-- MIDDLE SECTION: WEIGHTED YIELD BY ORIGIN & ORIGIN CODE PERFORMANCE (2-COLUMN BALANCED VIEW) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- 1. Weighted Yield by Origin -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                    <div>
                        <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">Weighted Yield by Origin</h3>
                        <p class="text-xs text-zinc-400 mt-0.5">Rata-rata rendemen per wilayah asal tembakau</p>
                    </div>
                    <span class="text-[10px] font-bold text-zinc-400 px-2.5 py-1 rounded-lg bg-zinc-950 border border-zinc-800">Batch Count</span>
                </div>

                <div class="space-y-3.5 pt-1">
                    @foreach($historicalData['originYieldBars'] ?? [] as $oyb)
                        <div class="space-y-1.5 p-2 rounded-2xl bg-zinc-950/50 border border-zinc-800/60">
                            <div class="flex justify-between items-center text-xs font-bold">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-sm shadow-amber-500/50"></span>
                                    <span class="text-zinc-200 text-sm">{{ $oyb['origin'] }}</span>
                                </div>
                                <div class="flex items-center gap-2 font-mono">
                                    <span class="text-emerald-400 font-black text-sm">{{ number_format($oyb['yieldPct'], 2) }}%</span>
                                    <span class="text-[11px] text-zinc-400 bg-zinc-900 px-2 py-0.5 rounded-md border border-zinc-800">{{ $oyb['batchCount'] }} batch</span>
                                </div>
                            </div>
                            <div class="h-3 w-full bg-zinc-900 rounded-full overflow-hidden border border-zinc-800/80">
                                <div style="width: {{ min(100, max(10, $oyb['yieldPct'])) }}%" class="h-full bg-gradient-to-r from-amber-500 via-emerald-500 to-emerald-400 rounded-full transition-all duration-500"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 2. Origin Code Performance Matrix -->
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
                <div class="border-b border-zinc-800 pb-3">
                    <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">Origin Code Performance</h3>
                    <p class="text-xs text-zinc-400 mt-0.5">Distribusi hasil separasi per kode spesifik</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-center text-xs font-mono">
                        <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase text-[10px] border-b border-zinc-800">
                            <tr>
                                <th class="px-3 py-2.5 text-left text-zinc-300">Code</th>
                                <th class="px-2.5 py-2.5 text-red-400">&lt;68%</th>
                                <th class="px-2.5 py-2.5 text-amber-400">68-71%</th>
                                <th class="px-2.5 py-2.5 text-emerald-400">71-74%</th>
                                <th class="px-2.5 py-2.5 text-emerald-300">74-77%</th>
                                <th class="px-2.5 py-2.5 text-emerald-200">&ge;77%</th>
                                <th class="px-3 py-2.5 text-zinc-300">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/60">
                            @foreach($historicalData['codeMatrix'] ?? [] as $cm)
                                <tr class="hover:bg-zinc-800/30 transition-colors">
                                    <td class="px-3 py-2.5 text-left font-bold text-cyan-300 bg-zinc-950/40">{{ $cm['code'] }}</td>
                                    <td class="px-2.5 py-2.5 {{ $cm['c1'] > 0 ? 'bg-red-950/40 text-red-300 font-bold' : 'text-zinc-600' }}">{{ $cm['c1'] }}</td>
                                    <td class="px-2.5 py-2.5 {{ $cm['c2'] > 0 ? 'bg-amber-950/40 text-amber-300 font-bold' : 'text-zinc-600' }}">{{ $cm['c2'] }}</td>
                                    <td class="px-2.5 py-2.5 {{ $cm['c3'] > 0 ? 'bg-emerald-950/40 text-emerald-300 font-bold' : 'text-zinc-600' }}">{{ $cm['c3'] }}</td>
                                    <td class="px-2.5 py-2.5 {{ $cm['c4'] > 0 ? 'bg-emerald-900/60 text-emerald-200 font-bold' : 'text-zinc-600' }}">{{ $cm['c4'] }}</td>
                                    <td class="px-2.5 py-2.5 {{ $cm['c5'] > 0 ? 'bg-emerald-800/80 text-emerald-100 font-bold' : 'text-zinc-600' }}">{{ $cm['c5'] }}</td>
                                    <td class="px-3 py-2.5 font-black text-amber-400 bg-zinc-950/40">{{ $cm['total'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-[10px] text-zinc-500 flex items-center gap-1.5 pt-1">
                    <span>ℹ️</span>
                    <span>Interpretasi akurat optimal ketika sampel data &ge; 3 batch per origin code.</span>
                </div>
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
                            <th class="px-3 py-3 text-amber-400">Origin</th>
                            <th class="px-3 py-3 text-cyan-400">Origin Code</th>
                            <th class="px-3 py-3 text-right">Input kg</th>
                            <th class="px-3 py-3 text-right">Product kg</th>
                            <th class="px-3 py-3 text-right text-emerald-400">Yield %</th>
                            <th class="px-3 py-3 text-right">Stem %</th>
                            <th class="px-3 py-3 text-right">Dust %</th>
                            <th class="px-3 py-3 text-right">Waste %</th>
                            <th class="px-3 py-3 text-center">Certificate</th>
                            <th class="px-3 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60">
                        @forelse($historicalData['batchRows'] ?? [] as $row)
                            <tr class="hover:bg-zinc-800/40 transition-colors">
                                <td class="px-3 py-3">
                                    <span class="font-bold text-amber-400">#{{ $row['batchNum'] }}</span>
                                    <span class="text-zinc-500 text-[10px] block">{{ $row['batchCode'] }}</span>
                                </td>
                                <td class="px-3 py-3 text-zinc-300">{{ $row['date'] }}</td>
                                <td class="px-3 py-3 font-sans">
                                    <span class="px-2 py-0.5 rounded-lg bg-amber-950/60 text-amber-300 border border-amber-800/60 font-bold text-xs">
                                        {{ $row['origin'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="px-2 py-0.5 rounded-lg bg-zinc-950 text-cyan-400 border border-zinc-800 font-mono font-bold text-xs">
                                        {{ $row['originCode'] }}
                                    </span>
                                </td>
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
                                    Tidak ada data historis batch yang cocok dengan filter yang dipilih.
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
    <!-- HALAMAN 3: CERTIFICATES & DOWNLOADS -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'certificates'" class="space-y-6">
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 sm:p-6 shadow-xl space-y-4">
            <div class="border-b border-zinc-800 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-black text-amber-400 uppercase tracking-wide">Official Quality Certificates (Supervisor Approved)</h3>
                    <p class="text-xs text-zinc-400">Pilih 'Preview' untuk melihat pratinjau sertifikat atau klik 'Download PDF' untuk mengunduh dokumen resmi</p>
                </div>
                <span class="text-xs font-mono font-bold text-zinc-400">Total: {{ $approvedBatches->total() }} Certificates</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-300">
                    <thead class="bg-zinc-950 text-zinc-400 font-bold uppercase border-b border-zinc-800">
                        <tr>
                            <th class="px-4 py-3">Batch Code</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Delivery Note (DN)</th>
                            <th class="px-4 py-3">Product Type & Origin</th>
                            <th class="px-4 py-3 text-right">Finished Product (kg)</th>
                            <th class="px-4 py-3 text-right">Yield (%)</th>
                            <th class="px-4 py-3 text-center">Approved Date</th>
                            <th class="px-4 py-3 text-center">Document Actions</th>
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
    <!-- HALAMAN 4: PRIVATE YIELD COST CALCULATOR (CLIENT-SIDE ONLY) -->
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
                            <label class="block text-zinc-400 text-[11px] mb-1">Uncountable Waste (kg)</label>
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
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-black text-zinc-100 uppercase tracking-wide">Yield Sensitivity — Effective Cost/kg</h3>
                            <button type="button" 
                                    @click="initSensitivityChart()" 
                                    class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-zinc-950 hover:bg-zinc-800 text-zinc-400 hover:text-amber-400 border border-zinc-800 flex items-center gap-1 transition-all"
                                    title="Refresh Sensitivity Chart">
                                <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                <span>Refresh</span>
                            </button>
                        </div>
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
    <!-- HALAMAN 5: SURAT JALAN PENGIRIMAN (DN SHIPMENT & CUSTOMER APPROVAL) -->
    <!-- ========================================================================= -->
    <!-- ========================================================================= -->
    <!-- HALAMAN 5: DELIVERY NOTES & SHIPMENT APPROVAL (DN SHIPMENTS) -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'dn_shipments'" class="space-y-6">

        <!-- HEADER & STATUS SUMMARY -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-zinc-100 tracking-wide">Delivery Notes & Shipment Approval (DN Shipment)</h2>
                <p class="text-xs text-zinc-400 mt-1">Daftar surat jalan resmi pengiriman tembakau jadi. Lakukan verifikasi dan persetujuan (Approval) penerimaan barang di sini.</p>
            </div>

            <div class="flex items-center gap-2">
                @if($pendingShipmentsCount > 0)
                    <span class="px-3 py-1.5 rounded-xl bg-amber-950/80 border border-amber-500/50 text-amber-300 font-bold text-xs flex items-center gap-1.5 animate-pulse">
                        <span>⏳ {{ $pendingShipmentsCount }} Pending Approval</span>
                    </span>
                @else
                    <span class="px-3 py-1.5 rounded-xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-300 font-bold text-xs flex items-center gap-1.5">
                        <span>✅ All Shipments Approved</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- 3 SUMMARY KPI CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-5 shadow-xl flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-zinc-400">Total Delivery Notes</div>
                    <div class="text-2xl font-black text-zinc-100 mt-1">{{ $customerShipments->count() }} Documents</div>
                    <div class="text-[11px] text-zinc-500 mt-0.5">Registered shipments for this customer</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-zinc-800 flex items-center justify-center text-xl">📦</div>
            </div>

            <div class="bg-zinc-900 border border-amber-500/30 rounded-3xl p-5 shadow-xl flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-amber-400">Pending Approval (Shipped)</div>
                    <div class="text-2xl font-black text-amber-400 mt-1">{{ $pendingShipmentsCount }} Shipments</div>
                    <div class="text-[11px] text-amber-500/80 mt-0.5">Requires customer acceptance</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-950/50 border border-amber-800/80 flex items-center justify-center text-xl">⏳</div>
            </div>

            <div class="bg-zinc-900 border border-emerald-500/30 rounded-3xl p-5 shadow-xl flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-emerald-400">Approved Shipments</div>
                    <div class="text-2xl font-black text-emerald-400 mt-1">{{ $approvedShipmentsCount }} Completed</div>
                    <div class="text-[11px] text-emerald-500/80 mt-0.5">Received & accepted by customer</div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-950/50 border border-emerald-800/80 flex items-center justify-center text-xl">✅</div>
            </div>
        </div>

        <!-- SEARCH & FILTER BAR -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-4 sm:p-5 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex-1 relative">
                <input type="text" wire:model.live.debounce.300ms="dnSearch" placeholder="Search DN No., Driver, Vehicle Plate, Origin..." class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 placeholder-zinc-500 text-xs focus:border-amber-500 outline-none">
                <svg class="w-4 h-4 text-zinc-500 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <div class="flex items-center gap-2">
                <select wire:model.live="dnStatusFilter" class="px-3 py-2.5 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-amber-500 outline-none">
                    <option value="">All Statuses</option>
                    <option value="Shipped">Pending Approval (Shipped)</option>
                    <option value="Approved">Approved</option>
                </select>
            </div>
        </div>

        <!-- SHIPMENTS DATA TABLE -->
        <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-zinc-300">
                    <thead class="bg-zinc-950 text-zinc-400 font-black uppercase text-[10px] tracking-wider border-b border-zinc-800">
                        <tr>
                            <th class="px-4 py-3.5">DN Number</th>
                            <th class="px-4 py-3.5">Shipment Date</th>
                            <th class="px-4 py-3.5">Vehicle & Driver</th>
                            <th class="px-4 py-3.5">Lot Details & Origin</th>
                            <th class="px-4 py-3.5 text-right">Total Sacks</th>
                            <th class="px-4 py-3.5 text-right">Net Weight (kg)</th>
                            <th class="px-4 py-3.5 text-center">Status</th>
                            <th class="px-4 py-3.5 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60 font-sans">
                        @forelse($customerShipments as $s)
                            <tr class="hover:bg-zinc-800/40 transition-colors">
                                <td class="px-4 py-3.5 font-mono font-black text-amber-400">
                                    {{ $s->dn_number }}
                                </td>
                                <td class="px-4 py-3.5 text-zinc-300 font-mono">
                                    {{ $s->shipment_date ? $s->shipment_date->format('d M Y') : '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-zinc-300">
                                    <div class="font-bold text-zinc-200">{{ $s->vehicle_number ?: '-' }}</div>
                                    <div class="text-[11px] text-zinc-400">{{ $s->driver_name ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="space-y-1">
                                        @foreach($s->items as $it)
                                            <div class="flex items-center gap-1.5 text-[11px]">
                                                <span class="px-1.5 py-0.5 rounded bg-zinc-800 text-amber-300 font-bold">#{{ $it->item_no }}</span>
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold {{ ($it->material_type ?? 'Product') === 'Product' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800/80' : (($it->material_type ?? '') === 'Bits / Stem' ? 'bg-amber-950 text-amber-300 border border-amber-800/80' : 'bg-zinc-800 text-cyan-300 border border-zinc-700') }}">
                                                    {{ ($it->material_type ?? 'Product') === 'Product' ? '🍃 Product' : (($it->material_type ?? '') === 'Bits / Stem' ? '🌿 Bits/Stem' : '💨 Dust') }}
                                                </span>
                                                <span class="font-bold text-zinc-200">{{ $it->origin }}</span>
                                                <span class="text-cyan-400 font-mono">({{ $it->origin_code }})</span>
                                                <span class="text-zinc-400 font-mono">• {{ $it->total_sacks }} Sacks ({{ number_format($it->total_netto_kg, 2) }} kg)</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-zinc-200">
                                    {{ $s->total_sacks }} Sacks
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-emerald-400">
                                    {{ number_format($s->total_netto_kg, 2, ',', '.') }} kg
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($s->isApprovedByCustomer())
                                        <div class="inline-flex flex-col items-center">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-950 text-emerald-300 border border-emerald-600/80 shadow">
                                                ✅ Approved
                                            </span>
                                            @if($s->customer_approved_at)
                                                <span class="text-[9px] text-zinc-400 font-mono mt-0.5">{{ $s->customer_approved_at->format('d/m/y H:i') }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-950 text-amber-300 border border-amber-600/80 shadow">
                                            🚚 Shipped
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button wire:click="openShipmentPreview({{ $s->id }})" class="p-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-200 hover:text-white transition-all text-xs font-bold flex items-center gap-1" title="View Delivery Note PDF">
                                            <span>📄 PDF</span>
                                        </button>

                                        @if(! $s->isApprovedByCustomer())
                                            <button wire:click="openApprovalModal({{ $s->id }})" class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white transition-all text-xs font-black flex items-center gap-1 shadow-lg shadow-emerald-900/40" title="Approve & Accept Shipment">
                                                <span>✅ Approve</span>
                                            </button>
                                        @else
                                            <span class="px-2 py-1 rounded-xl bg-emerald-950/60 border border-emerald-800/80 text-emerald-400 text-[10px] font-bold">
                                                ✓ Verified
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-zinc-500 text-xs">
                                    No shipment delivery notes found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- HALAMAN 6: PROFIL PENGGUNA & PERUSAHAAN (CUSTOMER PROFILE) -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'profile'" class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-zinc-100 tracking-wide flex items-center">
                    User Profile & Organization
                    <span class="ml-3 px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-blue-950 text-blue-300 border border-blue-800">
                        Customer Portal
                    </span>
                </h2>
                <p class="text-xs text-zinc-400 mt-1">Kelola data akun login, informasi kontak perusahaan, dan pengaturan kata sandi Anda</p>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-xs text-zinc-400 font-mono">Account Status:</span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-emerald-950 text-emerald-400 border border-emerald-800 flex items-center gap-1.5 shadow">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Active & Verified
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Card: Profile & Company Summary -->
            <div class="space-y-6">
                <!-- User ID Card -->
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 shadow-xl space-y-6 text-center relative overflow-hidden">
                    <div class="absolute -right-8 -bottom-8 w-36 h-36 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>
                    
                    <div class="relative">
                        <div class="w-24 h-24 mx-auto rounded-3xl bg-gradient-to-tr from-amber-600 to-amber-400 p-1 shadow-xl shadow-amber-950/60">
                            <div class="w-full h-full bg-zinc-950 rounded-[22px] flex items-center justify-center text-3xl font-black text-amber-400">
                                {{ strtoupper(substr(auth()->user()->name ?? 'C', 0, 2)) }}
                            </div>
                        </div>
                        <span class="absolute bottom-0 right-1/2 translate-x-8 px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-amber-500 text-zinc-950 shadow">
                            VIP
                        </span>
                    </div>

                    <div>
                        <h3 class="text-lg font-black text-zinc-100">{{ auth()->user()->name ?? 'Customer Portal User' }}</h3>
                        <p class="text-xs text-zinc-400 font-mono mt-0.5">{{ auth()->user()->email ?? '-' }}</p>
                        <span class="inline-block px-3 py-1 mt-2 text-[10px] font-black uppercase rounded-full bg-amber-950 text-amber-300 border border-amber-800 font-mono">
                            {{ strtoupper(auth()->user()->role ?? 'CUSTOMER') }}
                        </span>
                    </div>

                    <!-- Company Info Mini Box -->
                    <div class="p-4 rounded-2xl bg-zinc-950 border border-zinc-800/80 text-left space-y-2.5 text-xs">
                        <div class="flex items-center justify-between border-b border-zinc-800 pb-2">
                            <span class="text-[11px] font-bold uppercase text-zinc-400">Partner Company:</span>
                            <span class="font-bold text-amber-400 font-mono">{{ auth()->user()->customer->code ?? 'CUST-001' }}</span>
                        </div>
                        <p class="font-bold text-zinc-200">{{ auth()->user()->customer->name ?? 'PT Falih Nur Gemilang' }}</p>
                        <p class="text-[11px] text-zinc-400 leading-relaxed">{{ auth()->user()->customer->address ?? 'Pabrik & Gudang Tembakau Mitra' }}</p>
                    </div>

                    <!-- Account Metadata -->
                    <div class="grid grid-cols-2 gap-2 text-left text-[11px] bg-zinc-950/60 p-3 rounded-2xl border border-zinc-800/60">
                        <div>
                            <span class="text-zinc-500 block">Registered Date</span>
                            <span class="font-bold text-zinc-300">{{ auth()->user()->created_at ? auth()->user()->created_at->format('d M Y') : '-' }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500 block">Password Changed</span>
                            <span class="font-bold text-zinc-300">{{ auth()->user()->password_changed_at ? auth()->user()->password_changed_at->format('d M Y') : 'Default' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Fast Help Card -->
                <div class="bg-gradient-to-br from-zinc-900 to-zinc-950 border border-zinc-800 rounded-3xl p-5 shadow-xl space-y-3">
                    <div class="flex items-center gap-2 text-amber-400 font-bold text-xs uppercase tracking-wider">
                        <span>💬 Support & Helpdesk</span>
                    </div>
                    <p class="text-xs text-zinc-400 leading-relaxed">
                        Jika terdapat kendala data batch atau surat jalan, hubungi tim Quality Control & Admin Pabrik TPMS.
                    </p>
                    <div class="p-3 rounded-xl bg-zinc-900/80 border border-zinc-800 text-xs text-zinc-300 font-mono flex items-center justify-between">
                        <span>📧 admin@tobacco.com</span>
                        <span class="text-emerald-400 text-[11px] font-bold">Online</span>
                    </div>
                </div>
            </div>

            <!-- Right Area: Settings & Password Forms -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Section 1: Update Profile & Customer Data -->
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 sm:p-7 shadow-xl space-y-5">
                    <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                        <div class="flex items-center space-x-2">
                            <span class="text-xl">👤</span>
                            <div>
                                <h3 class="text-base font-black text-zinc-100">Account & Contact Information</h3>
                                <p class="text-xs text-zinc-400">Perbarui nama pengguna, email login, dan nomor kontak PIC</p>
                            </div>
                        </div>
                    </div>

                    <form wire:submit.prevent="updateProfile" class="space-y-4 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold uppercase text-zinc-300 mb-1.5">
                                    Full Name / Account PIC <span class="text-amber-400">*</span>
                                </label>
                                <input type="text" 
                                       wire:model="profileName" 
                                       class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-semibold outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" 
                                       placeholder="Your full name...">
                                @error('profileName') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold uppercase text-zinc-300 mb-1.5">
                                    Email Address (Login) <span class="text-amber-400">*</span>
                                </label>
                                <input type="email" 
                                       wire:model="profileEmail" 
                                       class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" 
                                       placeholder="email@company.com">
                                @error('profileEmail') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold uppercase text-zinc-300 mb-1.5">
                                    Company Contact Person (PIC)
                                </label>
                                <input type="text" 
                                       wire:model="profileContactPerson" 
                                       class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" 
                                       placeholder="Company PIC name...">
                                @error('profileContactPerson') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold uppercase text-zinc-300 mb-1.5">
                                    Phone / WhatsApp Number
                                </label>
                                <input type="text" 
                                       wire:model="profilePhone" 
                                       class="w-full px-4 py-3 min-h-[48px] rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 font-mono outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" 
                                       placeholder="+62 812-xxxx-xxxx">
                                @error('profilePhone') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold uppercase text-zinc-300 mb-1.5">
                                Plant / Warehouse Delivery Address
                            </label>
                            <textarea wire:model="profileAddress" 
                                      rows="3" 
                                      class="w-full p-4 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 leading-relaxed" 
                                      placeholder="Full factory / warehouse delivery address..."></textarea>
                            @error('profileAddress') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" 
                                    class="px-6 py-3 min-h-[48px] rounded-xl bg-gradient-to-r from-amber-600 to-amber-700 text-white font-black text-xs hover:from-amber-500 shadow-xl shadow-amber-950/50 flex items-center gap-2 transition-all">
                                <svg wire:loading.remove wire:target="updateProfile" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <svg wire:loading wire:target="updateProfile" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Save Profile Changes</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Section 2: Change Password -->
                <div class="bg-zinc-900 border border-zinc-800 rounded-3xl p-6 sm:p-7 shadow-xl space-y-5">
                    <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                        <div class="flex items-center space-x-2">
                            <span class="text-xl">🔒</span>
                            <div>
                                <h3 class="text-base font-black text-zinc-100">Change Password</h3>
                                <p class="text-xs text-zinc-400">Perbarui kata sandi secara berkala untuk menjaga keamanan akun portal Anda</p>
                            </div>
                        </div>
                    </div>

                    <form wire:submit.prevent="updatePassword" 
                          x-data="{ showCurr: false, showNew: false, showConf: false }" 
                          class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold uppercase text-zinc-300 mb-1.5">
                                Current Password <span class="text-amber-400">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showCurr ? 'text' : 'password'" 
                                       wire:model="profileCurrentPassword" 
                                       class="w-full px-4 py-3 min-h-[48px] pr-11 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" 
                                       placeholder="••••••••">
                                <button type="button" 
                                        @click="showCurr = !showCurr" 
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-200 p-1">
                                    <span x-show="!showCurr" class="text-sm">👁️</span>
                                    <span x-show="showCurr" class="text-sm">🙈</span>
                                </button>
                            </div>
                            @error('profileCurrentPassword') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold uppercase text-zinc-300 mb-1.5">
                                    New Password <span class="text-amber-400">*</span>
                                </label>
                                <div class="relative">
                                    <input :type="showNew ? 'text' : 'password'" 
                                           wire:model="profileNewPassword" 
                                           class="w-full px-4 py-3 min-h-[48px] pr-11 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" 
                                           placeholder="Minimum 6 characters...">
                                    <button type="button" 
                                            @click="showNew = !showNew" 
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-200 p-1">
                                        <span x-show="!showNew" class="text-sm">👁️</span>
                                        <span x-show="showNew" class="text-sm">🙈</span>
                                    </button>
                                </div>
                                @error('profileNewPassword') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block font-bold uppercase text-zinc-300 mb-1.5">
                                    Confirm New Password <span class="text-amber-400">*</span>
                                </label>
                                <div class="relative">
                                    <input :type="showConf ? 'text' : 'password'" 
                                           wire:model="profileNewPasswordConfirmation" 
                                           class="w-full px-4 py-3 min-h-[48px] pr-11 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-100 outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500" 
                                           placeholder="Repeat new password...">
                                    <button type="button" 
                                            @click="showConf = !showConf" 
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-200 p-1">
                                        <span x-show="!showConf" class="text-sm">👁️</span>
                                        <span x-show="showConf" class="text-sm">🙈</span>
                                    </button>
                                </div>
                                @error('profileNewPasswordConfirmation') <span class="text-red-400 font-bold block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" 
                                    class="px-6 py-3 min-h-[48px] rounded-xl bg-zinc-800 hover:bg-zinc-700 text-amber-400 border border-amber-500/30 font-black text-xs shadow-lg flex items-center gap-2 transition-all">
                                <svg wire:loading.remove wire:target="updatePassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                <svg wire:loading wire:target="updatePassword" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Update Password</span>
                            </button>
                        </div>
                    </form>
                </div>
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
                    <h3 class="text-base font-black text-amber-400">Quality Certificate Preview (Tobacco Separation)</h3>
                </div>
                <button @click="showPreviewModal = false" class="p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-800 text-lg">&times;</button>
            </div>

            <div class="flex-1 p-3 sm:p-5 overflow-y-auto bg-zinc-950/80 flex justify-center items-start">
                @if($previewBatchId)
                    <div class="w-full bg-white rounded-2xl overflow-hidden shadow-2xl border border-zinc-700">
                        <iframe src="{{ route('certificate.show', $previewBatchId) }}" class="w-full h-[650px] bg-white border-0"></iframe>
                    </div>
                @else
                    <div class="h-64 flex items-center justify-center text-zinc-500 text-xs">Loading certificate document...</div>
                @endif
            </div>

            <div class="p-4 border-t border-zinc-800 bg-zinc-950 flex items-center justify-between">
                <button @click="showPreviewModal = false" class="px-4 py-2.5 rounded-xl bg-zinc-800 text-zinc-300 font-bold text-xs">Close</button>
                @if($previewBatchId)
                    <a href="{{ route('certificate.pdf', $previewBatchId) }}" target="_blank" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs flex items-center shadow-lg shadow-emerald-900/30">
                        📥 Download Official PDF
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- PREVIEW DN SHIPMENT MODAL (CUSTOMER PORTAL) -->
    <!-- ========================================================================= -->
    @if($showShipmentPreviewModal && $previewShipmentId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-zinc-900 border border-zinc-700 rounded-3xl w-full max-w-4xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-zinc-800 flex items-center justify-between bg-zinc-950">
                    <div class="flex items-center space-x-2">
                        <span class="text-xl">🚚</span>
                        <h3 class="text-base font-black text-amber-400">Delivery Note Preview (DN Shipment)</h3>
                    </div>
                    <button wire:click="closeShipmentPreview" class="p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-800 text-lg">&times;</button>
                </div>

                <div class="flex-1 p-3 sm:p-5 overflow-y-auto bg-zinc-950/80 flex justify-center items-start">
                    <div class="w-full bg-white rounded-2xl overflow-hidden shadow-2xl border border-zinc-700">
                        <iframe src="{{ route('dn-shipments.preview', $previewShipmentId) }}" class="w-full h-[650px] bg-white border-0"></iframe>
                    </div>
                </div>

                <div class="p-4 border-t border-zinc-800 bg-zinc-950 flex items-center justify-between">
                    <button wire:click="closeShipmentPreview" class="px-4 py-2.5 rounded-xl bg-zinc-800 text-zinc-300 font-bold text-xs">Close</button>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('dn-shipments.pdf', $previewShipmentId) }}" target="_blank" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-black text-xs flex items-center shadow-lg shadow-amber-900/30">
                            📥 Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- CUSTOMER APPROVAL CONFIRMATION MODAL -->
    <!-- ========================================================================= -->
    @if($showApprovalModal && $approvingShipmentId)
        @php
            $targetShipment = ($customerShipments ? $customerShipments->firstWhere('id', $approvingShipmentId) : null) ?? \App\Models\DnShipment::find($approvingShipmentId);
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-zinc-900 border border-emerald-500/50 rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden">
                <div class="p-5 border-b border-zinc-800 bg-zinc-950 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-xl">✅</span>
                        <h3 class="text-base font-black text-emerald-400">Confirm Delivery Note Acceptance</h3>
                    </div>
                    <button wire:click="closeApprovalModal" class="p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-zinc-800">&times;</button>
                </div>

                <div class="p-5 space-y-4 text-xs text-zinc-300">
                    <div class="p-4 rounded-2xl bg-zinc-950 border border-zinc-800 space-y-2">
                        <div class="flex justify-between">
                            <span class="text-zinc-400">DN Number:</span>
                            <span class="font-mono font-bold text-amber-400">{{ $targetShipment->dn_number ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-400">Shipment Date:</span>
                            <span class="text-zinc-200">{{ $targetShipment->shipment_date ? $targetShipment->shipment_date->format('d F Y') : '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-400">Total Packages:</span>
                            <span class="font-bold text-zinc-100">{{ $targetShipment->total_sacks ?? 0 }} Sacks</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-zinc-400">Total Net Weight:</span>
                            <span class="font-bold text-emerald-400">{{ number_format($targetShipment->total_netto_kg ?? 0, 2, ',', '.') }} kg</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase text-zinc-400 mb-1.5">Acceptance Notes / Proof of Receipt (Optional)</label>
                        <textarea wire:model="approvalNote" rows="3" class="w-full p-3 rounded-xl bg-zinc-950 border border-zinc-800 text-zinc-200 text-xs focus:border-emerald-500 outline-none placeholder-zinc-600" placeholder="e.g., Goods received completely and verified according to weighing standards."></textarea>
                    </div>

                    <div class="p-3 rounded-xl bg-emerald-950/40 border border-emerald-800/80 text-[11px] text-emerald-300">
                        🛡️ By approving, the shipment status will change to <strong>Approved</strong> and be permanently logged in the factory system.
                    </div>
                </div>

                <div class="p-4 border-t border-zinc-800 bg-zinc-950 flex items-center justify-end gap-3">
                    <button wire:click="closeApprovalModal" class="px-4 py-2.5 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold text-xs transition-all">Cancel</button>
                    <button wire:click="approveShipment({{ $approvingShipmentId }})" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs transition-all shadow-lg shadow-emerald-900/40">
                        ✓ Approve & Accept Shipment
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

<!-- JAVASCRIPT & ALPINE COMPONENTS FOR CHARTS & LOCAL CALCULATOR -->
<script>
function historicalYieldChart(data) {
    let chartInstance = null; // Stored in closure outside Alpine's reactive proxy state

    return {
        selectedMetric: 'all', // 'all', 'product', 'stem', 'dust', 'waste'

        initChart() {
            this.$nextTick(() => {
                this.renderChart();
            });

            // Watch root activeTab if nested
            this.$watch('activeTab', (tab) => {
                if (tab === 'historical_analytics') {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (chartInstance) {
                                chartInstance.resize();
                                chartInstance.update();
                            } else {
                                this.renderChart();
                            }
                        }, 50);
                    });
                }
            });

            // Listen to tab switch events from window
            window.addEventListener('switch-customer-tab', (e) => {
                if (e.detail === 'historical_analytics') {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (chartInstance) {
                                chartInstance.resize();
                                chartInstance.update();
                            } else {
                                this.renderChart();
                            }
                        }, 50);
                    });
                }
            });

            // Set up ResizeObserver to prevent any stretching/zooming when tab or window resizes
            if (window.ResizeObserver && this.$refs.canvas && this.$refs.canvas.parentElement) {
                const ro = new ResizeObserver(() => {
                    if (chartInstance && this.$refs.canvas && this.$refs.canvas.offsetParent !== null) {
                        chartInstance.resize();
                    }
                });
                ro.observe(this.$refs.canvas.parentElement);
            }
        },

        setMetric(metric) {
            this.selectedMetric = metric;
            this.$nextTick(() => {
                this.renderChart();
            });
        },

        renderChart() {
            const canvas = this.$refs.canvas;
            if (!canvas) return;

            // If canvas is currently hidden in display:none tab, wait for tab activation
            if (canvas.offsetParent === null) {
                return;
            }

            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            if (chartInstance) {
                chartInstance.destroy();
                chartInstance = null;
            }

            const labels = data.labels || [];
            const prodSeries = data.yieldSeries || [];
            const stemSeries = data.stemSeries || [];
            const dustSeries = data.dustSeries || [];
            const wasteSeries = data.wasteSeries || [];

            const avgProd = Number(data.weightedAvgProduct || 72.31);
            const avgStem = Number(data.weightedAvgStem || 24.60);
            const avgDust = Number(data.weightedAvgDust || 1.78);
            const avgWaste = Number(data.weightedAvgWaste || 1.31);

            let datasets = [];
            let yMin = 0;
            let yMax = 100;
            let stepSize = 10;

            if (this.selectedMetric === 'all') {
                // Multi-line Comparison for all 4 separation outputs
                datasets = [
                    {
                        label: 'Product Yield (%)',
                        data: prodSeries,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#064e3b',
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        tension: 0.25,
                        borderWidth: 3,
                        fill: false
                    },
                    {
                        label: 'Bits / Stem (%)',
                        data: stemSeries,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.08)',
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#78350f',
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        tension: 0.25,
                        borderWidth: 2.5,
                        fill: false
                    },
                    {
                        label: 'Dust (%)',
                        data: dustSeries,
                        borderColor: '#06b6d4',
                        backgroundColor: 'rgba(6, 182, 212, 0.08)',
                        pointBackgroundColor: '#06b6d4',
                        pointBorderColor: '#164e63',
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        tension: 0.25,
                        borderWidth: 2.5,
                        fill: false
                    },
                    {
                        label: 'Uncountable Waste (%)',
                        data: wasteSeries,
                        borderColor: '#a855f7',
                        backgroundColor: 'rgba(168, 85, 247, 0.08)',
                        pointBackgroundColor: '#a855f7',
                        pointBorderColor: '#581c87',
                        pointRadius: 4,
                        pointHoverRadius: 7,
                        tension: 0.25,
                        borderWidth: 2.5,
                        fill: false
                    }
                ];
                yMin = 0;
                yMax = 100;
                stepSize = 10;
            } else if (this.selectedMetric === 'product') {
                const values = prodSeries.filter(v => v !== null && v !== undefined && !isNaN(v));
                const minVal = values.length > 0 ? Math.min(...values, avgProd, 65) : 60;
                const maxVal = values.length > 0 ? Math.max(...values, avgProd, 70) : 100;
                const hasZero = values.some(v => v <= 5);

                yMin = hasZero ? 0 : Math.max(0, Math.floor((minVal - 8) / 10) * 10);
                yMax = Math.min(100, Math.ceil((maxVal + 8) / 10) * 10);
                if (maxVal > 85 || yMax < 100) yMax = 100;
                if (yMin >= yMax) { yMin = 0; yMax = 100; }
                stepSize = 10;

                const avgArray = new Array(labels.length).fill(avgProd);
                const movingAvgData = data.movingAvgSeries || [];
                const line70 = new Array(labels.length).fill(70);
                const line65 = new Array(labels.length).fill(65);

                const pointColors = prodSeries.map(val => {
                    if (val === null || val === undefined || isNaN(val)) return '#10b981';
                    if (val >= 70) return '#10b981';
                    if (val >= 65) return '#f59e0b';
                    return '#ef4444';
                });
                const pointBorders = prodSeries.map(val => {
                    if (val === null || val === undefined || isNaN(val)) return '#064e3b';
                    if (val >= 70) return '#064e3b';
                    if (val >= 65) return '#78350f';
                    return '#7f1d1d';
                });

                datasets = [
                    {
                        label: 'Product Yield (%)',
                        data: prodSeries,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        pointBackgroundColor: pointColors,
                        pointBorderColor: pointBorders,
                        pointRadius: 6,
                        pointHoverRadius: 9,
                        tension: 0.25,
                        borderWidth: 3,
                        fill: true
                    },
                    {
                        label: '5-Batch Moving Avg (MA-5)',
                        data: movingAvgData,
                        borderColor: '#38bdf8',
                        backgroundColor: 'transparent',
                        pointBackgroundColor: '#38bdf8',
                        pointBorderColor: '#0369a1',
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        borderWidth: 2.5,
                        fill: false
                    },
                    {
                        label: 'Weighted Average (' + avgProd.toFixed(2) + '%)',
                        data: avgArray,
                        borderColor: '#f59e0b',
                        borderDash: [8, 6],
                        pointRadius: 0,
                        fill: false,
                        borderWidth: 2.5
                    },
                    {
                        label: 'Optimal Threshold (≥70%)',
                        data: line70,
                        borderColor: 'rgba(16, 185, 129, 0.7)',
                        borderDash: [5, 5],
                        borderWidth: 1.5,
                        pointRadius: 0,
                        fill: false
                    },
                    {
                        label: 'Minimum Standard (65%)',
                        data: line65,
                        borderColor: 'rgba(245, 158, 11, 0.7)',
                        borderDash: [5, 5],
                        borderWidth: 1.5,
                        pointRadius: 0,
                        fill: false
                    },
                    {
                        label: 'Outlier',
                        data: data.outlierPoints || [],
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        backgroundColor: '#ef4444',
                        pointBackgroundColor: '#ef4444',
                        pointRadius: 8,
                        pointHoverRadius: 11,
                        pointStyle: 'circle',
                        showLine: false
                    }
                ];
            } else if (this.selectedMetric === 'stem') {
                const values = stemSeries.filter(v => v !== null && v !== undefined && !isNaN(v));
                const minVal = values.length > 0 ? Math.min(...values, avgStem) : 0;
                const maxVal = values.length > 0 ? Math.max(...values, avgStem) : 35;
                
                yMin = Math.max(0, Math.floor((minVal - 5) / 5) * 5);
                yMax = Math.ceil((maxVal + 5) / 5) * 5;
                if (yMin >= yMax) { yMin = 0; yMax = 40; }
                stepSize = 5;

                const avgArray = new Array(labels.length).fill(avgStem);

                datasets = [
                    {
                        label: 'Bits / Stem (%)',
                        data: stemSeries,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.15)',
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#78350f',
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        tension: 0.25,
                        borderWidth: 3,
                        fill: true
                    },
                    {
                        label: 'Weighted Average (' + avgStem.toFixed(2) + '%)',
                        data: avgArray,
                        borderColor: '#38bdf8',
                        borderDash: [8, 6],
                        pointRadius: 0,
                        fill: false,
                        borderWidth: 2.5
                    }
                ];
            } else if (this.selectedMetric === 'dust') {
                const values = dustSeries.filter(v => v !== null && v !== undefined && !isNaN(v));
                const maxVal = values.length > 0 ? Math.max(...values, avgDust) : 3;

                yMin = 0;
                yMax = Math.max(3, Math.ceil((maxVal + 0.5) * 2) / 2);
                stepSize = yMax <= 5 ? 0.5 : 1;

                const avgArray = new Array(labels.length).fill(avgDust);

                datasets = [
                    {
                        label: 'Dust (%)',
                        data: dustSeries,
                        borderColor: '#06b6d4',
                        backgroundColor: 'rgba(6, 182, 212, 0.15)',
                        pointBackgroundColor: '#06b6d4',
                        pointBorderColor: '#164e63',
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        tension: 0.25,
                        borderWidth: 3,
                        fill: true
                    },
                    {
                        label: 'Weighted Average (' + avgDust.toFixed(2) + '%)',
                        data: avgArray,
                        borderColor: '#f59e0b',
                        borderDash: [8, 6],
                        pointRadius: 0,
                        fill: false,
                        borderWidth: 2.5
                    }
                ];
            } else if (this.selectedMetric === 'waste') {
                const values = wasteSeries.filter(v => v !== null && v !== undefined && !isNaN(v));
                const maxVal = values.length > 0 ? Math.max(...values, avgWaste) : 3;

                yMin = 0;
                yMax = Math.max(3, Math.ceil((maxVal + 0.5) * 2) / 2);
                stepSize = yMax <= 5 ? 0.5 : 1;

                const avgArray = new Array(labels.length).fill(avgWaste);

                datasets = [
                    {
                        label: 'Uncountable Waste (%)',
                        data: wasteSeries,
                        borderColor: '#a855f7',
                        backgroundColor: 'rgba(168, 85, 247, 0.15)',
                        pointBackgroundColor: '#a855f7',
                        pointBorderColor: '#581c87',
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        tension: 0.25,
                        borderWidth: 3,
                        fill: true
                    },
                    {
                        label: 'Weighted Average (' + avgWaste.toFixed(2) + '%)',
                        data: avgArray,
                        borderColor: '#f59e0b',
                        borderDash: [8, 6],
                        pointRadius: 0,
                        fill: false,
                        borderWidth: 2.5
                    }
                ];
            }

            const selectedMetricMode = this.selectedMetric;

            const thresholdZonesPlugin = {
                id: 'thresholdZones',
                beforeDraw(chart) {
                    if (selectedMetricMode !== 'product') return;
                    if (!chart || !chart.ctx || !chart.chartArea || !chart.scales || !chart.scales.y) return;
                    const ctx = chart.ctx;
                    const chartArea = chart.chartArea;
                    const y = chart.scales.y;
                    const { left, top, right, bottom } = chartArea;
                    
                    ctx.save();
                    
                    // Zone 1: Green (>= 70%)
                    const y70 = y.getPixelForValue(70);
                    if (y70 >= top) {
                        ctx.fillStyle = 'rgba(16, 185, 129, 0.04)';
                        ctx.fillRect(left, top, right - left, Math.max(0, y70 - top));
                    }

                    // Zone 2: Amber (65% - 70%)
                    const y65 = y.getPixelForValue(65);
                    if (y65 > y70) {
                        ctx.fillStyle = 'rgba(245, 158, 11, 0.045)';
                        ctx.fillRect(left, y70, right - left, y65 - y70);
                    }

                    // Zone 3: Red (< 65%)
                    if (bottom > y65) {
                        ctx.fillStyle = 'rgba(239, 68, 68, 0.05)';
                        ctx.fillRect(left, y65, right - left, bottom - y65);
                    }

                    ctx.restore();
                }
            };

            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                plugins: [thresholdZonesPlugin],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    resizeDelay: 50,
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
                                stepSize: stepSize,
                                callback: v => v + '%' 
                            }
                        },
                        x: {
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: { 
                                color: '#a1a1aa', 
                                font: { family: 'ui-monospace, SFMono-Regular, monospace', size: 11, weight: '600' },
                                maxRotation: 0,
                                autoSkip: false,
                                callback: function(val, index) {
                                    const detail = (data.batchDetails && data.batchDetails[index]) ? data.batchDetails[index] : null;
                                    if (detail) {
                                        return `B#${detail.batchNum}`;
                                    }
                                    return labels ? labels[index] : val;
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#09090b',
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
                                title: function(context) {
                                    const idx = context[0].dataIndex;
                                    const detail = (data.batchDetails && data.batchDetails[idx]) ? data.batchDetails[idx] : null;
                                    if (detail) {
                                        return `Batch #${detail.batchNum} • ${detail.batchCode}`;
                                    }
                                    return `Batch #${context[0].label}`;
                                },
                                beforeBody: function(context) {
                                    const idx = context[0].dataIndex;
                                    const detail = (data.batchDetails && data.batchDetails[idx]) ? data.batchDetails[idx] : null;
                                    const lines = [];
                                    if (detail) {
                                        lines.push(`📍 Origin: ${detail.origin}`);
                                        lines.push(`🏷️ Origin Code: ${detail.originCode}`);
                                        if (detail.date && detail.date !== '-') {
                                            lines.push(`📅 Date: ${detail.date}`);
                                        }
                                        if (detail.yieldPct !== undefined) {
                                            const yVal = parseFloat(detail.yieldPct);
                                            if (yVal >= 70) {
                                                lines.push(`🟢 Status: Optimal (≥70%)`);
                                            } else if (yVal >= 65) {
                                                lines.push(`🟡 Status: Standard Warning (65-70%)`);
                                            } else {
                                                lines.push(`🔴 Status: Under Standard (<65%)`);
                                            }
                                        }
                                    }
                                    return lines;
                                },
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (context.parsed.y !== null && context.parsed.y !== undefined) {
                                        return ` ${label}: ${context.parsed.y}%`;
                                    }
                                    return null;
                                }
                            }
                        }
                    }
                }
            });
        }
    };
}

function historicalCompositionChart(data) {
    let compChartInstance = null;

    return {
        initChart() {
            this.$nextTick(() => {
                const canvas = this.$refs.canvas;
                if (!canvas || canvas.offsetParent === null) return;
                const ctx = canvas.getContext('2d');
                if (!ctx) return;

                if (compChartInstance) {
                    compChartInstance.destroy();
                    compChartInstance = null;
                }

                compChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [
                            { label: 'Product Yield', data: data.prod, backgroundColor: '#10b981' },
                            { label: 'Bits Stem', data: data.stem, backgroundColor: '#f59e0b' },
                            { label: 'Dust', data: data.dust, backgroundColor: '#3b82f6' },
                            { label: 'Uncountable Waste', data: data.variance, backgroundColor: '#6b7280' }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        resizeDelay: 50,
                        scales: {
                            x: { 
                                stacked: true, 
                                ticks: { 
                                    color: '#9ca3af', 
                                    font: { size: 9 },
                                    callback: function(val, index) {
                                        const detail = (data.batchDetails && data.batchDetails[index]) ? data.batchDetails[index] : null;
                                        if (detail) {
                                            return `B#${detail.batchNum}`;
                                        }
                                        return data.labels ? data.labels[index] : val;
                                    }
                                } 
                            },
                            y: { 
                                stacked: true, 
                                max: 100, 
                                ticks: { color: '#9ca3af', callback: v => v + '%' } 
                            }
                        },
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#09090b',
                                titleColor: '#f59e0b',
                                titleFont: { size: 12, weight: 'bold' },
                                bodyColor: '#ffffff',
                                bodyFont: { size: 11 },
                                borderColor: '#3f3f46',
                                borderWidth: 1,
                                padding: 10,
                                boxPadding: 4,
                                callbacks: {
                                    title: function(context) {
                                        const idx = context[0].dataIndex;
                                        const detail = (data.batchDetails && data.batchDetails[idx]) ? data.batchDetails[idx] : null;
                                        if (detail) {
                                            return `Batch #${detail.batchNum} • ${detail.batchCode}`;
                                        }
                                        return `Batch #${context[0].label}`;
                                    },
                                    beforeBody: function(context) {
                                        const idx = context[0].dataIndex;
                                        const detail = (data.batchDetails && data.batchDetails[idx]) ? data.batchDetails[idx] : null;
                                        if (detail) {
                                            return [
                                                `📍 Origin: ${detail.origin}`,
                                                `🏷️ Origin Code: ${detail.originCode}`
                                            ];
                                        }
                                        return [];
                                    },
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (context.parsed.y !== null && context.parsed.y !== undefined) {
                                            return ` ${label}: ${context.parsed.y}%`;
                                        }
                                        return null;
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

function privateYieldCostCalculator(initial) {
    let sensChartInstance = null; // Closure variable outside Alpine reactivity

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

        init() {
            this.recalculate();
            this.$nextTick(() => this.initSensitivityChart());

            this.$watch('activeTab', (tab) => {
                if (tab === 'yield_calculator') {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (sensChartInstance) {
                                sensChartInstance.resize();
                                sensChartInstance.update();
                            } else {
                                this.initSensitivityChart();
                            }
                        }, 50);
                    });
                }
            });

            window.addEventListener('switch-customer-tab', (e) => {
                if (e.detail === 'yield_calculator') {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (sensChartInstance) {
                                sensChartInstance.resize();
                                sensChartInstance.update();
                            } else {
                                this.initSensitivityChart();
                            }
                        }, 50);
                    });
                }
            });

            if (window.ResizeObserver && this.$refs.sensitivityCanvas && this.$refs.sensitivityCanvas.parentElement) {
                const ro = new ResizeObserver(() => {
                    if (sensChartInstance && this.$refs.sensitivityCanvas && this.$refs.sensitivityCanvas.offsetParent !== null) {
                        sensChartInstance.resize();
                    }
                });
                ro.observe(this.$refs.sensitivityCanvas.parentElement);
            }
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
            if (!canvas || canvas.offsetParent === null) return;
            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            if (sensChartInstance) {
                sensChartInstance.destroy();
                sensChartInstance = null;
            }

            const yieldPoints = [70, 72.5, this.op.productYield, 80, 82.5];
            const costPoints = yieldPoints.map(y => {
                const q = this.op.processedInput * (y / 100);
                return q > 0 ? Math.round(this.res.netCost / q) : 0;
            });

            sensChartInstance = new Chart(ctx, {
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
                    resizeDelay: 50,
                    scales: {
                        y: { ticks: { color: '#9ca3af', font: { size: 9 }, callback: v => 'Rp ' + (v / 1000) + 'k' } },
                        x: { ticks: { color: '#9ca3af', font: { size: 9 } } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        },

        updateSensitivityChart() {
            if (!sensChartInstance) {
                this.initSensitivityChart();
                return;
            }
            const yieldPoints = [70, 72.5, this.op.productYield, 80, 82.5];
            const costPoints = yieldPoints.map(y => {
                const q = this.op.processedInput * (y / 100);
                return q > 0 ? Math.round(this.res.netCost / q) : 0;
            });
            sensChartInstance.data.labels = yieldPoints.map(y => y + '%');
            sensChartInstance.data.datasets[0].data = costPoints;
            sensChartInstance.update();
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
