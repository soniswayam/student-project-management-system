<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SpreadsheetExporter
{
    /** Brand colours (ARGB) shared by every export for a consistent look. */
    private const BRAND = 'FF0D3B66';   // deep navy — matches the PDF letterhead

    private const BRAND_LIGHT = 'FFF5F8FC'; // zebra stripe

    /**
     * Stream a professionally formatted .xlsx download built from a title,
     * heading row and data rows. Used by every admin export so all sheets
     * share one design (branded title, generated-on line, filtered & frozen
     * header, bordered zebra-striped rows, auto-sized columns).
     *
     * @param  array<int, string>  $headings
     * @param  array<int, array<int, mixed>>  $rows
     */
    public static function download(string $filename, string $title, array $headings, array $rows): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Report');

        $lastCol = max(1, count($headings));
        $lastColLetter = Coordinate::stringFromColumnIndex($lastCol);

        // ---- Title row (merged, branded) ----
        $sheet->setCellValue([1, 1], $title);
        $sheet->mergeCells([1, 1, $lastCol, 1]);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15)->getColor()->setARGB(self::BRAND);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // ---- Subtitle: generated-on + record count ----
        $subtitle = 'Generated on '.now()->format('d M Y, H:i').'  ·  '.count($rows).' record(s)';
        $sheet->setCellValue([1, 2], $subtitle);
        $sheet->mergeCells([1, 2, $lastCol, 2]);
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->getColor()->setARGB('FF64748B');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ---- Heading row ----
        $headingRow = 4;
        foreach ($headings as $i => $heading) {
            $sheet->setCellValue([$i + 1, $headingRow], $heading);
        }
        $headStyle = $sheet->getStyle("A{$headingRow}:{$lastColLetter}{$headingRow}");
        $headStyle->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('FFFFFFFF');
        $headStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::BRAND);
        $headStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($headingRow)->setRowHeight(20);

        // ---- Data rows (zebra striped) ----
        $r = $headingRow + 1;
        foreach ($rows as $index => $row) {
            foreach (array_values($row) as $i => $value) {
                $sheet->setCellValueExplicit(
                    [$i + 1, $r],
                    (string) ($value ?? ''),
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
            }
            if ($index % 2 === 1) {
                $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::BRAND_LIGHT);
            }
            $r++;
        }

        $lastRow = $r - 1;

        // ---- Borders + alignment across the whole table ----
        if ($lastRow >= $headingRow) {
            $table = $sheet->getStyle("A{$headingRow}:{$lastColLetter}{$lastRow}");
            $table->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFD0D7E2');
            $table->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        }

        // ---- Auto-size columns, freeze the header, enable filters ----
        for ($c = 1; $c <= $lastCol; $c++) {
            $sheet->getColumnDimensionByColumn($c)->setAutoSize(true);
        }
        $sheet->freezePane("A{$headingRow}");
        if ($lastRow >= $headingRow) {
            $sheet->setAutoFilter("A{$headingRow}:{$lastColLetter}{$lastRow}");
        }
        $sheet->setSelectedCell('A'.($headingRow + 1));

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}
