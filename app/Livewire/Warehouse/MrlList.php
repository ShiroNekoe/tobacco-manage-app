<?php

namespace App\Livewire\Warehouse;

use App\Models\MaterialReceiptList;
use Livewire\Component;
use Livewire\WithPagination;

class MrlList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = MaterialReceiptList::with(['supplier', 'deliveryNote', 'receivedBy'])
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('mrl_number', 'like', '%' . $this->search . '%')
                    ->orWhere('batch_number', 'like', '%' . $this->search . '%')
                    ->orWhere('origin_region', 'like', '%' . $this->search . '%')
                    ->orWhere('tobacco_grade', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $mrls = $query->paginate(10);

        return view('livewire.warehouse.mrl-list', compact('mrls'));
    }
}
