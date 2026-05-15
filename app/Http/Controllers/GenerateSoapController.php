<?php

namespace App\Http\Controllers;

use App\Exceptions\ClinicalScribeException;
use App\Models\Consultation;
use App\Services\ClinicalScribeService;
use Illuminate\Support\Facades\Log;

class GenerateSoapController extends Controller
{
    public function __invoke(Consultation $consultation, ClinicalScribeService $scribe)
    {
        $consultation->update(['status' => Consultation::STATUS_GENERATING]);

        try {
            $soap = $scribe->transform($consultation->raw_notes, $consultation->patient_name);
        } catch (ClinicalScribeException $e) {
            Log::warning('SOAP generation failed', [
                'consultation_id' => $consultation->id,
                'error' => $e->getMessage(),
            ]);

            $consultation->update(['status' => Consultation::STATUS_FAILED]);

            return back()->with('error', 'Unable to generate SOAP note. Please try again.');
        }

        $consultation->update([
            'structured_soap' => $soap,
            'status' => Consultation::STATUS_COMPLETED,
        ]);

        return back()->with('success', 'SOAP note generated.');
    }
}
