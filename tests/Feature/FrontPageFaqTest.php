<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FrontPageFaqTest extends TestCase
{
    public function test_front_page_faq_uses_bundled_defaults_when_no_override_exists(): void
    {
        $this->withFaqStorage(null, function () {
            $this->blade('<x-home.faq-section />')
                ->assertSee('How do I search for a book in the library catalog?');
        });
    }

    public function test_valid_local_faq_overrides_defaults_and_ignores_invalid_entries(): void
    {
        $this->withFaqStorage(json_encode(['faqs' => [
            ['id' => 'same', 'question' => 'Local question?', 'answer' => 'Local answer.'],
            ['id' => 'same', 'question' => 'Second question?', 'answer' => 'Second answer.'],
            ['question' => 'Incomplete question'],
        ]]), function () {
            $this->blade('<x-home.faq-section />')
                ->assertSee('Local question?')->assertSee('Local answer.')
                ->assertSee('Second question?')->assertDontSee('Incomplete question')
                ->assertDontSee('How do I search for a book in the library catalog?')
                ->assertSee("toggle('col1_0')", false)->assertSee("toggle('col2_0')", false);
        });
    }

    public function test_malformed_override_uses_bundled_defaults(): void
    {
        $this->withFaqStorage('{invalid json', function () {
            $this->blade('<x-home.faq-section />')
                ->assertSee('How do I search for a book in the library catalog?');
        });
    }

    private function withFaqStorage(?string $contents, callable $callback): void
    {
        $originalPath = app()->storagePath();
        $testPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'faq-test-'.uniqid();
        File::ensureDirectoryExists($testPath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public');

        try {
            if ($contents !== null) {
                File::put($testPath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'faq.json', $contents);
            }
            app()->useStoragePath($testPath);
            $callback();
        } finally {
            app()->useStoragePath($originalPath);
            File::delete($testPath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'faq.json');
            rmdir($testPath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public');
            rmdir($testPath.DIRECTORY_SEPARATOR.'app');
            rmdir($testPath);
        }
    }
}
