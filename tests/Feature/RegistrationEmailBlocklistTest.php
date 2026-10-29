<?php

namespace Tests\Feature;

use App\Models\RegistrationEmailBlocklist;
use Tests\TestCase;

class RegistrationEmailBlocklistTest extends TestCase
{
    public function test_blocklist_uses_the_migrated_table_name(): void
    {
        $model = new RegistrationEmailBlocklist();

        $this->assertSame('registration_email_blocklist', $model->getTable());
        $this->assertStringContainsString(
            'registration_email_blocklist',
            RegistrationEmailBlocklist::query()->toSql(),
        );
    }
}
