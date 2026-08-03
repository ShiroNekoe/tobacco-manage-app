<?php

namespace App\Livewire\Admin;

use App\Models\Batch;
use App\Models\BatchInterimSeparation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AdminTracking extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount()
    {
        if (! Auth::user() || ! (Auth::user()->isAdmin() || Auth::user()->isSupervisor())) {
            abort(403, 'Hanya Admin atau Supervisor yang dapat melihat halaman Tracking Produksi.');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Batch::with(['customer', 'productType', 'origin', 'lastSavedBy', 'weighingItems'])
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('batch_code', 'like', '%' . $this->search . '%')
                    ->orWhereHas('lastSavedBy', fn ($uq) => $uq->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', '%' . $this->search . '%'));
            });
        }

        $activeBatches = $query->paginate(6);

        $interimQuery = BatchInterimSeparation::with(['batch', 'user'])->latest();
        if ($this->search) {
            $interimQuery->where(function ($iq) {
                $iq->whereHas('batch', fn ($bq) => $bq->where('batch_code', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%' . $this->search . '%'));
            });
        }
        $interimReports = $interimQuery->take(15)->get();

        return view('livewire.admin.admin-tracking', compact('activeBatches', 'interimReports'));
    }
}
