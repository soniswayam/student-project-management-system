<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;

class DocumentText
{
    private const MAX_CHARS = 200_000;

    public static function extract(string $absolutePath): ?string
    {
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            return null;
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        try {
            $text = match ($extension) {
                'pdf' => self::fromPdf($absolutePath),
                'docx' => self::fromDocx($absolutePath),
                'txt', 'md' => file_get_contents($absolutePath) ?: null,
                default => null,
            };
        } catch (\Throwable $e) {
            Log::warning('Document text extraction failed for '.basename($absolutePath).': '.$e->getMessage());

            return null;
        }

        return self::tidy($text);
    }

    private static function fromPdf(string $path): ?string
    {
        $parser = new PdfParser;

        return $parser->parseFile($path)->getText();
    }

    private static function fromDocx(string $path): ?string
    {
        $zip = new \ZipArchive;

        if ($zip->open($path) !== true) {
            return null;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            return null;
        }

        $xml = preg_replace('/<\/w:(p|tab|br|tr)>/', ' ', $xml) ?? $xml;

        return strip_tags($xml);
    }

    private static function tidy(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = trim($text);

        if ($text === '') {
            return null;
        }

        return mb_substr($text, 0, self::MAX_CHARS);
    }
}
