<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LibrarianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Get accounts with role 2 (Librarian) or 3 (Assistant Librarian) that don't have a librarian profile yet
        $librarianAccounts = DB::table('accounts')
            ->whereIn('role_id', [2, 3])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('librarians')
                    ->whereColumn('librarians.account_id', 'accounts.id');
            })
            ->get();

        $firstNames = ['Alice', 'Bob', 'Charlie'];
        $lastNames = ['Miller', 'Smith', 'Jones'];

        $librarians = [];
        foreach ($librarianAccounts as $index => $account) {
            $firstName = $firstNames[$index % count($firstNames)];
            $lastName = $lastNames[$index % count($lastNames)];
            
            $librarians[] = [
                'account_id' => $account->id,
                'school_id_number' => sprintf('LIB-2026-%04d', $index + 101),
                'first_name' => $firstName,
                'middle_name' => 'S.',
                'last_name' => $lastName,
                'contact_num' => '09' . rand(100000000, 999999999),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($librarians)) {
            DB::table('librarians')->insertOrIgnore($librarians);
        }

        $this->command->info('Librarian profiles seeded successfully!');
    }
}
