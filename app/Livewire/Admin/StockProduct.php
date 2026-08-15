<?php

namespace App\Livewire\Admin;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\DnShipmentItem;
use App\Models\Origin;
use App\Models\ProductType;
use App\Livewire\Customer\CustomerDashboard;
use Livewire\Component;
use Livewire\WithPagination;

class StockProduct extends Component
{
    use WithPagination;

    // Search & Filter Properties
    public string $search = '';
    public string $filterCustomerId = '';
    public string $filterOrigin = '';
    public string $filterStockStatus = 'all'; // 'all', 'available', 'partial', 'depleted'
    public int $perPage = 15;

    // Sort
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    // Detail Modal Selection
    public ?int $selectedBatchId = null;
    public bool $showDetailModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterCustomerId' => ['except' => ''],
        'filterOrigin' => ['except' => ''],
        'filterStockStatus' => ['except' => 'all'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCustomerId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterOrigin(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStockStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterCustomerId', 'filterOrigin', 'filterStockStatus']);
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function showBatchStockDetail(int $batchId): void
    {
        $this->selectedBatchId = $batchId;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->selectedBatchId = null;
        $this->showDetailModal = false;
    }

    /**
     * Compute Stock Metrics for a single Batch
     */
    public static function computeBatchStock(Batch $batch): array
    {
        // 1. Output Produksi
        $producedStdSacks = (int) ($batch->separation_product_sack ?: 0);
        $hasRemnant = (float) ($batch->separation_product_remnant_kg ?: 0) > 0;
        $producedTotalSacks = $producedStdSacks + ($hasRemnant ? 1 : 0);
        $producedNettoKg = (float) ($batch->separation_product_kg ?: 0);
        $producedGrossKg = (float) ($batch->separation_product_gross_kg ?: 0);
        $producedTareKg = (float) ($batch->separation_product_tare_kg ?: 0);

        // If separation gross/tare are 0 but netto > 0, estimate standard gross/tare
        if ($producedGrossKg <= 0 && $producedNettoKg > 0) {
            $tarePerSack = (float) ($batch->product_tare_per_sack ?: 0.70);
            $producedTareKg = round($producedTotalSacks * $tarePerSack, 2);
            $producedGrossKg = round($producedNettoKg + $producedTareKg, 2);
        }

        // 2. Terkirim via DN Shipment Items
        $shipmentItems = $batch->dnShipmentItems ?? collect();
        $shippedSacks = (int) $shipmentItems->sum('total_sacks');
        $shippedNettoKg = (float) $shipmentItems->sum('total_netto_kg');
        $shippedGrossKg = (float) $shipmentItems->sum('total_gross_kg');
        $shippedTareKg = (float) $shipmentItems->sum('total_tare_kg');

        // Linked DN List
        $linkedDns = $shipmentItems->map(function ($item) {
            $dn = $item->dnShipment;
            return [
                'dn_id' => $item->dn_shipment_id,
                'dn_number' => $dn?->dn_number ?: ('DN-' . $item->dn_shipment_id),
                'shipment_date' => $dn?->shipment_date ? $dn->shipment_date->format('d/m/Y') : '-',
                'sacks' => $item->total_sacks,
                'netto_kg' => $item->total_netto_kg,
                'status' => $dn?->status ?: 'Shipped',
            ];
        })->values()->all();

        // 3. Sisa Stock di Gudang
        $remainingSacks = max(0, $producedTotalSacks - $shippedSacks);
        $remainingNettoKg = max(0.0, round($producedNettoKg - $shippedNettoKg, 2));
        $remainingGrossKg = max(0.0, round($producedGrossKg - $shippedGrossKg, 2));
        $remainingTareKg = max(0.0, round($producedTareKg - $shippedTareKg, 2));

        // 4. Status Stock
        if ($producedTotalSacks <= 0 && $producedNettoKg <= 0) {
            $status = 'unproduced';
            $statusLabel = 'Belum Ada Output';
            $statusColor = 'zinc';
        } elseif ($remainingSacks <= 0 && $remainingNettoKg <= 0) {
            $status = 'depleted';
            $statusLabel = 'Habis Terkirim';
            $statusColor = 'neutral';
        } elseif ($shippedSacks > 0 && $remainingSacks > 0) {
            $status = 'partial';
            $statusLabel = 'Terkirim Sebagian';
            $statusColor = 'amber';
        } else {
            $status = 'available';
            $statusLabel = 'Tersedia Utuh';
            $statusColor = 'emerald';
        }

        // Resolve Origin Info
        $originInfo = CustomerDashboard::resolveOriginAndCode($batch);

        // Standard weights per sack configured in Batch
        $stdGrossPerSack = (float) ($batch->product_kg_per_sack ?: 50.70);
        $stdTarePerSack = (float) ($batch->product_tare_per_sack ?: 0.70);
        $stdNettoPerSack = max(0.01, round($stdGrossPerSack - $stdTarePerSack, 2));

        // Remnant details
        $remnantGrossKg = (float) ($batch->separation_product_remnant_gross_kg ?: 0);
        $remnantTareKg = (float) ($batch->separation_product_remnant_tare_kg ?: 0);
        $remnantNettoKg = (float) ($batch->separation_product_remnant_kg ?: 0);

        return [
            'batch_id' => $batch->id,
            'batch_code' => $batch->batch_code,
            'customer_name' => $batch->customer ? $batch->customer->name : 'Non-Customer / Internal',
            'customer_id' => $batch->customer_id,
            'origin' => $originInfo['origin'],
            'origin_code' => $originInfo['originCode'],
            'material_code' => $batch->material_code ?: ($batch->productType ? $batch->productType->name : 'Rajangan'),
            'pack_type' => $batch->pack_type ?: 'Karung',
            'date_of_receipt' => $batch->date_of_receipt ? $batch->date_of_receipt->format('d M Y') : '-',
            // Standar Berat Per Sak (Konfigurasi Batch)
            'std_gross_per_sack' => $stdGrossPerSack,
            'std_tare_per_sack' => $stdTarePerSack,
            'std_netto_per_sack' => $stdNettoPerSack,
            'produced_std_sacks' => $producedStdSacks,
            'has_remnant' => $hasRemnant,
            'remnant_gross_kg' => $remnantGrossKg,
            'remnant_tare_kg' => $remnantTareKg,
            'remnant_netto_kg' => $remnantNettoKg,
            // Produksi
            'produced_sacks' => $producedTotalSacks,
            'produced_netto_kg' => $producedNettoKg,
            'produced_gross_kg' => $producedGrossKg,
            'produced_tare_kg' => $producedTareKg,
            // Terkirim
            'shipped_sacks' => $shippedSacks,
            'shipped_netto_kg' => $shippedNettoKg,
            'shipped_gross_kg' => $shippedGrossKg,
            'shipped_tare_kg' => $shippedTareKg,
            'linked_dns' => $linkedDns,
            'dn_count' => count($linkedDns),
            // Sisa
            'remaining_sacks' => $remainingSacks,
            'remaining_netto_kg' => $remainingNettoKg,
            'remaining_gross_kg' => $remainingGrossKg,
            'remaining_tare_kg' => $remainingTareKg,
            // Status
            'status' => $status,
            'status_label' => $statusLabel,
            'status_color' => $statusColor,
            'shipped_pct' => $producedNettoKg > 0 ? min(100, round(($shippedNettoKg / $producedNettoKg) * 100, 1)) : 0,
        ];
    }

    public function render()
    {
        // 1. Fetch All Batches with Relations for Global KPI & Filtering
        $baseQuery = Batch::with(['customer', 'origin', 'productType', 'batchOrigins.origin', 'dnShipmentItems.dnShipment']);

        if (! empty($this->search)) {
            $s = trim($this->search);
            $baseQuery->where(function ($q) use ($s) {
                $q->where('batch_code', 'like', "%{$s}%")
                    ->orWhere('material_code', 'like', "%{$s}%")
                    ->orWhereHas('customer', function ($cq) use ($s) {
                        $cq->where('name', 'like', "%{$s}%")
                            ->orWhere('code', 'like', "%{$s}%");
                    })
                    ->orWhereHas('origin', function ($oq) use ($s) {
                        $oq->where('name', 'like', "%{$s}%");
                    });
            });
        }

        if (! empty($this->filterCustomerId)) {
            $baseQuery->where('customer_id', $this->filterCustomerId);
        }

        $allBatches = $baseQuery->get();

        // 2. Compute Stock Metrics for all matching batches
        $computedRows = $allBatches->map(function ($b) {
            return self::computeBatchStock($b);
        });

        // Filter by Origin if selected
        if (! empty($this->filterOrigin)) {
            $computedRows = $computedRows->filter(function ($row) {
                return strtolower($row['origin']) === strtolower($this->filterOrigin);
            });
        }

        // Filter by Stock Status
        if ($this->filterStockStatus !== 'all') {
            $computedRows = $computedRows->filter(function ($row) {
                return $row['status'] === $this->filterStockStatus;
            });
        }

        // 3. Global KPI Calculations (across all produced batches in DB)
        $allDbBatches = Batch::with(['dnShipmentItems.dnShipment'])->get();
        $globalStats = [
            'total_produced_sacks' => 0,
            'total_produced_netto_kg' => 0.0,
            'total_shipped_sacks' => 0,
            'total_shipped_netto_kg' => 0.0,
            'total_remaining_sacks' => 0,
            'total_remaining_netto_kg' => 0.0,
            'available_batches_count' => 0,
            'partial_batches_count' => 0,
            'depleted_batches_count' => 0,
        ];

        foreach ($allDbBatches as $b) {
            $st = self::computeBatchStock($b);
            $globalStats['total_produced_sacks'] += $st['produced_sacks'];
            $globalStats['total_produced_netto_kg'] += $st['produced_netto_kg'];
            $globalStats['total_shipped_sacks'] += $st['shipped_sacks'];
            $globalStats['total_shipped_netto_kg'] += $st['shipped_netto_kg'];
            $globalStats['total_remaining_sacks'] += $st['remaining_sacks'];
            $globalStats['total_remaining_netto_kg'] += $st['remaining_netto_kg'];

            if ($st['status'] === 'available') {
                $globalStats['available_batches_count']++;
            } elseif ($st['status'] === 'partial') {
                $globalStats['partial_batches_count']++;
            } elseif ($st['status'] === 'depleted') {
                $globalStats['depleted_batches_count']++;
            }
        }

        // Sort collection
        $sortField = $this->sortField;
        $descending = $this->sortDirection === 'desc';
        $sortedRows = $computedRows->sortBy(function ($row) use ($sortField) {
            return $row[$sortField] ?? 0;
        }, SORT_REGULAR, $descending);

        // Paginate collection manually
        $currentPage = $this->getPage();
        $totalCount = $sortedRows->count();
        $pagedItems = $sortedRows->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedItems,
            $totalCount,
            $this->perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Detail Selected Batch Data
        $selectedBatchStock = null;
        if ($this->selectedBatchId) {
            $selectedBatchModel = Batch::with(['customer', 'origin', 'productType', 'batchOrigins.origin', 'dnShipmentItems.dnShipment'])->find($this->selectedBatchId);
            if ($selectedBatchModel) {
                $selectedBatchStock = self::computeBatchStock($selectedBatchModel);
            }
        }

        // Dropdown Lists
        $customers = Customer::orderBy('name')->get();
        $origins = Origin::orderBy('region_name')->get();

        return view('livewire.admin.stock-product', [
            'stockItems' => $paginator,
            'globalStats' => $globalStats,
            'customers' => $customers,
            'origins' => $origins,
            'selectedBatchStock' => $selectedBatchStock,
        ])->layout('layouts.app');
    }
}
