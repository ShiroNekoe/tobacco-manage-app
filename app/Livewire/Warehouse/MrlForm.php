<?php

namespace App\Livewire\Warehouse;

use App\Models\DeliveryNote;
use App\Models\MaterialReceiptList;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MrlForm extends Component
{
    public ?int $mrlId = null;
    public ?int $supplier_id = null;
    public ?int $delivery_note_id = null;
    public string $dn_number = '';
    public string $origin_region = '';
    public string $tobacco_grade = '';
    public string $batch_number = '';
    public $gross_weight = 0;
    public $tare_weight = 0;
    public $net_weight = 0;
    public $total_pack = 0;
    public string $status = 'ready_for_production';

    public function mount(?int $id = null)
    {
        if ($id) {
            $mrl = MaterialReceiptList::findOrFail($id);
            $this->mrlId = $mrl->id;
            $this->supplier_id = $mrl->supplier_id;
            $this->delivery_note_id = $mrl->delivery_note_id;
            $this->origin_region = $mrl->origin_region;
            $this->tobacco_grade = $mrl->tobacco_grade;
            $this->batch_number = $mrl->batch_number;
            $this->gross_weight = $mrl->gross_weight;
            $this->tare_weight = $mrl->tare_weight;
            $this->net_weight = $mrl->net_weight;
            $this->total_pack = $mrl->total_pack;
            $this->status = $mrl->status;
        }
    }

    public function updatedSupplierId($value)
    {
        if ($value) {
            $supplier = Supplier::find($value);
            if ($supplier) {
                $this->origin_region = $supplier->origin_region;
            }
        }
    }

    public function updatedGrossWeight()
    {
        $this->calculateNetWeight();
    }

    public function updatedTareWeight()
    {
        $this->calculateNetWeight();
    }

    public function calculateNetWeight()
    {
        $gross = (float) ($this->gross_weight ?? 0);
        $tare = (float) ($this->tare_weight ?? 0);
        $this->net_weight = max(0, round($gross - $tare, 2));
    }

    public function save()
    {
        if (Auth::user()->isOperator()) {
            abort(403, 'Operator tidak memiliki hak akses input MRL Gudang.');
        }

        $this->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'dn_number' => 'required_without:delivery_note_id|string',
            'origin_region' => 'required|string',
            'tobacco_grade' => 'required|string',
            'batch_number' => 'required|string',
            'gross_weight' => 'required|numeric|min:0.01',
            'tare_weight' => 'required|numeric|min:0',
            'total_pack' => 'required|integer|min:1',
        ]);

        $this->calculateNetWeight();

        if ($this->net_weight <= 0) {
            $this->addError('net_weight', 'Net Weight harus lebih besar dari 0. Gross harus melebihi Tare Weight.');
            return;
        }

        // Create DN if new string typed
        if (! $this->delivery_note_id) {
            $dn = DeliveryNote::create([
                'dn_number' => $this->dn_number,
                'supplier_id' => $this->supplier_id,
                'origin_region' => $this->origin_region,
                'tobacco_grade' => $this->tobacco_grade,
                'batch_number' => $this->batch_number,
                'delivery_date' => Carbon::now(),
                'status' => 'received',
            ]);
            $this->delivery_note_id = $dn->id;
        }

        if ($this->mrlId) {
            $mrl = MaterialReceiptList::findOrFail($this->mrlId);
            $mrl->update([
                'supplier_id' => $this->supplier_id,
                'delivery_note_id' => $this->delivery_note_id,
                'origin_region' => $this->origin_region,
                'tobacco_grade' => $this->tobacco_grade,
                'batch_number' => $this->batch_number,
                'gross_weight' => $this->gross_weight,
                'tare_weight' => $this->tare_weight,
                'net_weight' => $this->net_weight,
                'total_pack' => $this->total_pack,
                'status' => $this->status,
            ]);
            session()->flash('message', 'MRL ' . $mrl->mrl_number . ' berhasil diperbarui.');
        } else {
            $countToday = MaterialReceiptList::whereDate('created_at', Carbon::today())->count() + 1;
            $mrlNumber = 'MRL-' . Carbon::today()->format('Ymd') . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            $mrl = MaterialReceiptList::create([
                'mrl_number' => $mrlNumber,
                'supplier_id' => $this->supplier_id,
                'delivery_note_id' => $this->delivery_note_id,
                'origin_region' => $this->origin_region,
                'tobacco_grade' => $this->tobacco_grade,
                'batch_number' => $this->batch_number,
                'gross_weight' => $this->gross_weight,
                'tare_weight' => $this->tare_weight,
                'net_weight' => $this->net_weight,
                'total_pack' => $this->total_pack,
                'status' => 'ready_for_production',
                'received_by_user_id' => Auth::id(),
            ]);

            session()->flash('message', 'MRL ' . $mrl->mrl_number . ' berhasil diterbitkan.');
        }

        return redirect()->route('mrl.list');
    }

    public function render()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $deliveryNotes = DeliveryNote::orderBy('created_at', 'desc')->get();

        return view('livewire.warehouse.mrl-form', compact('suppliers', 'deliveryNotes'));
    }
}
