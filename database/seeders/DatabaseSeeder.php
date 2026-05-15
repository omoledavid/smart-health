<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Invoice;
use App\Models\Location;
use App\Models\Patient;
use App\Models\PatientInsurance;
use App\Models\Service;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
            'phone' => '+1 555-0100',
        ]);

        // Specializations
        $specs = collect([
            'Cardiology', 'Dermatology', 'Pediatrics', 'Neurology',
            'General Practice', 'Orthopedics', 'Psychiatry', 'Endocrinology',
        ])->map(fn ($name) => Specialization::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => "$name specialty care",
        ]));

        // Locations
        $locations = collect([
            ['name' => 'Trustcare Las Vegas', 'city' => 'Las Vegas', 'state' => 'NV', 'address' => '123 Main St', 'phone' => '+1 702-555-0101'],
            ['name' => 'Trustcare Reno', 'city' => 'Reno', 'state' => 'NV', 'address' => '500 Plumb Ln', 'phone' => '+1 775-555-0102'],
            ['name' => 'Trustcare Henderson', 'city' => 'Henderson', 'state' => 'NV', 'address' => '78 Sunset Rd', 'phone' => '+1 702-555-0103'],
        ])->map(fn ($attrs) => Location::create($attrs + ['timezone' => 'America/Los_Angeles']));

        // Services
        $services = collect([
            ['name' => 'General Consultation', 'duration_minutes' => 30, 'price_cents' => 7500],
            ['name' => 'Follow-up Visit', 'duration_minutes' => 20, 'price_cents' => 5000],
            ['name' => 'Annual Physical', 'duration_minutes' => 45, 'price_cents' => 15000],
            ['name' => 'Telehealth Consultation', 'duration_minutes' => 25, 'price_cents' => 6000],
            ['name' => 'Pediatric Checkup', 'duration_minutes' => 30, 'price_cents' => 8500],
            ['name' => 'Vaccination', 'duration_minutes' => 15, 'price_cents' => 4000],
        ])->map(fn ($attrs) => Service::create($attrs + ['is_active' => true]));

        // Doctors
        $doctors = collect();
        $doctorNames = [
            ['Sarah', 'Johnson'], ['Michael', 'Chen'], ['Priya', 'Patel'],
            ['David', 'Williams'], ['Emily', 'Garcia'], ['James', 'Brown'],
        ];
        foreach ($doctorNames as $i => [$first, $last]) {
            $user = User::factory()->create([
                'name' => "Dr. $first $last",
                'email' => strtolower("$first.$last@clinic.test"),
                'role' => User::ROLE_DOCTOR,
                'phone' => '+1 555-02' . str_pad((string) ($i + 10), 2, '0', STR_PAD_LEFT),
            ]);

            $doctor = Doctor::create([
                'user_id' => $user->id,
                'specialization_id' => $specs->random()->id,
                'license_number' => 'LIC-' . strtoupper(Str::random(8)),
                'bio' => "Dr. $first $last is an experienced clinician.",
                'years_experience' => rand(3, 25),
                'consultation_fee_cents' => rand(5000, 20000),
            ]);
            $doctor->locations()->sync($locations->random(rand(1, 2))->pluck('id'));
            $doctor->services()->sync($services->random(rand(2, 4))->pluck('id'));

            // Mon-Fri 9-17
            foreach (range(1, 5) as $dow) {
                DoctorAvailability::create([
                    'doctor_id' => $doctor->id,
                    'location_id' => $doctor->locations->first()?->id,
                    'day_of_week' => $dow,
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                ]);
            }
            $doctors->push($doctor);
        }

        // Patient demo user (for patient login)
        $patientUser = User::factory()->create([
            'name' => 'Jane Patient',
            'email' => 'patient@example.com',
            'role' => User::ROLE_PATIENT,
            'phone' => '+1 555-0301',
        ]);

        // Patients
        $firstNames = ['Alice','Bob','Carol','Daniel','Eva','Frank','Grace','Henry','Ivy','Jack','Kate','Liam','Mia','Noah','Olivia','Paul','Quinn','Ruby','Sam','Tina'];
        $lastNames = ['Anderson','Baker','Carter','Davis','Edwards','Foster','Green','Harris','Ingram','Jones'];
        $patients = collect();

        // Seed the demo patient first
        $patients->push(Patient::create([
            'user_id' => $patientUser->id,
            'mrn' => 'MRN-' . strtoupper(Str::random(8)),
            'first_name' => 'Jane',
            'last_name' => 'Patient',
            'email' => 'patient@example.com',
            'phone' => '+1 555-0301',
            'dob' => '1990-04-12',
            'gender' => 'female',
            'blood_group' => 'O+',
            'address' => '12 Demo Ave',
            'city' => 'Las Vegas',
            'state' => 'NV',
            'postal_code' => '89101',
            'emergency_contact_name' => 'John Patient',
            'emergency_contact_phone' => '+1 555-0302',
            'onboarding_status' => Patient::ONBOARDING_COMPLETED,
            'onboarding_step' => 4,
        ]));

        for ($i = 0; $i < 24; $i++) {
            $first = $firstNames[array_rand($firstNames)];
            $last = $lastNames[array_rand($lastNames)];
            $patients->push(Patient::create([
                'mrn' => 'MRN-' . strtoupper(Str::random(8)),
                'first_name' => $first,
                'last_name' => $last,
                'email' => strtolower("$first.$last.$i@mail.test"),
                'phone' => '+1 555-04' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'dob' => Carbon::now()->subYears(rand(20, 80))->subDays(rand(0, 365))->toDateString(),
                'gender' => ['male', 'female', 'other'][rand(0, 2)],
                'blood_group' => ['A+','A-','B+','B-','AB+','O+','O-'][rand(0, 6)],
                'address' => rand(1, 999) . ' Pine St',
                'city' => ['Las Vegas','Reno','Henderson'][rand(0, 2)],
                'state' => 'NV',
                'onboarding_status' => Patient::ONBOARDING_COMPLETED,
                'onboarding_step' => 4,
                'invite_token' => null,
            ]));
        }

        // A pending onboarding patient with invite token
        Patient::create([
            'mrn' => 'MRN-' . strtoupper(Str::random(8)),
            'first_name' => 'Pending',
            'last_name' => 'Newcomer',
            'email' => 'newcomer@example.com',
            'onboarding_status' => Patient::ONBOARDING_PENDING,
            'onboarding_step' => 0,
            'invite_token' => Str::random(48),
        ]);

        // Insurance for some patients
        foreach ($patients->take(10) as $p) {
            PatientInsurance::create([
                'patient_id' => $p->id,
                'provider_name' => ['Aetna', 'BlueCross', 'Cigna', 'United', 'Kaiser'][rand(0, 4)],
                'policy_number' => 'POL-' . strtoupper(Str::random(10)),
                'group_number' => 'GRP-' . rand(1000, 9999),
                'holder_name' => $p->full_name,
                'holder_relationship' => 'self',
                'effective_from' => Carbon::now()->subYears(1),
                'effective_to' => Carbon::now()->addYears(1),
                'is_primary' => true,
            ]);
        }

        // Appointments — all in the past (up to 60 days ago through today)
        for ($i = 0; $i < 60; $i++) {
            $when = Carbon::now()->subDays(rand(0, 60))->setTime(rand(9, 16), [0, 15, 30, 45][rand(0, 3)]);
            $doctor = $doctors->random();
            $patient = $patients->random();
            $service = $services->random();
            $status = $when->isFuture() ? Appointment::STATUS_SCHEDULED : Appointment::STATUS_COMPLETED;

            Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'location_id' => $doctor->locations->first()?->id,
                'service_id' => $service->id,
                'scheduled_at' => $when,
                'duration_minutes' => $service->duration_minutes,
                'status' => $status,
                'visit_type' => rand(0, 5) === 0 ? 'telehealth' : 'in_person',
                'reason' => 'Routine consultation',
            ]);
        }

        // A few sample consultations linked to completed appointments
        foreach (Appointment::where('status', Appointment::STATUS_COMPLETED)->limit(5)->get() as $appt) {
            Consultation::create([
                'appointment_id' => $appt->id,
                'patient_id' => $appt->patient_id,
                'doctor_id' => $appt->doctor_id,
                'patient_name' => $appt->patient->full_name,
                'raw_notes' => 'Patient presented with mild symptoms. Vitals normal. Discussed lifestyle interventions and prescribed follow-up.',
                'status' => Consultation::STATUS_DRAFT,
            ]);
        }

        // A couple of invoices for completed appointments
        foreach (Appointment::where('status', Appointment::STATUS_COMPLETED)->limit(8)->get() as $appt) {
            $price = $appt->service?->price_cents ?? 7500;
            $invoice = Invoice::create([
                'number' => 'INV-' . str_pad((string) $appt->id, 6, '0', STR_PAD_LEFT),
                'patient_id' => $appt->patient_id,
                'appointment_id' => $appt->id,
                'subtotal_cents' => $price,
                'tax_cents' => 0,
                'discount_cents' => 0,
                'total_cents' => $price,
                'paid_cents' => rand(0, 1) ? $price : 0,
                'status' => rand(0, 1) ? 'paid' : 'sent',
                'issued_on' => $appt->scheduled_at->toDateString(),
                'due_on' => $appt->scheduled_at->copy()->addDays(30)->toDateString(),
            ]);
            $invoice->items()->create([
                'service_id' => $appt->service_id,
                'description' => $appt->service?->name ?? 'Consultation',
                'quantity' => 1,
                'unit_price_cents' => $price,
                'total_cents' => $price,
            ]);
        }
    }
}
