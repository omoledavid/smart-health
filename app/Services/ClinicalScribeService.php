<?php

namespace App\Services;

use App\Exceptions\ClinicalScribeException;
use OpenAI\Contracts\ClientContract;
use Throwable;

class ClinicalScribeService
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
You are Smart Scribe, an expert clinical documentation assistant for licensed physicians. Transform the doctor's raw shorthand notes into a complete, professional SOAP note.

Return ONLY a valid JSON object with EXACTLY this shape — no prose, no markdown:
{
  "subjective": {
    "chief_complaint": "string",
    "history_of_present_illness": "string",
    "review_of_systems": "string"
  },
  "objective": {
    "vitals": "string",
    "physical_exam": "string",
    "diagnostics": "string"
  },
  "assessment": {
    "primary_diagnosis": "string",
    "differential_diagnoses": ["string"],
    "clinical_reasoning": "string"
  },
  "plan": {
    "medications": ["string"],
    "investigations": ["string"],
    "follow_up": "string",
    "patient_education": "string"
  }
}

Rules:
- Preserve clinical meaning exactly. NEVER invent vitals, labs, medications, or findings not present or clearly implied.
- Expand abbreviations into full medical terms (e.g., "SOB" -> "shortness of breath", "HTN" -> "hypertension").
- If a section has no information, write "Not documented." (or [] for arrays).
- Use neutral, professional clinical voice. No first-person.
- Output must be a single valid JSON object parseable by json_decode.
PROMPT;

    private const REQUIRED_SECTIONS = ['subjective', 'objective', 'assessment', 'plan'];

    public function __construct(private readonly ClientContract $client) {}

    public function transform(string $rawNotes, ?string $patientName = null): array
    {
        $userContent = $patientName
            ? "Patient: {$patientName}\n\nRaw notes:\n{$rawNotes}"
            : "Raw notes:\n{$rawNotes}";

        try {
            $response = $this->client->chat()->create([
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.2,
                'messages' => [
                    ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                    ['role' => 'user', 'content' => $userContent],
                ],
            ]);
        } catch (Throwable $e) {
            throw ClinicalScribeException::apiFailure($e->getMessage());
        }

        $raw = $response->choices[0]->message->content ?? '';
        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            throw ClinicalScribeException::invalidJson($raw);
        }

        foreach (self::REQUIRED_SECTIONS as $section) {
            if (! array_key_exists($section, $decoded)) {
                throw ClinicalScribeException::missingSection($section);
            }
        }

        return $decoded;
    }
}
