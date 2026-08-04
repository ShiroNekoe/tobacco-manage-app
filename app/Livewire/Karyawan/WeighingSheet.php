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
    public float $product_kg_per_sack = 20.00;
    public int $separation_product_sack = 0;
    public $separation_product_kg = 0;
    public float $separation_bits_stem_gross_kg = 0;
    public float $separation_bits_stem_tare_kg = 0;
    public float $separation_bits_stem_netto_kg = 0;
    public $separation_bits_stem_kg = 0;
    public float $separation_dust_gross_kg = 0;
    public float $separation_dust_tare_kg = 0;
    public float $separation_dust_netto_kg = 0;
    public $separation_dust_kg = 0;
    public $separation_waste_kg = 0;

    // Real-time dynamic totals
    public int $totalPack = 0;
    public float $totalGrossKg = 0;
    public float $totalTareKg = 0;
    public float $totalNettoKg = 0;

    // Yield Percentages
    public float $yieldProductPct = 0;
    public float $yieldBitsStemPct = 0;
    public float $yieldDustPct = 0;
    public float $yieldWastePct = 0;

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

    public function selectBatch(int $id)
    {
        $batch = Batch::with(['customer', 'deliveryNote', 'productType', 'origin', 'weighingItems'])->findOrFail($id);
        $this->batchId = $batch->id;
        $this->status = $batch->status;

        $user = Auth::user();
        $currentUserId = $user ? $user->id : 0;

        // Reset separation form for current session if new user/resume
        $this->product_kg_per_sack = (float) (($batch->product_kg_per_sack && (float)$batch->product_kg_per_sack > 0) ? $batch->product_kg_per_sack : 20.00);
        $this->separation_product_sack = (int) ($batch->separation_product_sack ?? 0);
        $this->separation_product_kg = (float) ($batch->separation_product_kg ?? 0);

        if ($this->separation_product_sack > 0 && $this->product_kg_per_sack > 0) {
            $this->separation_product_kg = max(0, round($this->separation_product_sack * $this->product_kg_per_sack, 2));
        } elseif ($this->separation_product_kg > 0 && $this->separation_product_sack == 0 && $this->product_kg_per_sack > 0) {
            $this->separation_product_sack = (int) round($this->separation_product_kg / $this->product_kg_per_sack);
        }

        $this->separation_bits_stem_gross_kg = (float) ($batch->separation_bits_stem_gross_kg ?? 0);
        $this->separation_bits_stem_tare_kg = (float) ($batch->separation_bits_stem_tare_kg ?? 0);
        $this->separation_bits_stem_netto_kg = (float) ($batch->separation_bits_stem_netto_kg ?? $batch->separation_bits_stem_kg ?? 0);
        if ($this->separation_bits_stem_gross_kg == 0 && $this->separation_bits_stem_netto_kg > 0) {
            $this->separation_bits_stem_gross_kg = $this->separation_bits_stem_netto_kg;
        }
        $this->separation_bits_stem_kg = $this->separation_bits_stem_netto_kg;

        $this->separation_dust_gross_kg = (float) ($batch->separation_dust_gross_kg ?? 0);
        $this->separation_dust_tare_kg = (float) ($batch->separation_dust_tare_kg ?? 0);
        $this->separation_dust_netto_kg = (float) ($batch->separation_dust_netto_kg ?? $batch->separation_dust_kg ?? 0);
        if ($this->separation_dust_gross_kg == 0 && $this->separation_dust_netto_kg > 0) {
            $this->separation_dust_gross_kg = $this->separation_dust_netto_kg;
        }
        $this->separation_dust_kg = $this->separation_dust_netto_kg;

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
        for ($i = 0; $i < $count; $i++) {
            $this->items[] = [
                'id' => null,
                'sack_number' => $startNum + $i,
                'gross_kg' => 0,
                'tare_kg' => 2.0,
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
        $this->items[] = [
            'id' => null,
            'sack_number' => $nextNum,
            'gross_kg' => 0,
            'tare_kg' => 2.0,
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

    public function updatedSeparationBitsStemGrossKg()
    {
        $this->recalculateTotals();
    }

    public function updatedSeparationBitsStemTareKg()
    {
        $this->recalculateTotals();
    }

    public function updatedSeparationBitsStemKg()
    {
        $this->recalculateTotals();
    }

    public function updatedSeparationDustGrossKg()
    {
        $this->recalculateTotals();
    }

    public function updatedSeparationDustTareKg()
    {
        $this->recalculateTotals();
    }

    public function updatedSeparationDustKg()
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

        // 1. Produk Jadi conversion: Sack -> Kg
        $prodSack = (int) ($this->separation_product_sack ?? 0);
        $kgPerSack = $this->parseFloat(($this->product_kg_per_sack && (float)$this->product_kg_per_sack > 0) ? $this->product_kg_per_sack : 20.00);
        $prodKg = max(0, round($prodSack * $kgPerSack, 2));
        $this->separation_product_kg = $prodKg;

        // 2. Bit Stem Netto calculation: Gross - Tare
        $stemGross = $this->parseFloat($this->separation_bits_stem_gross_kg ?? 0);
        $stemTare = $this->parseFloat($this->separation_bits_stem_tare_kg ?? 0);
        $stemNetto = max(0, round($stemGross - $stemTare, 2));
        $this->separation_bits_stem_netto_kg = $stemNetto;
        $this->separation_bits_stem_kg = $stemNetto;

        // 3. Debu Netto calculation: Gross - Tare
        $dustGross = $this->parseFloat($this->separation_dust_gross_kg ?? 0);
        $dustTare = $this->parseFloat($this->separation_dust_tare_kg ?? 0);
        $dustNetto = max(0, round($dustGross - $dustTare, 2));
        $this->separation_dust_netto_kg = $dustNetto;
        $this->separation_dust_kg = $dustNetto;

        // 4. Uncountable Waste calculation: Total Netto - (Produk + Bit Stem + Debu)
        $outputsSum = $prodKg + $stemNetto + $dustNetto;

        if ($this->totalNettoKg > 0) {
            $this->separation_waste_kg = max(0, round($this->totalNettoKg - $outputsSum, 2));

            $this->yieldProductPct = round(($prodKg / $this->totalNettoKg) * 100, 2);
            $this->yieldBitsStemPct = round(($stemNetto / $this->totalNettoKg) * 100, 2);
            $this->yieldDustPct = round(($dustNetto / $this->totalNettoKg) * 100, 2);
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

        // Require interim separation report numbers
        $this->validate([
            'separation_product_sack' => 'required|numeric|min:0',
            'separation_bits_stem_gross_kg' => 'required|numeric|min:0',
            'separation_bits_stem_tare_kg' => 'required|numeric|min:0',
            'separation_dust_gross_kg' => 'required|numeric|min:0',
            'separation_dust_tare_kg' => 'required|numeric|min:0',
        ]);

        $this->recalculateTotals();

        // Log interim separation report for shift/group
        BatchInterimSeparation::create([
            'batch_id' => $batch->id,
            'user_id' => Auth::id(),
            'shift' => $user->shift ?? 'Shift 1',
            'group' => $user->group ?? 'Group A',
            'separation_product_kg' => $this->separation_product_kg,
            'separation_product_sack' => $this->separation_product_sack,
            'separation_bits_stem_kg' => $this->separation_bits_stem_kg,
            'separation_bits_stem_gross_kg' => $this->separation_bits_stem_gross_kg,
            'separation_bits_stem_tare_kg' => $this->separation_bits_stem_tare_kg,
            'separation_bits_stem_netto_kg' => $this->separation_bits_stem_netto_kg,
            'separation_dust_kg' => $this->separation_dust_kg,
            'separation_dust_gross_kg' => $this->separation_dust_gross_kg,
            'separation_dust_tare_kg' => $this->separation_dust_tare_kg,
            'separation_dust_netto_kg' => $this->separation_dust_netto_kg,
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
            'mrl_total_pack' => $this->totalPack,
            'mrl_gross_weight' => $this->totalGrossKg,
            'mrl_tare_weight' => $this->totalTareKg,
            'mrl_netto_weight' => $this->totalNettoKg,
            'discrepancy_dn_vs_mrl_kg' => $discrepancy,
            'separation_product_kg' => $this->separation_product_kg,
            'separation_product_sack' => $this->separation_product_sack,
            'separation_bits_stem_kg' => $this->separation_bits_stem_kg,
            'separation_bits_stem_gross_kg' => $this->separation_bits_stem_gross_kg,
            'separation_bits_stem_tare_kg' => $this->separation_bits_stem_tare_kg,
            'separation_bits_stem_netto_kg' => $this->separation_bits_stem_netto_kg,
            'separation_dust_kg' => $this->separation_dust_kg,
            'separation_dust_gross_kg' => $this->separation_dust_gross_kg,
            'separation_dust_tare_kg' => $this->separation_dust_tare_kg,
            'separation_dust_netto_kg' => $this->separation_dust_netto_kg,
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
