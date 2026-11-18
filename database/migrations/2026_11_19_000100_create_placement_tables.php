<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extra pages a record also appears in (its own page_id, where it has one, stays the main/canonical page).
        Schema::create('page_placements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('placeable_type', 32); // morph alias, see AppServiceProvider
            $table->unsignedBigInteger('placeable_id');

            $table->unique(['placeable_type', 'placeable_id', 'page_id']);
            $table->index('page_id');
        });

        // Extra categories a post/FAQ also belongs to (its own category_id stays the main one).
        Schema::create('categorizables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('categorizable_type', 32);
            $table->unsignedBigInteger('categorizable_id');

            $table->unique(['categorizable_type', 'categorizable_id', 'category_id'], 'categorizables_unique');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorizables');
        Schema::dropIfExists('page_placements');
    }
};
