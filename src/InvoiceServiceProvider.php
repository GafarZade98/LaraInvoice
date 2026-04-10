<?php

declare(strict_types=1);

namespace GafarZade98\LaraInvoice;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class InvoiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/invoice.php', 'invoice');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'larainvoice');

        // Register all <x-invoice::*> Blade components.
        Blade::componentNamespace('GafarZade98\\LaraInvoice\\Components', 'invoice');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/invoice.php' => config_path('invoice.php'),
            ], 'invoice-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/larainvoice'),
            ], 'invoice-views');
        }
    }
}