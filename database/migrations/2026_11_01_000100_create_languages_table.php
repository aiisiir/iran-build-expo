<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 12)->unique();
            $table->string('name', 60);
            $table->string('native_name', 60);
            $table->string('direction', 3)->default('ltr'); // rtl | ltr
            $table->boolean('is_active')->default(true);
            // Single-default trick: only one row may hold 1, all others stay NULL (MariaDB has no partial unique index).
            $table->boolean('is_default')->nullable()->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
