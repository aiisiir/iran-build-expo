<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Shown in the site header: English name and opening hours (dates already exist).
        Schema::table('editions', function (Blueprint $table): void {
            $table->string('name_en', 150)->nullable()->after('name');
            $table->string('hours', 60)->nullable()->after('ends_on');
        });
        // "About" block: video file or stream address.
        Schema::table('page_modules', fn (Blueprint $table) => $table->string('video_url', 255)->nullable()->after('media_id'));
    }

    public function down(): void
    {
        Schema::table('editions', fn (Blueprint $table) => $table->dropColumn(['name_en', 'hours']));
        Schema::table('page_modules', fn (Blueprint $table) => $table->dropColumn('video_url'));
    }
};
