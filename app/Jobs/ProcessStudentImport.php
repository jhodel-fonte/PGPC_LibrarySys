<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\Student;
use App\Models\StudentImport;
use App\Models\StudentImportRow;
use App\Services\UserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class ProcessStudentImport implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 300;

    public function __construct(public int $importId) {}

    public function handle(): void
    {
        $import = StudentImport::findOrFail($this->importId);
        if ($import->status !== 'importing') return;
        $role = DB::table('roles')->where('name', 'Student')->value('id');
        $status = DB::table('account_statuses')->where('status_name', 'Active')->value('id');
        $libraryStatus = DB::table('library_statuses')->where('status', 'Active')->value('id');
        if (! $role || ! $status || ! $libraryStatus) throw new \RuntimeException('Student role or active status is not configured.');
        $ids = $import->rows()->where('status', 'pending')->orderBy('id')->limit(100)->pluck('id');
        foreach ($ids as $id) {
            if ($import->fresh()->status !== 'importing') return;
            try {
                $inviteRowId = DB::transaction(function () use ($id, $role, $status, $libraryStatus) {
                    $row = StudentImportRow::lockForUpdate()->findOrFail($id);
                    if ($row->status !== 'pending') return null;
                    $data = $row->data;
                    $email = $data['email'] ?: null;
                    $credentials = UserService::generateCredentials($data, 'student');
                    $username = $credentials['username'];
                    if (Account::withTrashed()->whereRaw('LOWER(username) = ?', [mb_strtolower($username)])->exists()) {
                        throw new \RuntimeException('Generated username is already in use.');
                    }
                    $account = Account::create(['role_id' => $role, 'status_id' => $status, 'username' => $username,
                        'email' => $email, 'password_hash' => Hash::make($credentials['password']),
                        'password_changed_at' => $credentials['force_change'] ? null : now(), 'is_email_verified' => false]);
                    if (! $email) {
                        $account->forceFill(['setup_email_required' => true, 'setup_password_prompt' => true])->save();
                    }
                    Student::create(array_merge(collect($data)->only(['school_id_number', 'first_name', 'middle_name', 'last_name', 'program', 'year_level', 'contact_num', 'gender', 'date_of_birth'])->all(),
                        ['account_id' => $account->id, 'library_status_id' => $libraryStatus]));
                    $row->update(['status' => 'imported', 'account_id' => $account->id, 'email_status' => $email ? 'pending' : 'email_required']);
                    return $email ? $row->id : null;
                });
                if ($inviteRowId) {
                    try { SendStudentImportInvitation::dispatch($inviteRowId)->onConnection('database'); }
                    catch (Throwable $exception) {
                        StudentImportRow::whereKey($inviteRowId)->update(['email_status' => 'failed', 'email_error' => 'Could not queue invitation.']);
                        report($exception);
                    }
                }
            } catch (Throwable $exception) {
                StudentImportRow::whereKey($id)->where('status', 'pending')->update(['status' => 'failed', 'error' => 'Could not create student. Check for a duplicate ID, username, or email.']);
                report($exception);
            }
        }
        if ($import->fresh()->status !== 'importing') return;
        if ($import->rows()->where('status', 'pending')->exists()) {
            self::dispatch($this->importId)->onConnection('database');
        } else {
            $import->update(['status' => 'completed']);
        }
    }

    public function failed(?Throwable $exception): void
    {
        StudentImport::whereKey($this->importId)->where('status', 'importing')->update(['status' => 'failed', 'error' => 'The import stopped. Previously imported students were kept.']);
    }
}
