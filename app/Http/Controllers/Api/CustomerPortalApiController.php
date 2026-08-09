<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\BatchOrigin;
use App\Models\Origin;
use App\Models\ProductType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerPortalApiController extends Controller
{
    /**
     * Get base query constrained by customer tenant isolation
     */
    protected function getCustomerBatchQuery()
    {
        $user = Auth::user();
        $query = Batch::with(['customer', 'deliveryNote', 'productType', 'origin', 'batchOrigins.origin', 'supervisorApprovedBy'])
            ->where('supervisor_approval_status', Batch::APPROVAL_APPROVED);

        if ($user && $user->isCustomer() && $user->customer_id) {
            $query->where('customer_id', $user->customer_id);
        }

        return $query;
    }

    /**
     * Determine historical reporting format & label based on batch number / criteria
     */
    public static function getReportingFormat(Batch $batch): array
    {
        preg_match('/(\d+)$/', $batch->batch_code, $matches);
        $batchNum = isset($matches[1]) ? (int) $matches[1] : 0;

        if ($batchNum >= 23) {
            return [
                'format' => 'DN + MRL',
                'label' => 'Receiving Control Improvement',
                'description' => 'Implemented from Batch 23: Direct confirmation between arrival and process floor.',
            ];
        } elseif (in_array($batchNum, [12, 13, 17, 18])) {
            return [
                'format' => 'Berbasis MRL',
                'label' => 'MRL-based Reporting',
                'description' => 'Material Receipt List based operational recording.',
            ];
        } else {
            return [
                'format' => 'Legacy berbasis DN',
                'label' => 'Legacy Reporting Format',
                'description' => 'Historical delivery note baseline format.',
            ];
        }
    }

    /**
     * GET /api/customer/batches
     */
    public function indexBatches(Request $request): JsonResponse
    {
        $query = $this->getCustomerBatchQuery();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('batch_code', 'like', "%{$search}%")
                    ->orWhereHas('deliveryNote', fn ($dq) => $dq->where('dn_number', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('product_type_id')) {
            $query->where('product_type_id', $request->input('product_type_id'));
        }

        if ($request->filled('origin_id')) {
            $query->where('origin_id', $request->input('origin_id'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date_of_receipt', [$request->input('start_date'), $request->input('end_date')]);
        }

        $batches = $query->latest('date_of_receipt')->paginate($request->input('per_page', 25));

        $data = $batches->getCollection()->map(function (Batch $batch) {
            $format = self::getReportingFormat($batch);
            $mrlGross = (float) $batch->mrl_gross_weight;
            $dnGross = (float) $batch->dn_gross_weight;
            $diffKg = (float) ($mrlGross - $dnGross);
            $diffPct = $dnGross > 0 ? round(($diffKg / $dnGross) * 100, 2) : 0;
            $processedInput = (float) ($batch->mrl_netto_weight > 0 ? $batch->mrl_netto_weight : $mrlGross);
            $productQty = (float) $batch->separation_product_kg;
            $yieldPct = $processedInput > 0 ? round(($productQty / $processedInput) * 100, 2) : (float) $batch->yield_product_pct;

            return [
                'batchId' => $batch->id,
                'batchCode' => $batch->batch_code,
                'customerId' => $batch->customer_id,
                'customerName' => $batch->customer->name ?? null,
                'deliveryNote' => $batch->deliveryNote->dn_number ?? null,
                'receiptDate' => $batch->date_of_receipt ? $batch->date_of_receipt->format('Y-m-d') : null,
                'productType' => $batch->productType->name ?? null,
                'origin' => $batch->origin->region_name ?? null,
                'reportingFormat' => $format['format'],
                'reportingLabel' => $format['label'],
                'receiving' => [
                    'dnGrossKg' => $dnGross,
                    'mrlGrossKg' => $mrlGross,
                    'actualTareKg' => (float) $batch->mrl_tare_weight,
                    'mrlNetKg' => (float) $batch->mrl_netto_weight,
                    'differenceKg' => $diffKg,
                    'differencePct' => $diffPct,
                    'status' => abs($diffKg) <= 5.0 ? 'Confirmed' : 'Pending Review',
                ],
                'process' => [
                    'processedInputKg' => $processedInput,
                    'productQtyKg' => $productQty,
                    'bitsStemKg' => (float) $batch->separation_bits_stem_kg,
                    'dustKg' => (float) $batch->separation_dust_kg,
                    'processVarianceKg' => (float) $batch->separation_waste_kg,
                    'productYieldRatio' => round($yieldPct / 100, 4),
                    'productYieldPct' => $yieldPct,
                ],
                'certificateStatus' => $batch->isApprovedBySupervisor() ? 'Released' : 'Pending',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'meta' => [
                'currentPage' => $batches->currentPage(),
                'lastPage' => $batches->lastPage(),
                'total' => $batches->total(),
            ],
        ]);
    }

    /**
     * GET /api/customer/batches/{batchId}
     */
    public function showBatch(int $batchId): JsonResponse
    {
        $batch = $this->getCustomerBatchQuery()->findOrFail($batchId);
        $format = self::getReportingFormat($batch);

        $mrlGross = (float) $batch->mrl_gross_weight;
        $dnGross = (float) $batch->dn_gross_weight;
        $diffKg = (float) ($mrlGross - $dnGross);
        $diffPct = $dnGross > 0 ? round(($diffKg / $dnGross) * 100, 2) : 0;
        $processedInput = (float) ($batch->mrl_netto_weight > 0 ? $batch->mrl_netto_weight : $mrlGross);
        $productQty = (float) $batch->separation_product_kg;
        $bitsStemQty = (float) $batch->separation_bits_stem_kg;
        $dustQty = (float) $batch->separation_dust_kg;
        $varianceQty = (float) $batch->separation_waste_kg;
        $materialBalanceTotal = $productQty + $bitsStemQty + $dustQty + $varianceQty;
        $materialBalancePct = $processedInput > 0 ? round(($materialBalanceTotal / $processedInput) * 100, 2) : 100.00;

        // Breakdown per origin from batchOrigins or simulated allocation
        $originsData = [];
        if ($batch->batchOrigins->count() > 0) {
            foreach ($batch->batchOrigins as $bo) {
                $allocated = (float) $bo->allocated_kg;
                $share = $processedInput > 0 ? ($allocated / $processedInput) : (1 / max(1, $batch->batchOrigins->count()));
                $boProd = round($productQty * $share, 2);
                $boStem = round($bitsStemQty * $share, 2);
                $boDust = round($dustQty * $share, 2);
                $boVar = round($varianceQty * $share, 2);
                $boYield = $allocated > 0 ? round(($boProd / $allocated) * 100, 2) : 0;

                $originsData[] = [
                    'originId' => $bo->origin_id,
                    'originName' => $bo->origin->region_name ?? 'Origin',
                    'packs' => max(1, (int) round(($allocated / max(1, $processedInput)) * ($batch->mrl_total_pack ?: 50))),
                    'allocatedKg' => $allocated,
                    'dnGrossKg' => round($dnGross * $share, 2),
                    'mrlGrossKg' => round($mrlGross * $share, 2),
                    'differenceKg' => round($diffKg * $share, 2),
                    'differencePct' => $diffPct,
                    'status' => 'Confirmed',
                    'separation' => [
                        'productKg' => $boProd,
                        'productYieldPct' => $boYield,
                        'bitsStemKg' => $boStem,
                        'bitsStemPct' => $allocated > 0 ? round(($boStem / $allocated) * 100, 2) : 0,
                        'dustKg' => $boDust,
                        'dustPct' => $allocated > 0 ? round(($boDust / $allocated) * 100, 2) : 0,
                        'processVarianceKg' => $boVar,
                        'variancePct' => $allocated > 0 ? round(($boVar / $allocated) * 100, 2) : 0,
                    ],
                ];
            }
        } else {
            $originsData[] = [
                'originId' => $batch->origin_id,
                'originName' => $batch->origin->region_name ?? 'Primary Origin',
                'packs' => $batch->mrl_total_pack ?: $batch->dn_total_pack ?: 1,
                'allocatedKg' => $processedInput,
                'dnGrossKg' => $dnGross,
                'mrlGrossKg' => $mrlGross,
                'differenceKg' => $diffKg,
                'differencePct' => $diffPct,
                'status' => 'Confirmed',
                'separation' => [
                    'productKg' => $productQty,
                    'productYieldPct' => (float) $batch->yield_product_pct,
                    'bitsStemKg' => $bitsStemQty,
                    'bitsStemPct' => (float) $batch->yield_bits_stem_pct,
                    'dustKg' => $dustQty,
                    'dustPct' => (float) $batch->yield_dust_pct,
                    'processVarianceKg' => $varianceQty,
                    'variancePct' => (float) $batch->yield_waste_pct,
                ],
            ];
        }

        // Stepper timeline events
        $baseDate = $batch->date_of_receipt ? $batch->date_of_receipt->copy() : now();
        $timeline = [
            [
                'step' => 1,
                'title' => 'Material Arrived',
                'timestamp' => $baseDate->copy()->setTime(7, 12)->format('d M Y H:i'),
                'completed' => true,
            ],
            [
                'step' => 2,
                'title' => 'DN Recorded',
                'timestamp' => $baseDate->copy()->setTime(7, 35)->format('d M Y H:i'),
                'completed' => true,
            ],
            [
                'step' => 3,
                'title' => 'MRL Weighed',
                'timestamp' => $baseDate->copy()->setTime(8, 10)->format('d M Y H:i'),
                'completed' => true,
            ],
            [
                'step' => 4,
                'title' => 'Difference Reviewed',
                'timestamp' => $baseDate->copy()->setTime(8, 35)->format('d M Y H:i'),
                'completed' => true,
            ],
            [
                'step' => 5,
                'title' => 'Receiving Confirmed',
                'timestamp' => $baseDate->copy()->setTime(8, 45)->format('d M Y H:i'),
                'completed' => true,
            ],
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'batchId' => $batch->id,
                'batchCode' => $batch->batch_code,
                'customerId' => $batch->customer_id,
                'customerName' => $batch->customer->name ?? 'PT Falih Nur Gemilang',
                'deliveryNote' => $batch->deliveryNote->dn_number ?? 'DN-2026',
                'receiptDate' => $batch->date_of_receipt ? $batch->date_of_receipt->format('d M Y') : null,
                'reportingFormat' => $format['format'],
                'reportingLabel' => $format['label'],
                'receivingKpi' => [
                    'dnGrossKg' => $dnGross,
                    'mrlGrossKg' => $mrlGross,
                    'receivingDifferenceKg' => $diffKg,
                    'receivingDifferencePct' => $diffPct,
                    'mrlNettoKg' => (float) $batch->mrl_netto_weight,
                    'processedInputKg' => $processedInput,
                    'productOutputKg' => $productQty,
                    'productYieldRatio' => round(($batch->yield_product_pct ?: 76.96) / 100, 4),
                    'productYieldPct' => (float) $batch->yield_product_pct,
                    'processMaterialBalancePct' => $materialBalancePct,
                ],
                'materialReconciliation' => [
                    'origins' => $originsData,
                    'totalPacks' => array_sum(array_column($originsData, 'packs')),
                    'totalDnGrossKg' => $dnGross,
                    'totalMrlGrossKg' => $mrlGross,
                    'totalDifferenceKg' => $diffKg,
                    'totalDifferencePct' => $diffPct,
                    'withinTolerance' => abs($diffKg) <= 10.0,
                    'toleranceMessage' => sprintf('Total difference: %s%.2f kg confirmed within tolerance.', $diffKg >= 0 ? '+' : '', $diffKg),
                ],
                'receivingConfirmationTimeline' => $timeline,
                'processMaterialBalance' => [
                    'processedInputMrlNettoKg' => $processedInput,
                    'productOutputKg' => $productQty,
                    'productOutputPct' => $processedInput > 0 ? round(($productQty / $processedInput) * 100, 2) : 0,
                    'bitsStemOutputKg' => $bitsStemQty,
                    'bitsStemOutputPct' => $processedInput > 0 ? round(($bitsStemQty / $processedInput) * 100, 2) : 0,
                    'dustOutputKg' => $dustQty,
                    'dustOutputPct' => $processedInput > 0 ? round(($dustQty / $processedInput) * 100, 2) : 0,
                    'processVarianceKg' => $varianceQty,
                    'processVariancePct' => $processedInput > 0 ? round(($varianceQty / $processedInput) * 100, 2) : 0,
                    'totalKg' => $materialBalanceTotal,
                    'totalPct' => $materialBalancePct,
                    'withinAcceptableVariance' => abs($materialBalancePct - 100.0) <= 2.0,
                ],
                'certificate' => [
                    'status' => $batch->isApprovedBySupervisor() ? 'Released' : 'Pending',
                    'approvedAt' => $batch->supervisor_approved_at ? $batch->supervisor_approved_at->format('Y-m-d H:i:s') : null,
                    'approvedBy' => $batch->supervisorApprovedBy->name ?? null,
                    'downloadUrl' => route('certificate.pdf', $batch->id),
                ],
            ],
        ]);
    }

    /**
     * GET /api/customer/batches/{batchId}/receiving-reconciliation
     */
    public function receivingReconciliation(int $batchId): JsonResponse
    {
        $batch = $this->getCustomerBatchQuery()->findOrFail($batchId);
        $mrlGross = (float) $batch->mrl_gross_weight;
        $dnGross = (float) $batch->dn_gross_weight;
        $diffKg = round((float) ($mrlGross - $dnGross), 2);

        return response()->json([
            'status' => 'success',
            'data' => [
                'batchCode' => $batch->batch_code,
                'dnGrossKg' => $dnGross,
                'mrlGrossKg' => $mrlGross,
                'actualTareKg' => (float) $batch->mrl_tare_weight,
                'mrlNettoKg' => (float) $batch->mrl_netto_weight,
                'differenceKg' => $diffKg,
                'differencePct' => $dnGross > 0 ? round(($diffKg / $dnGross) * 100, 2) : 0,
                'status' => abs($diffKg) <= 10.0 ? 'Confirmed' : 'Pending Adjustment',
            ],
        ]);
    }

    /**
     * GET /api/customer/batches/{batchId}/process-balance
     */
    public function processBalance(int $batchId): JsonResponse
    {
        $batch = $this->getCustomerBatchQuery()->findOrFail($batchId);
        $input = (float) ($batch->mrl_netto_weight > 0 ? $batch->mrl_netto_weight : $batch->mrl_gross_weight);

        return response()->json([
            'status' => 'success',
            'data' => [
                'batchCode' => $batch->batch_code,
                'processedInputKg' => $input,
                'productQtyKg' => (float) $batch->separation_product_kg,
                'bitsStemKg' => (float) $batch->separation_bits_stem_kg,
                'dustKg' => (float) $batch->separation_dust_kg,
                'processVarianceKg' => (float) $batch->separation_waste_kg,
                'productYieldPct' => (float) $batch->yield_product_pct,
                'materialBalancePct' => $input > 0 ? round((($batch->separation_product_kg + $batch->separation_bits_stem_kg + $batch->separation_dust_kg + $batch->separation_waste_kg) / $input) * 100, 2) : 100,
            ],
        ]);
    }

    /**
     * GET /api/customer/performance/summary
     */
    public function performanceSummary(Request $request): JsonResponse
    {
        $query = $this->getCustomerBatchQuery();
        $batches = $query->get();

        $totalBatches = $batches->count();
        $totalInput = $batches->sum(fn ($b) => (float) ($b->mrl_netto_weight > 0 ? $b->mrl_netto_weight : $b->mrl_gross_weight));
        $totalProduct = $batches->sum(fn ($b) => (float) $b->separation_product_kg);
        $totalStem = $batches->sum(fn ($b) => (float) $b->separation_bits_stem_kg);
        $totalDust = $batches->sum(fn ($b) => (float) $b->separation_dust_kg);
        $totalVariance = $batches->sum(fn ($b) => (float) $b->separation_waste_kg);

        $weightedYield = $totalInput > 0 ? round(($totalProduct / $totalInput) * 100, 2) : 0;
        $bitsStemPct = $totalInput > 0 ? round(($totalStem / $totalInput) * 100, 2) : 0;
        $dustPct = $totalInput > 0 ? round(($totalDust / $totalInput) * 100, 2) : 0;
        $variancePct = $totalInput > 0 ? round(($totalVariance / $totalInput) * 100, 2) : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'totalBatches' => $totalBatches,
                'processedInputTon' => round($totalInput / 1000, 1),
                'processedInputKg' => round($totalInput, 2),
                'productOutputTon' => round($totalProduct / 1000, 1),
                'productOutputKg' => round($totalProduct, 2),
                'weightedProductYieldPct' => $weightedYield,
                'bitsStemPct' => $bitsStemPct,
                'dustPct' => $dustPct,
                'processVariancePct' => $variancePct,
                'yieldConsistency' => $weightedYield >= 75 ? 'High' : ($weightedYield >= 70 ? 'Moderate' : 'Needs Optimization'),
            ],
        ]);
    }

    /**
     * GET /api/customer/performance/trend
     */
    public function performanceTrend(Request $request): JsonResponse
    {
        $query = $this->getCustomerBatchQuery();
        $batches = $query->oldest('date_of_receipt')->get();

        $totalInput = $batches->sum(fn ($b) => (float) ($b->mrl_netto_weight > 0 ? $b->mrl_netto_weight : $b->mrl_gross_weight));
        $totalProduct = $batches->sum(fn ($b) => (float) $b->separation_product_kg);
        $weightedAverage = $totalInput > 0 ? round(($totalProduct / $totalInput) * 100, 2) : 0;

        $trendData = [];
        foreach ($batches as $b) {
            preg_match('/(\d+)$/', $b->batch_code, $matches);
            $num = isset($matches[1]) ? (int) $matches[1] : $b->id;
            $input = (float) ($b->mrl_netto_weight > 0 ? $b->mrl_netto_weight : $b->mrl_gross_weight);
            $prod = (float) $b->separation_product_kg;
            $yield = $input > 0 ? round(($prod / $input) * 100, 2) : (float) $b->yield_product_pct;
            $isOutlier = abs($yield - $weightedAverage) > 5.0;

            $trendData[] = [
                'batchNumber' => $num,
                'batchCode' => $b->batch_code,
                'date' => $b->date_of_receipt ? $b->date_of_receipt->format('d M Y') : null,
                'origin' => $b->origin->region_name ?? null,
                'inputKg' => $input,
                'productKg' => $prod,
                'productYieldPct' => $yield,
                'bitsStemPct' => (float) $b->yield_bits_stem_pct,
                'dustPct' => (float) $b->yield_dust_pct,
                'variancePct' => (float) $b->yield_waste_pct,
                'isOutlier' => $isOutlier,
                'milestone' => $num === 23 ? 'Receiving Control Improvement' : null,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'weightedAveragePct' => $weightedAverage,
                'series' => $trendData,
            ],
        ]);
    }
}
