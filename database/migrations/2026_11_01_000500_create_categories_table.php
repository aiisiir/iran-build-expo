<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('language_id')->constrained();
            $table->string('translation_key', 26)->nullable(); // links equivalent records across languages (hreflang)
            $table->string('type', 10); // App\Enums\CategoryType
            $table->string('name', 120);
            $table->string('slug', 120);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Slugs of soft-deleted rows stay reserved on purpose.
            $table->unique(['language_id', 'type', 'slug']);
            $table->unique(['translation_key', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
