<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        // Find Patron accounts that don't have profiles yet
        $studentAccounts = DB::table('accounts')
            ->where('role_id', 4) // Patron
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('students')
                    ->whereColumn('students.account_id', 'accounts.id');
            })
            ->get();

        $firstNames = ['John', 'Jane', 'Mark', 'Mary', 'David', 'Sarah', 'James', 'Patricia', 'Robert', 'Jennifer'];
        $lastNames = ['Cruz', 'Santos', 'Reyes', 'Ramos', 'Aquino', 'Dela Cruz', 'Garcia', 'Torres', 'Diaz', 'Bautista'];
        $programs = ['BSIT', 'BSCS', 'BSCE', 'BSEE', 'BSBA', 'BSHM'];
        $years = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

        $students = [];
        foreach ($studentAccounts as $index => $account) {
            $firstName = $firstNames[$index % count($firstNames)];
            $lastName = $lastNames[$index % count($lastNames)];
            
            $students[] = [
                'account_id' => $account->id,
                'school_id_number' => sprintf('2026-%04d', $index + 1001),
                'first_name' => $firstName,
                'middle_name' => 'M.',
                'last_name' => $lastName,
                'contact_num' => '09' . rand(100000000, 999999999),
                'library_status_id' => 1, // Active status (corresponds to LibraryStatusSeeder ID 1)
                'note' => 'Regular student patron.',
                'program' => $programs[$index % count($programs)],
                'year_level' => $years[$index % count($years)],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($students)) {
            DB::table('students')->insertOrIgnore($students);
        }

        $this->command->info('Student profiles seeded successfully!');
    }
}
