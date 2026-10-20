<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Frames of one full turn of the real xonça, in order: the 360° view.
        Schema::table('xoncas', fn (Blueprint $table) => $table->json('spin')->nullable()->after('images'));
    }

    public function down(): void
    {
        Schema::table('xoncas', fn (Blueprint $table) => $table->dropColumn('spin'));
    }
};
