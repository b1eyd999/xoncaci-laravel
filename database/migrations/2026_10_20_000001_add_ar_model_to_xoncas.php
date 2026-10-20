<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xoncas', function (Blueprint $table) {
            // A scan of the real xonça (.glb). iPhones get their .usdz made
            // from it in the browser unless one is uploaded.
            $table->string('model_glb')->nullable()->after('images');
            $table->string('model_usdz')->nullable()->after('model_glb');
        });
    }

    public function down(): void
    {
        Schema::table('xoncas', function (Blueprint $table) {
            $table->dropColumn(['model_glb', 'model_usdz']);
        });
    }
};
