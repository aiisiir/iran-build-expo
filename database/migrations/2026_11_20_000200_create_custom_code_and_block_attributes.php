<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Site-wide custom CSS/JS written by the superadmin only; served as two cached files so the CSP can stay strict.
        Schema::create('custom_snippets', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 120);
            $table->string('type', 3); // css | js
            $table->mediumText('code');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('page_modules', function (Blueprint $table): void {
            $table->string('css_class', 120)->nullable();
            $table->string('css_id', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('page_modules', fn (Blueprint $table) => $table->dropColumn(['css_class', 'css_id']));
        Schema::dropIfExists('custom_snippets');
    }
};
