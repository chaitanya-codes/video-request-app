<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('video_requests', function (Blueprint $table) {
            $table->id();
            $table->string('video_name')->nullable();
            $table->text('description')->nullable();
            $table->enum('orientation', ['landscape', 'portrait'])->default('landscape');
            $table->enum('output_format', ['mp4', 'scorm'])->default('mp4');
            $table->enum('avatar_gender', ['male', 'female', 'none'])->default('male');
            $table->integer('num_modules')->nullable();
            $table->string('logo_path')->nullable();
            $table->json('files_path')->nullable();
            $table->string('primary_brand_color')->nullable();
            $table->string('secondary_1_brand_color')->nullable();
            $table->string('secondary_2_brand_color')->nullable();
            $table->string('brand_theme')->nullable();
            $table->text('brand_design_notes')->nullable();
            $table->boolean('animation_required')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_requests');
    }
};
