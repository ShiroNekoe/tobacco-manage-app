<?php

namespace App\Livewire\Production;

use App\Models\ProductionRun;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProductionList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $shiftFilter = '';
    public string $groupFilter = '';
    public string $statusFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function unlock(int $id)
    {
        $user = Auth::user();
        if (! ($user->isAdmin() || $user->isSupervisor())) {
            abort(403, 'Hanya Administrator atau Supervisor yang memiliki hak akses Reopen/Unlock data.');
        }

        $run = ProductionRun::findOrFail($id);
        $run->update([
            'status' => 'running',
            'machine_status' => 'running',
            'unlocked_at' => Carbon::now(),
            'unlocked_by_user_id' => Auth::id(),
        ]);

        session()->flash('message', 'Data produksi ' . $run->production_code . ' berhasil dibuka kembali (Unlocked).');
    }

    public function render()
    {
        $query = ProductionRun::with(['mrl.supplier', 'certificate', 'createdBy'])
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('production_code', 'like', '%' . $this->search . '%')
                    ->orWhereHas('mrl', function ($mq) {
                        $mq->where('mrl_number', 'like', '%' . $this->search . '%')
                            ->orWhere('batch_number', 'like', '%' . $this->search . '%')
                            ->orWhere('origin_region', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->shiftFilter) {
            $query->where('shift', $this->shiftFilter);
        }

        if ($this->groupFilter) {
            $query->where('group_name', $this->groupFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $productions = $query->paginate(10);

        return view('livewire.production.production-list', compact('productions'));
    }
}
