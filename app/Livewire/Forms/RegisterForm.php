<?php

namespace App\Livewire\Forms;

use App\Models\Account;
use App\Models\Role;
use App\Models\Student;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Form;

class RegisterForm extends Form
{
    public string $student_id_number = '';
    public string $first_name = '';
    public ?string $middle_name = '';
    public string $last_name = '';
    public string $program = '';
    public string $year_level = '';
    public string $email = '';
    public ?string $contact_num = '';
    public string $username = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;

    /**
     * Validation rules for student registration.
     */
    public function rules(): array
    {
        return [
            'student_id_number' => ['required', 'string', 'max:50', 'unique:students,school_id_number'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'program' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(config('pgpc.college.programs', [])))],
            'year_level' => ['required', 'string', 'in:1st Year,2nd Year,3rd Year,4th Year'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:accounts,email'],
            'contact_num' => ['nullable', 'string', 'max:20'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'unique:accounts,username'],
            'password' => [
                'required',
                'string',
                'min:8',
                function ($attribute, $value, $fail) {
                    if (! preg_match('/[A-Z]/', $value)) {
                        $fail('The password must contain at least one capital letter.');
                    }
                    if (! preg_match('/[0-9]/', $value)) {
                        $fail('The password must contain at least one number.');
                    }
                },
                'same:password_confirmation',
            ],
            'terms' => ['accepted'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'password.min' => 'The password must be at least 8 characters.',
            'password.same' => 'The password confirmation does not match.',
            'terms.accepted' => 'You must agree to the Terms of Service and Privacy Policy.',
        ];
    }

    /**
     * Custom validation attribute names.
     */
    public function validationAttributes(): array
    {
        return [
            'student_id_number' => 'Student ID number',
            'first_name' => 'first name',
            'middle_name' => 'middle name',
            'last_name' => 'last name',
            'program' => 'program',
            'year_level' => 'year level',
            'email' => 'email address',
            'contact_num' => 'contact number',
            'username' => 'username',
            'password' => 'password',
            'terms' => 'terms of service',
        ];
    }

    /**
     * Store the new registered student account.
     */
    public function store(): Account
    {
        $this->validate();

        return DB::transaction(function () {
            $studentRoleId = Role::where('name', 'Student')->value('id') ?? 4;
            $googleAuth = session('google_auth');

            $account = Account::create([
                'username' => trim($this->username),
                'email' => strtolower(trim($this->email)),
                'password_hash' => Hash::make($this->password),
                'role_id' => $studentRoleId,
                'status_id' => 1, // Active
                'provider' => $googleAuth ? 'google' : null,
                'provider_id' => $googleAuth['id'] ?? null,
                'is_email_verified' => ! empty($googleAuth),
                'email_verified_at' => ! empty($googleAuth) ? now() : null,
                'terms_acknowledged_at' => now(),
                'privacy_acknowledged_at' => now(),
            ]);

            session()->forget('google_auth');

            Student::create([
                'account_id' => $account->id,
                'school_id_number' => trim($this->student_id_number),
                'first_name' => trim($this->first_name),
                'middle_name' => $this->middle_name ? trim($this->middle_name) : null,
                'last_name' => trim($this->last_name),
                'contact_num' => $this->contact_num ? trim($this->contact_num) : null,
                'library_status_id' => 1, // Active
                'program' => trim($this->program),
                'year_level' => trim($this->year_level),
            ]);

            event(new Registered($account));

            return $account;
        });
    }
}
