<?php

use App\Support\CatalogText;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Fills Russian and English for the starting catalogue on sites that
    // were installed before translations existed. Rows the owner already
    // translated are skipped.
    public function up(): void
    {
        CatalogText::apply();
    }
};
