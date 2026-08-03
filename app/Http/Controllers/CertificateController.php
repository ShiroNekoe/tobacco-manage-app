<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateController extends Controller
{
    public function show(Batch $batch)
    {
        $user = Auth::user();

        // Enforce Supervisor Approval Gate for Customer accounts
        if ($user && $user->isCustomer() && ! $batch->isApprovedBySupervisor()) {
            abort(403, 'Sertifikat ini belum disetujui (ACC) oleh Supervisor.');
        }

        $batch->load(['customer', 'deliveryNote', 'productType', 'origin', 'weighingItems', 'createdBy', 'supervisorApprovedBy']);

        return view('certificates.process-certificate-pdf', compact('batch'));
    }

    public function downloadPdf(Batch $batch)
    {
        $user = Auth::user();

        // Enforce Supervisor Approval Gate for Customer accounts
        if ($user && $user->isCustomer() && ! $batch->isApprovedBySupervisor()) {
            abort(403, 'Sertifikat ini belum disetujui (ACC) oleh Supervisor.');
        }

        $batch->load(['customer', 'deliveryNote', 'productType', 'origin', 'weighingItems', 'createdBy', 'supervisorApprovedBy']);

        $pdf = Pdf::loadView('certificates.process-certificate-pdf', compact('batch'))
            ->setPaper('a4', 'portrait');

        $filename = 'Process_Certificate_' . $batch->batch_code . '.pdf';

        return $pdf->download($filename);
    }
}
