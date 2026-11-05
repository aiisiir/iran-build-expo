<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Operational data (not public content): no language column.
        Schema::create('badge_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 60)->unique();
            $table->char('color', 7)->default('#1d3557'); // card accent colour
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('badges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('edition_id')->constrained();
            $table->foreignId('badge_type_id')->constrained();
            $table->string('full_name', 120);
            $table->text('phone'); // encrypted cast (APP_KEY)
            $table->text('email'); // encrypted cast
            $table->string('gender', 6); // App\Enums\Gender
            $table->unsignedTinyInteger('age')->nullable();
            $table->text('address')->nullable(); // encrypted cast
            $table->string('education_degree', 60)->nullable();
            $table->string('education_field', 120)->nullable();
            $table->string('activity_field', 120)->nullable();
            $table->string('photo_path', 191); // private storage, never under the web root
            $table->string('status', 10)->default('pending'); // App\Enums\BadgeStatus
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->unsignedSmallInteger('print_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['edition_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
        Schema::dropIfExists('badge_types');
    }
};
