<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Renderer;

use Laratusk\Larasvg\Facades\SvgConverter;
use RuntimeException;

final class PdfRenderer
{
    /**
     * Convert an SVG string to PDF and return the raw PDF bytes.
     */
    public function convert(string $svgContent): string
    {
        $pdf = SvgConverter::openFromContent($svgContent, 'svg')
            ->setFormat('pdf')
            ->toStdout(null);

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
        SvgConverter::openFromContent($svgContent, 'svg')
            ->setFormat('pdf')
            ->toFile($outputPath);

        if (!file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new RuntimeException(
                'SVG to PDF conversion produced empty output. ' .
                'Check that a supported converter (cairosvg, inkscape, rsvg-convert) is installed.'
            );
        }

        return $outputPath;
    }
}