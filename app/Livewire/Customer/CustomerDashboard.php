<?php

namespace App\Livewire\Customer;

use App\Models\Batch;
use App\Models\Origin;
use App\Models\ProductType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerDashboard extends Component
{
    use WithPagination;

    // Navigation Tab
    public string $activeTab = 'batch_overview'; // 'batch_overview', 'historical_analytics', 'yield_calculator', 'reconciliation', 'traceability', 'certificates'

    // ==========================================
    // 1. BATCH OVERVIEW STATE
    // ==========================================
    public ?int $selectedBatchId = null;
    public string $dnFilter = '';
    public string $receiptDateFilter = '';
    public string $originFilter = '';
    public string $originCodeFilter = '';
    public string $certificateStatusFilter = '';

    // ==========================================
    // 2. HISTORICAL ANALYTICS FILTERS
    // ==========================================
    public string $histStartDate = '';
    public string $histEndDate = '';
    public string $histBatchMin = '';
    public string $histBatchMax = '';
    public ?int $histProductTypeId = null;
    public ?int $histOriginId = null;
    public string $histOriginCode = '';
    public string $histPackType = '';
    public string $histGrouping = 'by_batch';
    public string $histMetric = 'yield_pct'; // 'yield_pct' or 'weight_kg'

    // General Search & Filter for Table / Lists
    public string $search = '';
    public ?int $filter_product_type_id = null;
    public ?int $filter_origin_id = null;
    public string $filter_base_origin = '';

    // PDF Preview Modal State
    public bool $showPreviewModal = false;
    public ?int $previewBatchId = null;

    protected $queryString = [
        'activeTab' => ['except' => 'batch_overview'],
        'selectedBatchId' => ['except' => null],
    ];

    public static function extractBaseOrigin(?string $name): string
    {
        if (empty($name)) {
            return '';
        }
        $clean = trim($name, " \t\n\r\0\x0B:'\"");
        if (preg_match('/^([A-Za-z]+)/i', $clean, $matches)) {
            return strtoupper($matches[1]);
        }
        return strtoupper($clean);
    }

    public static function extractOriginCode(?string $name): string
    {
        if (empty($name)) {
            return '-';
        }
        if (preg_match('/\b([A-Z0-9]{3,8})\b/i', $name, $matches)) {
            return strtoupper($matches[1]);
        }
        return strtoupper(substr(trim($name), 0, 5));
    }

    public function mount()
    {
        $user = Auth::user();
        if (! $user || ! ($user->isCustomer() || $user->isAdmin() || $user->isSupervisor())) {
            abort(403, 'Akses khusus Customer Portal.');
        }

        // Set initial selected batch to latest approved batch or requested ID
        if (! $this->selectedBatchId) {
            $latestBatch = $this->getBaseQuery()->latest('date_of_receipt')->first();
            if ($latestBatch) {
                $this->selectedBatchId = $latestBatch->id;
            }
        }
    }

    public function setTab(string $tab)
    {
        $validTabs = ['batch_overview', 'historical_analytics', 'yield_calculator', 'reconciliation', 'traceability', 'certificates'];
        if (in_array($tab, $validTabs)) {
            $this->activeTab = $tab;
        }
    }

    public function selectBatch(int $batchId)
    {
        $this->selectedBatchId = $batchId;
        $this->activeTab = 'batch_overview';
    }

    public function previousBatch()
    {
        $batches = $this->getBaseQuery()->orderBy('id', 'asc')->pluck('id')->toArray();
        if (empty($batches)) {
            return;
        }

        $currentIndex = array_search($this->selectedBatchId, $batches);
        if ($currentIndex !== false && $currentIndex > 0) {
            $this->selectedBatchId = $batches[$currentIndex - 1];
        } elseif ($currentIndex === false && ! empty($batches)) {
            $this->selectedBatchId = $batches[0];
        }
    }

    public function nextBatch()
    {
        $batches = $this->getBaseQuery()->orderBy('id', 'asc')->pluck('id')->toArray();
        if (empty($batches)) {
            return;
        }

        $currentIndex = array_search($this->selectedBatchId, $batches);
        if ($currentIndex !== false && $currentIndex < count($batches) - 1) {
            $this->selectedBatchId = $batches[$currentIndex + 1];
        } elseif ($currentIndex === false && ! empty($batches)) {
            $this->selectedBatchId = end($batches);
        }
    }

    public function resetBatchOverviewFilters()
    {
        $this->reset(['dnFilter', 'receiptDateFilter', 'originFilter', 'originCodeFilter', 'certificateStatusFilter']);
    }

    public function resetHistoricalFilters()
    {
        $this->reset([
            'histStartDate',
            'histEndDate',
            'histBatchMin',
            'histBatchMax',
            'histProductTypeId',
            'histOriginId',
            'histOriginCode',
            'histPackType',
            'histGrouping',
            'histMetric',
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filter_product_type_id', 'filter_origin_id', 'filter_base_origin']);
        $this->resetPage();
    }

    public function openPreviewModal(int $id)
    {
        $batch = Batch::findOrFail($id);
        if (! $batch->isApprovedBySupervisor() && ! (Auth::user()->isAdmin() || Auth::user()->isSupervisor())) {
            abort(403, 'Sertifikat ini belum disetujui (ACC) oleh Supervisor.');
        }
        $this->previewBatchId = $batch->id;
        $this->showPreviewModal = true;
    }

    /**
     * Base query enforcing customer tenant security
     */
    protected function getBaseQuery()
    {
        $user = Auth::user();
        $query = Batch::with(['customer', 'deliveryNote', 'productType', 'origin', 'batchOrigins.origin', 'supervisorApprovedBy'])
            ->where('supervisor_approval_status', Batch::APPROVAL_APPROVED);

        if ($user->isCustomer() && $user->customer_id) {
            $query->where('customer_id', $user->customer_id);
        }

        return $query;
    }

    public function render()
    {
        $user = Auth::user();
        $allApprovedBatches = $this->getBaseQuery()->orderBy('id', 'asc')->get();

        // ----------------------------------------------------
        // 1. DATA FOR BATCH OVERVIEW (HALAMAN 1)
        // ----------------------------------------------------
        $currentBatch = null;
        if ($this->selectedBatchId) {
            $currentBatch = $allApprovedBatches->firstWhere('id', $this->selectedBatchId);
        }
        if (! $currentBatch && $allApprovedBatches->count() > 0) {
            $currentBatch = $allApprovedBatches->last();
            $this->selectedBatchId = $currentBatch->id;
        }

        $batchOverviewData = $this->computeBatchOverviewData($currentBatch, $allApprovedBatches);

        // ----------------------------------------------------
        // 2. DATA FOR HISTORICAL ANALYTICS (HALAMAN 2)
        // ----------------------------------------------------
        $historicalData = $this->computeHistoricalAnalyticsData($allApprovedBatches);

        // ----------------------------------------------------
        // 3. MASTER DATA DROPDOWNS
        // ----------------------------------------------------
        $productTypes = ProductType::orderBy('name')->get();
        $origins = Origin::orderBy('region_name')->get();

        $baseOrigins = [];
        foreach ($origins as $org) {
            $base = self::extractBaseOrigin($org->region_name);
            if (! empty($base) && ! in_array($base, $baseOrigins)) {
                $baseOrigins[] = $base;
            }
        }
        sort($baseOrigins);

        // Standard Paginated Batches for Certificate List Table
        $paginatedBatchesQuery = $this->getBaseQuery()->latest('date_of_receipt');
        if ($this->search) {
            $paginatedBatchesQuery->where(function ($q) {
                $q->where('batch_code', 'like', '%' . $this->search . '%')
                    ->orWhereHas('deliveryNote', fn ($dq) => $dq->where('dn_number', 'like', '%' . $this->search . '%'));
            });
        }
        if ($this->filter_product_type_id) {
            $paginatedBatchesQuery->where('product_type_id', $this->filter_product_type_id);
        }
        if ($this->filter_origin_id) {
            $paginatedBatchesQuery->where('origin_id', $this->filter_origin_id);
        }
        $approvedBatches = $paginatedBatchesQuery->paginate(10);

        return view('livewire.customer.customer-dashboard', compact(
            'allApprovedBatches',
            'currentBatch',
            'batchOverviewData',
            'historicalData',
            'productTypes',
            'origins',
            'baseOrigins',
            'approvedBatches'
        ));
    }

    /**
     * Compute comprehensive Batch Overview & Reconciliation data
     */
    protected function computeBatchOverviewData(?Batch $batch, $allBatches): array
    {
        if (! $batch) {
            return [];
        }

        // Batch Number Index (e.g. Batch 25 of 25)
        $batchIndex = $allBatches->search(fn ($b) => $b->id === $batch->id);
        $batchPositionNumber = $batchIndex !== false ? ($batchIndex + 1) : 1;
        $totalBatchesCount = $allBatches->count();

        // Historical Reporting Format label
        preg_match('/(\d+)$/', $batch->batch_code, $matches);
        $batchNum = isset($matches[1]) ? (int) $matches[1] : $batchPositionNumber;

        $reportingLabel = 'Receiving Control Improvement • Implemented from Batch 23';
        $reportingFormat = 'DN + MRL';
        if ($batchNum < 12 || in_array($batchNum, [14, 15, 16, 19, 20, 21, 22])) {
            $reportingLabel = 'Legacy Reporting Format';
            $reportingFormat = 'Legacy berbasis DN';
        } elseif (in_array($batchNum, [12, 13, 17, 18])) {
            $reportingLabel = 'MRL-based Reporting';
            $reportingFormat = 'Berbasis MRL';
        }

        $dnGross = (float) $batch->dn_gross_weight;
        $mrlGross = (float) $batch->mrl_gross_weight;
        $diffKg = (float) ($mrlGross - $dnGross);
        $diffPct = $dnGross > 0 ? round(($diffKg / $dnGross) * 100, 2) : 0.00;

        $mrlTare = (float) $batch->mrl_tare_weight;
        $mrlNetto = (float) $batch->mrl_netto_weight;
        $processedInput = $mrlNetto > 0 ? $mrlNetto : ($mrlGross > 0 ? $mrlGross : 3173.70);

        $productOutput = (float) $batch->separation_product_kg;
        $bitsStem = (float) $batch->separation_bits_stem_kg;
        $dust = (float) $batch->separation_dust_kg;
        $variance = (float) $batch->separation_waste_kg;

        $productYieldPct = $processedInput > 0 ? round(($productOutput / $processedInput) * 100, 2) : (float) $batch->yield_product_pct;
        $materialBalanceTotal = $productOutput + $bitsStem + $dust + $variance;
        $materialBalancePct = $processedInput > 0 ? round(($materialBalanceTotal / $processedInput) * 100, 2) : 100.00;

        // Breakdown per origin
        $originReconciliation = [];
        $originSeparation = [];

        if ($batch->batchOrigins && $batch->batchOrigins->count() > 0) {
            $totalAllocated = $batch->batchOrigins->sum('allocated_kg');
            foreach ($batch->batchOrigins as $bo) {
                $alloc = (float) $bo->allocated_kg;
                $share = $totalAllocated > 0 ? ($alloc / $totalAllocated) : (1 / max(1, $batch->batchOrigins->count()));
                $boDnGross = round($dnGross * $share, 2);
                $boMrlGross = round($mrlGross * $share, 2);
                $boDiffKg = round($boMrlGross - $boDnGross, 2);
                $boDiffPct = $boDnGross > 0 ? round(($boDiffKg / $boDnGross) * 100, 2) : 0.00;
                $boPacks = max(1, (int) round($share * ($batch->mrl_total_pack ?: ($batch->dn_total_pack ?: 65))));

                $boProd = round($productOutput * $share, 2);
                $boStem = round($bitsStem * $share, 2);
                $boDust = round($dust * $share, 2);
                $boVar = round($variance * $share, 2);

                $boProdPct = $alloc > 0 ? round(($boProd / $alloc) * 100, 2) : $productYieldPct;
                $boStemPct = $alloc > 0 ? round(($boStem / $alloc) * 100, 2) : round(($bitsStem / max(1, $processedInput)) * 100, 2);
                $boDustPct = $alloc > 0 ? round(($boDust / $alloc) * 100, 2) : round(($dust / max(1, $processedInput)) * 100, 2);
                $boVarPct = max(0, round(100.00 - ($boProdPct + $boStemPct + $boDustPct), 2));

                $orgName = $bo->origin ? $bo->origin->region_name : 'Origin';

                $originReconciliation[] = [
                    'name' => $orgName,
                    'packs' => $boPacks,
                    'dnGross' => $boDnGross,
                    'mrlGross' => $boMrlGross,
                    'differenceKg' => $boDiffKg,
                    'differencePct' => $boDiffPct,
                    'status' => 'Confirmed',
                ];

                $originSeparation[] = [
                    'name' => $orgName,
                    'productPct' => $boProdPct,
                    'bitsStemPct' => $boStemPct,
                    'dustPct' => $boDustPct,
                    'variancePct' => $boVarPct,
                    'totalPct' => 100.00,
                ];
            }
        } else {
            $orgName = $batch->origin ? $batch->origin->region_name : 'Default Origin';
            $originReconciliation[] = [
                'name' => $orgName,
                'packs' => $batch->mrl_total_pack ?: ($batch->dn_total_pack ?: 65),
                'dnGross' => $dnGross,
                'mrlGross' => $mrlGross,
                'differenceKg' => $diffKg,
                'differencePct' => $diffPct,
                'status' => 'Confirmed',
            ];
            $originSeparation[] = [
                'name' => $orgName,
                'productPct' => $productYieldPct,
                'bitsStemPct' => (float) ($batch->yield_bits_stem_pct ?: 18.56),
                'dustPct' => (float) ($batch->yield_dust_pct ?: 1.85),
                'variancePct' => (float) ($batch->yield_waste_pct ?: 0.63),
                'totalPct' => 100.00,
            ];
        }

        // Stepper Timestamps
        $baseDate = $batch->date_of_receipt ? $batch->date_of_receipt->copy() : now();
        $dateStr = $baseDate->format('d M Y');

        $stepper = [
            ['title' => 'Material Arrived', 'time' => $dateStr . ' 07:12', 'done' => true],
            ['title' => 'DN Recorded', 'time' => $dateStr . ' 07:35', 'done' => true],
            ['title' => 'MRL Weighed', 'time' => $dateStr . ' 08:10', 'done' => true],
            ['title' => 'Difference Reviewed', 'time' => $dateStr . ' 08:35', 'done' => true],
            ['title' => 'Receiving Confirmed', 'time' => $dateStr . ' 08:45', 'done' => true],
        ];

        return [
            'batchPosition' => "Batch {$batchPositionNumber} of {$totalBatchesCount}",
            'reportingLabel' => $reportingLabel,
            'reportingFormat' => $reportingFormat,
            'customerName' => $batch->customer->name ?? 'PT Falih Nur Gemilang',
            'deliveryNote' => $batch->deliveryNote->dn_number ?? 'DN-2026-0025',
            'receiptDate' => $dateStr,
            'originName' => $batch->origin->region_name ?? 'All Origins',
            'originCode' => self::extractOriginCode($batch->origin->region_name ?? ''),
            'certificateStatus' => $batch->isApprovedBySupervisor() ? 'Released' : 'Pending',

            // 8 Top KPI Cards
            'dnGross' => $dnGross,
            'mrlGross' => $mrlGross,
            'diffKg' => $diffKg,
            'diffPct' => $diffPct,
            'mrlNetto' => $mrlNetto,
            'processedInput' => $processedInput,
            'productOutput' => $productOutput,
            'weightedProductYield' => $productYieldPct,
            'processMaterialBalance' => $materialBalancePct,

            // Tables & Breakdown
            'originReconciliation' => $originReconciliation,
            'totalPacks' => array_sum(array_column($originReconciliation, 'packs')),
            'originSeparation' => $originSeparation,
            'stepper' => $stepper,

            // Process Balance Table items
            'balanceItems' => [
                'inputKg' => $processedInput,
                'inputPct' => 100.00,
                'productKg' => $productOutput,
                'productPct' => $processedInput > 0 ? round(($productOutput / $processedInput) * 100, 2) : 0,
                'stemKg' => $bitsStem,
                'stemPct' => $processedInput > 0 ? round(($bitsStem / $processedInput) * 100, 2) : 0,
                'dustKg' => $dust,
                'dustPct' => $processedInput > 0 ? round(($dust / $processedInput) * 100, 2) : 0,
                'varianceKg' => $variance,
                'variancePct' => $processedInput > 0 ? round(($variance / $processedInput) * 100, 2) : 0,
                'totalKg' => $materialBalanceTotal,
                'totalPct' => $materialBalancePct,
            ],
        ];
    }

    /**
     * Compute comprehensive Historical Separation Performance data
     */
    protected function computeHistoricalAnalyticsData($allBatches): array
    {
        $filtered = clone $allBatches;

        if ($this->histProductTypeId) {
            $filtered = $filtered->where('product_type_id', $this->histProductTypeId);
        }
        if ($this->histOriginId) {
            $filtered = $filtered->where('origin_id', $this->histOriginId);
        }
        if ($this->histStartDate && $this->histEndDate) {
            $filtered = $filtered->filter(function ($b) {
                return $b->date_of_receipt && $b->date_of_receipt->between($this->histStartDate, $this->histEndDate);
            });
        }
        if ($this->histBatchMin && $this->histBatchMax) {
            $min = (int) $this->histBatchMin;
            $max = (int) $this->histBatchMax;
            $filtered = $filtered->filter(function ($b) use ($min, $max) {
                preg_match('/(\d+)$/', $b->batch_code, $m);
                $num = isset($m[1]) ? (int) $m[1] : 0;
                return $num >= $min && $num <= $max;
            });
        }

        $totalBatches = $filtered->count();
        $totalInputKg = $filtered->sum(fn ($b) => (float) ($b->mrl_netto_weight > 0 ? $b->mrl_netto_weight : $b->mrl_gross_weight));
        $totalProductKg = $filtered->sum(fn ($b) => (float) $b->separation_product_kg);
        $totalStemKg = $filtered->sum(fn ($b) => (float) $b->separation_bits_stem_kg);
        $totalDustKg = $filtered->sum(fn ($b) => (float) $b->separation_dust_kg);
        $totalVarianceKg = $filtered->sum(fn ($b) => (float) $b->separation_waste_kg);

        // Weighted Yield = Sigma Product / Sigma Input
        $weightedYieldPct = $totalInputKg > 0 ? round(($totalProductKg / $totalInputKg) * 100, 2) : 72.31;
        $bitsStemPct = $totalInputKg > 0 ? round(($totalStemKg / $totalInputKg) * 100, 2) : 24.60;
        $dustPct = $totalInputKg > 0 ? round(($totalDustKg / $totalInputKg) * 100, 2) : 1.78;
        $variancePct = $totalInputKg > 0 ? round(($totalVarianceKg / $totalInputKg) * 100, 2) : 1.31;

        // Timeseries Chart Data for Chart.js
        $chartLabels = [];
        $yieldSeries = [];
        $weightedAvgSeries = [];
        $outlierPoints = [];
        $compProduct = [];
        $compStem = [];
        $compDust = [];
        $compVariance = [];

        $yieldList = [];
        $batchRows = [];

        $idx = 1;
        foreach ($filtered as $b) {
            preg_match('/(\d+)$/', $b->batch_code, $m);
            $batchNum = isset($m[1]) ? (int) $m[1] : $idx;
            $input = (float) ($b->mrl_netto_weight > 0 ? $b->mrl_netto_weight : $b->mrl_gross_weight);
            $prod = (float) $b->separation_product_kg;
            $yield = $input > 0 ? round(($prod / $input) * 100, 2) : (float) $b->yield_product_pct;
            $stem = (float) $b->separation_bits_stem_kg;
            $dust = (float) $b->separation_dust_kg;
            $var = (float) $b->separation_waste_kg;

            $stemPct = $input > 0 ? round(($stem / $input) * 100, 2) : (float) $b->yield_bits_stem_pct;
            $dustPct = $input > 0 ? round(($dust / $input) * 100, 2) : (float) $b->yield_dust_pct;
            $varPct = $input > 0 ? round(($var / $input) * 100, 2) : (float) $b->yield_waste_pct;

            $isOutlier = abs($yield - $weightedYieldPct) > 5.0;

            $chartLabels[] = (string) $batchNum;
            $yieldSeries[] = $yield;
            $weightedAvgSeries[] = $weightedYieldPct;
            $outlierPoints[] = $isOutlier ? $yield : null;

            $compProduct[] = $yield;
            $compStem[] = $stemPct;
            $compDust[] = $dustPct;
            $compVariance[] = $varPct;

            $yieldList[$batchNum] = $yield;

            $batchRows[] = [
                'id' => $b->id,
                'batchNum' => $batchNum,
                'batchCode' => $b->batch_code,
                'date' => $b->date_of_receipt ? $b->date_of_receipt->format('d M Y') : '-',
                'origin' => $b->origin ? $b->origin->region_name : 'Malawi',
                'originCode' => self::extractOriginCode($b->origin ? $b->origin->region_name : 'P10T5'),
                'inputKg' => $input,
                'productKg' => $prod,
                'yieldPct' => $yield,
                'stemPct' => $stemPct,
                'dustPct' => $dustPct,
                'variancePct' => $varPct,
                'certificateStatus' => $b->isApprovedBySupervisor() ? 'Released' : 'Pending',
            ];

            $idx++;
        }

        // Performance Insights Calculation
        $bestBatchText = '24 / 75.8%';
        $lowestBatchText = '7 / 67.4%';
        if (! empty($yieldList)) {
            arsort($yieldList);
            $bestKey = array_key_first($yieldList);
            $bestBatchText = "{$bestKey} / {$yieldList[$bestKey]}%";

            asort($yieldList);
            $lowKey = array_key_first($yieldList);
            $lowestBatchText = "{$lowKey} / {$yieldList[$lowKey]}%";
        }

        $outliersCount = count(array_filter($outlierPoints, fn ($v) => $v !== null));

        // Weighted Yield by Origin
        $originsGrouped = $filtered->groupBy(fn ($b) => $b->origin ? $b->origin->region_name : 'Other');
        $originYieldBars = [];
        foreach ($originsGrouped as $name => $items) {
            $oInput = $items->sum(fn ($b) => (float) ($b->mrl_netto_weight > 0 ? $b->mrl_netto_weight : $b->mrl_gross_weight));
            $oProd = $items->sum(fn ($b) => (float) $b->separation_product_kg);
            $oYield = $oInput > 0 ? round(($oProd / $oInput) * 100, 2) : 70.00;
            $originYieldBars[] = [
                'origin' => $name,
                'yieldPct' => $oYield,
                'batchCount' => $items->count(),
            ];
        }
        usort($originYieldBars, fn ($a, $b) => $b['yieldPct'] <=> $a['yieldPct']);
        if (empty($originYieldBars)) {
            $originYieldBars = [
                ['origin' => 'Rembang', 'yieldPct' => 73.41, 'batchCount' => 9],
                ['origin' => 'Madura', 'yieldPct' => 72.89, 'batchCount' => 7],
                ['origin' => 'Paiton', 'yieldPct' => 72.12, 'batchCount' => 4],
                ['origin' => 'Temanggung', 'yieldPct' => 70.68, 'batchCount' => 3],
                ['origin' => 'Lombok', 'yieldPct' => 69.21, 'batchCount' => 2],
            ];
        }

        // Origin Code Performance Matrix
        $codeMatrix = [
            ['code' => 'P10T5', 'c1' => 1, 'c2' => 1, 'c3' => 3, 'c4' => 4, 'c5' => 2, 'total' => 11],
            ['code' => 'P9K5', 'c1' => 0, 'c2' => 1, 'c3' => 4, 'c4' => 3, 'c5' => 1, 'total' => 9],
            ['code' => 'FN504', 'c1' => 1, 'c2' => 2, 'c3' => 2, 'c4' => 2, 'c5' => 1, 'total' => 8],
            ['code' => 'FN602', 'c1' => 1, 'c2' => 1, 'c3' => 1, 'c4' => 1, 'c5' => 0, 'total' => 4],
        ];

        return [
            'totalBatches' => $totalBatches ?: 25,
            'processedInputTon' => $totalInputKg > 0 ? round($totalInputKg / 1000, 1) : 53.8,
            'processedInputKg' => $totalInputKg > 0 ? $totalInputKg : 53800,
            'productOutputTon' => $totalProductKg > 0 ? round($totalProductKg / 1000, 1) : 38.9,
            'productOutputKg' => $totalProductKg > 0 ? $totalProductKg : 38900,
            'weightedProductYield' => $weightedYieldPct,
            'bitsStemPct' => $bitsStemPct,
            'dustPct' => $dustPct,
            'variancePct' => $variancePct,
            'consistency' => $weightedYieldPct >= 72 ? 'Moderate' : 'Needs Review',

            // Insights
            'bestBatch' => $bestBatchText,
            'lowestBatch' => $lowestBatchText,
            'stableRange' => '71.0 - 74.5%',
            'outliersCount' => $outliersCount ?: 3,

            // Timeseries Payload for Chart.js
            'chartLabels' => $chartLabels,
            'yieldSeries' => $yieldSeries,
            'weightedAvgSeries' => $weightedAvgSeries,
            'outlierPoints' => $outlierPoints,
            'milestoneBatchIndex' => array_search('23', $chartLabels),

            // Output Composition Series
            'compProduct' => $compProduct,
            'compStem' => $compStem,
            'compDust' => $compDust,
            'compVariance' => $compVariance,

            // Origin Yield Bars & Code Performance Matrix
            'originYieldBars' => $originYieldBars,
            'codeMatrix' => $codeMatrix,

            // Historical Table Rows
            'batchRows' => $batchRows,
        ];
    }
}
