<?php

namespace App\Livewire\Dashboard;

use App\Models\ProductionRun;
use App\Models\SystemSetting;
use Livewire\Component;

class ManagementDashboard extends Component
{
    public string $startDate = '';
    public string $endDate = '';
    public string $originFilter = '';

    public function mount()
    {
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $targetCapacity = (float) SystemSetting::get('target_capacity_kg_hr', 1000);

        // Fetch active/locked production runs
        $query = ProductionRun::with(['mrl'])
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate);

        if ($this->originFilter) {
            $query->whereHas('mrl', function ($q) {
                $q->where('origin_region', 'like', '%' . $this->originFilter . '%');
            });
        }

        $allRuns = $query->get();

        // 1. Live Machine Statuses (Group A, Group B, Group C)
        $groups = ['group_a' => 'Group A', 'group_b' => 'Group B', 'group_c' => 'Group C'];
        $liveStatus = [];

        foreach ($groups as $key => $name) {
            $latestRun = ProductionRun::where('group_name', $key)->latest()->first();
            $status = $latestRun ? $latestRun->machine_status : 'stopped';
            $runCode = $latestRun ? $latestRun->production_code : '-';
            $shift = $latestRun ? strtoupper(str_replace('_', ' ', $latestRun->shift)) : '-';

            $liveStatus[$key] = [
                'name' => $name,
                'status' => $status, // running, stopped, completed
                'latest_code' => $runCode,
                'shift' => $shift,
                'is_running' => $status === 'running',
            ];
        }

        // 2. Overview Metrics
        $totalInputNetKg = $allRuns->sum(fn ($r) => $r->mrl ? $r->mrl->net_weight : 0);
        $totalProductKg = $allRuns->sum('product_weight');
        $totalBitsKg = $allRuns->sum('bits_stem_weight');
        $totalDustKg = $allRuns->sum('dust_weight');
        $totalWasteKg = $allRuns->sum('waste_weight');

        $avgYield = $totalInputNetKg > 0 ? round(($totalProductKg / $totalInputNetKg) * 100, 2) : 0;
        $avgCapacity = $allRuns->count() > 0 ? round($allRuns->avg('capacity_kg_hr'), 2) : 0;
        $avgUptime = $allRuns->count() > 0 ? round($allRuns->avg('uptime_pct'), 2) : 0;
        $avgPerformance = $allRuns->count() > 0 ? round($allRuns->avg('performance_pct'), 2) : 0;

        // 3. Group Rankings
        $groupStats = [];
        foreach (['group_a', 'group_b', 'group_c'] as $gKey) {
            $gRuns = $allRuns->where('group_name', $gKey);
            $gCount = $gRuns->count();
            $gProduct = $gRuns->sum('product_weight');
            $gNet = $gRuns->sum(fn ($r) => $r->mrl ? $r->mrl->net_weight : 0);
            $gYield = $gNet > 0 ? round(($gProduct / $gNet) * 100, 2) : 0;
            $gCap = $gCount > 0 ? round($gRuns->avg('capacity_kg_hr'), 2) : 0;
            $gUptime = $gCount > 0 ? round($gRuns->avg('uptime_pct'), 2) : 0;
            $gPerf = $gCount > 0 ? round($gRuns->avg('performance_pct'), 2) : 0;

            $groupStats[strtoupper(str_replace('_', ' ', $gKey))] = [
                'count' => $gCount,
                'yield_pct' => $gYield,
                'capacity_kg_hr' => $gCap,
                'uptime_pct' => $gUptime,
                'performance_pct' => $gPerf,
            ];
        }

        // 4. Shift Performance Comparison
        $shiftStats = [];
        foreach (['shift_1', 'shift_2'] as $sKey) {
            $sRuns = $allRuns->where('shift', $sKey);
            $sCount = $sRuns->count();
            $sProduct = $sRuns->sum('product_weight');
            $sNet = $sRuns->sum(fn ($r) => $r->mrl ? $r->mrl->net_weight : 0);
            $sYield = $sNet > 0 ? round(($sProduct / $sNet) * 100, 2) : 0;
            $sCap = $sCount > 0 ? round($sRuns->avg('capacity_kg_hr'), 2) : 0;
            $sUptime = $sCount > 0 ? round($sRuns->avg('uptime_pct'), 2) : 0;
            $sPerf = $sCount > 0 ? round($sRuns->avg('performance_pct'), 2) : 0;

            $shiftStats[strtoupper(str_replace('_', ' ', $sKey))] = [
                'count' => $sCount,
                'yield_pct' => $sYield,
                'capacity_kg_hr' => $sCap,
                'uptime_pct' => $sUptime,
                'performance_pct' => $sPerf,
            ];
        }

        return view('livewire.dashboard.management-dashboard', compact(
            'targetCapacity',
            'liveStatus',
            'totalInputNetKg',
            'totalProductKg',
            'totalBitsKg',
            'totalDustKg',
            'totalWasteKg',
            'avgYield',
            'avgCapacity',
            'avgUptime',
            'avgPerformance',
            'groupStats',
            'shiftStats',
            'allRuns'
        ));
    }
}
