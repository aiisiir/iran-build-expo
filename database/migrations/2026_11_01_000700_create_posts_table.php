<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // News, announcements and poster/slider items: one table, told apart by category.
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('language_id')->constrained();
            $table->string('translation_key', 64)->nullable();
            $table->foreignId('page_id')->constrained(); // the page whose URL prefixes this post
            $table->foreignId('category_id')->constrained();
            $table->foreignId('edition_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 191);
            $table->string('slug', 160);
            $table->string('lead', 300)->nullable();
            $table->text('summary')->nullable();
            $table->longText('body')->nullable(); // sanitized HTML
            $table->foreignId('image_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('source_name', 191)->nullable();
            $table->string('link_url', 2048)->nullable(); // optional internal/external link (posters, announcements)
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('status', 10)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->string('meta_title', 191)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['language_id', 'page_id', 'slug']);
            $table->unique(['translation_key', 'language_id']);
            $table->index(['page_id', 'category_id', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
