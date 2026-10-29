<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class BladeSyntaxTest extends TestCase
{
    public function test_all_blade_views_compile_to_valid_php(): void
    {
        $errors = [];
        $checked = 0;
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));

        foreach ($files as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            try {
                $checked++;
                token_get_all(Blade::compileString(file_get_contents($file->getPathname())), TOKEN_PARSE);
            } catch (\Throwable $e) {
                $errors[] = $file->getPathname().': '.$e->getMessage();
            }
        }

        $this->assertGreaterThan(0, $checked, 'No Blade views were found.');
        $this->assertSame([], $errors, implode("\n", $errors));
    }
}
