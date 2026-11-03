<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpreadsheetExport
{
    public static function download(string $filename, array $sheets)
    {
        $spreadsheet = self::workbook($sheets);
        return response()->streamDownload(function () use ($spreadsheet) {
            try {
                (new Xlsx($spreadsheet))->save('php://output');
            } finally {
                $spreadsheet->disconnectWorksheets();
            }
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public static function workbook(array $sheets): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        foreach ($sheets as $name => $rows) {
            $sheet = $spreadsheet->getSheetCount() === 1 && $spreadsheet->getActiveSheet()->getTitle() === 'Worksheet'
                ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $sheet->setTitle($name);
            foreach ($rows as $rowIndex => $row) {
                foreach (array_values($row) as $column => $value) {
                    $cell = $sheet->getCell([$column + 1, $rowIndex + 1]);
                    if (is_int($value) || is_float($value)) {
                        $cell->setValue($value);
                    } else {
                        $cell->setValueExplicit((string) ($value ?? ''), DataType::TYPE_STRING);
                    }
                }
            }
            $sheet->freezePane('A2');
        }
        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }
}
