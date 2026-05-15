<?php

use App\Models\Consultation;
use App\Services\ClinicalScribeService;

beforeEach(function () {
    $this->soap = [
        'subjective' => [
            'chief_complaint' => 'Shortness of breath',
            'history_of_present_illness' => '2 days of progressive dyspnea.',
            'review_of_systems' => 'Mild fever, no chest pain.',
        ],
        'objective' => [
            'vitals' => 'T 38.1°C, HR 92, RR 20, BP 128/82.',
            'physical_exam' => 'Lungs clear bilaterally.',
            'diagnostics' => 'Not documented.',
        ],
        'assessment' => [
            'primary_diagnosis' => 'Viral upper respiratory infection',
            'differential_diagnoses' => ['Community-acquired pneumonia'],
            'clinical_reasoning' => 'Consistent with viral URI.',
        ],
        'plan' => [
            'medications' => ['Acetaminophen 500mg PRN'],
            'investigations' => [],
            'follow_up' => 'Return in 48–72h if not improving.',
            'patient_education' => 'Rest and hydration.',
        ],
    ];
});

it('stores a consultation with raw notes', function () {
    $response = $this->post(route('consultations.store'), [
        'patient_name' => 'Jane Doe',
        'raw_notes' => 'Pt c/o SOB x2d, fever 38.1.',
    ]);

    $consultation = Consultation::first();

    expect($consultation)->not->toBeNull()
        ->and($consultation->patient_name)->toBe('Jane Doe')
        ->and($consultation->status)->toBe(Consultation::STATUS_DRAFT);

    $response->assertRedirect(route('consultations.show', $consultation));
});

it('validates required fields on store', function () {
    $this->post(route('consultations.store'), [])
        ->assertSessionHasErrors(['patient_name', 'raw_notes']);
});

it('generates and persists structured SOAP via the scribe service', function () {
    $consultation = Consultation::factory()->create();

    $this->mock(ClinicalScribeService::class)
        ->shouldReceive('transform')
        ->once()
        ->with($consultation->raw_notes, $consultation->patient_name)
        ->andReturn($this->soap);

    $this->post(route('consultations.generate', $consultation))
        ->assertRedirect();

    $consultation->refresh();

    expect($consultation->status)->toBe(Consultation::STATUS_COMPLETED)
        ->and($consultation->structured_soap)->toBe($this->soap);
});

it('marks consultation as failed when scribe throws', function () {
    $consultation = Consultation::factory()->create();

    $this->mock(ClinicalScribeService::class)
        ->shouldReceive('transform')
        ->andThrow(new \App\Exceptions\ClinicalScribeException('boom'));

    $this->post(route('consultations.generate', $consultation));

    expect($consultation->fresh()->status)->toBe(Consultation::STATUS_FAILED);
});

it('allows editing the structured SOAP via update', function () {
    $consultation = Consultation::factory()->create([
        'structured_soap' => $this->soap,
        'status' => Consultation::STATUS_COMPLETED,
    ]);

    $edited = $this->soap;
    $edited['assessment']['primary_diagnosis'] = 'Acute bronchitis';
    $edited['plan']['medications'] = ['Acetaminophen 500mg PRN', 'Dextromethorphan'];

    $this->patch(route('consultations.update', $consultation), [
        'structured_soap' => $edited,
    ])->assertRedirect();

    expect($consultation->fresh()->structured_soap['assessment']['primary_diagnosis'])
        ->toBe('Acute bronchitis')
        ->and($consultation->fresh()->structured_soap['plan']['medications'])
        ->toBe(['Acetaminophen 500mg PRN', 'Dextromethorphan']);
});

it('regenerates SOAP by re-running the scribe and overwriting previous output', function () {
    $consultation = Consultation::factory()->create([
        'structured_soap' => ['stale' => true],
        'status' => Consultation::STATUS_COMPLETED,
    ]);

    $this->mock(ClinicalScribeService::class)
        ->shouldReceive('transform')
        ->once()
        ->andReturn($this->soap);

    $this->post(route('consultations.generate', $consultation))->assertRedirect();

    expect($consultation->fresh()->structured_soap)->toBe($this->soap);
});

it('returns a PDF stream on export when soap is present', function () {
    $consultation = Consultation::factory()->create([
        'structured_soap' => $this->soap,
        'status' => Consultation::STATUS_COMPLETED,
    ]);

    $response = $this->get(route('consultations.export', $consultation));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('returns 404 when exporting a consultation without SOAP', function () {
    $consultation = Consultation::factory()->create();

    $this->get(route('consultations.export', $consultation))->assertNotFound();
});
