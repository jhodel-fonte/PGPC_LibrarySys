<?php

namespace Tests\Feature\Auth;

use App\Livewire\Pages\Auth\StudentLogin;
use Livewire\Livewire;
use Tests\TestCase;

class StudentMobileLoginViewTest extends TestCase
{
    public function test_mobile_view_hides_employee_login_and_regular_view_keeps_it(): void
    {
        $this->get('/student?mobileview=1')->assertOk()->assertDontSee('Employee Login')
            ->assertCookie('pgpc_mobile_view', '1');
        $this->get('/student')->assertOk()->assertSee('Employee Login');
        $this->withCookie('pgpc_mobile_view', '1')->get('/student')->assertOk()->assertDontSee('Employee Login');
        Livewire::withQueryParams(['mobileview' => '1'])->test(StudentLogin::class)
            ->call('$refresh')->assertDontSee('Employee Login');
    }
}
