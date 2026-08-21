<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PROCESS CERTIFICATE - {{ $batch->batch_code }}</title>
    <style>
        @page { size: A4 portrait; margin: 12mm 10mm 12mm 10mm; }
        * { box-sizing: border-box; }
        html, body { 
            background-color: #ffffff !important; 
            background: #ffffff !important; 
            color: #000000 !important; 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 8.5pt; 
            line-height: 1.25; 
            margin: 0;
            padding: 0;
        }
        
        .header-title { font-size: 15pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px; text-decoration: underline; color: #000000; }
        
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9pt; color: #000000; }
        .meta-table td { padding: 2px 0; color: #000000; }
        .meta-table td.label { font-weight: bold; width: 100px; color: #000000; }
        
        .section-header { font-size: 9.5pt; font-weight: bold; text-transform: uppercase; margin-top: 14px; margin-bottom: 4px; text-decoration: underline; color: #000000; }
        .material-title { font-size: 9pt; font-weight: bold; margin-top: 10px; margin-bottom: 4px; text-decoration: underline; color: #000000; }
        
        table.pdf-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; font-size: 8pt; background-color: #ffffff; color: #000000; }
        table.pdf-table th, table.pdf-table td { border: 1px solid #000; padding: 3px 4px; text-align: center; color: #000000; background-color: #ffffff; }
        table.pdf-table th { background-color: #ffffff; font-weight: bold; text-transform: uppercase; font-size: 7.5pt; border-bottom: 2px solid #000; color: #000000; }
        table.pdf-table td.text-left { text-align: left; }
        table.pdf-table td.text-right { text-align: right; }
        
        .remarks-box { font-size: 7.5pt; color: #dc2626; font-style: italic; margin-bottom: 12px; }
        .remarks-box strong { color: #dc2626; font-style: normal; text-decoration: underline; }
        .remarks-box ol { margin: 1px 0 0 14px; padding: 0; }
        .remarks-box li { margin-bottom: 0px; }

        .custom-remark-text { font-style: italic; margin-top: 2px; font-weight: bold; color: #15803d; }
        .grand-total-row td { font-weight: bold; background-color: #ffffff; border-top: 2px solid #000; border-bottom: 2px solid #000; color: #000000; }
        
        .sig-container { width: 100%; margin-top: 20px; page-break-inside: avoid; clear: both; }
        .sig-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .sig-table td { width: 50%; text-align: center; vertical-align: top; font-size: 8.5pt; border: none !important; padding: 0 15px; color: #000000; }
        .sig-space { height: 45px; }
        .sig-name { font-weight: bold; text-decoration: underline; font-size: 8.5pt; color: #000000; }
        .sig-title { font-size: 7.5pt; color: #444; margin-top: 2px; }

        @media screen {
            html {
                background-color: #27272a !important;
                padding: 16px 8px;
            }
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                max-width: 210mm;
                min-height: 297mm;
                margin: 0 auto !important;
                padding: 14mm 12mm !important;
                box-shadow: 0 10px 35px rgba(0, 0, 0, 0.5);
                border-radius: 4px;
            }
        }
    </style>
</head>
<body>

    @php
        $batches = isset($relatedBatches) && count($relatedBatches) > 0 ? $relatedBatches : collect([$batch]);
    @endphp

    <!-- Document Header -->
    <div class="header-title">PROCESS CERTIFICATE</div>

    <!-- Header Metadata -->
    <table class="meta-table">
        <tr>
            <td class="label">Customer</td>
            <td>: {{ $batch->customer->name ?? '-' }}</td>
            <td class="label" style="text-align: right;">Issued Date :</td>
            <td style="width: 120px; text-align: right;">{{ $batch->locked_at ? $batch->locked_at->format('d/m/Y') : date('d/m/Y') }}</td>
        </tr>
    </table>

    <!-- SECTION 1: DELIVERY NOTE (DN) -->
    <div class="section-header">DELIVERY NOTE (DN) :</div>
    <table class="pdf-table">
        <thead>
            <tr>
                <th>Product Type</th>
                <th>Origin</th>
                <th>Total Pack (Unit)</th>
                <th>Pack Type</th>
                <th>Gross (Kg)</th>
                <th>Tare (Kg)</th>
                <th>Netto (Kg)</th>
                <th>Date of Receipt</th>
                <th>Delivery Note Number</th>
            </tr>
        </thead>
        <tbody>
            @foreach($batches as $b)
                @if(!empty($b->dn_header_details) && is_array($b->dn_header_details) && count($b->dn_header_details) > 0)
                    @foreach($b->dn_header_details as $hRow)
                        <tr>
                            <td class="text-left">{{ $hRow['product_type'] ?? ($b->productType->name ?? 'RAJANGAN') }}</td>
                            <td>{{ $hRow['raw_origin'] ?? trim(($b->origin->region_name ?? '') . ' ' . ($b->material_code ?? '')) }}</td>
                            @php
                                $packVal = (int)($hRow['packs'] ?? 0);
                                if ($packVal === 0 && isset($b->sections_data[$loop->index]['sacks'])) {
                                    $packVal = count($b->sections_data[$loop->index]['sacks']);
                                }
                            @endphp
                            <td>{{ $packVal > 0 ? $packVal : '-' }}</td>
                            <td>{{ $hRow['pack_type'] ?? $b->pack_type }}</td>
                            <td class="text-right">{{ number_format($hRow['gross_kg'] ?? $b->dn_gross_weight, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($hRow['tare_kg'] ?? $b->dn_tare_weight, 2, ',', '.') }}</td>
                            <td class="text-right" style="font-weight:bold;">{{ number_format($hRow['netto_kg'] ?? $b->dn_netto_weight, 2, ',', '.') }}</td>
                            <td>{{ $b->date_of_receipt ? $b->date_of_receipt->format('d/m/Y') : '-' }}</td>
                            <td>{{ (!empty($hRow['dn_number']) && $hRow['dn_number'] !== '-') ? $hRow['dn_number'] : ($b->deliveryNote ? $b->deliveryNote->formatted_dn_number : '-') }}</td>
                        </tr>
                    @endforeach
                @else
                    @php
                        $originDisplay = trim(($b->origin->region_name ?? '') . ' ' . ($b->material_code ?? ''));
                    @endphp
                    <tr>
                        <td class="text-left">{{ $b->productType->name ?? '-' }}</td>
                        <td>{{ $originDisplay ?: '-' }}</td>
                        <td>{{ $b->dn_total_pack }}</td>
                        <td>{{ $b->pack_type }}</td>
                        <td class="text-right">{{ number_format($b->dn_gross_weight, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($b->dn_tare_weight, 2, ',', '.') }}</td>
                        <td class="text-right" style="font-weight:bold;">{{ number_format($b->dn_netto_weight, 2, ',', '.') }}</td>
                        <td>{{ $b->date_of_receipt ? $b->date_of_receipt->format('d/m/Y') : '-' }}</td>
                        <td>{{ $b->deliveryNote ? $b->deliveryNote->formatted_dn_number : '-' }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    @php
        $isBoxOrC48 = false;
        foreach($batches as $b) {
            $pt = strtolower($b->pack_type ?? '');
            if (str_contains($pt, 'box') || str_contains($pt, 'c48') || str_contains($pt, 'c-48')) {
                $isBoxOrC48 = true;
                break;
            }
        }
    @endphp
    <div class="remarks-box">
        <strong>Remark :</strong>
        <ol>
            @if($isBoxOrC48)
                <li>Gross qty. based on average minus teoritical tare weight</li>
            @else
                <li>Gross qty. Based on Delivery Note.</li>
            @endif
            <li>Tare qty. Based on actual weighing during the process.</li>
        </ol>
        @foreach($batches as $b)
            @if(!empty($b->custom_dn_remark))
                <div class="custom-remark-text">Catatan Khusus DN ({{ $b->batch_code }}): {{ $b->custom_dn_remark }}</div>
            @endif
        @endforeach
    </div>

    <!-- SECTION 2: MATERIAL RECEIPT LIST (MRL) -->
    <div class="section-header">MATERIAL RECEIPT LIST (MRL) :</div>
    <table class="pdf-table">
        <thead>
            <tr>
                <th>Product Type</th>
                <th>Origin</th>
                <th>Total Pack (Unit)</th>
                <th>Pack Type</th>
                <th>Gross (Kg)</th>
                <th>Tare (Kg)</th>
                <th>Netto (Kg)</th>
                <th>Date of Receipt</th>
                <th>Weight Discrepancy DN vs MRL (Kg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($batches as $b)
                @if(!empty($b->mrl_header_details) && is_array($b->mrl_header_details) && count($b->mrl_header_details) > 0)
                    @foreach($b->mrl_header_details as $mRow)
                        <tr>
                            <td class="text-left">{{ $mRow['product_type'] ?? ($b->productType->name ?? 'RAJANGAN') }}</td>
                            <td>{{ $mRow['raw_origin'] ?? trim(($b->origin->region_name ?? '') . ' ' . ($b->material_code ?? '')) }}</td>
                            @php
                                $mPackVal = (int)($mRow['packs'] ?? 0);
                                if ($mPackVal === 0 && isset($b->sections_data[$loop->index]['sacks'])) {
                                    $mPackVal = count($b->sections_data[$loop->index]['sacks']);
                                }
                            @endphp
                            <td>{{ $mPackVal > 0 ? $mPackVal : '-' }}</td>
                            <td>{{ $mRow['pack_type'] ?? $b->pack_type }}</td>
                            <td class="text-right">{{ number_format($mRow['gross_kg'] ?? $b->mrl_gross_weight, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($mRow['tare_kg'] ?? $b->mrl_tare_weight, 2, ',', '.') }}</td>
                            <td class="text-right" style="font-weight:bold;">{{ number_format($mRow['netto_kg'] ?? $b->mrl_netto_weight, 2, ',', '.') }}</td>
                            <td>{{ $b->date_of_receipt ? $b->date_of_receipt->format('d/m/Y') : '-' }}</td>
                            <td class="text-right" style="font-weight:bold; color: {{ ($mRow['discrepancy_kg'] ?? 0) > 0 ? '#b91c1c' : 'inherit' }};">
                                {{ number_format($mRow['discrepancy_kg'] ?? $b->discrepancy_dn_vs_mrl_kg, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                @elseif(!empty($b->dn_header_details) && is_array($b->dn_header_details) && count($b->dn_header_details) > 0)
                    @foreach($b->dn_header_details as $hRow)
                        <tr>
                            <td class="text-left">{{ $hRow['product_type'] ?? ($b->productType->name ?? 'RAJANGAN') }}</td>
                            <td>{{ $hRow['raw_origin'] ?? trim(($b->origin->region_name ?? '') . ' ' . ($b->material_code ?? '')) }}</td>
                            <td>{{ $hRow['packs'] ?? $b->mrl_total_pack }}</td>
                            <td>{{ $hRow['pack_type'] ?? $b->pack_type }}</td>
                            <td class="text-right">{{ number_format($hRow['gross_kg'] ?? $b->mrl_gross_weight, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($hRow['tare_kg'] ?? $b->mrl_tare_weight, 2, ',', '.') }}</td>
                            <td class="text-right" style="font-weight:bold;">{{ number_format($hRow['netto_kg'] ?? $b->mrl_netto_weight, 2, ',', '.') }}</td>
                            <td>{{ $b->date_of_receipt ? $b->date_of_receipt->format('d/m/Y') : '-' }}</td>
                            <td class="text-right" style="font-weight:bold;">
                                {{ number_format($b->discrepancy_dn_vs_mrl_kg, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    @php
                        $originDisplay = trim(($b->origin->region_name ?? '') . ' ' . ($b->material_code ?? ''));
                    @endphp
                    <tr>
                        <td class="text-left">{{ $b->productType->name ?? '-' }}</td>
                        <td>{{ $originDisplay ?: '-' }}</td>
                        <td>{{ $b->mrl_total_pack }}</td>
                        <td>{{ $b->pack_type }}</td>
                        <td class="text-right">{{ number_format($b->mrl_gross_weight, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($b->mrl_tare_weight, 2, ',', '.') }}</td>
                        <td class="text-right" style="font-weight:bold;">{{ number_format($b->mrl_netto_weight, 2, ',', '.') }}</td>
                        <td>{{ $b->date_of_receipt ? $b->date_of_receipt->format('d/m/Y') : '-' }}</td>
                        <td class="text-right" style="font-weight:bold;">
                            {{ number_format($b->discrepancy_dn_vs_mrl_kg, 2, ',', '.') }}
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <div class="remarks-box">
        <strong>Remark :</strong>
        <ol>
            @if($isBoxOrC48)
                <li>Gross qty. based on average minus teoritical tare weight</li>
            @else
                <li>Gross qty. Based on Material receipt list.</li>
            @endif
            <li>Tare qty. Based on actual weighing during the process.</li>
            @foreach($batches as $b)
                @if(!empty($b->discrepancy_explanation))
                    <li>Penjelasan Selisih ({{ $b->batch_code }}): {{ $b->discrepancy_explanation }}</li>
                @endif
            @endforeach
        </ol>
        @foreach($batches as $b)
            @if(!empty($b->custom_mrl_remark))
                <div class="custom-remark-text">Catatan Khusus MRL ({{ $b->batch_code }}): {{ $b->custom_mrl_remark }}</div>
            @endif
        @endforeach
    </div>

    <!-- SECTION 3: SEPARATION RESULTS REPORT -->
    <div class="section-header">SEPARATION RESULTS REPORT :</div>
    
    @php $materialCounter = 1; @endphp
    @foreach($batches as $b)
        @if(!empty($b->sections_data) && is_array($b->sections_data) && count($b->sections_data) > 0)
            @foreach($b->sections_data as $sec)
                @php
                    $rawOrig = $sec['raw_origin'] ?? 'Material';
                    $sacks = $sec['sacks'] ?? [];
                    $secCount = count($sacks);
                    $secGross = array_sum(array_column($sacks, 'gross_kg'));
                    $secTare = array_sum(array_column($sacks, 'tare_kg'));
                    $secNetto = array_sum(array_column($sacks, 'netto_kg'));

                    $sep = $sec['separation'] ?? null;
                    $prodKg = $sep['product_qty'] ?? 0;
                    $bitsStemKg = $sep['bits_stem_qty'] ?? 0;
                    $dustKg = $sep['dust_qty'] ?? 0;
                    $wasteKg = $sep['uncountable_waste_qty'] ?? 0;
                    $totSeparation = ($sep['total_qty'] ?? 0) > 0 ? $sep['total_qty'] : ($prodKg + $bitsStemKg + $dustKg + $wasteKg);
                    if ($totSeparation == 0) { $totSeparation = $secNetto; }

                    $yProd = $totSeparation > 0 ? round(($prodKg / $totSeparation) * 100, 2) : 0;
                    $yBits = $totSeparation > 0 ? round(($bitsStemKg / $totSeparation) * 100, 2) : 0;
                    $yDust = $totSeparation > 0 ? round(($dustKg / $totSeparation) * 100, 2) : 0;
                    $yWaste = $totSeparation > 0 ? round(100 - ($yProd + $yBits + $yDust), 2) : 0;
                @endphp

                <div class="material-title">{{ $materialCounter++ }}. Material Desc : {{ $rawOrig }}</div>

                <!-- Sack Weighing Grid Table -->
                <table class="pdf-table">
                    <thead>
                        <tr>
                            <th>Product Type</th>
                            <th>Origin</th>
                            <th>Pack Type</th>
                            <th style="width: 30px;">No</th>
                            <th>Gross (Kg)</th>
                            <th>Tare (Kg)</th>
                            <th>Netto (Kg)</th>
                            <th>Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sacks as $item)
                            <tr>
                                <td class="text-left">{{ $b->productType->name ?? 'RAJANGAN' }}</td>
                                <td>{{ $sec['clean_region'] ?? $b->origin->region_name }}</td>
                                <td>{{ $sec['pack_type'] ?? $b->pack_type }}</td>
                                <td>{{ $item['sack_number'] }}</td>
                                <td class="text-right">{{ number_format($item['gross_kg'], 2, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($item['tare_kg'], 2, ',', '.') }}</td>
                                <td class="text-right" style="font-weight:bold;">{{ number_format($item['netto_kg'], 2, ',', '.') }}</td>
                                <td>{{ $item['remark'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">No sack weighing item recorded.</td>
                            </tr>
                        @endforelse

                        <!-- GRAND TOTAL ROW FOR THIS MATERIAL -->
                        <tr class="grand-total-row">
                            <td colspan="3" class="text-right">GRAND TOTAL</td>
                            <td>{{ $secCount }}</td>
                            <td class="text-right">{{ number_format($secGross, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($secTare, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($secNetto, 2, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>

                <!-- SEPARATION RESULT TABLE FOR THIS MATERIAL -->
                <div style="font-size: 8.5pt; font-weight: bold; margin-top: 6px; margin-bottom: 2px; text-decoration: underline;">Separation Result :</div>
                <table class="pdf-table">
                    <thead>
                        <tr>
                            <th>Product Type</th>
                            <th>Origin</th>
                            <th>Pack Type</th>
                            <th>Product Qty (Kg)</th>
                            <th>Bits Stem Qty (Kg)</th>
                            <th>Dust Qty (Kg)</th>
                            <th>Uncountable Waste Qty (Kg)</th>
                            <th>TOTAL Qty (Kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-left">{{ $b->productType->name ?? 'RAJANGAN' }}</td>
                            <td>{{ $sec['clean_region'] ?? $b->origin->region_name }}</td>
                            <td>{{ $sec['pack_type'] ?? $b->pack_type }}</td>
                            <td class="text-right" style="font-weight:bold;">{{ number_format($prodKg, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($bitsStemKg, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($dustKg, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($wasteKg, 2, ',', '.') }}</td>
                            <td class="text-right" style="font-weight:bold;">{{ number_format($totSeparation, 2, ',', '.') }}</td>
                        </tr>
                        <tr style="font-weight:bold; background-color:#ffffff; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000;">
                            <td colspan="3" class="text-right">PERCENTAGE (YIELD)</td>
                            <td class="text-right">{{ number_format($yProd, 2, ',', '.') }}%</td>
                            <td class="text-right">{{ number_format($yBits, 2, ',', '.') }}%</td>
                            <td class="text-right">{{ number_format($yDust, 2, ',', '.') }}%</td>
                            <td class="text-right">{{ number_format($yWaste, 2, ',', '.') }}%</td>
                            <td class="text-right">100,00%</td>
                        </tr>
                    </tbody>
                </table>

                <div class="remarks-box">
                    <strong>Remark :</strong>
                    <ol>
                        <li>Gross qty. based on actual weighing during the process.</li>
                        <li>Tare qty. based on actual weighing during the process.</li>
                        <li>Uncountable waste qty. based on teoritical calculation.</li>
                        <li>Percentage Yield based on total Nett. qty actual weighing.</li>
                    </ol>
                </div>
            @endforeach
        @else
            @php
                $matDesc = trim(($b->origin->region_name ?? '') . ' ' . ($b->material_code ?? ''));
                $mrlNetto = (float) $b->mrl_netto_weight;
                $prodKg = (float) $b->separation_product_kg;
                $bitsStemKg = (float) ($b->separation_bits_stem_netto_kg ?: $b->separation_bits_stem_kg);
                $dustKg = (float) $b->separation_dust_kg;
                $wasteKg = (float) $b->separation_waste_kg;

                $yProd = $mrlNetto > 0 ? round(($prodKg / $mrlNetto) * 100, 2) : (float) $b->yield_product_pct;
                $yBits = $mrlNetto > 0 ? round(($bitsStemKg / $mrlNetto) * 100, 2) : (float) $b->yield_bits_stem_pct;
                $yDust = $mrlNetto > 0 ? round(($dustKg / $mrlNetto) * 100, 2) : (float) $b->yield_dust_pct;
                $yWaste = $mrlNetto > 0 ? round(($wasteKg / $mrlNetto) * 100, 2) : (float) $b->yield_waste_pct;
            @endphp

            <div class="material-title">{{ $materialCounter++ }}. Material Desc : {{ $matDesc ?: ($b->productType->name ?? 'Material') }}</div>

            <!-- Sack Weighing Grid Table -->
            <table class="pdf-table">
                <thead>
                    <tr>
                        <th>Product Type</th>
                        <th>Origin</th>
                        <th>Pack Type</th>
                        <th style="width: 30px;">No</th>
                        <th>Gross (Kg)</th>
                        <th>Tare (Kg)</th>
                        <th>Netto (Kg)</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($b->weighingItems->sortBy('sack_number') as $item)
                        <tr>
                            <td class="text-left">{{ $b->productType->name ?? '-' }}</td>
                            <td>{{ $b->origin->region_name ?? '-' }}</td>
                            <td>{{ $b->pack_type }}</td>
                            <td>{{ $item->sack_number }}</td>
                            <td class="text-right">{{ number_format($item->gross_kg, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($item->tare_kg, 2, ',', '.') }}</td>
                            <td class="text-right" style="font-weight:bold;">{{ number_format($item->netto_kg, 2, ',', '.') }}</td>
                            <td>{{ $item->remark ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No sack weighing item recorded.</td>
                        </tr>
                    @endforelse

                    <!-- GRAND TOTAL ROW FOR THIS MATERIAL -->
                    <tr class="grand-total-row">
                        <td colspan="3" class="text-right">GRAND TOTAL</td>
                        <td>{{ $b->weighingItems->count() }}</td>
                        <td class="text-right">{{ number_format($b->mrl_gross_weight, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($b->mrl_tare_weight, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($b->mrl_netto_weight, 2, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <!-- SEPARATION RESULT TABLE FOR THIS MATERIAL -->
            <div style="font-size: 8.5pt; font-weight: bold; margin-top: 6px; margin-bottom: 2px; text-decoration: underline;">Separation Result :</div>
            <table class="pdf-table">
                <thead>
                    <tr>
                        <th>Product Type</th>
                        <th>Origin</th>
                        <th>Pack Type</th>
                        <th>Product Qty (Kg)</th>
                        <th>Bits Stem Qty (Kg)</th>
                        <th>Dust Qty (Kg)</th>
                        <th>Uncountable Waste Qty (Kg)</th>
                        <th>TOTAL Qty (Kg)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-left">{{ $b->productType->name ?? '-' }}</td>
                        <td>{{ $b->origin->region_name ?? '-' }}</td>
                        <td>{{ $b->pack_type }}</td>
                        <td class="text-right" style="font-weight:bold;">{{ number_format($prodKg, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($bitsStemKg, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($dustKg, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($wasteKg, 2, ',', '.') }}</td>
                        <td class="text-right" style="font-weight:bold;">{{ number_format($mrlNetto, 2, ',', '.') }}</td>
                    </tr>
                    <tr style="font-weight:bold; background-color:#ffffff; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000;">
                        <td colspan="3" class="text-right">PERCENTAGE (YIELD)</td>
                        <td class="text-right">{{ number_format($yProd, 2, ',', '.') }}%</td>
                        <td class="text-right">{{ number_format($yBits, 2, ',', '.') }}%</td>
                        <td class="text-right">{{ number_format($yDust, 2, ',', '.') }}%</td>
                        <td class="text-right">{{ number_format($yWaste, 2, ',', '.') }}%</td>
                        <td class="text-right">100,00%</td>
                    </tr>
                </tbody>
            </table>

            <div class="remarks-box">
                <strong>Remark :</strong>
                <ol>
                    <li>Gross qty. based on actual weighing during the process.</li>
                    <li>Tare qty. based on actual weighing during the process.</li>
                    <li>Uncountable waste qty. based on teoritical calculation.</li>
                    <li>Percentage Yield based on total Nett. qty actual weighing.</li>
                    @if(($b->separation_product_remnant_kg ?? 0) > 0)
                        <li>Product Remnant Weighing Detail: Gross {{ number_format($b->separation_product_remnant_gross_kg, 2, ',', '.') }} kg, Tare {{ number_format($b->separation_product_remnant_tare_kg, 2, ',', '.') }} kg, Netto {{ number_format($b->separation_product_remnant_kg, 2, ',', '.') }} kg.</li>
                    @endif
                </ol>
                @if(!empty($b->custom_separation_remark))
                    <div class="custom-remark-text">Catatan Khusus Pemisahan: {{ $b->custom_separation_remark }}</div>
                @endif
            </div>
        @endif
    @endforeach

    <!-- Signature Sign-off (Admin & Supervisor) -->
    <div class="sig-container">
        <table class="sig-table">
            <tr>
                <td>
                    <div style="font-size: 8.5pt;">Dibuat / Diverifikasi Oleh:</div>
                    <div style="font-weight: bold; font-size: 7.5pt; color: #444; text-transform: uppercase;">(Admin / Operator QC)</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">({{ $batch->createdBy->name ?? 'Administrator' }})</div>
                    <div class="sig-title">Admin / Factory Production QC</div>
                </td>
                <td>
                    <div style="font-size: 8.5pt;">Disetujui / Di-ACC Oleh:</div>
                    <div style="font-weight: bold; font-size: 7.5pt; color: #444; text-transform: uppercase;">(Supervisor)</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">({{ $batch->supervisorApprovedBy->name ?? ($batch->createdBy->name ?? 'Supervisor Produksi') }})</div>
                    <div class="sig-title">
                        Supervisor Produksi
                        @if($batch->supervisor_approved_at)
                            <div style="font-size: 7pt; color: #15803d; font-weight: bold; margin-top: 2px;">
                                ACC: {{ $batch->supervisor_approved_at->format('d/m/Y H:i') }}
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
