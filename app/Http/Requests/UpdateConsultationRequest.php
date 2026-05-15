<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_name' => ['sometimes', 'required', 'string', 'max:120'],
            'raw_notes' => ['sometimes', 'required', 'string', 'min:5', 'max:10000'],
            'structured_soap' => ['sometimes', 'array'],
            'structured_soap.subjective' => ['sometimes', 'array'],
            'structured_soap.objective' => ['sometimes', 'array'],
            'structured_soap.assessment' => ['sometimes', 'array'],
            'structured_soap.plan' => ['sometimes', 'array'],
        ];
    }
}
