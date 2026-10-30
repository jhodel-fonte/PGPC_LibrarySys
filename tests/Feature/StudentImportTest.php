<?php

namespace Tests\Feature;

use App\Jobs\ProcessStudentImport;
use App\Jobs\SendStudentImportInvitation;
use App\Mail\StudentImportInvitation;
use App\Models\Account;
use App\Models\StudentImport;
use App\Services\StudentSpreadsheetImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class StudentImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_detects_columns_validates_rows_and_sends_an_invitation(): void
    {
        Storage::fake('local');
        Queue::fake();
        Mail::fake();
        $staff = Account::factory()->create();
        Storage::disk('local')->put('student-imports/test.csv', "Student list\nEmail,Age,Student ID,Surname,Given Name\nstudent-import@example.com,20,IMP-100,Doe,Jane\ninvalid-email,22,IMP-101,Smith,John\n");
        $import = StudentImport::create(['uploaded_by' => $staff->id, 'filename' => 'test.csv', 'file_path' => 'student-imports/test.csv']);
        $service = app(StudentSpreadsheetImportService::class);
        $service->inspect($import);
        $import->refresh();
        $this->assertSame(1, $import->header_row);
        $service->stage($import, $import->mapping);
        $this->assertSame(1, $import->rows()->where('status', 'pending')->count());
        $this->assertSame(1, $import->rows()->where('status', 'invalid')->count());
        $import->update(['status' => 'importing']);
        (new ProcessStudentImport($import->id))->handle();
        $row = $import->rows()->where('status', 'imported')->firstOrFail();
        $this->assertDatabaseHas('students', ['school_id_number' => 'IMP-100', 'first_name' => 'Jane', 'last_name' => 'Doe']);
        $this->assertSame('pending', $row->email_status);
        Queue::assertPushed(SendStudentImportInvitation::class);
        (new SendStudentImportInvitation($row->id))->handle();
        $this->assertSame('sent', $row->fresh()->email_status);
        Mail::assertSent(StudentImportInvitation::class, fn ($mail) => $mail->hasTo('student-import@example.com'));
        $this->assertSame('completed', $import->fresh()->status);
    }

    public function test_cancelled_import_does_not_create_students(): void
    {
        $staff = Account::factory()->create();
        $import = StudentImport::create(['uploaded_by' => $staff->id, 'filename' => 'test.csv', 'status' => 'cancelled']);
        $import->rows()->create(['row_number' => 2, 'data' => ['school_id_number' => 'CANCEL-1', 'first_name' => 'A', 'last_name' => 'B', 'email' => 'cancel@example.com']]);
        (new ProcessStudentImport($import->id))->handle();
        $this->assertDatabaseMissing('students', ['school_id_number' => 'CANCEL-1']);
    }

    public function test_authorized_staff_can_upload_and_review_columns(): void
    {
        Storage::fake('local');
        $admin = Account::factory()->create(['role_id' => 1]);
        Livewire::actingAs($admin)->test(\App\Livewire\Pages\Dashboard\ImportStudents::class)
            ->set('spreadsheet', UploadedFile::fake()->createWithContent('students.csv', "Student ID,First Name,Last Name,Email\nSTAFF-1,Jane,Doe,staff-test@example.com\n"))
            ->call('inspect')
            ->call('review')
            ->assertHasNoErrors()
            ->assertSee('STAFF-1')
            ->assertSee('Import valid students');
    }

    public function test_unprivileged_account_cannot_open_student_import(): void
    {
        $account = Account::factory()->create(['role_id' => 4]);
        Livewire::actingAs($account)->test(\App\Livewire\Pages\Dashboard\ImportStudents::class)->assertForbidden();
    }

    public function test_job_processes_at_most_one_hundred_rows_per_batch(): void
    {
        Queue::fake();
        $staff = Account::factory()->create();
        $import = StudentImport::create(['uploaded_by' => $staff->id, 'filename' => 'test.csv', 'status' => 'importing']);
        for ($i = 1; $i <= 101; $i++) {
            $import->rows()->create(['row_number' => $i, 'data' => ['school_id_number' => 'BATCH-'.$i, 'first_name' => 'Jane', 'last_name' => 'Doe', 'email' => 'batch-'.$i.'@example.com']]);
        }
        (new ProcessStudentImport($import->id))->handle();
        $this->assertSame(100, $import->rows()->where('status', 'imported')->count());
        $this->assertSame(1, $import->rows()->where('status', 'pending')->count());
        Queue::assertPushed(ProcessStudentImport::class);
    }

    public function test_invited_student_can_set_password_and_verify_email(): void
    {
        $account = Account::factory()->create(['role_id' => 4, 'is_email_verified' => false, 'email_verified_at' => null]);
        $token = Password::broker()->createToken($account);
        Volt::test('pages.auth.reset-password', ['token' => $token])
            ->set('email', $account->email)
            ->set('password', 'LibraryPass123')
            ->set('password_confirmation', 'LibraryPass123')
            ->call('resetPassword')
            ->assertHasNoErrors();
        $account->refresh();
        $this->assertTrue($account->is_email_verified);
        $this->assertNotNull($account->password_changed_at);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('LibraryPass123', $account->password_hash));
    }

    public function test_excel_spreadsheet_columns_are_detected_and_reviewed(): void
    {
        Storage::fake('local');
        $staff = Account::factory()->create();
        Storage::disk('local')->makeDirectory('student-imports');
        $path = Storage::disk('local')->path('student-imports/test.xlsx');
        $sheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet->getActiveSheet()->fromArray([['Student roster'], ['Last Name', 'Email', 'First Name', 'Student ID'], ['Doe', 'excel-test@example.com', 'Jane', 'EXCEL-1']]);
        \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($sheet, 'Xlsx')->save($path);
        $sheet->disconnectWorksheets();
        $import = StudentImport::create(['uploaded_by' => $staff->id, 'filename' => 'test.xlsx', 'file_path' => 'student-imports/test.xlsx']);
        $service = app(StudentSpreadsheetImportService::class);
        $service->inspect($import);
        $import->refresh();
        $service->stage($import, $import->mapping);
        $this->assertSame(1, $import->rows()->where('status', 'pending')->count());
        $this->assertSame('EXCEL-1', $import->rows()->firstOrFail()->data['school_id_number']);
    }

    public function test_student_without_email_must_verify_email_then_can_skip_password_change(): void
    {
        Storage::fake('local'); Queue::fake(); Mail::fake();
        $staff = Account::factory()->create();
        Storage::disk('local')->put('student-imports/no-email.csv', "Student ID,First Name,Last Name\nNOEMAIL-1,Jane,Doe\n");
        $import = StudentImport::create(['uploaded_by' => $staff->id, 'filename' => 'no-email.csv', 'file_path' => 'student-imports/no-email.csv']);
        $service = app(StudentSpreadsheetImportService::class);
        $service->inspect($import); $import->refresh(); $service->stage($import, $import->mapping);
        $import->update(['status' => 'importing']);
        (new ProcessStudentImport($import->id))->handle();
        $row = $import->rows()->firstOrFail();
        $user = Account::findOrFail($row->account_id);
        $this->assertNull($user->email);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check(\App\Services\UserService::generateDefaultPassword($row->data, 'student'), $user->password_hash));
        $this->assertSame('email_required', $row->email_status);
        Queue::assertNotPushed(SendStudentImportInvitation::class);
        $this->actingAs($user)->get(route('student.dashboard'))->assertRedirect(route('student.setup'));
        Livewire::actingAs($user)->test(\App\Livewire\Pages\Auth\StudentSetup::class)->call('skipPassword')->assertForbidden();
        Livewire::actingAs($user)->test(\App\Livewire\Pages\Auth\StudentSetup::class)
            ->set('email', 'new-student@example.com')->call('sendVerification')->assertHasNoErrors();
        Mail::assertSent(\App\Mail\StudentSetupEmail::class);
        $user->refresh();
        $link = \Illuminate\Support\Facades\URL::temporarySignedRoute('student.setup.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($link)->assertRedirect(route('student.setup'));
        $this->assertFalse($user->fresh()->setup_email_required);
        $this->assertSame('verified', $row->fresh()->email_status);
        Livewire::actingAs($user->fresh())->test(\App\Livewire\Pages\Auth\StudentSetup::class)
            ->call('skipPassword')->assertRedirect(route('student.dashboard'));
        $this->assertFalse($user->fresh()->setup_password_prompt);
    }

    public function test_student_can_change_default_password_after_verifying_email(): void
    {
        $user = Account::factory()->create(['role_id' => 4]);
        $user->forceFill(['setup_email_required' => false, 'setup_password_prompt' => true])->save();
        Livewire::actingAs($user)->test(\App\Livewire\Pages\Auth\StudentSetup::class)
            ->set('password', 'NewPassword123')->set('password_confirmation', 'NewPassword123')
            ->call('changePassword')->assertHasNoErrors()->assertRedirect(route('student.dashboard'));
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewPassword123', $user->fresh()->password_hash));
        $this->assertFalse($user->fresh()->setup_password_prompt);
    }

    public function test_email_setup_rejects_duplicates_and_another_users_verification_link(): void
    {
        $existing = Account::factory()->create(['email' => 'owned@example.com']);
        $student = Account::factory()->create(['role_id' => 4]);
        $student->forceFill(['setup_email_required' => true, 'setup_password_prompt' => true])->save();
        Livewire::actingAs($student)->test(\App\Livewire\Pages\Auth\StudentSetup::class)
            ->set('email', 'OWNED@example.com')->call('sendVerification')->assertHasErrors(['email']);
        $link = \Illuminate\Support\Facades\URL::temporarySignedRoute('student.setup.verify', now()->addHour(), ['id' => $existing->id, 'hash' => sha1($existing->email)]);
        $this->actingAs($student)->get($link)->assertForbidden();
        $this->assertTrue($student->fresh()->setup_email_required);
    }
}
