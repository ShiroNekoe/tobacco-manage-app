<?php

namespace App\Livewire\Production;

use App\Models\DowntimeEvent;
use App\Models\MaterialReceiptList;
use App\Models\ProductCertificate;
use App\Models\ProductionRun;
use App\Services\KpiCalculatorService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProductionForm extends Component
{
    public ?int $productionRunId = null;
    public ?int $mrl_id = null;
    public string $shift = 'shift_1';
    public string $group_name = 'group_a';
    public string $group_leader_name = '';
    public string $operator_1_name = '';
    public string $operator_2_name = '';

    public ?string $start_time = null;
    public ?string $finish_time = null;

    public $product_weight = 0;
    public $bits_stem_weight = 0;
    public $dust_weight = 0;

    // Multi-row downtime events
    public array $downtimeEvents = [];

    public string $remarks = '';
    public string $status = 'running';
    public string $machine_status = 'running';

    // Live KPI variables
    public float $netWeight = 0;
    public float $wasteWeight = 0;
    public float $productYieldPct = 0;
    public float $bitsStemPct = 0;
    public float $dustPct = 0;
    public float $wastePct = 0;
    public float $uptimeHours = 0;
    public float $capacityKgHr = 0;
    public float $uptimePct = 0;
    public float $performancePct = 0;
    public int $totalDowntimeMinutes = 0;

    public function mount(?int $id = null, ?int $mrl_id = null)
    {
        $user = Auth::user();
        if ($user && $user->isWarehouse()) {
            abort(403, 'Warehouse tidak diizinkan mengisi form produksi.');
        }

        $this->group_leader_name = $user->isGroupLeader() ? $user->name : ($user->name ?? '');
        $this->operator_1_name = $user->isOperator() ? $user->name : '';

        if ($id) {
            $run = ProductionRun::with(['mrl', 'downtimeEvents'])->findOrFail($id);
            $this->productionRunId = $run->id;
            $this->mrl_id = $run->mrl_id;
            $this->shift = $run->shift;
            $this->group_name = $run->group_name;
            $this->group_leader_name = $run->group_leader_name;
            $this->operator_1_name = $run->operator_1_name;
            $this->operator_2_name = $run->operator_2_name;
            $this->start_time = $run->start_time ? $run->start_time->format('Y-m-d\TH:i') : null;
            $this->finish_time = $run->finish_time ? $run->finish_time->format('Y-m-d\TH:i') : null;
            $this->product_weight = $run->product_weight;
            $this->bits_stem_weight = $run->bits_stem_weight;
            $this->dust_weight = $run->dust_weight;
            $this->remarks = $run->remarks ?? '';
            $this->status = $run->status;
            $this->machine_status = $run->machine_status;

            foreach ($run->downtimeEvents as $event) {
                $this->downtimeEvents[] = [
                    'minutes' => $event->downtime_minutes,
                    'reason' => $event->reason,
                    'remarks' => $event->remarks ?? '',
                ];
            }
        } elseif ($mrl_id) {
            $this->mrl_id = $mrl_id;
            $this->start_time = Carbon::now()->format('Y-m-d\TH:i');
        } else {
            $this->start_time = Carbon::now()->format('Y-m-d\TH:i');
        }

        // Default at least 1 downtime row if empty
        if (empty($this->downtimeEvents)) {
            $this->downtimeEvents[] = [
                'minutes' => 0,
                'reason' => 'Cleaning / Ganti Mess',
                'remarks' => '',
            ];
        }

        $this->loadMrlDataAndRecalculate();
    }

    public function updatedMrlId()
    {
        $this->loadMrlDataAndRecalculate();
    }

    public function updatedProductWeight()
    {
        $this->calculateKpis();
    }

    public function updatedBitsStemWeight()
    {
        $this->calculateKpis();
    }

    public function updatedDustWeight()
    {
        $this->calculateKpis();
    }

    public function addDowntimeRow()
    {
        $this->downtimeEvents[] = [
            'minutes' => 0,
            'reason' => 'Cleaning / Ganti Mess',
            'remarks' => '',
        ];
        $this->calculateKpis();
    }

    public function removeDowntimeRow(int $index)
    {
        if (count($this->downtimeEvents) > 1) {
            unset($this->downtimeEvents[$index]);
            $this->downtimeEvents = array_values($this->downtimeEvents);
        }
        $this->calculateKpis();
    }

    public function loadMrlDataAndRecalculate()
    {
        if ($this->mrl_id) {
            $mrl = MaterialReceiptList::find($this->mrl_id);
            if ($mrl) {
                $this->netWeight = (float) $mrl->net_weight;
            }
        }
        $this->calculateKpis();
    }

    public function calculateKpis()
    {
        $this->totalDowntimeMinutes = 0;
        foreach ($this->downtimeEvents as $event) {
            $this->totalDowntimeMinutes += (int) ($event['minutes'] ?? 0);
        }

        if ($this->netWeight > 0) {
            $kpiCalc = new KpiCalculatorService();
            try {
                $res = $kpiCalc->calculate(
                    $this->netWeight,
                    (float) $this->product_weight,
                    (float) $this->bits_stem_weight,
                    (float) $this->dust_weight,
                    $this->totalDowntimeMinutes
                );

                $this->wasteWeight = $res['waste_weight'];
                $this->productYieldPct = $res['product_yield_pct'];
                $this->bitsStemPct = $res['bits_stem_pct'];
                $this->dustPct = $res['dust_pct'];
                $this->wastePct = $res['waste_pct'];
                $this->uptimeHours = $res['uptime_hours'];
                $this->capacityKgHr = $res['capacity_kg_hr'];
                $this->uptimePct = $res['uptime_pct'];
                $this->performancePct = $res['performance_pct'];
                $this->resetErrorBag('product_weight');
            } catch (\InvalidArgumentException $e) {
                $this->addError('product_weight', $e->getMessage());
            }
        }
    }

    public function saveDraft()
    {
        $this->saveProduction('running');
    }

    public function finishProduction()
    {
        $this->saveProduction('completed');
    }

    protected function saveProduction(string $targetStatus)
    {
        $user = Auth::user();

        if ($this->productionRunId) {
            $existing = ProductionRun::findOrFail($this->productionRunId);
            if ($existing->isLocked() && ! ($user->isAdmin() || $user->isSupervisor())) {
                abort(403, 'Data produksi telah dikunci. Hanya Administrator dan Supervisor yang dapat mengubah.');
            }
        }

        $this->validate([
            'mrl_id' => 'required|exists:material_receipt_lists,id',
            'shift' => 'required|in:shift_1,shift_2',
            'group_name' => 'required|in:group_a,group_b,group_c',
            'group_leader_name' => 'required|string',
            'operator_1_name' => 'required|string',
            'operator_2_name' => 'required|string',
            'start_time' => 'required|date',
            'product_weight' => 'required|numeric|min:0',
            'bits_stem_weight' => 'required|numeric|min:0',
            'dust_weight' => 'required|numeric|min:0',
        ]);

        $mrl = MaterialReceiptList::findOrFail($this->mrl_id);
        $this->netWeight = (float) $mrl->net_weight;

        $totalOutputs = (float) $this->product_weight + (float) $this->bits_stem_weight + (float) $this->dust_weight;
        if (round($totalOutputs, 4) > round($this->netWeight, 4)) {
            $this->addError('product_weight', sprintf('Total bobot keluaran (%.2f kg) melebihi Net Weight (%.2f kg).', $totalOutputs, $this->netWeight));
            return;
        }

        $this->calculateKpis();

        $finishTimestamp = $targetStatus === 'completed' ? Carbon::now() : ($this->finish_time ? Carbon::parse($this->finish_time) : null);
        $machineStatus = $targetStatus === 'completed' ? 'completed' : 'running';
        $finalStatus = $targetStatus === 'completed' ? 'locked' : 'running';

        if ($this->productionRunId) {
            $run = ProductionRun::findOrFail($this->productionRunId);
            $run->update([
                'mrl_id' => $this->mrl_id,
                'shift' => $this->shift,
                'group_name' => $this->group_name,
                'group_leader_name' => $this->group_leader_name,
                'operator_1_name' => $this->operator_1_name,
                'operator_2_name' => $this->operator_2_name,
                'start_time' => $this->start_time,
                'finish_time' => $finishTimestamp,
                'product_weight' => $this->product_weight,
                'bits_stem_weight' => $this->bits_stem_weight,
                'dust_weight' => $this->dust_weight,
                'waste_weight' => $this->wasteWeight,
                'total_downtime_minutes' => $this->totalDowntimeMinutes,
                'product_yield_pct' => $this->productYieldPct,
                'bits_stem_pct' => $this->bitsStemPct,
                'dust_pct' => $this->dustPct,
                'waste_pct' => $this->wastePct,
                'uptime_hours' => $this->uptimeHours,
                'capacity_kg_hr' => $this->capacityKgHr,
                'uptime_pct' => $this->uptimePct,
                'performance_pct' => $this->performancePct,
                'machine_status' => $machineStatus,
                'status' => $finalStatus,
                'remarks' => $this->remarks,
                'locked_at' => $targetStatus === 'completed' ? Carbon::now() : null,
            ]);

            // Refresh downtime events
            $run->downtimeEvents()->delete();
        } else {
            $countToday = ProductionRun::whereDate('created_at', Carbon::today())->count() + 1;
            $code = 'PRD-' . Carbon::today()->format('Ymd') . '-' . str_pad($countToday, 3, '0', STR_PAD_LEFT);

            $run = ProductionRun::create([
                'production_code' => $code,
                'mrl_id' => $this->mrl_id,
                'shift' => $this->shift,
                'group_name' => $this->group_name,
                'group_leader_name' => $this->group_leader_name,
                'operator_1_name' => $this->operator_1_name,
                'operator_2_name' => $this->operator_2_name,
                'start_time' => $this->start_time,
                'finish_time' => $finishTimestamp,
                'product_weight' => $this->product_weight,
                'bits_stem_weight' => $this->bits_stem_weight,
                'dust_weight' => $this->dust_weight,
                'waste_weight' => $this->wasteWeight,
                'total_downtime_minutes' => $this->totalDowntimeMinutes,
                'product_yield_pct' => $this->productYieldPct,
                'bits_stem_pct' => $this->bitsStemPct,
                'dust_pct' => $this->dustPct,
                'waste_pct' => $this->wastePct,
                'uptime_hours' => $this->uptimeHours,
                'capacity_kg_hr' => $this->capacityKgHr,
                'uptime_pct' => $this->uptimePct,
                'performance_pct' => $this->performancePct,
                'machine_status' => $machineStatus,
                'status' => $finalStatus,
                'remarks' => $this->remarks,
                'created_by_user_id' => Auth::id(),
                'locked_at' => $targetStatus === 'completed' ? Carbon::now() : null,
            ]);
        }

        // Save downtime events
        foreach ($this->downtimeEvents as $evt) {
            if (! empty($evt['minutes']) && $evt['minutes'] > 0) {
                DowntimeEvent::create([
                    'production_run_id' => $run->id,
                    'downtime_minutes' => (int) $evt['minutes'],
                    'reason' => $evt['reason'] ?? 'Cleaning / Maintenance',
                    'remarks' => $evt['remarks'] ?? '',
                ]);
            }
        }

        // Mark MRL status as in_production or completed
        $mrl->update(['status' => $targetStatus === 'completed' ? 'completed' : 'in_production']);

        // Auto-generate Product Certificate if completed
        if ($targetStatus === 'completed') {
            $certNumber = 'CERT-' . Carbon::today()->format('Ymd') . '-' . str_pad($run->id, 4, '0', STR_PAD_LEFT);
            ProductCertificate::updateOrCreate(
                ['production_run_id' => $run->id],
                [
                    'certificate_number' => $certNumber,
                    'issued_at' => Carbon::now(),
                    'issued_by_user_id' => Auth::id(),
                    'data_snapshot' => [
                        'mrl_number' => $mrl->mrl_number,
                        'dn_number' => $mrl->deliveryNote ? $mrl->deliveryNote->dn_number : '-',
                        'batch_number' => $mrl->batch_number,
                        'origin_region' => $mrl->origin_region,
                        'tobacco_grade' => $mrl->tobacco_grade,
                        'net_weight' => $mrl->net_weight,
                        'product_weight' => $run->product_weight,
                        'bits_stem_weight' => $run->bits_stem_weight,
                        'dust_weight' => $run->dust_weight,
                        'waste_weight' => $run->waste_weight,
                        'product_yield_pct' => $run->product_yield_pct,
                        'bits_stem_pct' => $run->bits_stem_pct,
                        'dust_pct' => $run->dust_pct,
                        'waste_pct' => $run->waste_pct,
                        'capacity_kg_hr' => $run->capacity_kg_hr,
                        'uptime_pct' => $run->uptime_pct,
                        'performance_pct' => $run->performance_pct,
                        'group' => strtoupper(str_replace('_', ' ', $run->group_name)),
                        'shift' => strtoupper(str_replace('_', ' ', $run->shift)),
                        'group_leader' => $run->group_leader_name,
                        'operators' => $run->operator_1_name . ', ' . $run->operator_2_name,
                    ],
                ]
            );

            session()->flash('message', 'Proses produksi ' . $run->production_code . ' selesai! Sertifikat ' . $certNumber . ' telah diterbitkan dan data dikunci.');
        } else {
            session()->flash('message', 'Draft proses produksi ' . $run->production_code . ' berhasil disimpan.');
        }

        return redirect()->route('production.list');
    }

    public function unlockRecord()
    {
        $user = Auth::user();
        if (! ($user->isAdmin() || $user->isSupervisor())) {
            abort(403, 'Hanya Administrator atau Supervisor yang dapat membuka kembali data terkunci.');
        }

        if ($this->productionRunId) {
            $run = ProductionRun::findOrFail($this->productionRunId);
            $run->update([
                'status' => 'running',
                'machine_status' => 'running',
                'unlocked_at' => Carbon::now(),
                'unlocked_by_user_id' => Auth::id(),
            ]);

            $this->status = 'running';
            $this->machine_status = 'running';
            session()->flash('message', 'Data produksi ' . $run->production_code . ' berhasil dibuka kembali (Unlocked).');
        }
    }

    public function render()
    {
        $approvedMrls = MaterialReceiptList::whereIn('status', ['ready_for_production', 'in_production'])
            ->orWhere('id', $this->mrl_id)
            ->latest()
            ->get();

        return view('livewire.production.production-form', compact('approvedMrls'));
    }
}
