<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('language_id')->constrained();
            $table->string('translation_key', 26)->nullable();
            $table->string('title', 191);
            $table->string('slug', 160);
            $table->boolean('is_home')->nullable(); // 1 for the language's home page, NULL otherwise
            $table->longText('body')->nullable(); // sanitized HTML
            $table->string('meta_title', 191)->nullable();
            $table->string('meta_description', 300)->nullable();
            $table->foreignId('og_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->boolean('noindex')->default(false);
            $table->string('status', 10)->default('draft'); // App\Enums\PublishStatus
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['language_id', 'slug']);
            $table->unique(['language_id', 'is_home']);
            $table->unique(['translation_key', 'language_id']);
        });

        // Orders rows of a page: each module is a configurable block (news list, links, text, gallery, FAQ, companies, 3D tour).
        Schema::create('page_modules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('type', 12); // App\Enums\ModuleType
            $table->string('title', 191)->nullable();
            $table->boolean('show_title')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('display', 10)->default('list'); // App\Enums\ModuleDisplay
            $table->unsignedTinyInteger('item_limit')->default(6);
            $table->foreignId('source_page_id')->nullable()->constrained('pages')->nullOnDelete(); // posts/companies read from this page
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('link_group_id')->nullable(); // FK added after link_groups exists
            $table->unsignedBigInteger('gallery_id')->nullable(); // FK added after galleries exists
            $table->foreignId('media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->longText('body')->nullable(); // sanitized HTML for "content" modules
            $table->timestamps();
            $table->softDeletes();

            $table->index(['page_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_modules');
        Schema::dropIfExists('pages');
    }
};
