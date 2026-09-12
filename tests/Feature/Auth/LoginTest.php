<?php

namespace Tests\Feature\Auth;

use App\Models\Account;
use App\Models\AccountStatus;
use App\Models\Librarian;
use App\Models\Role;
use App\Models\Student;
use Database\Seeders\AccountStatusSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected Role $adminRole;
    protected Role $headLibrarianRole;
    protected Role $librarianRole;
    protected Role $studentRole;

    protected AccountStatus $activeStatus;
    protected AccountStatus $inactiveStatus;
    protected AccountStatus $suspendedStatus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(AccountStatusSeeder::class);

        $this->adminRole = Role::where('name', 'Admin')->first();
        $this->headLibrarianRole = Role::where('name', 'Head Librarian')->first();
        $this->librarianRole = Role::where('name', 'Librarian')->first();
        $this->studentRole = Role::where('name', 'Student')->first();

        $this->activeStatus = AccountStatus::whereRaw('LOWER(status_name) = ?', ['active'])->first();
        $this->inactiveStatus = AccountStatus::whereRaw('LOWER(status_name) = ?', ['inactive'])->first()
            ?? AccountStatus::create(['status_name' => 'Inactive', 'description' => 'Inactive account']);
        $this->suspendedStatus = AccountStatus::whereRaw('LOWER(status_name) = ?', ['suspended'])->first()
            ?? AccountStatus::create(['status_name' => 'Suspended', 'description' => 'Suspended account']);
    }

    // =========================================================================
    // 1. PAGE RENDERING SCENARIOS
    // =========================================================================

    public function test_student_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSeeVolt('pages.auth.student-login')
            ->assertSee('Welcome back');
    }

    public function test_employee_login_page_renders_successfully(): void
    {
        $response = $this->get('/employee');

        $response->assertOk()
            ->assertSeeVolt('pages.auth.employee-login')
            ->assertSee('Employee Portal');
    }

    // =========================================================================
    // 2. STUDENT PORTAL LOGIN SCENARIOS
    // =========================================================================

    public function test_student_can_authenticate_with_email(): void
    {
        $studentAccount = Account::factory()->create([
            'role_id' => $this->studentRole->id,
            'status_id' => $this->activeStatus->id,
            'email' => 'student@test.com',
            'username' => 'student_user',
            'password_hash' => Hash::make('Student12345'),
        ]);

        $component = Volt::test('pages.auth.student-login')
            ->set('form.email', 'student@test.com')
            ->set('form.password', 'Student12345')
            ->call('login');

        $component->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($studentAccount);
    }

    public function test_student_can_authenticate_with_username(): void
    {
        $studentAccount = Account::factory()->create([
            'role_id' => $this->studentRole->id,
            'status_id' => $this->activeStatus->id,
            'email' => 'john_student@test.com',
            'username' => 'john_student',
            'password_hash' => Hash::make('Student12345'),
        ]);

        $component = Volt::test('pages.auth.student-login')
            ->set('form.email', 'john_student')
            ->set('form.password', 'Student12345')
            ->call('login');

        $component->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($studentAccount);
    }

    public function test_student_can_authenticate_with_student_id_number(): void
    {
        $studentAccount = Account::factory()->create([
            'role_id' => $this->studentRole->id,
            'status_id' => $this->activeStatus->id,
            'email' => 'johndoe@test.com',
            'username' => 'johndoe',
            'password_hash' => Hash::make('Student12345'),
        ]);

        Student::create([
            'account_id' => $studentAccount->id,
            'school_id_number' => 'STU-2026-001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'library_status_id' => 1,
        ]);

        $component = Volt::test('pages.auth.student-login')
            ->set('form.email', 'STU-2026-001')
            ->set('form.password', 'Student12345')
            ->call('login');

        $component->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($studentAccount);
    }

    public function test_student_login_fails_with_incorrect_password(): void
    {
        Account::factory()->create([
            'role_id' => $this->studentRole->id,
            'status_id' => $this->activeStatus->id,
            'email' => 'student@test.com',
            'username' => 'student_user',
            'password_hash' => Hash::make('CorrectPassword'),
        ]);

        $component = Volt::test('pages.auth.student-login')
            ->set('form.email', 'student@test.com')
            ->set('form.password', 'WrongPassword')
            ->call('login');

        $component->assertHasErrors(['form.email'])
            ->assertDispatched('login-failed');

        $this->assertGuest();
    }

    public function test_student_login_fails_with_non_existent_account(): void
    {
        $component = Volt::test('pages.auth.student-login')
            ->set('form.email', 'unknown@test.com')
            ->set('form.password', 'AnyPassword123')
            ->call('login');

        $component->assertHasErrors(['form.email'])
            ->assertDispatched('login-failed');

        $this->assertGuest();
    }

    public function test_staff_member_is_blocked_from_student_portal_with_specific_message(): void
    {
        Account::factory()->create([
            'role_id' => $this->adminRole->id,
            'status_id' => $this->activeStatus->id,
            'email' => 'admin@admin.com',
            'username' => 'admin_user',
            'password_hash' => Hash::make('Admin12345'),
        ]);

        $component = Volt::test('pages.auth.student-login')
            ->set('form.email', 'admin@admin.com')
            ->set('form.password', 'Admin12345')
            ->call('login');

        $component->assertHasErrors(['form.email'])
            ->assertDispatched('login-failed');

        $this->assertGuest();
    }

    // =========================================================================
    // 3. EMPLOYEE PORTAL LOGIN SCENARIOS
    // =========================================================================

    public function test_admin_can_authenticate_on_employee_portal(): void
    {
        $adminAccount = Account::factory()->create([
            'role_id' => $this->adminRole->id,
            'status_id' => $this->activeStatus->id,
            'email' => 'admin@admin.com',
            'username' => 'admin_account',
            'password_hash' => Hash::make('Admin12345'),
        ]);

        $component = Volt::test('pages.auth.employee-login')
            ->set('form.email', 'admin@admin.com')
            ->set('form.password', 'Admin12345')
            ->call('login');

        $component->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($adminAccount);
    }

    public function test_head_librarian_can_authenticate_on_employee_portal(): void
    {
        $headLibrarianAccount = Account::factory()->create([
            'role_id' => $this->headLibrarianRole->id,
            'status_id' => $this->activeStatus->id,
            'email' => 'headlib@test.com',
            'username' => 'head_lib',
            'password_hash' => Hash::make('HeadLib12345'),
        ]);

        $component = Volt::test('pages.auth.employee-login')
            ->set('form.email', 'head_lib')
            ->set('form.password', 'HeadLib12345')
            ->call('login');

        $component->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($headLibrarianAccount);
    }

    public function test_librarian_can_authenticate_with_employee_id_number(): void
    {
        $librarianAccount = Account::factory()->create([
            'role_id' => $this->librarianRole->id,
            'status_id' => $this->activeStatus->id,
            'email' => 'jane_lib@test.com',
            'username' => 'jane_lib',
            'password_hash' => Hash::make('Lib12345'),
        ]);

        Librarian::create([
            'account_id' => $librarianAccount->id,
            'school_id_number' => 'EMP-LIB-001',
            'first_name' => 'Jane',
            'last_name' => 'Librarian',
        ]);

        $component = Volt::test('pages.auth.employee-login')
            ->set('form.email', 'EMP-LIB-001')
            ->set('form.password', 'Lib12345')
            ->call('login');

        $component->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($librarianAccount);
    }

    public function test_student_is_blocked_from_employee_portal_with_specific_message(): void
    {
        Account::factory()->create([
            'role_id' => $this->studentRole->id,
            'status_id' => $this->activeStatus->id,
            'email' => 'student@test.com',
            'username' => 'student_acc',
            'password_hash' => Hash::make('Student12345'),
        ]);

        $component = Volt::test('pages.auth.employee-login')
            ->set('form.email', 'student@test.com')
            ->set('form.password', 'Student12345')
            ->call('login');

        $component->assertHasErrors(['form.email'])
            ->assertDispatched('login-failed');

        $this->assertGuest();
    }

    // =========================================================================
    // 4. ACCOUNT STATUS & SECURITY SCENARIOS
    // =========================================================================

    public function test_inactive_account_cannot_login(): void
    {
        Account::factory()->create([
            'role_id' => $this->adminRole->id,
            'status_id' => $this->inactiveStatus->id,
            'email' => 'inactive_admin@admin.com',
            'username' => 'inactive_admin',
            'password_hash' => Hash::make('Admin12345'),
        ]);

        $component = Volt::test('pages.auth.employee-login')
            ->set('form.email', 'inactive_admin@admin.com')
            ->set('form.password', 'Admin12345')
            ->call('login');

        $component->assertHasErrors(['form.email'])
            ->assertDispatched('login-failed');

        $this->assertGuest();
    }

    public function test_suspended_account_cannot_login(): void
    {
        Account::factory()->create([
            'role_id' => $this->studentRole->id,
            'status_id' => $this->suspendedStatus->id,
            'email' => 'suspended_student@test.com',
            'username' => 'suspended_stu',
            'password_hash' => Hash::make('Student12345'),
        ]);

        $component = Volt::test('pages.auth.student-login')
            ->set('form.email', 'suspended_student@test.com')
            ->set('form.password', 'Student12345')
            ->call('login');

        $component->assertHasErrors(['form.email'])
            ->assertDispatched('login-failed');

        $this->assertGuest();
    }

    public function test_already_authenticated_staff_redirects_away_from_employee_login(): void
    {
        $admin = Account::factory()->create([
            'role_id' => $this->adminRole->id,
            'status_id' => $this->activeStatus->id,
            'email' => 'logged_in_admin@test.com',
            'username' => 'logged_in_admin',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/employee');
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_already_authenticated_student_redirects_away_from_student_login(): void
    {
        $student = Account::factory()->create([
            'role_id' => $this->studentRole->id,
            'status_id' => $this->activeStatus->id,
            'email' => 'logged_in_stu@test.com',
            'username' => 'logged_in_stu',
        ]);

        $this->actingAs($student);

        $response = $this->get('/login');
        $response->assertRedirect('/');
    }
}
