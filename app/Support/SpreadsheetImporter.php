<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetImporter
{
    /**
     * Read a CSV / XLSX / XLS upload into a list of associative rows keyed by
     * the (normalised) header names in the first row. Blank rows are skipped.
     *
     * A row like  name | email | roll_no  becomes
     *   ['name' => '…', 'email' => '…', 'roll_no' => '…']
     *
     * @return array<int, array<string, string>>  0-based data rows
     */
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

        // First non-empty row is the header.
        $headers = array_map(
            fn ($h) => self::normaliseHeader((string) ($h ?? '')),
            array_shift($matrix)
        );

        $rows = [];
        foreach ($matrix as $line) {
            // Skip fully empty rows.
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

    /** Normalise a header cell: lower-case, trim, collapse spaces/dashes to underscores. */
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
