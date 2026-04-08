<?php

namespace GafarZade98\LaraInvoice\Services;

use Laratusk\Larasvg\Facades\SvgConverter;
use RuntimeException;

class PdfConverter
{
    /**
     * Convert an SVG string to PDF and return the raw PDF bytes.
     */
    public function convert(string $svgContent): string
    {
        $pdf = SvgConverter::openFromContent($svgContent, 'svg')
            ->setFormat('pdf')
            ->toStdout('pdf');

        if (empty($pdf)) {
            throw new RuntimeException(
                'SVG to PDF conversion produced empty output. ' .
                'Check that a supported converter (cairosvg, inkscape, rsvg-convert) is installed.'
            );
        }

        return $pdf;
    }

    /**
     * Convert an SVG string to PDF and write the result to a local file path.
     */
    public function toFile(string $svgContent, string $outputPath): string
    {
        return SvgConverter::openFromContent($svgContent, 'svg')
            ->setFormat('pdf')
            ->toFile($outputPath);
    }
}