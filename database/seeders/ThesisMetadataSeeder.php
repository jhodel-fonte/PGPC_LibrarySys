<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ThesisMetadataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Get the BookData representing "The Art of Computer Programming" (ID 4) or similar to simulate a thesis
        $bookData = DB::table('book_datas')->where('id', 4)->first();

        if ($bookData) {
            DB::table('thesis_metadatas')->insertOrIgnore([
                'id' => 1,
                'book_data_id' => $bookData->id,
                'defense_month' => 'March 1997',
                'adviser_name' => 'Prof. Richard Feynman',
                'dean' => 'Dr. Stephen Hawking',
                'program' => 'BSCS',
                'year_level' => '4th Year',
                'project_cost' => 15000.00,
                'remarks' => 'Outstanding Capstone Project Awardee.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('Thesis metadata seeded successfully!');
    }
}
