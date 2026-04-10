<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Layout;

final class FontMetrics
{
    /**
     * Per-character width ratios for Helvetica/Arial at 1 pt.
     * Values are the fraction of fontSize that each character occupies.
     * Covers printable ASCII 32–126.
     *
     * @var array<string, float>
     */
    private static array $widths = [
        // 32 — space
        ' '  => 0.278,
        // 33 — !
        '!'  => 0.278,
        // 34 — "
        '"'  => 0.355,
        // 35 — #
        '#'  => 0.556,
        // 36 — $
        '$'  => 0.556,
        // 37 — %
        '%'  => 0.889,
        // 38 — &
        '&'  => 0.667,
        // 39 — '
        "'"  => 0.191,
        // 40 — (
        '('  => 0.333,
        // 41 — )
        ')'  => 0.333,
        // 42 — *
        '*'  => 0.389,
        // 43 — +
        '+'  => 0.584,
        // 44 — ,
        ','  => 0.278,
        // 45 — -
        '-'  => 0.333,
        // 46 — .
        '.'  => 0.278,
        // 47 — /
        '/'  => 0.278,
        // 48–57 — digits
        '0'  => 0.556,
        '1'  => 0.556,
        '2'  => 0.556,
        '3'  => 0.556,
        '4'  => 0.556,
        '5'  => 0.556,
        '6'  => 0.556,
        '7'  => 0.556,
        '8'  => 0.556,
        '9'  => 0.556,
        // 58 — :
        ':'  => 0.278,
        // 59 — ;
        ';'  => 0.278,
        // 60 — <
        '<'  => 0.584,
        // 61 — =
        '='  => 0.584,
        // 62 — >
        '>'  => 0.584,
        // 63 — ?
        '?'  => 0.556,
        // 64 — @
        '@'  => 1.015,
        // 65–90 — uppercase
        'A'  => 0.667,
        'B'  => 0.667,
        'C'  => 0.722,
        'D'  => 0.722,
        'E'  => 0.667,
        'F'  => 0.611,
        'G'  => 0.778,
        'H'  => 0.722,
        'I'  => 0.278,
        'J'  => 0.500,
        'K'  => 0.667,
        'L'  => 0.556,
        'M'  => 0.833,
        'N'  => 0.722,
        'O'  => 0.778,
        'P'  => 0.667,
        'Q'  => 0.778,
        'R'  => 0.722,
        'S'  => 0.667,
        'T'  => 0.611,
        'U'  => 0.722,
        'V'  => 0.667,
        'W'  => 0.944,
        'X'  => 0.667,
        'Y'  => 0.667,
        'Z'  => 0.611,
        // 91 — [
        '['  => 0.278,
        // 92 — \
        '\\' => 0.278,
        // 93 — ]
        ']'  => 0.278,
        // 94 — ^
        '^'  => 0.469,
        // 95 — _
        '_'  => 0.556,
        // 96 — `
        '`'  => 0.333,
        // 97–122 — lowercase
        'a'  => 0.556,
        'b'  => 0.556,
        'c'  => 0.500,
        'd'  => 0.556,
        'e'  => 0.556,
        'f'  => 0.278,
        'g'  => 0.556,
        'h'  => 0.556,
        'i'  => 0.222,
        'j'  => 0.222,
        'k'  => 0.500,
        'l'  => 0.222,
        'm'  => 0.833,
        'n'  => 0.556,
        'o'  => 0.556,
        'p'  => 0.556,
        'q'  => 0.556,
        'r'  => 0.333,
        's'  => 0.500,
        't'  => 0.278,
        'u'  => 0.556,
        'v'  => 0.500,
        'w'  => 0.722,
        'x'  => 0.500,
        'y'  => 0.500,
        'z'  => 0.500,
        // 123 — {
        '{'  => 0.334,
        // 124 — |
        '|'  => 0.260,
        // 125 — }
        '}'  => 0.334,
        // 126 — ~
        '~'  => 0.584,
    ];

    private const BOLD_FACTOR = 1.12;

    // ---------------------------------------------------------------------------
    // Public API
    // ---------------------------------------------------------------------------

    /**
     * Measure the rendered width of a string at the given font size.
     */
    public static function stringWidth(string $text, float $fontSize, bool $bold = false): float
    {
        $width = 0.0;
        $len   = mb_strlen($text);

        for ($i = 0; $i < $len; $i++) {
            $char   = mb_substr($text, $i, 1);
            $ratio  = self::$widths[$char] ?? 0.556; // default to average width
            $width += $ratio * $fontSize;
        }

        return $bold ? $width * self::BOLD_FACTOR : $width;
    }

    /**
     * Word-wrap $text to fit within $maxWidth at $fontSize.
     *
     * @return string[]
     */
    public static function wrap(string $text, float $maxWidth, float $fontSize, bool $bold = false): array
    {
        $words  = explode(' ', $text);
        $lines  = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current . ' ' . $word;

            if (self::stringWidth($candidate, $fontSize, $bold) <= $maxWidth) {
                $current = $candidate;
            } else {
                if ($current !== '') {
                    $lines[] = $current;
                }

                // If a single word is wider than maxWidth, push it as-is
                $current = $word;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines ?: [''];
    }

    /**
     * Standard line-height for the given font size.
     */
    public static function lineHeight(float $fontSize): float
    {
        return $fontSize * 1.4;
    }

    /**
     * Measure wrapped text and return rendering metadata.
     *
     * @return array{lines: string[], totalHeight: float, lineHeight: float}
     */
    public static function measure(
        string $text,
        float  $maxWidth,
        float  $fontSize,
        bool   $bold = false,
    ): array {
        $lines      = self::wrap($text, $maxWidth, $fontSize, $bold);
        $lineHeight = self::lineHeight($fontSize);
        $total      = $lineHeight * count($lines);

        return [
            'lines'       => $lines,
            'totalHeight' => $total,
            'lineHeight'  => $lineHeight,
        ];
    }
}