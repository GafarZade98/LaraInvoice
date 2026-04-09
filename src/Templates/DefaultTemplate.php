<?php

namespace GafarZade98\LaraInvoice\Templates;

class DefaultTemplate extends AbstractTemplate
{
    protected function view(): string
    {
        return 'larainvoice::default';
    }
}