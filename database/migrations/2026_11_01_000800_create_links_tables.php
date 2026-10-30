<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One mechanism for menu, logo, quick links, organizers, supporters and footer links.
        Schema::create('link_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('language_id')->constrained();
            $table->string('placement', 20); // App\Enums\LinkPlacement
            $table->string('title', 191)->nullable();
            $table->boolean('show_title')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['language_id', 'placement', 'is_active']);
        });

        Schema::create('links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('link_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('links')->nullOnDelete(); // unlimited menu depth
            $table->string('label', 191);
            $table->boolean('show_label')->default(true); // false keeps the text for SEO/screen readers but hides it
            $table->string('type', 8)->default('url'); // App\Enums\LinkType
            $table->foreignId('page_id')->nullable()->constrained()->nullOnDelete();
            $table->string('url', 2048)->nullable();
            $table->foreignId('file_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('icon_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('css_class', 120)->nullable();
            $table->string('target', 8)->default('_self');
            $table->string('title_attr', 191)->nullable();
            $table->text('body')->nullable(); // sanitized HTML
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['link_group_id', 'parent_id', 'sort_order']);
        });

        Schema::table('page_modules', function (Blueprint $table): void {
            $table->foreign('link_group_id')->references('id')->on('link_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('page_modules', fn (Blueprint $table) => $table->dropForeign(['link_group_id']));
        Schema::dropIfExists('links');
        Schema::dropIfExists('link_groups');
    }
};
