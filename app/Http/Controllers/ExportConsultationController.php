<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class ExportConsultationController extends Controller
{
    public function __invoke(Consultation $consultation): Response
    {
        abort_if(empty($consultation->structured_soap), 404, 'SOAP note has not been generated yet.');

        $pdf = Pdf::loadView('pdf.soap', [
            'consultation' => $consultation,
            'soap' => $consultation->structured_soap,
        ])->setPaper('a4');

        return $pdf->stream("soap-{$consultation->id}.pdf");
    }
}
