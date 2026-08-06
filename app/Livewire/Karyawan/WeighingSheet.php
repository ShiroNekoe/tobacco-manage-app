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
    public string $pause_notes = '';

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
        $batch = Batch::with(['customer', 'deliveryNote', 'productType', 'origin', 'weighingItems'])->find($id);
        if (! $batch) {
            $this->batchId = null;
            $this->status = 'OPEN';
            $this->items = [];
            $this->recalculateTotals();
            return;
        }

        $this->batchId = $batch->id;
        $this->status = $batch->status;

        $user = Auth::user();
        $currentUserId = $user ? $user->id : 0;

        // Reset separation form for current session if new user/resume
        $this->product_kg_per_sack = (float) (($batch->product_kg_per_sack && (float)$batch->product_kg_per_sack > 0) ? $batch->product_kg_per_sack : 25.20);
        $this->product_tare_per_sack = (float) (isset($batch->product_tare_per_sack) ? $batch->product_tare_per_sack : 0.20);
        $this->separation_product_sack = (int) ($batch->separation_product_sack ?? 0);
        $this->separation_product_gross_kg = (float) ($batch->separation_product_gross_kg ?? 0);
        $this->separation_product_tare_kg = (float) ($batch->separation_product_tare_kg ?? 0);
        $this->separation_product_kg = (float) ($batch->separation_product_kg ?? 0);
        $this->separation_product_remnant_gross_kg = (float) ($batch->separation_product_remnant_gross_kg ?? 0);
        $this->separation_product_remnant_tare_kg = (float) ($batch->separation_product_remnant_tare_kg ?? 0);
        $this->separation_product_remnant_kg = (float) ($batch->separation_product_remnant_kg ?? 0);

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

        // Dust items
        if (! empty($batch->dust_items) && is_array($batch->dust_items)) {
            $this->dust_items = $batch->dust_items;
        } else {
            $this->dust_items = [
                [
                    'gross_kg' => (float) ($batch->separation_dust_gross_kg ?? 0),
                    'tare_kg' => (float) ($batch->separation_dust_tare_kg ?? 0),
                    'netto_kg' => (float) ($batch->separation_dust_netto_kg ?? $batch->separation_dust_kg ?? 0),
                ]
            ];
        }

        $this->separation_waste_kg = (float) ($batch->separation_waste_kg ?? 0);

        $this->items = [];
        if ($batch->weighingItems->count() > 0) {
            foreach ($batch->weighingItems->sortBy('sack_number') as $wItem) {
                $createdById = $wItem->created_by_user_id;
                // Row is locked for current user if created by a different predecessor worker
                $isLockedForUser = ($createdById !== null && $createdById !== $currentUserId && (float)$wItem->gross_kg > 0);

                $this->items[] = [
                    'id' => $wItem->id,
                    'sack_number' => $wItem->sack_number,
                    'gross_kg' => (float) $wItem->gross_kg,
                    'tare_kg' => (float) $wItem->tare_kg,
                    'netto_kg' => (float) $wItem->netto_kg,
                    'remark' => $wItem->remark ?? 'Normal',
                    'created_by_user_id' => $createdById,
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

            foreach ($this->items as $idx => &$it) {
                $it['sack_number'] = $idx + 1;
            }
            $this->recalculateTotals();
        }
    }

    public function isReadOnly(): bool
    {
        return in_array($this->status, ['CLOSED', 'locked']);
    }

    public function updatedItems()
    {
        $this->recalculateTotals();
    }

    public function updatedSeparationProductSack()
    {
        $this->recalculateTotals();
    }

    public function updatedSeparationProductKg()
    {
        $this->recalculateTotals();
    }

    public function updatedSeparationProductRemnantGrossKg()
    {
        $this->recalculateTotals();
    }

    public function updatedSeparationProductRemnantTareKg()
    {
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

    public function updatedDustItems()
    {
        $this->recalculateTotals();
    }

    public function updatedProductTarePerSack()
    {
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

        foreach ($this->items as &$item) {
            $gross = $this->parseFloat($item['gross_kg'] ?? 0);
            $tare = $this->parseFloat($item['tare_kg'] ?? 0);
            $netto = max(0, round($gross - $tare, 2));
            $item['netto_kg'] = $netto;

            if ($gross > 0) {
                $this->totalPack++;
                $this->totalGrossKg += $gross;
                $this->totalTareKg += $tare;
                $this->totalNettoKg += $netto;
            }
        }

        // 1. Produk Jadi calculation: Sack (Gross & Tare per Sack) + Remnant (Gross & Tare in Kg)
        $prodSack = (int) ($this->separation_product_sack ?? 0);
        $kgPerSack = $this->parseFloat(($this->product_kg_per_sack && (float)$this->product_kg_per_sack > 0) ? $this->product_kg_per_sack : 25.20);
        $tarePerSack = $this->parseFloat((isset($this->product_tare_per_sack) && (float)$this->product_tare_per_sack >= 0) ? $this->product_tare_per_sack : 0.20);
        
        $prodSackGross = max(0, round($prodSack * $kgPerSack, 2));
        $prodSackTare = max(0, round($prodSack * $tarePerSack, 2));

        $remGross = $this->parseFloat($this->separation_product_remnant_gross_kg);
        $remTare = $this->parseFloat($this->separation_product_remnant_tare_kg);
        $remNetto = max(0, round($remGross - $remTare, 2));
        $this->separation_product_remnant_kg = $remNetto;

        $totalProdGross = round($prodSackGross + $remGross, 2);
        $totalProdTare = round($prodSackTare + $remTare, 2);
        $prodNetto = max(0, round($totalProdGross - $totalProdTare, 2));

        $this->separation_product_gross_kg = $totalProdGross;
        $this->separation_product_tare_kg = $totalProdTare;
        $this->separation_product_kg = $prodNetto;

        // 2. Bit Stem multi-row calculations
        $stemGrossSum = 0;
        $stemTareSum = 0;
        $stemNettoSum = 0;

        foreach ($this->bit_stem_items as &$item) {
            $g = $this->parseFloat($item['gross_kg'] ?? 0);
            $t = $this->parseFloat($item['tare_kg'] ?? 0);
            $n = max(0, round($g - $t, 2));
            $item['netto_kg'] = $n;
            $stemGrossSum += $g;
            $stemTareSum += $t;
            $stemNettoSum += $n;
        }
        $this->separation_bits_stem_gross_kg = $stemGrossSum;
        $this->separation_bits_stem_tare_kg = $stemTareSum;
        $this->separation_bits_stem_netto_kg = $stemNettoSum;
        $this->separation_bits_stem_kg = $stemNettoSum;

        // 3. Debu multi-row calculations
        $dustGrossSum = 0;
        $dustTareSum = 0;
        $dustNettoSum = 0;

        foreach ($this->dust_items as &$item) {
            $g = $this->parseFloat($item['gross_kg'] ?? 0);
            $t = $this->parseFloat($item['tare_kg'] ?? 0);
            $n = max(0, round($g - $t, 2));
            $item['netto_kg'] = $n;
            $dustGrossSum += $g;
            $dustTareSum += $t;
            $dustNettoSum += $n;
        }
        $this->separation_dust_gross_kg = $dustGrossSum;
        $this->separation_dust_tare_kg = $dustTareSum;
        $this->separation_dust_netto_kg = $dustNettoSum;
        $this->separation_dust_kg = $dustNettoSum;

        // 4. Uncountable Waste calculation: Total Netto Input - (Netto Produk + Netto Bit Stem + Netto Debu)
        $outputsSum = $prodNetto + $stemNettoSum + $dustNettoSum;

        if ($this->totalNettoKg > 0) {
            $this->separation_waste_kg = max(0, round($this->totalNettoKg - $outputsSum, 2));

            $this->yieldProductPct = round(($prodNetto / $this->totalNettoKg) * 100, 2);
            $this->yieldBitsStemPct = round(($stemNettoSum / $this->totalNettoKg) * 100, 2);
            $this->yieldDustPct = round(($dustNettoSum / $this->totalNettoKg) * 100, 2);
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
        $this->showPauseModal = true;
    }

    public function submitPauseAndInterimReport()
    {
        if (! $this->batchId) return;

        $user = Auth::user();
        $batch = Batch::findOrFail($this->batchId);

        $this->recalculateTotals();

        // Log interim separation report for shift/group
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
            'separation_waste_kg' => $this->separation_waste_kg,
            'sacks_processed_count' => $this->totalPack,
            'notes' => $this->pause_notes ?: 'Jeda / Pause Shift Kerja',
        ]);

        $this->saveBatch('ACTIVE');
        $this->showPauseModal = false;
        session()->flash('message', 'Laporan Pemisahan Interim berhasil dicatat & sesi kerja dihentikan sementara (Paused).');
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

        $batch = Batch::findOrFail($this->batchId);
        $user = Auth::user();
        $currentUserId = $user ? $user->id : null;

        if ($batch->isLocked() && ! (Auth::user()->isAdmin() || Auth::user()->isSupervisor())) {
            abort(403, 'Data telah dikunci.');
        }

        $this->recalculateTotals();

        $discrepancy = round(((float) $batch->dn_netto_weight) - $this->totalNettoKg, 2);

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
            if ((float) ($it['gross_kg'] ?? 0) > 0) {
                if (! empty($it['id'])) {
                    $existing = WeighingItem::find($it['id']);
                    // Predecessor lock check: reject backend edit if created by another user
                    if ($existing && $existing->created_by_user_id && $existing->created_by_user_id !== $currentUserId) {
                        continue; // Keep original predecessor data
                    }

                    $existing->update([
                        'gross_kg' => $it['gross_kg'],
                        'tare_kg' => $it['tare_kg'],
                        'netto_kg' => $it['netto_kg'],
                        'remark' => $it['remark'] ?? 'Normal',
                    ]);
                } else {
                    WeighingItem::create([
                        'batch_id' => $batch->id,
                        'sack_number' => $it['sack_number'],
                        'gross_kg' => $it['gross_kg'],
                        'tare_kg' => $it['tare_kg'],
                        'netto_kg' => $it['netto_kg'],
                        'remark' => $it['remark'] ?? 'Normal',
                        'created_by_user_id' => $currentUserId,
                        'shift' => $user->shift ?? 'Shift 1',
                        'group' => $user->group ?? 'Group A',
                    ]);
                }
            }
        }

        $this->status = $targetStatus;
    }

    public function render()
    {
        $activeBatches = Batch::with(['customer', 'productType', 'origin'])
            ->latest()
            ->get();

        $selectedBatch = $this->batchId ? Batch::with(['customer', 'deliveryNote', 'productType', 'origin'])->find($this->batchId) : null;

        return view('livewire.karyawan.weighing-sheet', compact('activeBatches', 'selectedBatch'));
    }
}
