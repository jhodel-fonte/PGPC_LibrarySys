<?php

namespace Tests\Feature;

use App\Models\Account;
use Tests\TestCase;

class LibrarianGuideTest extends TestCase
{
    public function test_guide_only_describes_permitted_actions(): void
    {
        $user = new Account;
        $user->setRelation('role', null);
        $user->setRelation('permissions', collect());

        $html = view('livewire.components.navigation-bar.librarian-guide', ['signedInUser' => $user])->render();

        $this->assertStringContainsString('Librarian Guide', $html);
        $this->assertStringNotContainsString('Cataloging', $html);
        $this->assertStringNotContainsString('Choose Borrow', $html);

        $permission = new \App\Models\Permission(['name' => 'view_catalog']);
        $permission->setRelation('pivot', new \Illuminate\Database\Eloquent\Relations\Pivot(['is_allowed' => true]));
        $user->setRelation('permissions', collect([$permission]));

        $html = view('livewire.components.navigation-bar.librarian-guide', ['signedInUser' => $user])->render();

        $this->assertStringContainsString('Cataloging', $html);
        $this->assertStringNotContainsString('Use Add or Import', $html);
        $this->assertStringNotContainsString('choose Edit', $html);
    }
}
