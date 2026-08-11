<?php

namespace App\Http\Controllers;

use App\Models\DnShipment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DnShipmentController extends Controller
{
    /**
     * Preview HTML version of the DN Shipment (for iframe modal preview or web view)
     */
    public function show(DnShipment $shipment)
    {
        $shipment->load(['customer', 'productType', 'createdBy', 'items']);
        return view('shipments.dn-shipment-pdf', compact('shipment'));
    }

    /**
     * Download PDF version of the DN Shipment
     */
    public function downloadPdf(DnShipment $shipment)
    {
        $shipment->load(['customer', 'productType', 'createdBy', 'items']);
        $pdf = Pdf::loadView('shipments.dn-shipment-pdf', compact('shipment'))
            ->setPaper('a4', 'portrait');

        $safeDn = preg_replace('/[^A-Za-z0-9_\-]/', '_', $shipment->dn_number);
        $filename = 'DN_Shipment_' . $safeDn . '.pdf';

        return $pdf->download($filename);
    }
}
