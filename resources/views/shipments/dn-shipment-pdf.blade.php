<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>SURAT JALAN PENGIRIMAN - {{ $shipment->dn_number }}</title>
    <style>
        @page { size: A4 portrait; margin: 12mm 10mm 12mm 10mm; }
        * { box-sizing: border-box; }
        html, body { 
            background-color: #ffffff !important; 
            background: #ffffff !important; 
            color: #000000 !important; 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 8.5pt; 
            line-height: 1.3; 
            margin: 0;
            padding: 0;
        }
        
        .header-title { font-size: 14pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 2px; letter-spacing: 0.5px; text-decoration: underline; color: #000000; }
        .header-subtitle { font-size: 10pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 12px; color: #333333; }
        
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8.5pt; color: #000000; }
        .meta-table td { padding: 2px 4px; vertical-align: top; color: #000000; }
        .meta-table td.label { font-weight: bold; width: 130px; color: #000000; }
        
        .section-header { font-size: 9pt; font-weight: bold; text-transform: uppercase; margin-top: 12px; margin-bottom: 4px; text-decoration: underline; color: #000000; }
        
        table.pdf-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 8pt; background-color: #ffffff; color: #000000; }
        table.pdf-table th, table.pdf-table td { border: 1px solid #000; padding: 3px 5px; text-align: center; color: #000000; background-color: #ffffff; }
        table.pdf-table th { background-color: #f4f4f5; font-weight: bold; text-transform: uppercase; font-size: 7.5pt; border-bottom: 2px solid #000; color: #000000; }
        table.pdf-table td.text-left { text-align: left; }
        table.pdf-table td.text-right { text-align: right; }
        
        .grand-total-row td { font-weight: bold; background-color: #f9fafb !important; border-top: 2px solid #000; border-bottom: 2px solid #000; color: #000000; }
        
        .sack-breakdown-box { margin-top: 10px; margin-bottom: 10px; page-break-inside: avoid; }
        .sack-lot-title { font-size: 8.5pt; font-weight: bold; margin-bottom: 3px; color: #000; }
        
        .notes-box { margin-top: 10px; padding: 6px 8px; border: 1px dashed #666; font-size: 8pt; color: #333; }
        
        .sig-container { width: 100%; margin-top: 20px; page-break-inside: avoid; clear: both; }
        .sig-table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .sig-table td { width: 33.33%; text-align: center; vertical-align: top; font-size: 8.5pt; border: none !important; padding: 0 10px; color: #000000; }
        .sig-space { height: 48px; }
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
                padding: 15mm 12mm;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
                border-radius: 4px;
            }
        }
    </style>
</head>
<body>

    <!-- KOP SURAT / DOKUMEN HEADER -->
    <div class="header-title">SURAT JALAN PENGIRIMAN</div>
    <div class="header-subtitle">DELIVERY NOTE SHIPMENT • TOBACCO PROCESSING</div>

    <!-- META INFORMATION TABLE -->
    <table class="meta-table">
        <tr>
            <td class="label">NO. SURAT JALAN</td>
            <td>: <strong>{{ $shipment->dn_number }}</strong></td>
            <td class="label">KEPADA / CUSTOMER</td>
            <td>: <strong>{{ $shipment->customer->name ?? 'PT Falih Nur Gemilang' }}</strong></td>
        </tr>
        <tr>
            <td class="label">TANGGAL KIRIM</td>
            <td>: {{ $shipment->shipment_date ? $shipment->shipment_date->format('d F Y') : date('d F Y') }}</td>
            <td class="label">ALAMAT TUJUAN</td>
            <td>: {{ $shipment->destination ?: ($shipment->customer->address ?? '-') }}</td>
        </tr>
        <tr>
            <td class="label">NO. KENDARAAN</td>
            <td>: <strong>{{ $shipment->vehicle_number ?: '-' }}</strong></td>
            <td class="label">JENIS PRODUK</td>
            <td>: {{ $shipment->productType->name ?? 'Tobacco Cut Rag / Stem / Bits' }}</td>
        </tr>
        <tr>
            <td class="label">NAMA PENGEMUDI</td>
            <td>: {{ $shipment->driver_name ?: '-' }}</td>
            <td class="label">STATUS PENGIRIMAN</td>
            <td>: <strong>{{ strtoupper($shipment->status) }}</strong></td>
        </tr>
    </table>

    <!-- 1. RINGKASAN LOT / ORIGIN PENGIRIMAN -->
    <div class="section-header">1. RINGKASAN LOT PENGIRIMAN (SHIPMENT SUMMARY)</div>
    <table class="pdf-table">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 105px;" class="text-left">ORIGIN</th>
                <th style="width: 80px;">ORIGIN CODE</th>
                <th style="width: 85px;">JENIS MUATAN</th>
                <th style="width: 55px;">UTUH</th>
                <th style="width: 55px;">REMNANT</th>
                <th style="width: 55px;">TOTAL</th>
                <th style="width: 75px;" class="text-right">GROSS (KG)</th>
                <th style="width: 65px;" class="text-right">TARE (KG)</th>
                <th style="width: 80px;" class="text-right">NETTO (KG)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shipment->items as $index => $item)
                <tr>
                    <td>{{ $item->item_no ?: ($index + 1) }}</td>
                    <td class="text-left">
                        <strong>{{ $item->origin }}</strong>
                        @if($item->batch_code)
                            <br><span style="font-size: 8px; color: #666; font-family: monospace;">(Batch: {{ $item->batch_code }})</span>
                        @endif
                    </td>
                    <td><strong>{{ $item->origin_code ?: '' }}</strong></td>
                    <td>
                        <strong>{{ ($item->material_type ?? 'Product') === 'Product' ? 'Produk' : (($item->material_type ?? '') === 'Bits / Stem' ? 'Bits / Stem' : 'Dust') }}</strong>
                    </td>
                    <td>{{ $item->standard_sack_count }} Krg</td>
                    <td>{{ $item->has_remnant ? '1 Krg' : '-' }}</td>
                    <td><strong>{{ $item->total_sacks }} Krg</strong></td>
                    <td class="text-right">{{ number_format($item->total_gross_kg, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->total_tare_kg, 2, ',', '.') }}</td>
                    <td class="text-right font-bold"><strong>{{ number_format($item->total_netto_kg, 2, ',', '.') }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">Tidak ada data item lot pengiriman.</td>
                </tr>
            @endforelse
            <!-- GRAND TOTAL -->
            <tr class="grand-total-row">
                <td colspan="4" class="text-left"><strong>TOTAL PENGIRIMAN</strong></td>
                <td><strong>{{ $shipment->items->sum('standard_sack_count') }} Krg</strong></td>
                <td><strong>{{ $shipment->items->where('has_remnant', true)->count() }} Krg</strong></td>
                <td><strong>{{ $shipment->total_sacks }} Krg</strong></td>
                <td class="text-right"><strong>{{ number_format($shipment->total_gross_kg, 2, ',', '.') }} kg</strong></td>
                <td class="text-right"><strong>{{ number_format($shipment->total_tare_kg, 2, ',', '.') }} kg</strong></td>
                <td class="text-right"><strong>{{ number_format($shipment->total_netto_kg, 2, ',', '.') }} kg</strong></td>
            </tr>
        </tbody>
    </table>

    <!-- 2. RINCIAN TIMBANGAN KARUNG PER LOT / ORIGIN (DETAILED SACK BREAKDOWN) -->
    <div class="section-header">2. RINCIAN TIMBANGAN KARUNG (SACK-BY-SACK WEIGHING BREAKDOWN)</div>
    
    @foreach($shipment->items as $index => $item)
        @php
            $sacks = $item->generateSackList();
            $chunkSize = 10; // group in chunks of 10 sacks per sub-row
            $chunks = array_chunk($sacks, $chunkSize);
        @endphp

        <div class="sack-breakdown-box">
            <div class="sack-lot-title">
                Lot #{{ $item->item_no ?: ($index + 1) }}: <strong>{{ $item->origin }}</strong>@if(!empty($item->origin_code)) ({{ $item->origin_code }})@endif
                — <span style="color: #047857; font-weight: bold;">[{{ ($item->material_type ?? 'Product') === 'Product' ? 'Produk Utama' : (($item->material_type ?? '') === 'Bits / Stem' ? 'Bits / Stem (Gagang)' : 'Dust (Debu)') }}]</span>
                @if($item->batch_code)
                    <span style="color: #666; font-size: 8.5px; font-weight: normal;">[Batch: {{ $item->batch_code }}]</span>
                @endif
                — 
                <span>{{ $item->total_sacks }} Karung ({{ $item->standard_sack_count }} Karung Utuh @ {{ number_format($item->standard_netto_per_sack, 2, ',', '.') }} kg{{ $item->has_remnant ? ' + 1 Remnant @ ' . number_format($item->remnant_netto_kg, 2, ',', '.') . ' kg' : '' }})</span>
                — Netto: <strong>{{ number_format($item->total_netto_kg, 2, ',', '.') }} kg</strong>
            </div>

            <table class="pdf-table">
                <thead>
                    <tr>
                        <th style="width: 55px;">NO. KRUNG</th>
                        <th style="width: 140px;" class="text-left">TIPE KARUNG</th>
                        <th style="width: 95px;" class="text-right">GROSS (KG)</th>
                        <th style="width: 85px;" class="text-right">TARE (KG)</th>
                        <th style="width: 95px;" class="text-right">NETTO (KG)</th>
                        <th class="text-left">KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sacks as $s)
                        <tr>
                            <td><strong>#{{ $s['sack_no'] }}</strong></td>
                            <td class="text-left">
                                @if($s['type'] === 'Remnant')
                                    <span style="color: #b45309; font-weight: bold;">📦 Karung Remnant (Sisa)</span>
                                @else
                                    <span>Karung Utuh Standar</span>
                                @endif
                            </td>
                            <td class="text-right">{{ number_format($s['gross_kg'], 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($s['tare_kg'], 2, ',', '.') }}</td>
                            <td class="text-right font-bold"><strong>{{ number_format($s['netto_kg'], 2, ',', '.') }}</strong></td>
                            <td class="text-left" style="color: #555; font-size: 7.5pt;">
                                @if($s['type'] === 'Remnant')
                                    Sisa pengiriman lot terverifikasi
                                @else
                                    Kemasan standar {{ number_format($item->standard_netto_per_sack, 2, ',', '.') }} kg
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr class="grand-total-row">
                        <td colspan="2" class="text-left"><strong>SUBTOTAL LOT #{{ $item->item_no ?: ($index + 1) }} ({{ $item->total_sacks }} KARUNG)</strong></td>
                        <td class="text-right"><strong>{{ number_format($item->total_gross_kg, 2, ',', '.') }} kg</strong></td>
                        <td class="text-right"><strong>{{ number_format($item->total_tare_kg, 2, ',', '.') }} kg</strong></td>
                        <td class="text-right"><strong>{{ number_format($item->total_netto_kg, 2, ',', '.') }} kg</strong></td>
                        <td class="text-left" style="font-size: 7.5pt;"><strong>Terverifikasi</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach

    @if($shipment->notes)
        <div class="notes-box">
            <strong>Catatan Pengiriman:</strong> {{ $shipment->notes }}
        </div>
    @endif

    <!-- 3. TANDA TANGAN / PENGESAHAN DOKUMEN -->
    <div class="sig-container">
        <table class="sig-table">
            <tr>
                <td>
                    <div>Dikeluarkan Oleh:</div>
                    <div class="sig-title">Bagian Pengiriman / Gudang</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">( {{ $shipment->createdBy->name ?? 'Staf Gudang' }} )</div>
                    <div class="sig-title">PT Falih Nur Gemilang</div>
                </td>
                <td>
                    <div>Pengemudi / Sopir:</div>
                    <div class="sig-title">Transporter / Ekspedisi</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">( {{ $shipment->driver_name ?: 'Pengemudi / Sopir' }} )</div>
                    <div class="sig-title">No. Pol: {{ $shipment->vehicle_number ?: '-' }}</div>
                </td>
                <td>
                    <div>Diterima Oleh:</div>
                    <div class="sig-title">Penerima / Customer</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">( {{ $shipment->customer->contact_person ?? 'Penerima Barang' }} )</div>
                    <div class="sig-title">{{ $shipment->customer->name ?? 'Customer' }}</div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
