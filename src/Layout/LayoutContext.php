<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice\Layout;

class LayoutContext
{
    // ---------------------------------------------------------------------------
    // Singleton
    // ---------------------------------------------------------------------------

    private static ?self $instance = null;

    // ---------------------------------------------------------------------------
    // Page geometry
    // ---------------------------------------------------------------------------

    public float $pageWidth;
    public float $pageHeight;
    public float $margin;
    public float $contentWidth;

    // ---------------------------------------------------------------------------
    // Cursor
    // ---------------------------------------------------------------------------

    public float $x;
    public float $y;

    // ---------------------------------------------------------------------------
    // Column alignment (set by Col, read by Text)
    // ---------------------------------------------------------------------------

    public string $colAlign = 'left';

    // ---------------------------------------------------------------------------
    // Stacks
    // ---------------------------------------------------------------------------

    /** @var array<int, array<string, mixed>> */
    public array $rowStack = [];

    /** @var array<int, array<string, mixed>> */
    public array $tableStack = [];

    // ---------------------------------------------------------------------------
    // Private constructor — use make() / getInstance()
    // ---------------------------------------------------------------------------

    private function __construct() {}

    // ---------------------------------------------------------------------------
    // Singleton factory
    // ---------------------------------------------------------------------------

    /**
     * Reset the singleton and create a fresh instance with the given dimensions.
     */
    public static function make(float $pageWidth = 595, float $margin = 40): static
    {
        $ctx = new static();

        $ctx->pageWidth    = $pageWidth;
        $ctx->margin       = $margin;
        $ctx->contentWidth = $pageWidth - 2 * $margin;
        $ctx->x            = $margin;
        $ctx->y            = $margin;
        $ctx->pageHeight   = $margin;   // grows as content is added
        $ctx->colAlign     = 'left';
        $ctx->rowStack     = [];
        $ctx->tableStack   = [];

        static::$instance = $ctx;

        return $ctx;
    }

    public static function hasInstance(): bool
    {
        return static::$instance !== null;
    }

    /**
     * Return the existing instance, or create a default one.
     */
    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::make();
        }

        /** @var static $instance */
        $instance = static::$instance;

        return $instance;
    }

    // ---------------------------------------------------------------------------
    // Mutation helpers
    // ---------------------------------------------------------------------------

    /**
     * Update an existing instance's page geometry (called by the Page component).
     */
    public function resize(float $pageWidth, float $margin): void
    {
        $this->pageWidth    = $pageWidth;
        $this->margin       = $margin;
        $this->contentWidth = $pageWidth - 2 * $margin;
        $this->x            = $margin;
        $this->y            = $margin;
        $this->pageHeight   = $margin;
    }

    /**
     * Advance the Y cursor and update the tracked page height.
     */
    public function advanceY(float $delta): void
    {
        $this->y          += $delta;
        $this->pageHeight  = max($this->pageHeight, $this->y);
    }

    // ---------------------------------------------------------------------------
    // Row / Col helpers
    // ---------------------------------------------------------------------------

    /**
     * Begin a new row context, saving the current cursor state.
     */
    public function beginRow(): void
    {
        $this->rowStack[] = [
            'startY'    => $this->y,
            'maxY'      => $this->y,
            'x'         => $this->x,
            'width'     => $this->contentWidth,
            'colCursor' => $this->x,
            'align'     => $this->colAlign,
        ];
    }

    /**
     * Begin a column within the current row.
     *
     * @return array{x: float, width: float, startY: float}
     */
    public function beginCol(float $widthPercent, string $align = 'left'): array
    {
        $top = &$this->rowStack[count($this->rowStack) - 1];

        $colWidth  = $top['width'] * ($widthPercent / 100);
        $colX      = $top['colCursor'];

        // Advance the col cursor for the next sibling column
        $top['colCursor'] += $colWidth;

        // Restore Y to row start so each column starts at the same Y
        $this->x            = $colX;
        $this->y            = $top['startY'];
        $this->contentWidth = $colWidth;
        $this->colAlign     = $align;

        return [
            'x'       => $colX,
            'width'   => $colWidth,
            'startY'  => $top['startY'],
        ];
    }

    /**
     * End a column — record how far down this column rendered.
     */
    public function endCol(): void
    {
        if (empty($this->rowStack)) {
            return;
        }

        $top = &$this->rowStack[count($this->rowStack) - 1];
        $top['maxY'] = max($top['maxY'], $this->y);
    }

    /**
     * End the current row — advance Y to the tallest column's bottom and restore context.
     */
    public function endRow(): void
    {
        if (empty($this->rowStack)) {
            return;
        }

        $top = array_pop($this->rowStack);

        $this->y            = $top['maxY'];
        $this->x            = $top['x'];
        $this->contentWidth = $top['width'];
        $this->colAlign     = $top['align'];

        // Propagate maxY into parent row if nested
        if (!empty($this->rowStack)) {
            $parent = &$this->rowStack[count($this->rowStack) - 1];
            $parent['maxY'] = max($parent['maxY'], $this->y);
        }

        $this->pageHeight = max($this->pageHeight, $this->y);
    }

    // ---------------------------------------------------------------------------
    // Table helpers
    // ---------------------------------------------------------------------------

    /**
     * Begin a table context.
     *
     * @param float[] $widths  Column width percentages (must sum to 100)
     */
    public function beginTable(array $widths, float $rowHeight = 26): void
    {
        $startX      = $this->x;
        $totalWidth  = $this->contentWidth;
        $colDefs     = [];
        $cursor      = $startX;

        foreach ($widths as $pct) {
            $w         = $totalWidth * ($pct / 100);
            $colDefs[] = ['x' => $cursor, 'width' => $w];
            $cursor   += $w;
        }

        $this->tableStack[] = [
            'colDefs'   => $colDefs,
            'colIndex'  => 0,
            'rowHeight' => $rowHeight,
        ];

        // Claim the header row height so TableRow constructors see post-header Y
        $this->advanceY($rowHeight);
    }

    public function currentTable(): ?array
    {
        if (empty($this->tableStack)) {
            return null;
        }

        return $this->tableStack[count($this->tableStack) - 1];
    }

    /**
     * Consume the next column definition from the current table row.
     *
     * @return array{x: float, width: float}|null
     */
    public function nextTableCol(): ?array
    {
        if (empty($this->tableStack)) {
            return null;
        }

        $top = &$this->tableStack[count($this->tableStack) - 1];

        if ($top['colIndex'] >= count($top['colDefs'])) {
            return null;
        }

        $col = $top['colDefs'][$top['colIndex']];
        $top['colIndex']++;

        return $col;
    }

    /**
     * Reset the column index on the current table (called at the start of each data row).
     */
    public function resetTableColIndex(): void
    {
        if (empty($this->tableStack)) {
            return;
        }

        $this->tableStack[count($this->tableStack) - 1]['colIndex'] = 0;
    }

    public function currentTableRowHeight(): float
    {
        $table = $this->currentTable();

        return $table ? $table['rowHeight'] : 26.0;
    }

    public function endTable(): void
    {
        if (!empty($this->tableStack)) {
            array_pop($this->tableStack);
        }
    }
}