<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PROCESS CERTIFICATE - {{ $batch->batch_code }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm 12mm 15mm 12mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #000; font-size: 9.5pt; line-height: 1.3; }
        
        .header-title { font-size: 16pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px; }
        
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .meta-table td { padding: 3px 0; font-size: 9.5pt; }
        .meta-table td.label { font-weight: bold; width: 150px; }
        
        .section-header { font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-top: 14px; margin-bottom: 6px; }
        
        table.pdf-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; font-size: 8.5pt; }
        table.pdf-table th, table.pdf-table td { border: 1px solid #000; padding: 4px 5px; text-align: center; }
        table.pdf-table th { background-color: #e4e4e7; font-weight: bold; text-transform: uppercase; }
        table.pdf-table td.text-left { text-align: left; }
        table.pdf-table td.text-right { text-align: right; }
        
        .remarks-box { font-size: 8pt; color: #18181b; margin-bottom: 14px; }
        .remarks-box ol { margin: 2px 0 0 16px; padding: 0; }
        .remarks-box li { margin-bottom: 1px; }

        .custom-remark-text { font-style: italic; margin-top: 3px; font-weight: bold; color: #15803d; }

        .material-desc { font-weight: bold; font-size: 9pt; margin-top: 10px; margin-bottom: 4px; }
        .grand-total-row td { font-weight: bold; background-color: #f4f4f5; }
        
        .sig-container { width: 100%; margin-top: 30px; }
        .sig-box { float: right; width: 220px; text-align: center; font-size: 9pt; }
        .sig-space { height: 55px; }
    </style>
</head>
<body>

    <!-- Document Header -->
    <div class="header-title">PROCESS CERTIFICATE</div>

    <!-- Header Metadata -->
    <table class="meta-table">
        <tr>
            <td class="label">Customer Name</td>
            <td>: {{ $batch->customer->name ?? '-' }}</td>
            <td class="label" style="text-align: right;">Issued Date :</td>
            <td style="width: 120px; text-align: right;">{{ $batch->locked_at ? $batch->locked_at->format('d/m/Y') : date('d/m/Y') }}</td>
        </tr>
    </table>

    <!-- SECTION 1: DELIVERY NOTE (DN) -->
    <div class="section-header">1. DELIVERY NOTE (DN)</div>
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
            <tr>
                <td class="text-left">{{ $batch->productType->name ?? '-' }}</td>
                <td>{{ $batch->origin->region_name ?? '-' }}</td>
                <td>{{ $batch->dn_total_pack }}</td>
                <td>{{ $batch->pack_type }}</td>
                <td class="text-right">{{ number_format($batch->dn_gross_weight, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($batch->dn_tare_weight, 2, ',', '.') }}</td>
                <td class="text-right" style="font-weight:bold;">{{ number_format($batch->dn_netto_weight, 2, ',', '.') }}</td>
                <td>{{ $batch->date_of_receipt ? $batch->date_of_receipt->format('d/m/Y') : '-' }}</td>
                <td>{{ $batch->deliveryNote->dn_number ?? '-' }}</td>
            </tr>
        </tbody>
    </table>
    <div class="remarks-box">
        <strong>Remarks :</strong>
        <ol>
            <li>Gross qty. Based on Delivery Note.</li>
            <li>Tare qty. Based on actual weighing during the process.</li>
        </ol>
        @if(!empty($batch->custom_dn_remark))
            <div class="custom-remark-text">Catatan Khusus DN: {{ $batch->custom_dn_remark }}</div>
        @endif
    </div>

    <!-- SECTION 2: MATERIAL RECEIPT LIST (MRL) -->
    <div class="section-header">2. MATERIAL RECEIPT LIST (MRL)</div>
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
            <tr>
                <td class="text-left">{{ $batch->productType->name ?? '-' }}</td>
                <td>{{ $batch->origin->region_name ?? '-' }}</td>
                <td>{{ $batch->mrl_total_pack }}</td>
                <td>{{ $batch->pack_type }}</td>
                <td class="text-right">{{ number_format($batch->mrl_gross_weight, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($batch->mrl_tare_weight, 2, ',', '.') }}</td>
                <td class="text-right" style="font-weight:bold;">{{ number_format($batch->mrl_netto_weight, 2, ',', '.') }}</td>
                <td>{{ $batch->date_of_receipt ? $batch->date_of_receipt->format('d/m/Y') : '-' }}</td>
                <td class="text-right" style="font-weight:bold; color: {{ $batch->discrepancy_dn_vs_mrl_kg != 0 ? '#b91c1c' : '#000' }};">
                    {{ number_format($batch->discrepancy_dn_vs_mrl_kg, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>
    <div class="remarks-box">
        <strong>Remarks :</strong>
        <ol>
            <li>Gross qty. Based on Material receipt list.</li>
            <li>Tare qty. Based on actual weighing during the process.</li>
            <li><strong>Penjelasan Selisih:</strong> {{ $batch->discrepancy_explanation }}</li>
        </ol>
        @if(!empty($batch->custom_mrl_remark))
            <div class="custom-remark-text">Catatan Khusus MRL: {{ $batch->custom_mrl_remark }}</div>
        @endif
    </div>

    <!-- SECTION 3: SEPARATION RESULTS REPORT -->
    <div class="section-header">3. SEPARATION RESULTS REPORT</div>
    
    <div class="material-desc">1. Material Desc: {{ $batch->productType->name ?? '-' }}</div>
    
    <!-- Itemized Sack Weighing Grid Table (UNMERGED INDIVIDUAL ROWS AS PER SOP) -->
    <table class="pdf-table">
        <thead>
            <tr>
                <th>Product Type</th>
                <th>Origin</th>
                <th>Pack Type</th>
                <th style="width: 35px;">No</th>
                <th>Gross (Kg)</th>
                <th>Tare (Kg)</th>
                <th>Netto (Kg)</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batch->weighingItems->sortBy('sack_number') as $item)
                <tr>
                    <td class="text-left">{{ $batch->productType->name ?? '-' }}</td>
                    <td>{{ $batch->origin->region_name ?? '-' }}</td>
                    <td>{{ $batch->pack_type }}</td>
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

            <!-- GRAND TOTAL ROW -->
            <tr class="grand-total-row">
                <td colspan="3" class="text-right">GRAND TOTAL</td>
                <td>{{ $batch->weighingItems->count() }}</td>
                <td class="text-right">{{ number_format($batch->mrl_gross_weight, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($batch->mrl_tare_weight, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($batch->mrl_netto_weight, 2, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- SEPARATION RESULT SUMMARY TABLE -->
    <div style="margin-top: 12px;"></div>
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
                <td class="text-left">{{ $batch->productType->name ?? '-' }}</td>
                <td>{{ $batch->origin->region_name ?? '-' }}</td>
                <td>{{ $batch->pack_type }}</td>
                <td class="text-right" style="font-weight:bold; color:#15803d;">{{ number_format($batch->separation_product_kg, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($batch->separation_bits_stem_netto_kg ?: $batch->separation_bits_stem_kg, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($batch->separation_dust_kg, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($batch->separation_waste_kg, 2, ',', '.') }}</td>
                <td class="text-right" style="font-weight:bold;">{{ number_format($batch->mrl_netto_weight, 2, ',', '.') }}</td>
            </tr>
            <!-- PERCENTAGE (YIELD) ROW -->
            <tr style="font-weight:bold; background-color:#f4f4f5;">
                <td colspan="3" class="text-right">PERCENTAGE (YIELD)</td>
                <td class="text-right" style="color:#15803d;">{{ number_format($batch->yield_product_pct, 2, ',', '.') }}%</td>
                <td class="text-right">{{ number_format($batch->yield_bits_stem_pct, 2, ',', '.') }}%</td>
                <td class="text-right">{{ number_format($batch->yield_dust_pct, 2, ',', '.') }}%</td>
                <td class="text-right">{{ number_format($batch->yield_waste_pct, 2, ',', '.') }}%</td>
                <td class="text-right">100,00%</td>
            </tr>
        </tbody>
    </table>

    <div class="remarks-box">
        <strong>Remarks :</strong>
        <ol>
            <li>Gross qty. based on actual weighing during the process.</li>
            <li>Tare qty. based on actual weighing during the process.</li>
            <li>Uncountable waste qty. based on teoritical calculation.</li>
            <li>Percentage Yield based on total Nett. qty actual weighing.</li>
            @if(($batch->separation_bits_stem_gross_kg ?? 0) > 0)
                <li>Bits Stem Weighing Detail: Gross {{ number_format($batch->separation_bits_stem_gross_kg, 2, ',', '.') }} kg, Tare {{ number_format($batch->separation_bits_stem_tare_kg, 2, ',', '.') }} kg, Netto {{ number_format($batch->separation_bits_stem_netto_kg, 2, ',', '.') }} kg.</li>
            @endif
            @if(($batch->separation_dust_gross_kg ?? 0) > 0)
                <li>Dust Weighing Detail: Gross {{ number_format($batch->separation_dust_gross_kg, 2, ',', '.') }} kg, Tare {{ number_format($batch->separation_dust_tare_kg, 2, ',', '.') }} kg, Netto {{ number_format($batch->separation_dust_netto_kg ?: $batch->separation_dust_kg, 2, ',', '.') }} kg.</li>
            @endif
        </ol>
        @if(!empty($batch->custom_separation_remark))
            <div class="custom-remark-text">Catatan Khusus Pemisahan: {{ $batch->custom_separation_remark }}</div>
        @endif
    </div>

    <!-- Signature Sign-off -->
    <div class="sig-container">
        <div class="sig-box">
            <div>Authorized Signature,</div>
            <div class="sig-space"></div>
            <div><strong>({{ $batch->supervisorApprovedBy->name ?? ($batch->createdBy->name ?? 'Factory Quality Control') }})</strong></div>
        </div>
    </div>

</body>
</html>
