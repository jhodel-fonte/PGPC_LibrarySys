<?php

namespace Database\Factories;

use App\Models\Language_Book;
use App\Models\Language;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Language_Book>
 */
class LanguageBookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'language_id' => Language::factory(),
            'book_id' => Book::factory(),
        ];
    }
}
