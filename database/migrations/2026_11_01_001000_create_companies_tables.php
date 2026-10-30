<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('language_id')->constrained();
            $table->string('translation_key', 26)->nullable();
            $table->foreignId('page_id')->constrained(); // listing page whose URL prefixes this company
            $table->string('name', 191);
            $table->string('slug', 160);
            $table->foreignId('logo_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('industry', 120)->nullable(); // plain field, shown as a corner badge
            $table->longText('description')->nullable(); // sanitized HTML
            $table->string('meta_title', 191)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['language_id', 'page_id', 'slug']);
            $table->unique(['translation_key', 'language_id']);
        });

        Schema::create('company_edition', function (Blueprint $table): void {
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['company_id', 'edition_id']);
        });

        // Language is inherited from the company.
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 191);
            $table->string('slug', 160);
            $table->longText('description')->nullable(); // sanitized HTML
            $table->string('meta_title', 191)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'slug']);
        });

        Schema::create('product_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media_assets');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('company_edition');
        Schema::dropIfExists('companies');
    }
};
