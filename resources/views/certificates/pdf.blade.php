<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sertifikat Mutu Produksi - {{ $certificate->certificate_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #18181b; font-size: 12px; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #b45309; padding-bottom: 15px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; color: #78350f; text-transform: uppercase; margin: 0; }
        .subtitle { font-size: 11px; color: #52525b; margin-top: 4px; }
        .cert-no { font-family: monospace; font-size: 13px; color: #92400e; font-weight: bold; }
        .section-title { font-size: 12px; font-weight: bold; text-transform: uppercase; color: #92400e; margin-top: 20px; margin-bottom: 8px; border-bottom: 1px solid #e4e4e7; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #d4d4d8; padding: 8px; text-align: left; }
        table.data-table th { background-color: #f4f4f5; font-size: 10px; uppercase; font-weight: bold; color: #3f3f46; }
        .metric-box { background-color: #f4f4f5; border: 1px solid #e4e4e7; padding: 10px; text-align: center; border-radius: 4px; }
        .metric-value { font-size: 16px; font-weight: bold; font-family: monospace; color: #15803d; }
        .footer-sig { margin-top: 40px; }
        .sig-box { text-align: center; width: 33%; display: inline-block; vertical-align: top; }
        .sig-space { height: 50px; }
    </style>
</head>
<body>
    @php
        $snap = $certificate->data_snapshot ?? [];
        $run = $certificate->productionRun;
        $mrl = $run ? $run->mrl : null;
    @endphp

    <div class="header">
        <table style="width:100%; border:none;">
            <tr>
                <td style="border:none;">
                    <h1 class="title">SERTIFIKAT MUTU HASIL PRODUKSI TEMBAKAU</h1>
                    <div class="subtitle">Web-Based Tobacco Production Management System (Enterprise Traceability Document)</div>
                </td>
                <td style="border:none; text-align:right;">
                    <div class="cert-no">NO: {{ $certificate->certificate_number }}</div>
                    <div class="subtitle">Tanggal: {{ $certificate->issued_at ? $certificate->issued_at->format('d/m/Y H:i') : '-' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Traceability Header -->
    <div class="section-title">1. Tracebilitas Bahan Baku Mentah</div>
    <table class="data-table">
        <tr>
            <th>Nomor MRL</th>
            <td>{{ $snap['mrl_number'] ?? ($mrl ? $mrl->mrl_number : '-') }}</td>
            <th>Nomor Surat Jalan (DN)</th>
            <td>{{ ($mrl && $mrl->deliveryNote) ? $mrl->deliveryNote->formatted_dn_number : ($snap['dn_number'] ?? '-') }}</td>
        </tr>
        <tr>
            <th>Nomor Batch ID</th>
            <td>{{ $snap['batch_number'] ?? ($mrl ? $mrl->batch_number : '-') }}</td>
            <th>Asal Tembakau / Grade</th>
            <td>{{ $snap['origin_region'] ?? ($mrl ? $mrl->origin_region : '-') }} / {{ $snap['tobacco_grade'] ?? ($mrl ? $mrl->tobacco_grade : '-') }}</td>
        </tr>
        <tr>
            <th>Net Weight Input (kg)</th>
            <td colspan="3"><strong>{{ number_format($snap['net_weight'] ?? ($mrl ? $mrl->net_weight : 0), 2) }} kg</strong></td>
        </tr>
    </table>

    <!-- Production Outputs & Yield Breakdown -->
    <div class="section-title">2. Ringkasan Hasil Timbangan & Indikator Mutu (KPI Yield)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Komponen Keluaran</th>
                <th style="text-align:right;">Bobot Output (kg)</th>
                <th style="text-align:right;">Persentase (%)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Produk Rajangan Jadi (Usable Product)</strong></td>
                <td style="text-align:right; font-weight:bold; color:#15803d;">{{ number_format($run ? $run->product_weight : ($snap['product_weight'] ?? 0), 2) }} kg</td>
                <td style="text-align:right; font-weight:bold; color:#15803d;">{{ $run ? $run->product_yield_pct : ($snap['product_yield_pct'] ?? 0) }}%</td>
            </tr>
            <tr>
                <td>Bits Stem (Gagang Tembakau)</td>
                <td style="text-align:right;">{{ number_format($run ? $run->bits_stem_weight : 0, 2) }} kg</td>
                <td style="text-align:right;">{{ $run ? $run->bits_stem_pct : 0 }}%</td>
            </tr>
            <tr>
                <td>Dust (Debu Tembakau)</td>
                <td style="text-align:right;">{{ number_format($run ? $run->dust_weight : 0, 2) }} kg</td>
                <td style="text-align:right;">{{ $run ? $run->dust_pct : 0 }}%</td>
            </tr>
            <tr>
                <td>Waste Sisa / Loss</td>
                <td style="text-align:right;">{{ number_format($run ? $run->waste_weight : 0, 2) }} kg</td>
                <td style="text-align:right;">{{ $run ? $run->waste_pct : 0 }}%</td>
            </tr>
        </tbody>
    </table>

    <!-- Performance & Efficiency -->
    <div class="section-title">3. Efisiensi & Kapasitas Mesin</div>
    <table class="data-table">
        <tr>
            <th>Kapasitas Produksi</th>
            <td>{{ number_format($run ? $run->capacity_kg_hr : ($snap['capacity_kg_hr'] ?? 0), 2) }} kg/jam</td>
            <th>Performance Target</th>
            <td>{{ $run ? $run->performance_pct : ($snap['performance_pct'] ?? 0) }}%</td>
        </tr>
        <tr>
            <th>Total Uptime %</th>
            <td>{{ $run ? $run->uptime_pct : 0 }}%</td>
            <th>Total Downtime</th>
            <td>{{ $run ? $run->total_downtime_minutes : 0 }} Menit</td>
        </tr>
    </table>

    <!-- Authorization Sign-offs -->
    <div class="footer-sig">
        <div class="sig-box">
            <div>Group Leader</div>
            <div class="sig-space"></div>
            <div><strong>({{ $run ? $run->group_leader_name : ($snap['group_leader'] ?? 'Group Leader') }})</strong></div>
        </div>
        <div class="sig-box">
            <div>Diterbitkan Oleh</div>
            <div class="sig-space"></div>
            <div><strong>({{ $certificate->issuedBy->name ?? 'Supervisor / Admin' }})</strong></div>
        </div>
        <div class="sig-box">
            <div>Production Manager</div>
            <div class="sig-space"></div>
            <div><strong>(Manajer Produksi)</strong></div>
        </div>
    </div>
</body>
</html>
