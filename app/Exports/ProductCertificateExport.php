<?php

namespace App\Exports;

use App\Models\ProductCertificate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductCertificateExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected ProductCertificate $certificate;

    public function __construct(ProductCertificate $certificate)
    {
        $this->certificate = $certificate;
    }

    public function collection()
    {
        return collect([$this->certificate]);
    }

    public function headings(): array
    {
        return [
            'No Sertifikat',
            'No MRL',
            'No Surat Jalan (DN)',
            'Batch ID',
            'Asal Tembakau',
            'Grade Quality',
            'Shift',
            'Group',
            'Net Weight (kg)',
            'Product Weight (kg)',
            'Bits Stem (kg)',
            'Dust (kg)',
            'Waste (kg)',
            'Product Yield (%)',
            'Bits Stem (%)',
            'Dust (%)',
            'Waste (%)',
            'Kapasitas (kg/jam)',
            'Performance (%)',
            'Tanggal Terbit',
        ];
    }

    public function map($cert): array
    {
        $snap = $cert->data_snapshot ?? [];
        $run = $cert->productionRun;
        $mrl = $run ? $run->mrl : null;

        return [
            $cert->certificate_number,
            $snap['mrl_number'] ?? ($mrl ? $mrl->mrl_number : '-'),
            ($mrl && $mrl->deliveryNote) ? $mrl->deliveryNote->formatted_dn_number : ((!empty($snap['dn_number']) && !str_starts_with($snap['dn_number'], 'DN-BCH-')) ? $snap['dn_number'] : '-'),
            $snap['batch_number'] ?? ($mrl ? $mrl->batch_number : '-'),
            $snap['origin_region'] ?? ($mrl ? $mrl->origin_region : '-'),
            $snap['tobacco_grade'] ?? ($mrl ? $mrl->tobacco_grade : '-'),
            strtoupper(str_replace('_', ' ', $run ? $run->shift : ($snap['shift'] ?? '-'))),
            strtoupper(str_replace('_', ' ', $run ? $run->group_name : ($snap['group'] ?? '-'))),
            $snap['net_weight'] ?? ($mrl ? $mrl->net_weight : 0),
            $run ? $run->product_weight : ($snap['product_weight'] ?? 0),
            $run ? $run->bits_stem_weight : 0,
            $run ? $run->dust_weight : 0,
            $run ? $run->waste_weight : 0,
            ($run ? $run->product_yield_pct : ($snap['product_yield_pct'] ?? 0)) . '%',
            ($run ? $run->bits_stem_pct : 0) . '%',
            ($run ? $run->dust_pct : 0) . '%',
            ($run ? $run->waste_pct : 0) . '%',
            ($run ? $run->capacity_kg_hr : ($snap['capacity_kg_hr'] ?? 0)),
            ($run ? $run->performance_pct : ($snap['performance_pct'] ?? 0)) . '%',
            $cert->issued_at ? $cert->issued_at->format('Y-m-d H:i:s') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
