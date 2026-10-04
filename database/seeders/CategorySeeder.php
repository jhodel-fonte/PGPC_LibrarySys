<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = resource_path('files/library_of_congress_subclasses_master.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("JSON file not found at: {$jsonPath}");
            return;
        }

        $classes = json_decode(File::get($jsonPath), true);

        if (empty($classes) || !is_array($classes)) {
            $this->command->error("Invalid or empty JSON in {$jsonPath}");
            return;
        }

        DB::transaction(function () use ($classes) {
            foreach ($classes as $mainCode => $mainData) {
                $mainName = $mainData['name'] ?? $mainCode;

                // 1. Create or update main parent class (e.g. Code: 'A', Name: 'General Works', parent_id: null)
                $parent = Category::updateOrCreate(
                    [
                        'code' => $mainCode,
                        'parent_id' => null,
                    ],
                    [
                        'name' => $mainName,
                    ]
                );

                // 2. Create or update subclasses (e.g. Code: 'AC', Name: 'Collections; Series; Collected works', parent_id: $parent->id)
                if (!empty($mainData['subclasses']) && is_array($mainData['subclasses'])) {
                    foreach ($mainData['subclasses'] as $subCode => $subName) {
                        Category::updateOrCreate(
                            [
                                'code' => $subCode,
                                'parent_id' => $parent->id,
                            ],
                            [
                                'name' => $subName,
                            ]
                        );
                    }
                }
            }
        });

        $count = Category::count();
        $this->command->info("Library of Congress categories seeded successfully! Total categories in database: {$count}");
    }
}

