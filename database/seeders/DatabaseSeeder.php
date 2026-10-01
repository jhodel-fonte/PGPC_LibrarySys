<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Core Reference / Lookup Tables (No foreign dependencies)
            RoleSeeder::class,
            PermissionSeeder::class,
            AccountStatusSeeder::class,
            
            RolePermissionSeeder::class,
            LibraryStatusSeeder::class,
            BookConditionSeeder::class,
            ReservationStatusSeeder::class,
            BookTypeSeeder::class,
            LanguageSeeder::class,
            FineTypeSeeder::class,
            BorrowTypeSeeder::class,
            CategorySeeder::class,   // Base category lookup (if populated)
            PublisherSeeder::class,  // Base publisher lookup (if populated)

            // 2. Account Management (Depends on roles & account_statuses)
            AccountSeeder::class,

            // 3. User Profiles (Depends on accounts & library_statuses)
            StudentSeeder::class,
            LibrarianSeeder::class,
            SuperAdminSeeder::class, // Creates Superadmin Account & Librarian profile

            // 4. Authors
            AuthorSeeder::class,

            // 5. Book & Cataloging Foundation (Depends on languages, authors, categories)
            BookDataSeeder::class,

            // 6. Book Edition Details (Depends on book_datas, publishers, book_types)
            BookDetailSeeder::class,
            ThesisMetadataSeeder::class,

            // 7. Physical Book Copies (Depends on book_details, book_conditions)
            BookSeeder::class,

            // 8. Circulation & Transactions (Depends on books, reservation_statuses)
            BookReservationSeeder::class,

            // 9. Borrowing (Depends on books, students, librarians, borrow_types, book_conditions)
            BorrowingTransactionSeeder::class,

            // 10. Fines & Payments (Depends on borrowing_transactions, students, fine_types, librarians)
            FineSeeder::class,
            FinePaymentSeeder::class,
            
            // 11. Notifications and Preferences
            NotifTemplateSeeder::class,
            UserPreferenceSeeder::class,
            UserNotificationSeeder::class,
        ]);

        // Fix auto-increment sequences after seeding records
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
            $tables = [
                'accounts', 'students', 'librarians', 'roles', 'permissions',
                'role_permission', 'account_permission', 'account_statuses',
                'library_statuses', 'book_conditions', 'reservation_statuses',
                'book_types', 'languages', 'fine_types', 'borrow_types',
                'categories', 'publishers', 'authors', 'book_datas',
                'book_details', 'thesis_metadata', 'books', 'book_reservations',
                'borrowing_transactions', 'fines', 'fine_payments',
                'notif_templates', 'user_preferences', 'user_notifications',
                'system_settings', 'global_announcements', 'book_data_author', 'book_data_category'
            ];
            foreach ($tables as $table) {
                $sequenceName = $table . '_id_seq';
                $seqExists = \Illuminate\Support\Facades\DB::selectOne("SELECT to_regclass('{$sequenceName}') as exists");
                if ($seqExists && $seqExists->exists) {
                    $maxId = \Illuminate\Support\Facades\DB::table($table)->max('id');
                    if ($maxId !== null) {
                        \Illuminate\Support\Facades\DB::statement("SELECT setval('{$sequenceName}', {$maxId})");
                    }
                }
            }
        }
    }
}
