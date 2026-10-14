<?php

namespace Tests\Unit;

use App\Services\UserService;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    public function test_it_generates_student_username_default(): void
    {
        $data = [
            'school_id_number' => '23-1234',
            'email' => 'juan.santos@pgpc.edu.ph',
            'first_name' => 'Juan',
            'last_name' => 'Santos',
        ];

        $username = UserService::generateDefaultUsername($data, 'student');
        $this->assertEquals('23-1234', $username);
    }

    public function test_it_generates_student_password_in_lastname_and_id_format(): void
    {
        $data = [
            'school_id_number' => '23-1234',
            'email' => 'juan.santos@pgpc.edu.ph',
            'first_name' => 'Juan',
            'last_name' => 'Santos',
        ];

        $password = UserService::generateDefaultPassword($data, 'student');
        $this->assertEquals('santos23-1234', $password);
    }

    public function test_it_strips_spaces_from_last_name_in_password(): void
    {
        $data = [
            'school_id_number' => '23-1234',
            'email' => 'maria.delacruz@pgpc.edu.ph',
            'first_name' => 'Maria',
            'last_name' => 'Dela Cruz',
        ];

        $password = UserService::generateDefaultPassword($data, 'student');
        $this->assertEquals('delacruz23-1234', $password);
    }

    public function test_it_generates_librarian_credentials_from_librarian_config(): void
    {
        $data = [
            'school_id_number' => 'LIB-2026-001',
            'email' => 'maria.librarian@pgpc.edu.ph',
            'first_name' => 'Maria',
            'last_name' => 'Reyes',
        ];

        $credentials = UserService::generateCredentials($data, 'librarian');

        $this->assertEquals('lib-2026-001', $credentials['username']);
        $this->assertEquals('reyeslib-2026-001', $credentials['password']);
        $this->assertTrue($credentials['force_change']);
    }

    public function test_it_supports_head_librarian_role_detection(): void
    {
        $this->assertTrue(UserService::isLibrarianRole('librarian'));
        $this->assertTrue(UserService::isLibrarianRole('Head Librarian'));
        $this->assertTrue(UserService::isLibrarianRole('librarian_head'));
        $this->assertFalse(UserService::isLibrarianRole('student'));
        $this->assertFalse(UserService::isLibrarianRole(null));
    }

    public function test_it_falls_back_when_name_and_id_are_empty(): void
    {
        $data = [];
        $password = UserService::generateDefaultPassword($data, 'student');
        $this->assertEquals('Pgpc12345!', $password);
    }
}
