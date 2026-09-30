<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Toy, nişan, xınayaxdı, söz kəsmə… — what a xonça is carried to.
        Schema::create('occasions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('accent', 9)->default('#8a1c2b');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // A physical tray that is rented out. Its look in 3D comes from the
        // columns below, so the owner changes a colour and the site follows.
        Schema::create('xoncas', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('deposit', 10, 2)->default(0);
            $table->unsignedInteger('stock')->default(1);
            $table->string('style')->default('klassik');
            $table->string('shape')->default('round');
            $table->unsignedInteger('size_cm')->default(60);
            $table->string('tray_color', 9)->default('#c9a14a');
            $table->string('rim_color', 9)->default('#e8c77a');
            $table->string('cover_type')->default('tulle');
            $table->string('cover_color', 9)->default('#fff4f4');
            $table->string('ribbon_color', 9)->default('#b3122e');
            $table->json('ribbon_choices')->nullable();
            $table->boolean('fill_allowed')->default(true);
            $table->decimal('empty_discount', 10, 2)->default(0);
            $table->json('images')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('occasion_xonca', function (Blueprint $table) {
            $table->foreignId('occasion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('xonca_id')->constrained()->cascadeOnDelete();
            $table->primary(['occasion_id', 'xonca_id']);
        });

        Schema::create('fill_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        // Things that go on the tray. `model` picks the 3D shape drawn for it.
        Schema::create('fill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fill_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('model');
            $table->string('color', 9)->default('#c0392b');
            $table->string('color2', 9)->nullable();
            $table->decimal('scale', 4, 2)->default(1);
            $table->unsignedInteger('max_qty')->default(12);
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        // What a xonça carries when the customer takes it "as we fill it".
        Schema::create('xonca_default_fills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('xonca_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fill_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(1);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('customer_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->foreignId('occasion_id')->nullable()->constrained()->nullOnDelete();
            $table->date('event_date');
            $table->string('event_time')->nullable();
            $table->string('delivery_type')->default('delivery');
            $table->string('district')->nullable();
            $table->string('address')->nullable();
            $table->text('note')->nullable();
            $table->decimal('xonca_total', 10, 2)->default(0);
            $table->decimal('fill_total', 10, 2)->default(0);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->decimal('deposit_total', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('status')->default('new');
            $table->boolean('deposit_returned')->default(false);
            $table->text('admin_note')->nullable();
            $table->timestamps();
            $table->index(['event_date', 'status']);
        });

        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('xonca_id')->nullable()->constrained()->nullOnDelete();
            $table->string('xonca_name');
            $table->string('fill_mode')->default('ours');
            $table->string('ribbon_color', 9)->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('deposit', 10, 2)->default(0);
            $table->json('fill')->nullable();
            $table->decimal('fill_total', 10, 2)->default(0);
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('occasion')->nullable();
            $table->text('text');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'faqs', 'reviews', 'booking_items', 'bookings', 'xonca_default_fills',
            'fill_items', 'fill_categories', 'occasion_xonca', 'xoncas', 'occasions'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
