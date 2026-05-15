<?php

use App\Exceptions\ClinicalScribeException;
use App\Services\ClinicalScribeService;
use OpenAI\Contracts\ClientContract;
use OpenAI\Laravel\Testing\OpenAIFake;
use OpenAI\Responses\Chat\CreateResponse;

function fakeChatResponse(string $content): CreateResponse
{
    return CreateResponse::fake([
        'choices' => [
            [
                'message' => ['role' => 'assistant', 'content' => $content],
            ],
        ],
    ]);
}

function bindOpenAIFake(array $responses): OpenAIFake
{
    $fake = new OpenAIFake($responses);
    app()->instance(ClientContract::class, $fake);
    app()->instance('openai', $fake);

    return $fake;
}

function validSoapJson(): string
{
    return json_encode([
        'subjective' => ['chief_complaint' => 'cough', 'history_of_present_illness' => '', 'review_of_systems' => ''],
        'objective' => ['vitals' => '', 'physical_exam' => '', 'diagnostics' => ''],
        'assessment' => ['primary_diagnosis' => '', 'differential_diagnoses' => [], 'clinical_reasoning' => ''],
        'plan' => ['medications' => [], 'investigations' => [], 'follow_up' => '', 'patient_education' => ''],
    ]);
}

it('transforms raw notes into structured SOAP via OpenAI', function () {
    bindOpenAIFake([fakeChatResponse(validSoapJson())]);

    $service = app(ClinicalScribeService::class);
    $soap = $service->transform('Pt c/o cough x3d', 'John Smith');

    expect($soap)
        ->toHaveKeys(['subjective', 'objective', 'assessment', 'plan'])
        ->and($soap['subjective']['chief_complaint'])->toBe('cough');

    $fake = app(ClientContract::class);
    $fake->assertSent(\OpenAI\Resources\Chat::class, function (string $method, array $payload) {
        expect($method)->toBe('create');
        expect($payload['response_format'])->toBe(['type' => 'json_object']);
        expect($payload['messages'][0]['role'])->toBe('system');
        expect($payload['messages'][1]['content'])->toContain('John Smith');
        return true;
    });
});

it('throws ClinicalScribeException on malformed JSON', function () {
    bindOpenAIFake([fakeChatResponse('this is not json')]);

    app(ClinicalScribeService::class)->transform('notes');
})->throws(ClinicalScribeException::class, 'malformed JSON');

it('throws ClinicalScribeException when a required section is missing', function () {
    bindOpenAIFake([fakeChatResponse(json_encode(['subjective' => [], 'objective' => [], 'assessment' => []]))]);

    app(ClinicalScribeService::class)->transform('notes');
})->throws(ClinicalScribeException::class, 'plan');
