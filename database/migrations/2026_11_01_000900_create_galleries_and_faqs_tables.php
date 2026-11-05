<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('language_id')->constrained();
            $table->foreignId('edition_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 191);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('gallery_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gallery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media_assets');
            $table->string('caption', 191)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['gallery_id', 'sort_order']);
        });

        Schema::create('faqs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('language_id')->constrained();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('question', 300);
            $table->text('answer'); // sanitized HTML
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('page_modules', function (Blueprint $table): void {
            $table->foreign('gallery_id')->references('id')->on('galleries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('page_modules', fn (Blueprint $table) => $table->dropForeign(['gallery_id']));
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('galleries');
    }
};
