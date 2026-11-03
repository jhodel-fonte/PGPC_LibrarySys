<?php

namespace Tests\Feature;

use App\Jobs\ExportSpreadsheet;
use App\Livewire\Components\Usermanager\StudentTable;
use App\Models\Account;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class QueuedSpreadsheetExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_is_queued_and_only_the_owner_can_download_it(): void
    {
        Queue::fake();
        Storage::fake('local');
        $admin = Account::factory()->create(['role_id' => Role::where('name', 'Admin')->value('id')]);
        $other = Account::factory()->create(['role_id' => Role::where('name', 'Admin')->value('id')]);
        Livewire::actingAs($admin)->test(StudentTable::class)->call('exportExcel');
        $notification = $admin->notifications()->firstOrFail();
        $this->assertSame('queued', $notification->data['status']);
        Queue::assertPushed(ExportSpreadsheet::class, function ($job) use ($admin, $notification) {
            $this->assertSame($admin->id, $job->userId);
            $this->assertNull($job->queue);
            $this->assertSame('database', $job->connection);
            $job->handle();
            return $job->notificationId === $notification->id;
        });
        $this->assertSame('completed', $notification->fresh()->data['status']);
        Storage::disk('local')->assertExists('exports/'.$notification->id.'.xlsx');
        $this->actingAs($other)->get(route('exports.download', $notification->id))->assertNotFound();
        $this->actingAs($admin)->get(route('exports.download', $notification->id))->assertOk();
    }
    public function test_notification_view_can_be_encoded_in_a_livewire_response(): void
    {
        $html = view('livewire.components.notification.notification-content')->render();
        $this->assertNotEmpty(json_encode(['html' => $html], JSON_THROW_ON_ERROR));
    }

}
