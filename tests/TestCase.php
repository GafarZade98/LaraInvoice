<?php

namespace GafarZade98\LaraInvoice\Tests;

use GafarZade98\LaraInvoice\InvoiceServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [InvoiceServiceProvider::class];
    }

    protected function outputPath(string $filename): string
    {
        $dir = __DIR__ . '/output';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . '/' . $filename;
    }
}