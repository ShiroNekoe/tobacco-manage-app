<?php

namespace App\Livewire\Customer;

use App\Models\Batch;
use App\Models\Origin;
use App\Models\ProductType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerDashboard extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $filter_product_type_id = null;
    public ?int $filter_origin_id = null;

    // PDF Preview Modal State
    public bool $showPreviewModal = false;
    public ?int $previewBatchId = null;

    public function mount()
    {
        $user = Auth::user();
        if (! $user || ! ($user->isCustomer() || $user->isAdmin() || $user->isSupervisor())) {
            abort(403, 'Akses khusus Customer Portal.');
        }
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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterProductTypeId()
    {
        $this->resetPage();
    }

    public function updatedFilterOriginId()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filter_product_type_id', 'filter_origin_id']);
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $query = Batch::with(['customer', 'deliveryNote', 'productType', 'origin', 'supervisorApprovedBy'])
            ->where('supervisor_approval_status', Batch::APPROVAL_APPROVED)
            ->latest();

        if ($user->isCustomer() && $user->customer_id) {
            $query->where('customer_id', $user->customer_id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('batch_code', 'like', '%' . $this->search . '%')
                    ->orWhereHas('deliveryNote', fn ($dq) => $dq->where('dn_number', 'like', '%' . $this->search . '%'));
            });
        }

        if ($this->filter_product_type_id) {
            $query->where('product_type_id', $this->filter_product_type_id);
        }

        if ($this->filter_origin_id) {
            $query->where('origin_id', $this->filter_origin_id);
        }

        $approvedBatches = (clone $query)->paginate(10);

        // Fetch historical filtered data for Chart.js (up to 15 latest filtered approved batches)
        $chartBatches = (clone $query)->oldest()->take(15)->get();

        $chartLabels = [];
        $seriesProduct = [];
        $seriesBitsStem = [];
        $seriesDust = [];
        $seriesWaste = [];

        foreach ($chartBatches as $b) {
            $chartLabels[] = $b->batch_code . ' (' . ($b->locked_at ? $b->locked_at->format('d/m') : date('d/m')) . ')';
            $seriesProduct[] = (float) $b->separation_product_kg;
            $seriesBitsStem[] = (float) $b->separation_bits_stem_kg;
            $seriesDust[] = (float) $b->separation_dust_kg;
            $seriesWaste[] = (float) $b->separation_waste_kg;
        }

        $productTypes = ProductType::orderBy('name')->get();
        $origins = Origin::orderBy('region_name')->get();

        $selectedProductType = $this->filter_product_type_id ? ProductType::find($this->filter_product_type_id) : null;
        $selectedOrigin = $this->filter_origin_id ? Origin::find($this->filter_origin_id) : null;

        return view('livewire.customer.customer-dashboard', compact(
            'approvedBatches',
            'chartBatches',
            'chartLabels',
            'seriesProduct',
            'seriesBitsStem',
            'seriesDust',
            'seriesWaste',
            'productTypes',
            'origins',
            'selectedProductType',
            'selectedOrigin'
        ));
    }
}
