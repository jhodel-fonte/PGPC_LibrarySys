<?php

namespace Tests\Feature;

use App\Services\SpreadsheetExport;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class SpreadsheetExportTest extends TestCase
{
    public function test_workbook_preserves_text_identifiers_and_does_not_execute_formulas(): void
    {
        $response = SpreadsheetExport::download('report.xlsx', [
            'Students' => [['ID', 'Name'], ['00123', '=1+1']],
            'Empty' => [['Title']],
        ]);
        ob_start();
        $response->sendContent();
        $contents = ob_get_clean();
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        try {
            file_put_contents($path, $contents);
            $book = IOFactory::createReader('Xlsx')->load($path);
            $this->assertSame('00123', $book->getSheetByName('Students')->getCell('A2')->getValue());
            $this->assertSame(DataType::TYPE_STRING, $book->getSheetByName('Students')->getCell('B2')->getDataType());
            $this->assertSame('=1+1', $book->getSheetByName('Students')->getCell('B2')->getValue());
            $this->assertSame('Title', $book->getSheetByName('Empty')->getCell('A1')->getValue());
        } finally {
            unlink($path);
        }
    }
}
