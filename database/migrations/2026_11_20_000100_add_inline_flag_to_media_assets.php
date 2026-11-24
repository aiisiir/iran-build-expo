<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Uploaded from a record's form (not the media library): such a file belongs to that record and goes away with it.
        Schema::table('media_assets', function (Blueprint $table): void {
            $table->boolean('is_inline')->default(false)->after('kind');
        });
    }

    public function down(): void
    {
        Schema::table('media_assets', fn (Blueprint $table) => $table->dropColumn('is_inline'));
    }
};
