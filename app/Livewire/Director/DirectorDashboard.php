<?php

namespace App\Livewire\Director;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\Origin;
use App\Models\ProductType;
use Livewire\Component;

class DirectorDashboard extends Component
{
    public string $period = 'all';

    public function render()
    {
        $batches = Batch::with(['customer', 'deliveryNote', 'productType', 'origin'])
            ->latest()
            ->get();

        $totalBatches = $batches->count();
        $closedBatches = $batches->whereIn('status', ['CLOSED', 'locked'])->count();

        $totalNettoKg = $batches->sum('mrl_netto_weight');
        $totalProductKg = $batches->sum('separation_product_kg');
        $totalBitsStemKg = $batches->sum('separation_bits_stem_kg');
        $totalDustKg = $batches->sum('separation_dust_kg');
        $totalWasteKg = $batches->sum('separation_waste_kg');

        $avgYieldProductPct = $totalNettoKg > 0 ? round(($totalProductKg / $totalNettoKg) * 100, 2) : 0;
        $avgYieldBitsPct = $totalNettoKg > 0 ? round(($totalBitsStemKg / $totalNettoKg) * 100, 2) : 0;
        $avgYieldDustPct = $totalNettoKg > 0 ? round(($totalDustKg / $totalNettoKg) * 100, 2) : 0;
        $avgYieldWastePct = $totalNettoKg > 0 ? round(($totalWasteKg / $totalNettoKg) * 100, 2) : 0;

        // Discrepancy Count
        $discrepancyCount = $batches->filter(fn($b) => $b->discrepancy_dn_vs_mrl_kg != 0)->count();

        // Origin breakdown data for charts
        $originsData = Origin::all()->map(function ($orig) {
            $origBatches = Batch::where('origin_id', $orig->id)->get();
            $netto = $origBatches->sum('mrl_netto_weight');
            $prod = $origBatches->sum('separation_product_kg');
            $stem = $origBatches->sum('separation_bits_stem_kg');
            $dust = $origBatches->sum('separation_dust_kg');
            $waste = $origBatches->sum('separation_waste_kg');

            return [
                'name' => $orig->region_name,
                'total_kg' => $netto,
                'product_kg' => $prod,
                'stem_kg' => $stem,
                'dust_kg' => $dust,
                'waste_kg' => $waste,
                'yield_product' => $netto > 0 ? round(($prod / $netto) * 100, 2) : 0,
            ];
        });

        return view('livewire.director.director-dashboard', compact(
            'batches',
            'totalBatches',
            'closedBatches',
            'totalNettoKg',
            'totalProductKg',
            'totalBitsStemKg',
            'totalDustKg',
            'totalWasteKg',
            'avgYieldProductPct',
            'avgYieldBitsPct',
            'avgYieldDustPct',
            'avgYieldWastePct',
            'discrepancyCount',
            'originsData'
        ));
    }
}
