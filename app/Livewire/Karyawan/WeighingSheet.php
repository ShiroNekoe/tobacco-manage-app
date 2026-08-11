<?php

namespace App\Livewire\Karyawan;

use App\Models\Batch;
use App\Models\BatchInterimSeparation;
use App\Models\WeighingItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WeighingSheet extends Component
{
    public ?int $batchId = null;

    // Sack items array: [['id' => 1, 'sack_number' => 1, 'gross_kg' => 0, 'tare_kg' => 2, 'netto_kg' => 0, 'remark' => '', 'created_by_user_id' => 1, 'is_locked_for_user' => false]]
    public array $items = [];

    // Separation outputs for current session
    public $product_kg_per_sack = 25.20;
    public $product_tare_per_sack = 0.20;
    public int $separation_product_sack = 0;
    public $separation_product_gross_kg = 0;
    public $separation_product_tare_kg = 0;
    public $separation_product_kg = 0;
    public $separation_product_remnant_gross_kg = 0;
    public $separation_product_remnant_tare_kg = 0;
    public $separation_product_remnant_kg = 0;
    public $separation_bits_stem_gross_kg = 0;
    public $separation_bits_stem_tare_kg = 0;
    public $separation_bits_stem_netto_kg = 0;
    public $separation_bits_stem_kg = 0;
    public array $bit_stem_items = [];
    public $separation_dust_gross_kg = 0;
    public $separation_dust_tare_kg = 0;
    public $separation_dust_netto_kg = 0;
    public $separation_dust_kg = 0;
    public array $dust_items = [];
    public $separation_waste_kg = 0;

    // Real-time dynamic totals
    public int $totalPack = 0;
    public $totalGrossKg = 0;
    public $totalTareKg = 0;
    public $totalNettoKg = 0;

    // Yield Percentages
    public $yieldProductPct = 0;
    public $yieldBitsStemPct = 0;
    public $yieldDustPct = 0;
    public $yieldWastePct = 0;

    public string $status = 'OPEN';
    public bool $showPauseModal = false;
    public bool $showThankYouModal = false;
    public string $pause_notes = '';
    public int $process_stage = 1;

    // Process 1 Separation Data
    public int $p1_product_sack = 0;
    public $p1_remnant_gross_kg = 0;
    public $p1_remnant_tare_kg = 0;
    public $p1_remnant_netto_kg = 0;
    public $p1_product_kg = 0;
    public array $p1_dust_items = [];
    public $p1_dust_netto_kg = 0;

    // Process 2 Separation Data
    public int $p2_product_sack = 0;
    public $p2_remnant_gross_kg = 0;
    public $p2_remnant_tare_kg = 0;
    public $p2_remnant_netto_kg = 0;
    public $p2_product_kg = 0;
    public array $p2_dust_items = [];
    public $p2_dust_netto_kg = 0;

    public function setProcessStage(int $stage)
    {
        $this->process_stage = in_array($stage, [1, 2]) ? $stage : 1;
    }

    public function addP1DustRow()
    {
        $this->p1_dust_items[] = ['gross_kg' => 0, 'tare_kg' => 0, 'netto_kg' => 0];
        $this->recalculateTotals();
    }

    public function removeP1DustRow($index)
    {
        if (isset($this->p1_dust_items[$index])) {
            unset($this->p1_dust_items[$index]);
            $this->p1_dust_items = array_values($this->p1_dust_items);
        }
        $this->recalculateTotals();
    }

    public function addP2DustRow()
    {
        $this->p2_dust_items[] = ['gross_kg' => 0, 'tare_kg' => 0, 'netto_kg' => 0];
        $this->recalculateTotals();
    }

    public function removeP2DustRow($index)
    {
        if (isset($this->p2_dust_items[$index])) {
            unset($this->p2_dust_items[$index]);
            $this->p2_dust_items = array_values($this->p2_dust_items);
        }
        $this->recalculateTotals();
    }

    public function mount(?int $batch_id = null)
    {
        if ($batch_id) {
            $this->selectBatch($batch_id);
        } else {
            // Default select latest active draft/open batch
            $active = Batch::whereIn('status', ['OPEN', 'ACTIVE', 'draft', 'WAITING'])->latest()->first();
            if ($active) {
                $this->selectBatch($active->id);
            }
        }
    }

    public function selectBatch($id = null)
    {
        if (empty($id)) {
            $this->batchId = null;
            $this->status = 'OPEN';
            $this->items = [];
            $this->recalculateTotals();
            return;
        }

        $id = (int) $id;
        $batch = Batch::with(['customer', 'deliveryNote', 'productType', 'origin', 'weighingItems.createdBy'])->find($id);
        if (! $batch) {
            $this->batchId = null;
            $this->status = 'OPEN';
            $this->items = [];
            $this->recalculateTotals();
            return;
        }

        $this->batchId = $batch->id;
        $this->status = $batch->status;
        $this->product_kg_per_sack = ($batch->product_kg_per_sack && (float)$batch->product_kg_per_sack > 0) ? (float)$batch->product_kg_per_sack : 25.20;
        $this->product_tare_per_sack = (isset($batch->product_tare_per_sack) && $batch->product_tare_per_sack !== null) ? (float)$batch->product_tare_per_sack : 0.20;

        $user = Auth::user();
        $currentUserId = $user ? $user->id : 0;

        // Load Process 1 & Process 2 Separation Data
        $p1Data = $batch->separation_p1_data ?? [];
        $p2Data = $batch->separation_p2_data ?? [];

        if (! empty($p1Data)) {
            $this->p1_product_sack = (int) ($p1Data['product_sack'] ?? 0);
            $this->p1_remnant_gross_kg = (float) ($p1Data['product_remnant_gross_kg'] ?? 0);
            $this->p1_remnant_tare_kg = (float) ($p1Data['product_remnant_tare_kg'] ?? 0);
            $this->p1_dust_items = ! empty($p1Data['dust_items']) && is_array($p1Data['dust_items']) ? $p1Data['dust_items'] : [];
        } else {
            $this->p1_product_sack = (int) ($batch->separation_product_sack ?? 0);
            $this->p1_remnant_gross_kg = (float) ($batch->separation_product_remnant_gross_kg ?? 0);
            $this->p1_remnant_tare_kg = (float) ($batch->separation_product_remnant_tare_kg ?? 0);
            $this->p1_dust_items = (! empty($batch->dust_items) && is_array($batch->dust_items)) ? $batch->dust_items : [
                ['gross_kg' => (float) ($batch->separation_dust_gross_kg ?? 0), 'tare_kg' => (float) ($batch->separation_dust_tare_kg ?? 0), 'netto_kg' => (float) ($batch->separation_dust_netto_kg ?? 0)]
            ];
        }

        if (! empty($p2Data)) {
            $this->p2_product_sack = (int) ($p2Data['product_sack'] ?? 0);
            $this->p2_remnant_gross_kg = (float) ($p2Data['product_remnant_gross_kg'] ?? 0);
            $this->p2_remnant_tare_kg = (float) ($p2Data['product_remnant_tare_kg'] ?? 0);
            $this->p2_dust_items = ! empty($p2Data['dust_items']) && is_array($p2Data['dust_items']) ? $p2Data['dust_items'] : [];
        } else {
            $this->p2_product_sack = 0;
            $this->p2_remnant_gross_kg = 0;
            $this->p2_remnant_tare_kg = 0;
            $this->p2_dust_items = [];
        }

        if (empty($this->p1_dust_items)) {
            $this->p1_dust_items = [
                ['gross_kg' => (float) ($batch->separation_dust_gross_kg ?? 0), 'tare_kg' => (float) ($batch->separation_dust_tare_kg ?? 0), 'netto_kg' => (float) ($batch->separation_dust_netto_kg ?? 0)]
            ];
        }

        // Bit Stem items
        if (! empty($batch->bit_stem_items) && is_array($batch->bit_stem_items)) {
            $this->bit_stem_items = $batch->bit_stem_items;
        } else {
            $this->bit_stem_items = [
                [
                    'gross_kg' => (float) ($batch->separation_bits_stem_gross_kg ?? 0),
                    'tare_kg' => (float) ($batch->separation_bits_stem_tare_kg ?? 0),
                    'netto_kg' => (float) ($batch->separation_bits_stem_netto_kg ?? $batch->separation_bits_stem_kg ?? 0),
                ]
            ];
        }

        $this->separation_waste_kg = (float) ($batch->separation_waste_kg ?? 0);

        // Auto detect process_stage: set to 2 if Bit Stem has non-zero values
        $hasBitStemValues = false;
        if (! empty($this->bit_stem_items)) {
            foreach ($this->bit_stem_items as $bsi) {
                if ((float)($bsi['gross_kg'] ?? 0) > 0 || (float)($bsi['netto_kg'] ?? 0) > 0) {
                    $hasBitStemValues = true;
                    break;
                }
            }
        }
        if ((float)($batch->separation_bits_stem_kg ?? 0) > 0 || $hasBitStemValues) {
            $this->process_stage = 2;
        } else {
            $this->process_stage = 1;
        }

        $this->items = [];
        $currentShift = $user ? ($user->shift ?? 'Shift 1') : 'Shift 1';
        $currentGroup = $user ? ($user->group ?? 'Group A') : 'Group A';
        $isCurrentUserAdmin = $user && ($user->isAdmin() || $user->isSupervisor());

        if ($batch->weighingItems->count() > 0) {
            foreach ($batch->weighingItems->sortBy('sack_number') as $wItem) {
                $createdById = $wItem->created_by_user_id;
                $creator = $wItem->createdBy;
                $itemShift = $wItem->shift ?: ($creator ? $creator->shift : null);
                
                $isCreatorAdmin = $creator && ($creator->isAdmin() || $creator->isSupervisor());
                $isPreLaunchRow = ($wItem->remark === 'MRL Pre-Launch');

                // Row is locked ONLY if:
                // 1. Current user is NOT Admin/Supervisor
                // 2. The row was NOT created by Admin/Supervisor
                // 3. The row is NOT marked 'MRL Pre-Launch'
                // 4. The row has a recorded shift that is DIFFERENT from current user's shift
                // 5. The row has a weight recorded (> 0)
                $isLockedForUser = (! $isCurrentUserAdmin)
                    && (! $isCreatorAdmin)
                    && (! $isPreLaunchRow)
                    && (! empty($itemShift) && $itemShift !== $currentShift)
                    && ((float) $wItem->gross_kg > 0 || (float) $wItem->tare_kg > 0);

                $this->items[] = [
                    'id' => $wItem->id,
                    'sack_number' => $wItem->sack_number,
                    'gross_kg' => (float) $wItem->gross_kg,
                    'tare_kg' => (float) $wItem->tare_kg,
                    'netto_kg' => (float) $wItem->netto_kg,
                    'remark' => $wItem->remark ?? 'Normal',
                    'created_by_user_id' => $createdById,
                    'creator_name' => $creator ? trim(explode('(', $creator->name)[0]) : null,
                    'shift' => $itemShift,
                    'group' => $wItem->group ?: ($creator ? $creator->group : null),
                    'is_locked_for_user' => $isLockedForUser,
                ];
            }
        } else {
            // Generate default 10 initial rows for speed entry
            $this->generateDefaultSackRows(10);
        }

        $this->recalculateTotals();
    }

    public function generateDefaultSackRows(int $count = 10)
    {
        $startNum = count($this->items) + 1;
        $defaultTare = (float) ($this->product_tare_per_sack ?? 0.20);
        for ($i = 0; $i < $count; $i++) {
            $this->items[] = [
                'id' => null,
                'sack_number' => $startNum + $i,
                'gross_kg' => 0,
                'tare_kg' => $defaultTare,
                'netto_kg' => 0,
                'remark' => 'Normal',
                'created_by_user_id' => null,
                'creator_name' => null,
                'shift' => null,
                'group' => null,
                'is_locked_for_user' => false,
            ];
        }
    }

    public function addSackRow()
    {
        $nextNum = count($this->items) + 1;
        $defaultTare = (float) ($this->product_tare_per_sack ?? 0.20);
        $this->items[] = [
            'id' => null,
            'sack_number' => $nextNum,
            'gross_kg' => 0,
            'tare_kg' => $defaultTare,
            'netto_kg' => 0,
            'remark' => 'Normal',
            'created_by_user_id' => null,
            'creator_name' => null,
            'shift' => null,
            'group' => null,
            'is_locked_for_user' => false,
        ];
        $this->recalculateTotals();
    }

    public function removeSackRow(int $index)
    {
        if (count($this->items) > 1 && ! $this->isReadOnly()) {
            if (! empty($this->items[$index]['is_locked_for_user'])) {
                return; // Cannot remove predecessor locked rows
            }

            unset($this->items[$index]);
            $this->items = array_values($this->items);

            foreach ($this->items as $idx => $it) {
                $this->items[$idx]['sack_number'] = $idx + 1;
            }
            $this->recalculateTotals();
        }
    }

    public function isReadOnly(): bool
    {
        return in_array($this->status, ['CLOSED', 'locked']);
    }

    public function updated($property = null)
    {
        if ($property === 'separation_product_sack') {
            $this->p1_product_sack = (int) $this->separation_product_sack;
        }
        if ($property === 'separation_product_remnant_gross_kg') {
            $this->p1_remnant_gross_kg = $this->separation_product_remnant_gross_kg;
        }
        if ($property === 'separation_product_remnant_tare_kg') {
            $this->p1_remnant_tare_kg = $this->separation_product_remnant_tare_kg;
        }
        $this->recalculateTotals();
    }

    public function updatedItems()
    {
        $this->recalculateTotals();
    }

    public function updatedSeparationProductSack()
    {
        $this->p1_product_sack = (int) $this->separation_product_sack;
        $this->recalculateTotals();
    }

    public function updatedSeparationProductKg()
    {
        $this->recalculateTotals();
    }

    public function updatedSeparationProductRemnantGrossKg()
    {
        $this->p1_remnant_gross_kg = $this->separation_product_remnant_gross_kg;
        $this->recalculateTotals();
    }

    public function updatedSeparationProductRemnantTareKg()
    {
        $this->p1_remnant_tare_kg = $this->separation_product_remnant_tare_kg;
        $this->recalculateTotals();
    }

    public function addBitStemRow()
    {
        $this->bit_stem_items[] = ['gross_kg' => 0, 'tare_kg' => 0, 'netto_kg' => 0];
        $this->recalculateTotals();
    }

    public function removeBitStemRow(int $index)
    {
        if (count($this->bit_stem_items) > 1) {
            unset($this->bit_stem_items[$index]);
            $this->bit_stem_items = array_values($this->bit_stem_items);
            $this->recalculateTotals();
        }
    }

    public function addDustRow()
    {
        $this->dust_items[] = ['gross_kg' => 0, 'tare_kg' => 0, 'netto_kg' => 0];
        $this->recalculateTotals();
    }

    public function removeDustRow(int $index)
    {
        if (count($this->dust_items) > 1) {
            unset($this->dust_items[$index]);
            $this->dust_items = array_values($this->dust_items);
            $this->recalculateTotals();
        }
    }

    public function updatedBitStemItems()
    {
        $this->recalculateTotals();
    }

    public function updatedP1ProductSack() { $this->recalculateTotals(); }
    public function updatedP1RemnantGrossKg() { $this->recalculateTotals(); }
    public function updatedP1RemnantTareKg() { $this->recalculateTotals(); }
    public function updatedP1DustItems() { $this->recalculateTotals(); }
    public function updatedP2ProductSack() { $this->recalculateTotals(); }
    public function updatedP2RemnantGrossKg() { $this->recalculateTotals(); }
    public function updatedP2RemnantTareKg() { $this->recalculateTotals(); }
    public function updatedP2DustItems() { $this->recalculateTotals(); }

    public function updatedProductKgPerSack()
    {
        $this->recalculateTotals();
    }

    public function updatedProductTarePerSack()
    {
        $newTare = $this->parseFloat($this->product_tare_per_sack);
        foreach ($this->items as $idx => $item) {
            if (empty($item['is_locked_for_user']) && (float)($item['gross_kg'] ?? 0) == 0) {
                $this->items[$idx]['tare_kg'] = $newTare;
            }
        }
        $this->recalculateTotals();
    }

    protected function parseFloat($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        if (is_string($value)) {
            $clean = str_replace(',', '.', trim($value));
            if (is_numeric($clean)) {
                return (float) $clean;
            }
        }
        return 0.0;
    }

    public function recalculateTotals()
    {
        $this->totalPack = 0;
        $this->totalGrossKg = 0;
        $this->totalTareKg = 0;
        $this->totalNettoKg = 0;

        foreach ($this->items as $idx => $item) {
            $gross = $this->parseFloat($item['gross_kg'] ?? 0);
            $tare = $this->parseFloat($item['tare_kg'] ?? 0);
            $netto = max(0, round($gross - $tare, 2));
            $this->items[$idx]['netto_kg'] = $netto;

            if ($gross > 0) {
                $this->totalPack++;
                $this->totalGrossKg += $gross;
                $this->totalTareKg += $tare;
                $this->totalNettoKg += $netto;
            }
        }

        $kgPerSack = $this->parseFloat(($this->product_kg_per_sack && (float)$this->product_kg_per_sack > 0) ? $this->product_kg_per_sack : 25.20);
        $tarePerSack = $this->parseFloat((isset($this->product_tare_per_sack) && (float)$this->product_tare_per_sack >= 0) ? $this->product_tare_per_sack : 0.20);

        // 1. P1 Produk Jadi calculation
        $p1Sack = (int) ($this->p1_product_sack ?? 0);
        $p1SackGross = max(0, round($p1Sack * $kgPerSack, 2));
        $p1SackTare = max(0, round($p1Sack * $tarePerSack, 2));

        $p1RemGross = $this->parseFloat($this->p1_remnant_gross_kg);
        $p1RemTare = $this->parseFloat($this->p1_remnant_tare_kg);
        $p1RemNetto = max(0, round($p1RemGross - $p1RemTare, 2));
        $this->p1_remnant_netto_kg = $p1RemNetto;

        $p1TotalGross = round($p1SackGross + $p1RemGross, 2);
        $p1TotalTare = round($p1SackTare + $p1RemTare, 2);
        $this->p1_product_kg = max(0, round($p1TotalGross - $p1TotalTare, 2));

        // 2. P2 Produk Jadi calculation
        $p2Sack = (int) ($this->p2_product_sack ?? 0);
        $p2SackGross = max(0, round($p2Sack * $kgPerSack, 2));
        $p2SackTare = max(0, round($p2Sack * $tarePerSack, 2));

        $p2RemGross = $this->parseFloat($this->p2_remnant_gross_kg);
        $p2RemTare = $this->parseFloat($this->p2_remnant_tare_kg);
        $p2RemNetto = max(0, round($p2RemGross - $p2RemTare, 2));
        $this->p2_remnant_netto_kg = $p2RemNetto;

        $p2TotalGross = round($p2SackGross + $p2RemGross, 2);
        $p2TotalTare = round($p2SackTare + $p2RemTare, 2);
        $this->p2_product_kg = max(0, round($p2TotalGross - $p2TotalTare, 2));

        // Combined Produk Jadi
        $this->separation_product_sack = $p1Sack + $p2Sack;
        $this->separation_product_remnant_gross_kg = round($p1RemGross + $p2RemGross, 2);
        $this->separation_product_remnant_tare_kg = round($p1RemTare + $p2RemTare, 2);
        $this->separation_product_remnant_kg = round($p1RemNetto + $p2RemNetto, 2);
        $this->separation_product_gross_kg = round($p1TotalGross + $p2TotalGross, 2);
        $this->separation_product_tare_kg = round($p1TotalTare + $p2TotalTare, 2);
        $this->separation_product_kg = round($this->p1_product_kg + $this->p2_product_kg, 2);

        // 3. Bit Stem multi-row calculations (P2)
        $stemGrossSum = 0;
        $stemTareSum = 0;
        $stemNettoSum = 0;

        foreach ($this->bit_stem_items as $idx => $item) {
            $g = $this->parseFloat($item['gross_kg'] ?? 0);
            $t = $this->parseFloat($item['tare_kg'] ?? 0);
            $n = max(0, round($g - $t, 2));
            $this->bit_stem_items[$idx]['netto_kg'] = $n;
            $stemGrossSum += $g;
            $stemTareSum += $t;
            $stemNettoSum += $n;
        }
        $this->separation_bits_stem_gross_kg = $stemGrossSum;
        $this->separation_bits_stem_tare_kg = $stemTareSum;
        $this->separation_bits_stem_netto_kg = $stemNettoSum;
        $this->separation_bits_stem_kg = $stemNettoSum;

        // 4. P1 Dust multi-row calculations
        $p1DustGross = 0; $p1DustTare = 0; $p1DustNetto = 0;
        foreach ($this->p1_dust_items as $idx => $item) {
            $g = $this->parseFloat($item['gross_kg'] ?? 0);
            $t = $this->parseFloat($item['tare_kg'] ?? 0);
            $n = max(0, round($g - $t, 2));
            $this->p1_dust_items[$idx]['netto_kg'] = $n;
            $p1DustGross += $g; $p1DustTare += $t; $p1DustNetto += $n;
        }
        $this->p1_dust_netto_kg = $p1DustNetto;

        // 5. P2 Dust multi-row calculations
        $p2DustGross = 0; $p2DustTare = 0; $p2DustNetto = 0;
        foreach ($this->p2_dust_items as $idx => $item) {
            $g = $this->parseFloat($item['gross_kg'] ?? 0);
            $t = $this->parseFloat($item['tare_kg'] ?? 0);
            $n = max(0, round($g - $t, 2));
            $this->p2_dust_items[$idx]['netto_kg'] = $n;
            $p2DustGross += $g; $p2DustTare += $t; $p2DustNetto += $n;
        }
        $this->p2_dust_netto_kg = $p2DustNetto;

        // Combined Dust
        $this->dust_items = array_merge($this->p1_dust_items, $this->p2_dust_items);
        $this->separation_dust_gross_kg = round($p1DustGross + $p2DustGross, 2);
        $this->separation_dust_tare_kg = round($p1DustTare + $p2DustTare, 2);
        $this->separation_dust_netto_kg = round($p1DustNetto + $p2DustNetto, 2);
        $this->separation_dust_kg = $this->separation_dust_netto_kg;

        // 6. Uncountable Waste calculation
        $outputsSum = $this->separation_product_kg + $stemNettoSum + $this->separation_dust_netto_kg;
        if ($this->totalNettoKg > 0) {
            $this->separation_waste_kg = max(0, round($this->totalNettoKg - $outputsSum, 2));

            $this->yieldProductPct = round(($this->separation_product_kg / $this->totalNettoKg) * 100, 2);
            $this->yieldBitsStemPct = round(($stemNettoSum / $this->totalNettoKg) * 100, 2);
            $this->yieldDustPct = round(($this->separation_dust_netto_kg / $this->totalNettoKg) * 100, 2);
            $this->yieldWastePct = max(0, round(100.00 - ($this->yieldProductPct + $this->yieldBitsStemPct + $this->yieldDustPct), 2));
        } else {
            $this->separation_waste_kg = 0;
            $this->yieldProductPct = 0;
            $this->yieldBitsStemPct = 0;
            $this->yieldDustPct = 0;
            $this->yieldWastePct = 0;
        }
    }

    public function saveDraft()
    {
        $user = Auth::user();
        if ($user && $user->isCustomer()) {
            abort(403, 'Customer tidak memiliki akses untuk memasukkan data timbangan.');
        }

        $this->saveBatch('draft');
        session()->flash('message', 'Data timbangan berhasil disimpan (Draft). Baris timbangan Anda terkunci untuk shift berikutnya.');
    }

    public function openPauseModal()
    {
        $this->submitPauseAndInterimReport();
    }

    public function submitPauseAndInterimReport()
    {
        if (! $this->batchId) return;

        $user = Auth::user();
        $batch = Batch::findOrFail($this->batchId);

        $this->recalculateTotals();

        // Validation: Karyawan MUST fill out Separation Results before finishing shift
        if ($this->separation_product_sack <= 0 && (float) $this->separation_product_kg <= 0) {
            $this->addError('separation_product_sack', 'Harap isi Form Laporan Hasil Pemisahan (Produk Jadi / Rajangan) terlebih dahulu sebelum menyelesaikan shift.');
            session()->flash('error', '⚠️ Harap isi Form Laporan Hasil Pemisahan (Produk Jadi / Rajangan) terlebih dahulu sebelum menyelesaikan shift!');
            $this->showPauseModal = false;
            $this->dispatch('scroll-to-separation-form');
            return;
        }

        // Save batch items first so ownership and modified rows are persisted in DB
        $this->saveBatch('ACTIVE');

        // Count sacks processed specifically by this worker in this shift
        $workerSacksProcessedCount = WeighingItem::where('batch_id', $batch->id)
            ->where('created_by_user_id', Auth::id())
            ->where('gross_kg', '>', 0)
            ->count();

        // If 0 (e.g. pre-launch rows or admin), fallback to total active sacks in current session
        if ($workerSacksProcessedCount === 0) {
            $workerSacksProcessedCount = collect($this->items)->filter(fn($it) => (float)($it['gross_kg'] ?? 0) > 0)->count();
        }

        $p1Payload = [
            'product_sack' => $this->p1_product_sack,
            'product_remnant_gross_kg' => $this->p1_remnant_gross_kg,
            'product_remnant_tare_kg' => $this->p1_remnant_tare_kg,
            'product_remnant_netto_kg' => $this->p1_remnant_netto_kg,
            'product_kg' => $this->p1_product_kg,
            'dust_items' => $this->p1_dust_items,
            'dust_netto_kg' => $this->p1_dust_netto_kg,
        ];

        $p2Payload = [
            'product_sack' => $this->p2_product_sack,
            'product_remnant_gross_kg' => $this->p2_remnant_gross_kg,
            'product_remnant_tare_kg' => $this->p2_remnant_tare_kg,
            'product_remnant_netto_kg' => $this->p2_remnant_netto_kg,
            'product_kg' => $this->p2_product_kg,
            'bit_stem_items' => $this->bit_stem_items,
            'dust_items' => $this->p2_dust_items,
            'dust_netto_kg' => $this->p2_dust_netto_kg,
        ];

        // Log interim separation report for shift/group directly
        BatchInterimSeparation::create([
            'batch_id' => $batch->id,
            'user_id' => Auth::id(),
            'shift' => $user->shift ?? 'Shift 1',
            'group' => $user->group ?? 'Group A',
            'product_tare_per_sack' => $this->product_tare_per_sack,
            'separation_product_kg' => $this->separation_product_kg,
            'separation_product_sack' => $this->separation_product_sack,
            'separation_product_gross_kg' => $this->separation_product_gross_kg,
            'separation_product_tare_kg' => $this->separation_product_tare_kg,
            'separation_product_remnant_gross_kg' => $this->separation_product_remnant_gross_kg,
            'separation_product_remnant_tare_kg' => $this->separation_product_remnant_tare_kg,
            'separation_product_remnant_kg' => $this->separation_product_remnant_kg,
            'separation_bits_stem_kg' => $this->separation_bits_stem_kg,
            'separation_bits_stem_gross_kg' => $this->separation_bits_stem_gross_kg,
            'separation_bits_stem_tare_kg' => $this->separation_bits_stem_tare_kg,
            'separation_bits_stem_netto_kg' => $this->separation_bits_stem_netto_kg,
            'bit_stem_items' => $this->bit_stem_items,
            'separation_dust_kg' => $this->separation_dust_kg,
            'separation_dust_gross_kg' => $this->separation_dust_gross_kg,
            'separation_dust_tare_kg' => $this->separation_dust_tare_kg,
            'separation_dust_netto_kg' => $this->separation_dust_netto_kg,
            'dust_items' => $this->dust_items,
            'separation_p1_data' => $p1Payload,
            'separation_p2_data' => $p2Payload,
            'separation_waste_kg' => $this->separation_waste_kg,
            'sacks_processed_count' => $workerSacksProcessedCount,
            'notes' => $this->pause_notes ?: 'Selesai Shift Kerja (Done Shift)',
        ]);

        $this->showPauseModal = false;
        $this->showThankYouModal = true;
        session()->flash('message', '🛑 Laporan Selesai Shift berhasil dicatat & data terdeteksi di Live Tracking Admin!');
    }

    public function lockData()
    {
        $user = Auth::user();
        if ($user && $user->isCustomer()) {
            abort(403, 'Customer tidak memiliki akses untuk memasukkan data timbangan.');
        }

        $this->saveBatch('locked');
        session()->flash('message', 'Data timbangan berhasil dikirim & dikunci (Locked)!');
    }

    protected function saveBatch(string $targetStatus)
    {
        if (! $this->batchId) {
            $this->addError('batchId', 'Pilih Batch terlebih dahulu.');
            return;
        }

        $batch = Batch::find($this->batchId);
        if (! $batch) {
            $this->addError('batchId', 'Batch tidak ditemukan.');
            return;
        }

        $user = Auth::user();
        $currentUserId = $user ? $user->id : null;
        $currentShift = $user ? ($user->shift ?? 'Shift 1') : 'Shift 1';
        $currentGroup = $user ? ($user->group ?? 'Group A') : 'Group A';
        $isCurrentUserAdmin = $user && ($user->isAdmin() || $user->isSupervisor());

        if ($batch->isLocked() && ! $isCurrentUserAdmin) {
            abort(403, 'Data telah dikunci.');
        }

        $this->recalculateTotals();

        $discrepancy = round(((float) $batch->dn_netto_weight) - $this->totalNettoKg, 2);

        $p1Payload = [
            'product_sack' => $this->p1_product_sack,
            'product_remnant_gross_kg' => $this->p1_remnant_gross_kg,
            'product_remnant_tare_kg' => $this->p1_remnant_tare_kg,
            'product_remnant_netto_kg' => $this->p1_remnant_netto_kg,
            'product_kg' => $this->p1_product_kg,
            'dust_items' => $this->p1_dust_items,
            'dust_netto_kg' => $this->p1_dust_netto_kg,
        ];

        $p2Payload = [
            'product_sack' => $this->p2_product_sack,
            'product_remnant_gross_kg' => $this->p2_remnant_gross_kg,
            'product_remnant_tare_kg' => $this->p2_remnant_tare_kg,
            'product_remnant_netto_kg' => $this->p2_remnant_netto_kg,
            'product_kg' => $this->p2_product_kg,
            'bit_stem_items' => $this->bit_stem_items,
            'dust_items' => $this->p2_dust_items,
            'dust_netto_kg' => $this->p2_dust_netto_kg,
        ];

        $batch->update([
            'product_kg_per_sack' => $this->product_kg_per_sack,
            'product_tare_per_sack' => $this->product_tare_per_sack,
            'mrl_total_pack' => $this->totalPack,
            'mrl_gross_weight' => $this->totalGrossKg,
            'mrl_tare_weight' => $this->totalTareKg,
            'mrl_netto_weight' => $this->totalNettoKg,
            'discrepancy_dn_vs_mrl_kg' => $discrepancy,
            'separation_product_kg' => $this->separation_product_kg,
            'separation_product_sack' => $this->separation_product_sack,
            'separation_product_gross_kg' => $this->separation_product_gross_kg,
            'separation_product_tare_kg' => $this->separation_product_tare_kg,
            'separation_product_remnant_gross_kg' => $this->separation_product_remnant_gross_kg,
            'separation_product_remnant_tare_kg' => $this->separation_product_remnant_tare_kg,
            'separation_product_remnant_kg' => $this->separation_product_remnant_kg,
            'separation_bits_stem_kg' => $this->separation_bits_stem_kg,
            'separation_bits_stem_gross_kg' => $this->separation_bits_stem_gross_kg,
            'separation_bits_stem_tare_kg' => $this->separation_bits_stem_tare_kg,
            'separation_bits_stem_netto_kg' => $this->separation_bits_stem_netto_kg,
            'bit_stem_items' => $this->bit_stem_items,
            'separation_dust_kg' => $this->separation_dust_kg,
            'separation_dust_gross_kg' => $this->separation_dust_gross_kg,
            'separation_dust_tare_kg' => $this->separation_dust_tare_kg,
            'separation_dust_netto_kg' => $this->separation_dust_netto_kg,
            'dust_items' => $this->dust_items,
            'separation_p1_data' => $p1Payload,
            'separation_p2_data' => $p2Payload,
            'separation_waste_kg' => $this->separation_waste_kg,
            'yield_product_pct' => $this->yieldProductPct,
            'yield_bits_stem_pct' => $this->yieldBitsStemPct,
            'yield_dust_pct' => $this->yieldDustPct,
            'yield_waste_pct' => $this->yieldWastePct,
            'status' => $targetStatus,
            'start_time' => $batch->start_time ?: Carbon::now(),
            'last_saved_at' => Carbon::now(),
            'last_saved_by_user_id' => $currentUserId,
            'locked_at' => in_array($targetStatus, ['CLOSED', 'locked']) ? Carbon::now() : null,
        ]);

        // Sync Weighing Items DB while preserving predecessor lock rules
        foreach ($this->items as $it) {
            if ((float) ($it['gross_kg'] ?? 0) > 0 || (float) ($it['tare_kg'] ?? 0) > 0) {
                if (! empty($it['id'])) {
                    $existing = WeighingItem::with('createdBy')->find($it['id']);
                    
                    $isExistingCreatedByAdmin = $existing && $existing->createdBy && ($existing->createdBy->isAdmin() || $existing->createdBy->isSupervisor());
                    $isExistingPreLaunch = $existing && $existing->remark === 'MRL Pre-Launch';
                    $existingShift = $existing ? ($existing->shift ?: ($existing->createdBy ? $existing->createdBy->shift : null)) : null;

                    // Reject backend edit ONLY if:
                    // 1. Current user is NOT Admin/Supervisor
                    // 2. Row was NOT created by Admin/Supervisor
                    // 3. Row is NOT MRL Pre-Launch
                    // 4. Row belongs to a DIFFERENT shift
                    if (! $isCurrentUserAdmin 
                        && $existing 
                        && ! empty($existingShift) 
                        && $existingShift !== $currentShift 
                        && ! $isExistingCreatedByAdmin 
                        && ! $isExistingPreLaunch
                    ) {
                        continue; // Keep original predecessor worker data
                    }

                    $isRowModifiedByWorker = $existing && (
                        abs((float)$it['gross_kg'] - (float)$existing->gross_kg) > 0.001 ||
                        abs((float)$it['tare_kg'] - (float)$existing->tare_kg) > 0.001 ||
                        ($it['remark'] !== 'MRL Pre-Launch' && $it['remark'] !== $existing->remark)
                    );

                    $targetRemark = $it['remark'] ?? 'Normal';
                    if ($isExistingPreLaunch && ! $isRowModifiedByWorker) {
                        $targetRemark = 'MRL Pre-Launch';
                    } elseif ($targetRemark === 'MRL Pre-Launch' && $isRowModifiedByWorker) {
                        $targetRemark = 'Normal';
                    }

                    $targetGross = $isCurrentUserAdmin ? $it['gross_kg'] : ($existing ? $existing->gross_kg : 0);
                    $targetTare = $it['tare_kg'];
                    $targetNetto = max(0, round((float)$targetGross - (float)$targetTare, 2));

                    $updateData = [
                        'gross_kg' => $targetGross,
                        'tare_kg' => $targetTare,
                        'netto_kg' => $targetNetto,
                        'remark' => $targetRemark,
                    ];

                    // Transfer/update ownership to active KARYAWAN worker ONLY IF row was actually modified by this worker
                    if ($user && $user->isKaryawan()) {
                        if ($isRowModifiedByWorker || ! $isExistingPreLaunch) {
                            $updateData['created_by_user_id'] = $currentUserId;
                            $updateData['shift'] = $currentShift;
                            $updateData['group'] = $currentGroup;
                        }
                    }

                    $existing->update($updateData);
                } else {
                    $targetGross = $isCurrentUserAdmin ? $it['gross_kg'] : 0;
                    $targetTare = $it['tare_kg'];
                    $targetNetto = max(0, round((float)$targetGross - (float)$targetTare, 2));

                    WeighingItem::create([
                        'batch_id' => $batch->id,
                        'sack_number' => $it['sack_number'],
                        'gross_kg' => $targetGross,
                        'tare_kg' => $targetTare,
                        'netto_kg' => $targetNetto,
                        'remark' => ($it['remark'] === 'MRL Pre-Launch') ? 'Normal' : ($it['remark'] ?? 'Normal'),
                        'created_by_user_id' => $currentUserId,
                        'shift' => $user ? ($user->shift ?? 'Shift 1') : 'Shift 1',
                        'group' => $user ? ($user->group ?? 'Group A') : 'Group A',
                    ]);
                }
            }
        }

        $this->status = $targetStatus;
    }

    public function render()
    {
        $activeBatches = Batch::with(['customer', 'productType', 'origin'])
            ->where(function ($query) {
                $query->whereIn('status', ['OPEN', 'ACTIVE', 'draft', 'WAITING']);
                if ($this->batchId) {
                    $query->orWhere('id', $this->batchId);
                }
            })
            ->latest()
            ->get();

        $selectedBatch = $this->batchId ? Batch::with(['customer', 'deliveryNote', 'productType', 'origin'])->find($this->batchId) : null;

        return view('livewire.karyawan.weighing-sheet', compact('activeBatches', 'selectedBatch'));
    }
}
