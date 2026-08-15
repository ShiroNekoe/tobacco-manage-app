<?php

namespace App\Livewire\Admin;

use App\Models\Batch;
use App\Models\BatchOrigin;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Origin;
use App\Models\PackType;
use App\Models\ProductType;
use App\Models\WeighingItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class BatchManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    // Create Batch Form Fields
    public bool $showCreateModal = false;
    public string $batch_code = '';
    public ?int $customer_id = null;
    public string $dn_number = '';
    public ?int $product_type_id = null;
    public ?int $origin_id = null;
    public string $material_code = '';
    public array $selected_origins = [];
    public string $pack_type = 'Bale';
    public $product_kg_per_sack = 25.20;
    public $product_tare_per_sack = 0.20;
    public string $date_of_receipt = '';

    // Header DN Weight Input
    public $dn_gross_weight_input = '';

    // Target Sack Count Input & Dynamic Multi-Row MRL Pre-Launch Items (Only MRL Gross per Sack)
    public $target_sack_count = 5;
    public array $mrl_items = [];

    // Summary Totals
    public $dn_total_pack = 0;
    public $dn_gross_weight = 0;
    public $dn_tare_weight = 0;
    public $dn_netto_weight = 0;
    public $mrl_total_pack = 0;
    public $mrl_gross_weight = 0;

    // Discrepancy & Close Batch Fields
    public ?int $selectedBatchId = null;
    public bool $showCloseModal = false;
    public string $force_close_reason = '';
    public array $gateValidationErrors = [];

    // PDF Preview & Custom Remarks Modal
    public bool $showPdfRemarksModal = false;
    public ?int $pdfBatchId = null;
    public bool $addCustomRemarks = false;
    public string $custom_dn_remark = '';
    public string $custom_mrl_remark = '';
    public string $custom_separation_remark = '';
    public int $iframeKey = 0;

    public function mount()
    {
        $this->date_of_receipt = Carbon::now()->format('Y-m-d');
        $this->generateMrlRowsFromTargetCount();
    }

    public function openCreateModal()
    {
        $this->reset(['batch_code', 'customer_id', 'dn_number', 'product_type_id', 'origin_id', 'material_code', 'pack_type', 'selected_origins', 'dn_gross_weight_input']);
        $this->batch_code = '';
        $this->product_kg_per_sack = 25.20;
        $this->product_tare_per_sack = 0.20;
        $this->date_of_receipt = Carbon::now()->format('Y-m-d');
        $this->target_sack_count = 5;
        $this->generateMrlRowsFromTargetCount();
        $this->showCreateModal = true;
    }

    public function isBoxPackType(?string $type = null): bool
    {
        $packType = strtolower(trim($type ?? $this->pack_type ?? ''));
        return str_contains($packType, 'box') || str_contains($packType, 'c48') || str_contains($packType, 'c-48');
    }

    public function updatedPackType($value)
    {
        if ($this->isBoxPackType($value)) {
            $this->autoDistributeDnGrossForBox();
        }
        $this->recalculateMrlTotals();
    }

    public function updatedTargetSackCount()
    {
        $this->generateMrlRowsFromTargetCount();
        if ($this->isBoxPackType()) {
            $this->autoDistributeDnGrossForBox();
        }
        $this->recalculateMrlTotals();
    }

    public function updatedDnGrossWeightInput()
    {
        if ($this->isBoxPackType()) {
            $this->autoDistributeDnGrossForBox();
        }
        $this->recalculateMrlTotals();
    }

    public function autoDistributeDnGrossForBox(): void
    {
        if (! $this->isBoxPackType()) {
            return;
        }

        $dnGross = (float) ($this->dn_gross_weight_input ?? 0);
        $count = count($this->mrl_items);

        if ($dnGross > 0 && $count > 0) {
            $grossPerBox = round($dnGross / $count, 2);
            for ($i = 0; $i < $count; $i++) {
                if ($i === $count - 1) {
                    $this->mrl_items[$i]['mrl_gross_weight'] = round($dnGross - ($grossPerBox * ($count - 1)), 2);
                } else {
                    $this->mrl_items[$i]['mrl_gross_weight'] = $grossPerBox;
                }
            }
        }
    }

    public function updatedProductTarePerSack()
    {
        $this->recalculateMrlTotals();
    }

    public function generateMrlRowsFromTargetCount()
    {
        if ($this->target_sack_count === '' || $this->target_sack_count === null) {
            return;
        }

        $count = max(1, min(500, (int) $this->target_sack_count));
        $currentCount = count($this->mrl_items);

        if ($count > $currentCount) {
            for ($i = $currentCount + 1; $i <= $count; $i++) {
                $this->mrl_items[] = [
                    'sack_number' => $i,
                    'mrl_gross_weight' => '',
                ];
            }
        } elseif ($count < $currentCount) {
            $this->mrl_items = array_slice($this->mrl_items, 0, $count);
        }

        $this->recalculateMrlTotals();
    }

    public function removeMrlItemRow(int $index)
    {
        if (count($this->mrl_items) > 1) {
            unset($this->mrl_items[$index]);
            $this->mrl_items = array_values($this->mrl_items);
            foreach ($this->mrl_items as $idx => $item) {
                $this->mrl_items[$idx]['sack_number'] = $idx + 1;
            }
            $this->target_sack_count = count($this->mrl_items);
            $this->recalculateMrlTotals();
        }
    }

    public function updatedMrlItems()
    {
        $this->recalculateMrlTotals();
    }

    public function recalculateMrlTotals()
    {
        $this->dn_total_pack = count($this->mrl_items);
        $this->mrl_total_pack = count($this->mrl_items);
        $this->mrl_gross_weight = 0;
        
        $productTare = (float) ($this->product_tare_per_sack !== '' && $this->product_tare_per_sack !== null ? $this->product_tare_per_sack : 0.20);
        $this->dn_tare_weight = round(count($this->mrl_items) * $productTare, 2);

        foreach ($this->mrl_items as $item) {
            $mrlGross = (float) ($item['mrl_gross_weight'] ?? 0);
            $this->mrl_gross_weight += $mrlGross;
        }

        $dnGrossInput = (float) ($this->dn_gross_weight_input ?? 0);
        $this->dn_gross_weight = $dnGrossInput > 0 ? $dnGrossInput : $this->mrl_gross_weight;
        $this->dn_netto_weight = max(0, round($this->dn_gross_weight - $this->dn_tare_weight, 2));
    }

    public function createBatch()
    {
        $user = Auth::user();
        if ($user && ! ($user->isAdmin() || $user->isSupervisor())) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat Batch baru.');
        }

        if (is_string($this->product_kg_per_sack)) {
            $this->product_kg_per_sack = str_replace(',', '.', trim($this->product_kg_per_sack));
        }
        if (is_string($this->product_tare_per_sack)) {
            $this->product_tare_per_sack = str_replace(',', '.', trim($this->product_tare_per_sack));
        }

        if ($this->isBoxPackType() && empty($this->product_kg_per_sack)) {
            $this->product_kg_per_sack = 25.20;
        }

        $this->validate([
            'batch_code' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
            'dn_number' => 'nullable|string',
            'product_type_id' => 'required|exists:product_types,id',
            'origin_id' => 'required|exists:origins,id',
            'material_code' => 'nullable|string',
            'pack_type' => 'required|string',
            'product_kg_per_sack' => 'required|numeric|min:0.01',
            'product_tare_per_sack' => 'required|numeric|min:0',
            'date_of_receipt' => 'required|date',
            'mrl_items' => 'required|array|min:1',
            'mrl_items.*.mrl_gross_weight' => 'required|numeric|min:0.01',
        ]);

        $this->recalculateMrlTotals();

        $dnNumber = trim($this->dn_number);

        $dn = DeliveryNote::firstOrCreate(
            ['dn_number' => $dnNumber, 'customer_id' => $this->customer_id],
            [
                'delivery_date' => $this->date_of_receipt,
                'status' => 'received',
            ]
        );

        $discrepancy = round($this->mrl_gross_weight - $this->dn_gross_weight, 2);

        $batch = Batch::create([
            'batch_code' => trim($this->batch_code),
            'customer_id' => $this->customer_id,
            'delivery_note_id' => $dn->id,
            'product_type_id' => $this->product_type_id,
            'origin_id' => $this->origin_id,
            'material_code' => trim($this->material_code),
            'pack_type' => $this->pack_type,
            'product_kg_per_sack' => $this->product_kg_per_sack ?: 25.20,
            'product_tare_per_sack' => isset($this->product_tare_per_sack) ? $this->product_tare_per_sack : 0.20,
            'date_of_receipt' => $this->date_of_receipt,
            'dn_total_pack' => $this->dn_total_pack,
            'dn_gross_weight' => $this->dn_gross_weight,
            'dn_tare_weight' => $this->dn_tare_weight,
            'dn_netto_weight' => $this->dn_netto_weight,
            'mrl_total_pack' => $this->mrl_total_pack,
            'mrl_gross_weight' => $this->mrl_gross_weight,
            'mrl_tare_weight' => $this->dn_tare_weight,
            'mrl_netto_weight' => max(0, round($this->mrl_gross_weight - $this->dn_tare_weight, 2)),
            'discrepancy_dn_vs_mrl_kg' => $discrepancy,
            'status' => 'OPEN',
            'created_by_user_id' => Auth::id(),
        ]);

        $originsToAttach = ! empty($this->selected_origins) ? $this->selected_origins : [$this->origin_id];
        foreach ($originsToAttach as $origId) {
            BatchOrigin::create([
                'batch_id' => $batch->id,
                'origin_id' => $origId,
                'allocated_kg' => round($this->dn_netto_weight / count($originsToAttach), 2),
                'remaining_kg' => round($this->dn_netto_weight / count($originsToAttach), 2),
                'status' => 'active',
            ]);
        }

        // Save pre-launch MRL items into weighing_items for factory floor
        $dnTare = (float) ($this->product_tare_per_sack !== '' && $this->product_tare_per_sack !== null ? $this->product_tare_per_sack : 0.20);
        foreach ($this->mrl_items as $mItem) {
            $mrlGross = (float) ($mItem['mrl_gross_weight'] ?? 0);
            if ($mrlGross > 0) {
                WeighingItem::create([
                    'batch_id' => $batch->id,
                    'sack_number' => $mItem['sack_number'],
                    'gross_kg' => $mrlGross,
                    'tare_kg' => $dnTare,
                    'netto_kg' => max(0, round($mrlGross - $dnTare, 2)),
                    'remark' => 'MRL Pre-Launch',
                    'created_by_user_id' => Auth::id(),
                    'shift' => Auth::user()->shift ?? 'Shift 1',
                    'group' => Auth::user()->group ?? 'Group A',
                ]);
            }
        }

        $this->showCreateModal = false;
        $this->reset(['dn_number', 'dn_gross_weight', 'dn_tare_weight', 'dn_netto_weight', 'dn_total_pack', 'mrl_gross_weight', 'mrl_total_pack', 'selected_origins', 'dn_gross_weight_input']);
        session()->flash('message', 'Batch ' . $batch->batch_code . ' (' . count($this->mrl_items) . ' Sak/Bale MRL) berhasil dibuat & diverifikasi!');
        $this->dispatch('swal:alert', [
            'icon' => 'success',
            'title' => 'Batch Berhasil Dibuat!',
            'text' => 'Batch ' . $batch->batch_code . ' (' . count($this->mrl_items) . ' Sak/Bale MRL) berhasil dibuat & diverifikasi.',
        ]);
    }

    public function deleteBatch(int $id)
    {
        $user = Auth::user();
        if ($user && ! ($user->isAdmin() || $user->isSupervisor())) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus Batch.');
        }

        $batch = Batch::findOrFail($id);
        $batchCode = $batch->batch_code;
        $batch->delete();

        session()->flash('message', 'Batch ' . $batchCode . ' berhasil dihapus dari sistem.');
        $this->dispatch('swal:alert', [
            'icon' => 'success',
            'title' => 'Batch Terhapus!',
            'text' => 'Batch ' . $batchCode . ' berhasil dihapus dari sistem.',
        ]);
    }

    public function unlockBatch(int $id)
    {
        $user = Auth::user();
        if ($user && ! ($user->isAdmin() || $user->isSupervisor())) {
            abort(403, 'Hanya Admin atau Supervisor yang dapat membuka kembali Batch.');
        }

        $batch = Batch::findOrFail($id);
        $batch->update([
            'status' => 'draft',
            'unlocked_at' => Carbon::now(),
            'unlocked_by_user_id' => $user ? $user->id : null,
        ]);

        session()->flash('message', 'Batch ' . $batch->batch_code . ' berhasil dibuka kembali (Draft).');
        $this->dispatch('swal:alert', [
            'icon' => 'info',
            'title' => 'Batch Dibuka Kembali',
            'text' => 'Batch ' . $batch->batch_code . ' berhasil dibuka kembali (Draft).',
        ]);
    }

    public function openCloseModal(int $id)
    {
        $batch = Batch::findOrFail($id);
        $this->selectedBatchId = $batch->id;
        $this->force_close_reason = '';
        $this->gateValidationErrors = $batch->validateClosureGates();
        $this->showCloseModal = true;
    }

    public function closeBatch()
    {
        $user = Auth::user();
        if ($user && ! ($user->isAdmin() || $user->isSupervisor())) {
            abort(403, 'Hanya Admin atau Supervisor yang dapat menutup (Close) Batch.');
        }

        if (! $this->selectedBatchId) return;

        $batch = Batch::findOrFail($this->selectedBatchId);
        $errors = $batch->validateClosureGates($this->force_close_reason);

        if (count($errors) > 0) {
            $this->gateValidationErrors = $errors;
            return;
        }

        $batch->update([
            'status' => 'CLOSED',
            'force_close_reason' => $this->force_close_reason ?: null,
            'locked_at' => Carbon::now(),
        ]);

        $this->showCloseModal = false;
        session()->flash('message', 'Batch ' . $batch->batch_code . ' berhasil ditutup (CLOSED). Sertifikat siap diajukan ke Supervisor.');
    }

    public function approveCertificate(int $id)
    {
        $user = Auth::user();
        if (! $user || ! $user->isSupervisor()) {
            abort(403, 'Hanya role Supervisor yang berhak menyetujui (ACC / Approve) Sertifikat Produk.');
        }

        $batch = Batch::findOrFail($id);
        $batch->approveBySupervisor($user);

        session()->flash('message', 'Sertifikat Produk Batch ' . $batch->batch_code . ' berhasil di-ACC / Approved oleh Supervisor! Status batch kini APPROVED BY SUPERVISOR.');
    }

    public function revokeCertificateApproval(int $id)
    {
        $user = Auth::user();
        if (! $user || ! $user->isSupervisor()) {
            abort(403, 'Hanya role Supervisor yang berhak membatalkan persetujuan ACC Sertifikat Produk.');
        }

        $batch = Batch::findOrFail($id);
        $batch->update([
            'supervisor_approval_status' => Batch::APPROVAL_PENDING,
            'supervisor_approved_at' => null,
            'supervisor_approved_by_user_id' => null,
        ]);

        session()->flash('message', 'Persetujuan ACC Batch ' . $batch->batch_code . ' berhasil dibatalkan (Pending).');
    }

    public function openPdfRemarksModal(int $id)
    {
        $batch = Batch::findOrFail($id);
        $this->pdfBatchId = $batch->id;
        $this->addCustomRemarks = ! empty($batch->custom_dn_remark) || ! empty($batch->custom_mrl_remark) || ! empty($batch->custom_separation_remark);
        $this->custom_dn_remark = $batch->custom_dn_remark ?? '';
        $this->custom_mrl_remark = $batch->custom_mrl_remark ?? '';
        $this->custom_separation_remark = $batch->custom_separation_remark ?? '';
        $this->iframeKey++;
        $this->showPdfRemarksModal = true;
    }

    public function saveCustomRemarksAndRefreshPreview()
    {
        if (! $this->pdfBatchId) return;

        $batch = Batch::findOrFail($this->pdfBatchId);
        $batch->update([
            'custom_dn_remark' => $this->addCustomRemarks ? $this->custom_dn_remark : null,
            'custom_mrl_remark' => $this->addCustomRemarks ? $this->custom_mrl_remark : null,
            'custom_separation_remark' => $this->addCustomRemarks ? $this->custom_separation_remark : null,
        ]);

        $this->iframeKey++;
    }

    public function generatePdfWithRemarks()
    {
        if (! $this->pdfBatchId) return;

        $this->saveCustomRemarksAndRefreshPreview();
        $this->showPdfRemarksModal = false;

        return redirect()->route('certificate.pdf', $this->pdfBatchId);
    }

    public function render()
    {
        $query = Batch::with(['customer', 'deliveryNote', 'productType', 'origin', 'createdBy'])
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('batch_code', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('productType', fn ($pq) => $pq->where('name', 'like', '%' . $this->search . '%'));
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $batches = $query->paginate(10);
        $customers = Customer::orderBy('name')->get();
        $productTypes = ProductType::orderBy('name')->get();
        $origins = Origin::orderBy('region_name')->get();

        $packTypes = PackType::where('is_active', true)->orderBy('id')->get();
        if ($packTypes->isEmpty()) {
            $packTypes = collect([
                (object)['code' => 'Bale', 'name' => 'Bale'],
                (object)['code' => 'Sack', 'name' => 'Sack (Karung)'],
                (object)['code' => 'Box', 'name' => 'Box'],
                (object)['code' => 'C48', 'name' => 'C48'],
                (object)['code' => 'Box/C48', 'name' => 'Box / C48'],
            ]);
        }

        return view('livewire.admin.batch-management', compact('batches', 'customers', 'productTypes', 'origins', 'packTypes'));
    }
}
