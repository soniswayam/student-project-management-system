<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetImporter
{
    public static function rows(string $path, ?string $extension = null): array
    {
        $reader = $extension
            ? IOFactory::createReader(self::readerType($extension))
            : IOFactory::createReaderForFile($path);

        $reader->setReadDataOnly(true);
        $sheet = $reader->load($path)->getActiveSheet();
        $matrix = $sheet->toArray(null, true, false, false);

        if (empty($matrix)) {
            return [];
        }

        $headers = array_map(
            fn ($h) => self::normaliseHeader((string) ($h ?? '')),
            array_shift($matrix)
        );

        $rows = [];
        foreach ($matrix as $line) {
            if (count(array_filter($line, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $row = [];
            foreach ($headers as $i => $key) {
                if ($key === '') {
                    continue;
                }
                $row[$key] = trim((string) ($line[$i] ?? ''));
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private static function normaliseHeader(string $header): string
    {
        $header = strtolower(trim($header));

        return preg_replace('/[\s\-]+/', '_', $header) ?? $header;
    }

    private static function readerType(string $extension): string
    {
        return match (strtolower($extension)) {
            'csv', 'txt' => 'Csv',
            'xls' => 'Xls',
            default => 'Xlsx',
        };
    }
}
