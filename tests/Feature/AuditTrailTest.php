<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Book;
use App\Models\Role;
use App\Livewire\Pages\Dashboard\AuditLog;
use App\Services\AuditTrail;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('accounts', function (Blueprint $table) {
            $table->id(); $table->string('username'); $table->string('password_hash')->nullable();
            $table->string('ml_user_token')->nullable(); $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable(); $table->softDeletes(); $table->timestamps();
        });
        Schema::create('books', function (Blueprint $table) {
            $table->id(); $table->string('accession_number')->nullable(); $table->string('status')->nullable();
            $table->unsignedBigInteger('book_detail_id')->nullable();
            $table->softDeletes(); $table->timestamps();
        });
        foreach (['students', 'librarians'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id(); $table->unsignedBigInteger('account_id')->nullable();
                $table->string('first_name')->nullable(); $table->string('last_name')->nullable(); $table->softDeletes();
            });
        }
        Schema::create('book_datas', function (Blueprint $table) {
            $table->id(); $table->string('book_title'); $table->softDeletes();
        });
        Schema::create('book_details', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('book_data_id'); $table->softDeletes();
        });
        (require database_path('migrations/2026_11_01_000002_create_audit_logs_table.php'))->up();
        (require database_path('migrations/2026_11_01_000003_add_action_context_to_audit_logs.php'))->up();
    }

    public function test_records_actor_changes_and_rolls_back_with_transaction(): void
    {
        DB::table('accounts')->insert(['id' => 1, 'username' => 'librarian']);
        $actor = new Account();
        $actor->id = 1;
        $actor->username = 'librarian';
        $this->actingAs($actor);
        $book = Book::create(['accession_number' => 'B-123', 'status' => 'available']);
        $book->update(['status' => 'borrowed']);
        $this->assertDatabaseHas('audit_logs', ['actor_id' => 1, 'actor_name' => 'librarian', 'action' => 'book.created', 'subject_id' => (string) $book->id]);
        $log = DB::table('audit_logs')->where('action', 'book.updated')->first();
        $this->assertSame('available', json_decode($log->old_values, true)['status']);
        $this->assertSame('borrowed', json_decode($log->new_values, true)['status']);
        DB::beginTransaction();
        $book->update(['status' => 'available']);
        DB::rollBack();
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'book.updated')->count());
    }

    public function test_account_password_is_never_recorded(): void
    {
        $account = Account::create(['username' => 'reader', 'password_hash' => 'secret']);
        $log = DB::table('audit_logs')->where('subject_id', $account->id)->first();
        $this->assertStringNotContainsString('password', $log->new_values);
        $this->assertSame('System', $log->actor_name);
    }

    public function test_language_relationship_changes_are_recorded_and_roll_back(): void
    {
        Schema::create('language_books', function (Blueprint $table) {
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('language_id');
            $table->timestamps();
        });
        $book = Book::create(['accession_number' => 'B-123']);
        AuditTrail::syncRelationship($book, 'languages', [1]);
        AuditTrail::syncRelationship($book, 'languages', [1]);
        $logs = DB::table('audit_logs')->where('action', 'book.languages_changed')->get();
        $this->assertCount(1, $logs);
        $this->assertEquals([], json_decode($logs[0]->old_values, true)['languages']);
        $this->assertEquals([1], json_decode($logs[0]->new_values, true)['languages']);

        DB::beginTransaction();
        AuditTrail::syncRelationship($book, 'languages', [2]);
        DB::rollBack();
        $this->assertDatabaseHas('language_books', ['book_id' => $book->id, 'language_id' => 1]);
        $this->assertSame(1, DB::table('audit_logs')->where('action', 'book.languages_changed')->count());
    }

    public function test_action_groups_are_isolated_and_context_is_a_snapshot(): void
    {
        DB::table('accounts')->insert(['id' => 1, 'username' => 'librarian']);
        DB::table('librarians')->insert(['account_id' => 1, 'first_name' => 'Maria', 'last_name' => 'Santos']);
        DB::table('book_datas')->insert(['id' => 1, 'book_title' => 'Original title']);
        DB::table('book_details')->insert(['id' => 1, 'book_data_id' => 1]);
        $actor = new Account();
        $actor->id = 1; $actor->username = 'librarian';
        $this->actingAs($actor);
        AuditTrail::transaction('Reservation created', function () {
            $book = Book::create(['book_detail_id' => 1, 'accession_number' => 'B-1', 'status' => 'available']);
            $book->update(['status' => 'reserved']);
        });
        AuditTrail::transaction('Catalog entry created', fn () => Book::create(['status' => 'available']));
        $logs = DB::table('audit_logs')->orderBy('id')->get();
        $this->assertNotNull($logs[0]->group_id);
        $this->assertSame($logs[0]->group_id, $logs[1]->group_id);
        $this->assertNotSame($logs[0]->group_id, $logs[2]->group_id);
        $this->assertSame('Maria Santos', $logs[0]->actor_name);
        DB::table('book_datas')->where('id', 1)->update(['book_title' => 'Renamed']);
        $this->assertSame('Original title', json_decode($logs[0]->context, true)['Book']);
    }

    public function test_failed_action_rolls_back_and_does_not_leak_its_group(): void
    {
        try {
            AuditTrail::transaction('Failed action', function () {
                Book::create(['status' => 'available']);
                throw new \RuntimeException('failed');
            });
        } catch (\RuntimeException $exception) {
            $this->assertSame('failed', $exception->getMessage());
        }
        $this->assertDatabaseCount('books', 0);
        $this->assertDatabaseCount('audit_logs', 0);
        Book::create(['status' => 'available']);
        $this->assertNull(DB::table('audit_logs')->value('group_id'));
    }

    public function test_page_filters_groups_and_keeps_legacy_entries_separate(): void
    {
        $admin = new Account();
        $admin->id = 1;
        $admin->setRelation('role', new Role(['name' => 'Admin']));
        $this->actingAs($admin);
        $base = ['actor_name' => 'Maria Santos', 'created_at' => now(), 'subject_type' => 'Book', 'subject_id' => '12'];
        DB::table('audit_logs')->insert($base + ['action' => 'book.updated', 'group_id' => 'group-1', 'group_action' => 'Reservation created', 'context' => json_encode(['Book' => 'Computing'])]);
        DB::table('audit_logs')->insert(array_merge($base, ['action' => 'book_reservation.created', 'subject_type' => 'BookReservation', 'subject_id' => '99', 'group_id' => 'group-1', 'group_action' => 'Reservation created']));
        DB::table('audit_logs')->insert($base + ['action' => 'book.created']);

        $page = new AuditLog();
        $logs = $page->render()->getData()['logs'];
        $this->assertSame(2, $logs->total());
        $page->subject = '99';
        $logs = $page->render()->getData()['logs'];
        $this->assertSame(1, $logs->total());
        $this->assertCount(5, $logs->first()->details['sections']);
        $page->subject = 'Computing';
        $this->assertSame(1, $page->render()->getData()['logs']->total());
    }
}
