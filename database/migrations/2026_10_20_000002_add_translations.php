<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Russian and English copies of what the owner writes: {"ru": {...}, "en": {...}}. */
    private const TABLES = ['xoncas', 'occasions', 'fill_categories', 'fill_items', 'faqs', 'reviews'];

    public function up(): void
    {
        foreach (self::TABLES as $t) {
            Schema::table($t, fn (Blueprint $table) => $table->json('translations')->nullable());
        }
        // Which language the customer ordered in, so the owner answers in it.
        Schema::table('bookings', fn (Blueprint $table) => $table->string('locale', 2)->default('az'));
    }

    public function down(): void
    {
        foreach (self::TABLES as $t) {
            Schema::table($t, fn (Blueprint $table) => $table->dropColumn('translations'));
        }
        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn('locale'));
    }
};
