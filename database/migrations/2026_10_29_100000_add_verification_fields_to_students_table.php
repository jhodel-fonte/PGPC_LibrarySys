<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('account_statuses')) {
            $now = now();
            DB::table('account_statuses')->insertOrIgnore([
                [
                    'id' => 4,
                    'status_name' => 'Pending',
                    'description' => 'Account is awaiting email verification or admin approval.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'id' => 5,
                    'status_name' => 'Not Approved',
                    'description' => 'Verification document was not approved and requires resubmission.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'verification_document_path')) {
                $table->string('verification_document_path')->nullable()->after('image_url');
            }
            if (! Schema::hasColumn('students', 'verification_document_type')) {
                $table->string('verification_document_type', 50)->nullable()->after('verification_document_path');
            }
            if (! Schema::hasColumn('students', 'verification_submitted_at')) {
                $table->timestamp('verification_submitted_at')->nullable()->after('verification_document_type');
            }
            if (! Schema::hasColumn('students', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verification_submitted_at');
            }
            if (! Schema::hasColumn('students', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('verified_at');
                $table->foreign('verified_by')->references('id')->on('accounts')->nullOnDelete();
            }
            if (! Schema::hasColumn('students', 'verification_remarks')) {
                $table->text('verification_remarks')->nullable()->after('verified_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'verified_by')) {
                $table->dropForeign(['verified_by']);
            }

            $columns = array_filter([
                'verification_document_path',
                'verification_document_type',
                'verification_submitted_at',
                'verified_at',
                'verified_by',
                'verification_remarks',
            ], fn ($col) => Schema::hasColumn('students', $col));

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

