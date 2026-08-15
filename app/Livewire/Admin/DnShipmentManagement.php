<?php

namespace App\Livewire\Admin;

use App\Livewire\Customer\CustomerDashboard;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\DnShipment;
use App\Models\DnShipmentItem;
use App\Models\Origin;
use App\Models\ProductType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class DnShipmentManagement extends Component
{
    use WithPagination;

    // Filters & Search
    public string $search = '';
    public string $filterCustomerId = '';
    public string $filterDateFrom = '';
    public string $filterDateTo = '';
    public string $filterStatus = '';

    // Modal Visibility States
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    public bool $showPreviewModal = false;

    // Active Selection IDs
    public ?int $editingShipmentId = null;
    public ?int $deleteShipmentId = null;
    public ?int $previewShipmentId = null;

    // Form Fields (Header)
    public string $dn_number = '';
    public string $shipment_date = '';
    public ?int $customer_id = null;
    public ?int $product_type_id = null;
    public string $vehicle_number = '';
    public string $driver_name = '';
    public string $destination = '';
    public string $notes = '';
    public string $status = 'Shipped';

    // Dynamic Items (Lots / Origins)
    public array $items = [];

    protected $rules = [
        'dn_number' => 'nullable|string|max:100',
        'shipment_date' => 'required|date',
        'customer_id' => 'nullable|exists:customers,id',
        'product_type_id' => 'nullable|exists:product_types,id',
        'vehicle_number' => 'nullable|string|max:50',
        'driver_name' => 'nullable|string|max:100',
        'destination' => 'nullable|string|max:255',
        'notes' => 'nullable|string|max:1000',
        'status' => 'required|string|in:Draft,Shipped,Delivered,Approved',
        'items' => 'required|array|min:1',
        'items.*.batch_id' => 'nullable|exists:batches,id',
        'items.*.batch_code' => 'nullable|string|max:100',
        'items.*.origin' => 'required|string|max:100',
        'items.*.origin_code' => 'required|string|max:100',
        'items.*.material_type' => 'nullable|string|max:100',
        'items.*.standard_sack_count' => 'required|integer|min:1',
        'items.*.standard_gross_per_sack' => 'required|numeric|min:0.01',
        'items.*.standard_tare_per_sack' => 'required|numeric|min:0',
        'items.*.standard_netto_per_sack' => 'required|numeric|min:0.01',
        'items.*.has_remnant' => 'boolean',
        'items.*.remnant_gross_kg' => 'nullable|numeric|min:0',
        'items.*.remnant_tare_kg' => 'nullable|numeric|min:0',
        'items.*.remnant_netto_kg' => 'nullable|numeric|min:0',
    ];

    public function mount(): void
    {
        $this->shipment_date = date('Y-m-d');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCustomerId(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterCustomerId', 'filterDateFrom', 'filterDateTo', 'filterStatus']);
        $this->resetPage();
    }

    /**
     * Generate Next Suggested DN Shipment Number
     */
    public function generateNextDnNumber(): string
    {
        $year = date('Y');
        $count = DnShipment::whereYear('created_at', $year)->count() + 1;
        return sprintf('DNS-%s-%04d', $year, $count);
    }

    /**
     * Build dynamic Origin => Origin Codes Map from database
     */
    public function getOriginCodesMap(): array
    {
        $map = [
            'Lombok' => ["Lombok'24", "Lombok'23", "Lombok'22", 'P9K5', 'P9', 'FN504'],
            'Paiton' => ['P10T5', 'P9K5', 'P10', 'P10-5', "Paiton'24", "Paiton'23", 'FN602'],
            'Madura' => ['M24A', 'P11', 'FN504', 'FN602', "Madura'25", "Madura'24", "Madura'23"],
            'Rembang' => ['P8B4', 'R24A', 'FN504', 'FN-53', "Rembang'24", "Rembang'23"],
            'Temanggung' => ['FN405', 'FN504', 'TMG24', "Temanggung'24"],
            'Kendal' => ['KDL24', 'FN504', "Kendal'24"],
            'Bojonegoro' => ['BJN24', 'FN504', "Bojonegoro'24"],
            'Jember' => ['JBR24', 'FN504', "Jember'24"],
            'Bali' => ["Bali'24", 'BL24', 'FN504'],
            'Kasturi' => ['FN602', 'KST24', 'FN-533'],
            'Maesan' => ['Maesan', 'MSN24'],
            'Ploso' => ['Ploso', 'PN-512', 'PLS24'],
        ];

        // Augment with real origin records
        $allOrigins = Origin::all();
        foreach ($allOrigins as $org) {
            $info = CustomerDashboard::resolveOriginAndCode($org);
            $orig = $info['origin'];
            $code = $info['originCode'];
            if ($orig && $orig !== 'Unknown') {
                if (! isset($map[$orig])) {
                    $map[$orig] = [];
                }
                if ($code && $code !== '-' && ! in_array($code, $map[$orig])) {
                    $map[$orig][] = $code;
                }
            }
        }

        // Augment with real batch records (material_code / origin)
        $batches = Batch::with(['origin', 'batchOrigins.origin'])->get();
        foreach ($batches as $b) {
            $info = CustomerDashboard::resolveOriginAndCode($b);
            $orig = $info['origin'];
            $code = $info['originCode'];
            if ($orig && $orig !== 'Unknown') {
                if (! isset($map[$orig])) {
                    $map[$orig] = [];
                }
                if ($code && $code !== '-' && ! in_array($code, $map[$orig])) {
                    $map[$orig][] = $code;
                }
            }

            if ($b->batchOrigins) {
                foreach ($b->batchOrigins as $bo) {
                    if ($bo->origin) {
                        $boInfo = CustomerDashboard::resolveOriginAndCode($bo->origin, $b->material_code);
                        $boOrig = $boInfo['origin'];
                        $boCode = $boInfo['originCode'];
                        if ($boOrig && $boOrig !== 'Unknown') {
                            if (! isset($map[$boOrig])) {
                                $map[$boOrig] = [];
                            }
                            if ($boCode && $boCode !== '-' && ! in_array($boCode, $map[$boOrig])) {
                                $map[$boOrig][] = $boCode;
                            }
                        }
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Open Create Modal & Initialize Default Values
     */
    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->dn_number = '';
        $this->shipment_date = date('Y-m-d');

        // Select first customer if available
        $firstCustomer = Customer::first();
        if ($firstCustomer) {
            $this->customer_id = $firstCustomer->id;
            $this->destination = $firstCustomer->address ?? '';
        }

        // Select first product type if available
        $firstPt = ProductType::first();
        if ($firstPt) {
            $this->product_type_id = $firstPt->id;
        }

        // Add 1 initial lot/item
        $this->addItem();

        $this->showCreateModal = true;
    }

    /**
     * Add a new Lot/Origin row
     */
    public function addItem(): void
    {
        $itemNo = count($this->items) + 1;
        $defaultOrigin = 'Lombok';
        $defaultCode = "Lombok'24";

        $this->items[] = [
            'item_no' => $itemNo,
            'batch_id' => null,
            'batch_code' => '',
            'origin' => $defaultOrigin,
            'origin_code' => $defaultCode,
            'material_type' => 'Product',
            'standard_sack_count' => 10,
            'standard_gross_per_sack' => 50.70,
            'standard_tare_per_sack' => 0.70,
            'standard_netto_per_sack' => 50.00,
            'has_remnant' => false,
            'remnant_gross_kg' => 0.00,
            'remnant_tare_kg' => 0.70,
            'remnant_netto_kg' => 0.00,
            'total_sacks' => 10,
            'total_gross_kg' => 507.00,
            'total_tare_kg' => 7.00,
            'total_netto_kg' => 500.00,
        ];

        $this->recomputeAllItemTotals();
    }

    /**
     * Select a Batch for a specific Lot Item (Auto-fill origin, origin code, weights, and expand if multi-origin)
     */
    public function selectBatchForLot(int $index, ?int $batchId): void
    {
        if (! isset($this->items[$index])) return;

        if (! $batchId) {
            $this->items[$index]['batch_id'] = null;
            $this->items[$index]['batch_code'] = '';
            return;
        }

        $batch = Batch::with(['customer', 'origin', 'productType', 'batchOrigins.origin'])->find($batchId);
        if (! $batch) return;

        $currentMatType = $this->items[$index]['material_type'] ?? 'Product';

        // Check if this batch has multiple allocated origins (e.g. 2 or 3 origins in batchOrigins)
        if ($batch->batchOrigins && $batch->batchOrigins->count() > 1) {
            $subOrigins = $batch->batchOrigins;
            $totalAlloc = $subOrigins->sum('allocated_kg') ?: 1;
            $totalSacks = (int) ($batch->separation_product_sack ?: ($batch->product_sack_count ?: (count($subOrigins) * 10)));
            $tarePerSack = (float) ($batch->product_tare_per_sack ?? 0.20);
            $grossPerSack = (float) ($batch->product_kg_per_sack ?: 50.20);
            $nettoPerSack = max(0.0, round($grossPerSack - $tarePerSack, 2));
            $remnantKg = (float) ($batch->separation_product_remnant_kg ?: ($batch->product_remnant_kg ?: 0));
            $remnantGross = (float) ($batch->separation_product_remnant_gross_kg ?? 0);
            $remnantTare = (float) ($batch->separation_product_remnant_tare_kg ?? $tarePerSack);

            $newLots = [];
            foreach ($subOrigins as $sIdx => $bo) {
                $info = CustomerDashboard::resolveOriginAndCode($bo->origin ?: $batch->origin, $batch->material_code);
                $share = $bo->allocated_kg > 0 ? ($bo->allocated_kg / $totalAlloc) : (1 / count($subOrigins));
                $lotSacks = max(1, (int) round($totalSacks * $share));

                $isLast = ($sIdx === count($subOrigins) - 1);
                $hasRem = ($isLast && ($remnantKg > 0 || $remnantGross > 0));

                $newLots[] = [
                    'item_no' => 0, // will be re-indexed
                    'batch_id' => $batch->id,
                    'batch_code' => $batch->batch_code,
                    'origin' => $info['origin'] !== 'Unknown' ? $info['origin'] : 'Temanggung',
                    'origin_code' => $info['originCode'] !== '-' ? $info['originCode'] : ($batch->material_code ?: 'FN504'),
                    'material_type' => $currentMatType,
                    'standard_sack_count' => $lotSacks,
                    'standard_gross_per_sack' => $grossPerSack,
                    'standard_tare_per_sack' => $tarePerSack,
                    'standard_netto_per_sack' => $nettoPerSack,
                    'has_remnant' => $hasRem,
                    'remnant_gross_kg' => $hasRem ? ($remnantGross ?: round($remnantKg + $remnantTare, 2)) : 0.00,
                    'remnant_tare_kg' => $hasRem ? ($remnantTare ?: $tarePerSack) : 0.00,
                    'remnant_netto_kg' => $hasRem ? ($remnantKg ?: max(0.0, round($remnantGross - $remnantTare, 2))) : 0.00,
                    'total_sacks' => $lotSacks + ($hasRem ? 1 : 0),
                    'total_gross_kg' => 0.00,
                    'total_tare_kg' => 0.00,
                    'total_netto_kg' => 0.00,
                ];
            }

            // Splice into $this->items at $index replacing 1 row with all sub-origin rows
            array_splice($this->items, $index, 1, $newLots);

            // Re-index item_no
            foreach ($this->items as $idx => &$it) {
                $it['item_no'] = $idx + 1;
            }
        } else {
            // Single origin batch
            $subOrigin = ($batch->batchOrigins && $batch->batchOrigins->count() === 1) ? $batch->batchOrigins->first()->origin : null;
            $info = CustomerDashboard::resolveOriginAndCode($subOrigin ?: $batch->origin, $batch->material_code);

            $this->items[$index]['batch_id'] = $batch->id;
            $this->items[$index]['batch_code'] = $batch->batch_code;
            $this->items[$index]['origin'] = $info['origin'] !== 'Unknown' ? $info['origin'] : 'Lombok';
            $this->items[$index]['origin_code'] = $info['originCode'] !== '-' ? $info['originCode'] : ($batch->material_code ?: "Lombok'24");
            $this->items[$index]['material_type'] = $currentMatType;

            $this->applyBatchWeightsToLot($index, $batch, $currentMatType);
        }

        // Pre-fill customer / product type on header if empty
        if (! $this->customer_id && $batch->customer_id) {
            $this->customer_id = $batch->customer_id;
            $this->destination = $batch->customer->address ?? '';
        }
        if (! $this->product_type_id && $batch->product_type_id) {
            $this->product_type_id = $batch->product_type_id;
        }

        $this->recomputeAllItemTotals();
    }

    /**
     * Apply Batch Weights to Lot depending on Material Type (Product, Bits / Stem, Dust)
     */
    protected function applyBatchWeightsToLot(int $index, Batch $batch, string $materialType): void
    {
        if ($materialType === 'Bits / Stem') {
            $nettoKg = (float) ($batch->separation_bits_stem_kg ?: ($batch->bits_stem_kg ?? 0));
            $sackCount = max(0, (int) floor($nettoKg / 50.00));
            $remnantKg = round(fmod($nettoKg, 50.00), 2);
            if ($sackCount === 0 && $nettoKg > 0) {
                $this->items[$index]['standard_sack_count'] = 1;
                $this->items[$index]['standard_netto_per_sack'] = $nettoKg;
                $this->items[$index]['standard_tare_per_sack'] = 0.70;
                $this->items[$index]['standard_gross_per_sack'] = round($nettoKg + 0.70, 2);
                $this->items[$index]['has_remnant'] = false;
                $this->items[$index]['remnant_netto_kg'] = 0.00;
                $this->items[$index]['remnant_gross_kg'] = 0.00;
            } else {
                $this->items[$index]['standard_sack_count'] = max(1, $sackCount);
                $this->items[$index]['standard_netto_per_sack'] = 50.00;
                $this->items[$index]['standard_tare_per_sack'] = 0.70;
                $this->items[$index]['standard_gross_per_sack'] = 50.70;
                if ($remnantKg > 0) {
                    $this->items[$index]['has_remnant'] = true;
                    $this->items[$index]['remnant_tare_kg'] = 0.70;
                    $this->items[$index]['remnant_netto_kg'] = $remnantKg;
                    $this->items[$index]['remnant_gross_kg'] = round($remnantKg + 0.70, 2);
                } else {
                    $this->items[$index]['has_remnant'] = false;
                    $this->items[$index]['remnant_netto_kg'] = 0.00;
                    $this->items[$index]['remnant_gross_kg'] = 0.00;
                }
            }
        } elseif ($materialType === 'Dust') {
            $nettoKg = (float) ($batch->separation_dust_kg ?: ($batch->dust_kg ?? 0));
            $sackCount = max(0, (int) floor($nettoKg / 50.00));
            $remnantKg = round(fmod($nettoKg, 50.00), 2);
            if ($sackCount === 0 && $nettoKg > 0) {
                $this->items[$index]['standard_sack_count'] = 1;
                $this->items[$index]['standard_netto_per_sack'] = $nettoKg;
                $this->items[$index]['standard_tare_per_sack'] = 0.70;
                $this->items[$index]['standard_gross_per_sack'] = round($nettoKg + 0.70, 2);
                $this->items[$index]['has_remnant'] = false;
                $this->items[$index]['remnant_netto_kg'] = 0.00;
                $this->items[$index]['remnant_gross_kg'] = 0.00;
            } else {
                $this->items[$index]['standard_sack_count'] = max(1, $sackCount);
                $this->items[$index]['standard_netto_per_sack'] = 50.00;
                $this->items[$index]['standard_tare_per_sack'] = 0.70;
                $this->items[$index]['standard_gross_per_sack'] = 50.70;
                if ($remnantKg > 0) {
                    $this->items[$index]['has_remnant'] = true;
                    $this->items[$index]['remnant_tare_kg'] = 0.70;
                    $this->items[$index]['remnant_netto_kg'] = $remnantKg;
                    $this->items[$index]['remnant_gross_kg'] = round($remnantKg + 0.70, 2);
                } else {
                    $this->items[$index]['has_remnant'] = false;
                    $this->items[$index]['remnant_netto_kg'] = 0.00;
                    $this->items[$index]['remnant_gross_kg'] = 0.00;
                }
            }
        } else {
            // Product
            $sackCount = $batch->separation_product_sack ?: ($batch->product_sack_count ?? null);
            if ($sackCount && (int) $sackCount > 0) {
                $this->items[$index]['standard_sack_count'] = (int) $sackCount;
            }

            $tarePerSack = (float) ($batch->product_tare_per_sack ?? 0.20);
            $this->items[$index]['standard_tare_per_sack'] = $tarePerSack;

            $grossPerSack = (float) ($batch->product_kg_per_sack ?: 50.20);
            $this->items[$index]['standard_gross_per_sack'] = $grossPerSack;
            $this->items[$index]['standard_netto_per_sack'] = max(0.0, round($grossPerSack - $tarePerSack, 2));

            $remnantKg = (float) ($batch->separation_product_remnant_kg ?: ($batch->product_remnant_kg ?? 0));
            $remnantGross = (float) ($batch->separation_product_remnant_gross_kg ?? 0);
            $remnantTare = (float) ($batch->separation_product_remnant_tare_kg ?? $tarePerSack);

            if ($remnantGross > 0 || $remnantKg > 0) {
                $this->items[$index]['has_remnant'] = true;
                $this->items[$index]['remnant_tare_kg'] = $remnantTare ?: $tarePerSack;
                $this->items[$index]['remnant_gross_kg'] = $remnantGross ?: round($remnantKg + ($remnantTare ?: $tarePerSack), 2);
                $this->items[$index]['remnant_netto_kg'] = $remnantKg ?: max(0.0, round($remnantGross - $remnantTare, 2));
            } else {
                $this->items[$index]['has_remnant'] = false;
                $this->items[$index]['remnant_netto_kg'] = 0.00;
                $this->items[$index]['remnant_gross_kg'] = 0.00;
                $this->items[$index]['remnant_tare_kg'] = 0.00;
            }
        }
    }

    /**
     * Change Material Type for a specific Lot Item (Product, Bits / Stem, Dust)
     */
    public function selectMaterialTypeForLot(int $index, string $type): void
    {
        if (! isset($this->items[$index])) return;

        $this->items[$index]['material_type'] = $type;

        $batchId = $this->items[$index]['batch_id'] ?? null;
        if ($batchId) {
            $batch = Batch::find($batchId);
            if ($batch) {
                $this->applyBatchWeightsToLot($index, $batch, $type);
            }
        }

        $this->recomputeAllItemTotals();
    }

    /**
     * Change Origin for a specific Lot Item (Auto-select default code for this origin)
     */
    public function selectOriginForLot(int $index, string $origin): void
    {
        if (! isset($this->items[$index])) return;

        $this->items[$index]['origin'] = $origin;

        $map = $this->getOriginCodesMap();
        $availableCodes = $map[$origin] ?? [];

        if (! empty($availableCodes)) {
            if (! in_array($this->items[$index]['origin_code'], $availableCodes)) {
                $this->items[$index]['origin_code'] = $availableCodes[0];
            }
        }

        $this->recomputeAllItemTotals();
    }

    /**
     * Livewire 3 Hook for when items array properties change
     */
    public function updatedItems($value, $key): void
    {
        $parts = explode('.', (string) $key);
        if (count($parts) >= 2) {
            $index = (int) $parts[0];
            $field = $parts[1];

            if ($field === 'batch_id') {
                $this->selectBatchForLot($index, $value ? (int) $value : null);
                return;
            }

            if ($field === 'origin') {
                $this->selectOriginForLot($index, (string) $value);
                return;
            }

            if ($field === 'material_type') {
                $this->selectMaterialTypeForLot($index, (string) $value);
                return;
            }
        }

        $this->recomputeAllItemTotals();
    }

    /**
     * Remove an item row
     */
    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);

            // Re-index item numbers
            foreach ($this->items as $idx => &$it) {
                $it['item_no'] = $idx + 1;
            }

            $this->recomputeAllItemTotals();
        }
    }

    /**
     * Hook when customer selection changes
     */
    public function updatedCustomerId($val): void
    {
        if ($val) {
            $c = Customer::find($val);
            if ($c && empty($this->destination)) {
                $this->destination = $c->address ?? '';
            }
        }
    }

    /**
     * Recompute totals for all items
     */
    public function recomputeAllItemTotals(): void
    {
        foreach ($this->items as $idx => &$item) {
            $stdCount = max(0, (int) ($item['standard_sack_count'] ?? 0));
            $stdGross = (float) ($item['standard_gross_per_sack'] ?? 50.70);
            $stdTare = (float) ($item['standard_tare_per_sack'] ?? 0.70);
            
            // Standard Netto is gross minus tare
            $stdNetto = max(0, $stdGross - $stdTare);
            $item['standard_netto_per_sack'] = round($stdNetto, 2);

            $hasRemnant = !empty($item['has_remnant']);
            $remGross = $hasRemnant ? (float) ($item['remnant_gross_kg'] ?? 0) : 0.0;
            $remTare = $hasRemnant ? (float) ($item['remnant_tare_kg'] ?? 0.70) : 0.0;
            $remNetto = $hasRemnant ? max(0, $remGross - $remTare) : 0.0;
            $item['remnant_netto_kg'] = round($remNetto, 2);

            $item['total_sacks'] = $stdCount + ($hasRemnant ? 1 : 0);
            $item['total_gross_kg'] = round(($stdCount * $stdGross) + $remGross, 2);
            $item['total_tare_kg'] = round(($stdCount * $stdTare) + $remTare, 2);
            $item['total_netto_kg'] = round(($stdCount * $stdNetto) + $remNetto, 2);
        }
    }

    /**
     * Calculate Grand Total Sacks across items
     */
    public function getGrandTotalSacksProperty(): int
    {
        return array_sum(array_column($this->items, 'total_sacks'));
    }

    /**
     * Calculate Grand Total Gross (kg) across items
     */
    public function getGrandTotalGrossProperty(): float
    {
        return array_sum(array_column($this->items, 'total_gross_kg'));
    }

    /**
     * Calculate Grand Total Tare (kg) across items
     */
    public function getGrandTotalTareProperty(): float
    {
        return array_sum(array_column($this->items, 'total_tare_kg'));
    }

    /**
     * Calculate Grand Total Netto (kg) across items
     */
    public function getGrandTotalNettoProperty(): float
    {
        return array_sum(array_column($this->items, 'total_netto_kg'));
    }

    /**
     * Save New DN Shipment
     */
    public function saveShipment(): void
    {
        $this->recomputeAllItemTotals();
        $this->validate();

        DB::transaction(function () {
            $shipment = DnShipment::create([
                'dn_number' => $this->dn_number,
                'shipment_date' => $this->shipment_date,
                'customer_id' => $this->customer_id,
                'product_type_id' => $this->product_type_id,
                'vehicle_number' => $this->vehicle_number,
                'driver_name' => $this->driver_name,
                'destination' => $this->destination,
                'notes' => $this->notes,
                'status' => $this->status,
                'total_sacks' => $this->grandTotalSacks,
                'total_gross_kg' => $this->grandTotalGross,
                'total_tare_kg' => $this->grandTotalTare,
                'total_netto_kg' => $this->grandTotalNetto,
                'created_by' => Auth::id(),
            ]);

            foreach ($this->items as $idx => $it) {
                DnShipmentItem::create([
                    'dn_shipment_id' => $shipment->id,
                    'batch_id' => !empty($it['batch_id']) ? $it['batch_id'] : null,
                    'batch_code' => !empty($it['batch_code']) ? $it['batch_code'] : null,
                    'item_no' => $idx + 1,
                    'origin' => $it['origin'],
                    'origin_code' => $it['origin_code'],
                    'material_type' => $it['material_type'] ?? 'Product',
                    'standard_sack_count' => (int) $it['standard_sack_count'],
                    'standard_gross_per_sack' => (float) $it['standard_gross_per_sack'],
                    'standard_tare_per_sack' => (float) $it['standard_tare_per_sack'],
                    'standard_netto_per_sack' => (float) $it['standard_netto_per_sack'],
                    'has_remnant' => !empty($it['has_remnant']),
                    'remnant_gross_kg' => (float) ($it['remnant_gross_kg'] ?? 0),
                    'remnant_tare_kg' => (float) ($it['remnant_tare_kg'] ?? 0),
                    'remnant_netto_kg' => (float) ($it['remnant_netto_kg'] ?? 0),
                    'total_sacks' => (int) $it['total_sacks'],
                    'total_gross_kg' => (float) $it['total_gross_kg'],
                    'total_tare_kg' => (float) $it['total_tare_kg'],
                    'total_netto_kg' => (float) $it['total_netto_kg'],
                ]);
            }
        });

        session()->flash('message', "DN Pengiriman {$this->dn_number} berhasil dibuat dengan {$this->grandTotalSacks} karung!");
        $this->showCreateModal = false;
        $this->resetForm();
    }

    /**
     * Open Edit Modal
     */
    public function openEditModal(int $id): void
    {
        $shipment = DnShipment::with('items')->findOrFail($id);
        $this->editingShipmentId = $shipment->id;
        $this->dn_number = $shipment->dn_number;
        $this->shipment_date = $shipment->shipment_date ? $shipment->shipment_date->format('Y-m-d') : date('Y-m-d');
        $this->customer_id = $shipment->customer_id;
        $this->product_type_id = $shipment->product_type_id;
        $this->vehicle_number = $shipment->vehicle_number ?? '';
        $this->driver_name = $shipment->driver_name ?? '';
        $this->destination = $shipment->destination ?? '';
        $this->notes = $shipment->notes ?? '';
        $this->status = $shipment->status;

        $this->items = [];
        foreach ($shipment->items as $idx => $it) {
            $this->items[] = [
                'id' => $it->id,
                'batch_id' => $it->batch_id,
                'batch_code' => $it->batch_code ?? '',
                'item_no' => $it->item_no ?: ($idx + 1),
                'origin' => $it->origin,
                'origin_code' => $it->origin_code,
                'material_type' => $it->material_type ?: 'Product',
                'standard_sack_count' => (int) $it->standard_sack_count,
                'standard_gross_per_sack' => (float) $it->standard_gross_per_sack,
                'standard_tare_per_sack' => (float) $it->standard_tare_per_sack,
                'standard_netto_per_sack' => (float) $it->standard_netto_per_sack,
                'has_remnant' => (bool) $it->has_remnant,
                'remnant_gross_kg' => (float) $it->remnant_gross_kg,
                'remnant_tare_kg' => (float) $it->remnant_tare_kg,
                'remnant_netto_kg' => (float) $it->remnant_netto_kg,
                'total_sacks' => (int) $it->total_sacks,
                'total_gross_kg' => (float) $it->total_gross_kg,
                'total_tare_kg' => (float) $it->total_tare_kg,
                'total_netto_kg' => (float) $it->total_netto_kg,
            ];
        }

        if (empty($this->items)) {
            $this->addItem();
        } else {
            $this->recomputeAllItemTotals();
        }

        $this->showEditModal = true;
    }

    /**
     * Update DN Shipment
     */
    public function updateShipment(): void
    {
        if (! $this->editingShipmentId) return;

        $this->recomputeAllItemTotals();
        $this->validate([
            'dn_number' => 'nullable|string|max:100',
            'shipment_date' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'product_type_id' => 'nullable|exists:product_types,id',
            'vehicle_number' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:100',
            'destination' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|string|in:Draft,Shipped,Delivered,Approved',
            'items' => 'required|array|min:1',
            'items.*.batch_id' => 'nullable|exists:batches,id',
            'items.*.batch_code' => 'nullable|string|max:100',
            'items.*.origin' => 'required|string|max:100',
            'items.*.origin_code' => 'required|string|max:100',
            'items.*.material_type' => 'nullable|string|max:100',
            'items.*.standard_sack_count' => 'required|integer|min:1',
            'items.*.standard_gross_per_sack' => 'required|numeric|min:0.01',
            'items.*.standard_tare_per_sack' => 'required|numeric|min:0',
            'items.*.standard_netto_per_sack' => 'required|numeric|min:0.01',
            'items.*.has_remnant' => 'boolean',
            'items.*.remnant_gross_kg' => 'nullable|numeric|min:0',
            'items.*.remnant_tare_kg' => 'nullable|numeric|min:0',
            'items.*.remnant_netto_kg' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () {
            $shipment = DnShipment::findOrFail($this->editingShipmentId);
            $shipment->update([
                'dn_number' => $this->dn_number,
                'shipment_date' => $this->shipment_date,
                'customer_id' => $this->customer_id,
                'product_type_id' => $this->product_type_id,
                'vehicle_number' => $this->vehicle_number,
                'driver_name' => $this->driver_name,
                'destination' => $this->destination,
                'notes' => $this->notes,
                'status' => $this->status,
                'total_sacks' => $this->grandTotalSacks,
                'total_gross_kg' => $this->grandTotalGross,
                'total_tare_kg' => $this->grandTotalTare,
                'total_netto_kg' => $this->grandTotalNetto,
            ]);

            // Replace items
            $shipment->items()->delete();
            foreach ($this->items as $idx => $it) {
                DnShipmentItem::create([
                    'dn_shipment_id' => $shipment->id,
                    'batch_id' => !empty($it['batch_id']) ? $it['batch_id'] : null,
                    'batch_code' => !empty($it['batch_code']) ? $it['batch_code'] : null,
                    'item_no' => $idx + 1,
                    'origin' => $it['origin'],
                    'origin_code' => $it['origin_code'],
                    'material_type' => $it['material_type'] ?? 'Product',
                    'standard_sack_count' => (int) $it['standard_sack_count'],
                    'standard_gross_per_sack' => (float) $it['standard_gross_per_sack'],
                    'standard_tare_per_sack' => (float) $it['standard_tare_per_sack'],
                    'standard_netto_per_sack' => (float) $it['standard_netto_per_sack'],
                    'has_remnant' => !empty($it['has_remnant']),
                    'remnant_gross_kg' => (float) ($it['remnant_gross_kg'] ?? 0),
                    'remnant_tare_kg' => (float) ($it['remnant_tare_kg'] ?? 0),
                    'remnant_netto_kg' => (float) ($it['remnant_netto_kg'] ?? 0),
                    'total_sacks' => (int) $it['total_sacks'],
                    'total_gross_kg' => (float) $it['total_gross_kg'],
                    'total_tare_kg' => (float) $it['total_tare_kg'],
                    'total_netto_kg' => (float) $it['total_netto_kg'],
                ]);
            }
        });

        session()->flash('message', "DN Pengiriman {$this->dn_number} berhasil diperbarui!");
        $this->showEditModal = false;
        $this->resetForm();
    }

    /**
     * Open Preview Modal
     */
    public function openPreviewModal(int $id): void
    {
        $this->previewShipmentId = $id;
        $this->showPreviewModal = true;
    }

    /**
     * Confirm Delete
     */
    public function confirmDelete(int $id): void
    {
        $this->deleteShipmentId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Delete DN Shipment
     */
    public function deleteShipment(): void
    {
        if ($this->deleteShipmentId) {
            $s = DnShipment::find($this->deleteShipmentId);
            if ($s) {
                $num = $s->dn_number;
                $s->delete();
                session()->flash('message', "DN Pengiriman {$num} berhasil dihapus.");
            }
        }
        $this->showDeleteModal = false;
        $this->deleteShipmentId = null;
    }

    /**
     * Reset Form Fields
     */
    public function resetForm(): void
    {
        $this->editingShipmentId = null;
        $this->dn_number = '';
        $this->shipment_date = date('Y-m-d');
        $this->customer_id = null;
        $this->product_type_id = null;
        $this->vehicle_number = '';
        $this->driver_name = '';
        $this->destination = '';
        $this->notes = '';
        $this->status = 'Shipped';
        $this->items = [];
        $this->resetErrorBag();
    }

    /**
     * Compute Real-time Stock Information for a Lot's selected batch
     */
    public function getLotStockInfo(?int $batchId, int $currentLotSacks = 0, float $currentLotNetto = 0.0): ?array
    {
        if (! $batchId) return null;

        $batch = Batch::with(['dnShipmentItems'])->find($batchId);
        if (! $batch) return null;

        $producedStdSacks = (int) ($batch->separation_product_sack ?: 0);
        $hasRemnant = (float) ($batch->separation_product_remnant_kg ?: 0) > 0;
        $producedTotalSacks = $producedStdSacks + ($hasRemnant ? 1 : 0);
        $producedNettoKg = (float) ($batch->separation_product_kg ?: 0);

        // Calculate shipped sacks/netto from other shipments
        $shipmentItemsQuery = $batch->dnShipmentItems();
        if ($this->editingShipmentId) {
            $shipmentItemsQuery->where('dn_shipment_id', '!=', $this->editingShipmentId);
        }
        $otherShipments = $shipmentItemsQuery->get();

        $shippedSacks = (int) $otherShipments->sum('total_sacks');
        $shippedNettoKg = (float) $otherShipments->sum('total_netto_kg');

        $remainingSacksBefore = max(0, $producedTotalSacks - $shippedSacks);
        $remainingNettoBefore = max(0.0, round($producedNettoKg - $shippedNettoKg, 2));

        $remainingSacksAfter = max(0, $remainingSacksBefore - $currentLotSacks);
        $remainingNettoAfter = max(0.0, round($remainingNettoBefore - $currentLotNetto, 2));

        return [
            'produced_sacks' => $producedTotalSacks,
            'produced_netto_kg' => $producedNettoKg,
            'shipped_sacks' => $shippedSacks,
            'shipped_netto_kg' => $shippedNettoKg,
            'remaining_sacks_before' => $remainingSacksBefore,
            'remaining_netto_before' => $remainingNettoBefore,
            'remaining_sacks_after' => $remainingSacksAfter,
            'remaining_netto_after' => $remainingNettoAfter,
            'pack_type' => 'Karung',
        ];
    }

    public function render()
    {
        $query = DnShipment::with(['customer', 'productType', 'createdBy', 'items']);

        if ($this->search) {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('dn_number', 'like', $s)
                  ->orWhere('vehicle_number', 'like', $s)
                  ->orWhere('driver_name', 'like', $s)
                  ->orWhere('destination', 'like', $s)
                  ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', $s))
                  ->orWhereHas('items', fn ($iq) => $iq->where('origin', 'like', $s)->orWhere('origin_code', 'like', $s)->orWhere('batch_code', 'like', $s));
            });
        }

        if ($this->filterCustomerId) {
            $query->where('customer_id', $this->filterCustomerId);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterDateFrom) {
            $query->whereDate('shipment_date', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->whereDate('shipment_date', '<=', $this->filterDateTo);
        }

        $shipments = $query->orderBy('shipment_date', 'desc')->orderBy('id', 'desc')->paginate(10);

        $customers = Customer::orderBy('name')->get();
        $productTypes = ProductType::orderBy('name')->get();
        
        $originCodesMap = $this->getOriginCodesMap();
        $distinctOrigins = array_keys($originCodesMap);
        sort($distinctOrigins);

        // Batches for selection in lot items
        $availableBatches = Batch::with(['customer', 'origin', 'productType'])->orderBy('date_of_receipt', 'desc')->get();

        // Summary Aggregates
        $totalShipmentsCount = DnShipment::count();
        $totalSacksShipped = DnShipment::sum('total_sacks');
        $totalNettoShipped = DnShipment::sum('total_netto_kg');

        // Material types for selection
        $availableMaterialTypes = \App\Models\MaterialType::where('is_active', true)->orderBy('id')->get();
        if ($availableMaterialTypes->isEmpty()) {
            $availableMaterialTypes = collect([
                (object)['code' => 'Product', 'name' => 'Product'],
                (object)['code' => 'Bits / Stem', 'name' => 'Bits / Stem'],
                (object)['code' => 'Dust', 'name' => 'Dust'],
            ]);
        }

        return view('livewire.admin.dn-shipment-management', [
            'shipments' => $shipments,
            'customers' => $customers,
            'productTypes' => $productTypes,
            'distinctOrigins' => $distinctOrigins,
            'originCodesMap' => $originCodesMap,
            'availableBatches' => $availableBatches,
            'availableMaterialTypes' => $availableMaterialTypes,
            'totalShipmentsCount' => $totalShipmentsCount,
            'totalSacksShipped' => $totalSacksShipped,
            'totalNettoShipped' => $totalNettoShipped,
        ]);
    }
}
