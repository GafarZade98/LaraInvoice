<?php

namespace GafarZade98\LaraInvoice;

use Illuminate\Support\ServiceProvider;

class InvoiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/invoice.php', 'invoice');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/invoice.php' => config_path('invoice.php'),
            ], 'invoice-config');
        }
    }
}