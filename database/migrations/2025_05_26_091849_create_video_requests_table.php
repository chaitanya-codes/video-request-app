<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVideoRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_requests', function (Blueprint $table) {
            $table->id();
            $table->string('video_name')->nullable();
            $table->text('description')->nullable();
            $table->enum('orientation', ['landscape', 'portrait'])->default('landscape');
            $table->enum('output_format', ['mp4', 'scorm'])->default('mp4');
            $table->enum('avatar_gender', ['male', 'female'])->default('male');
            $table->integer('num_modules')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('brand_color')->nullable();
            $table->string('brand_theme')->nullable();
            $table->text('brand_design_notes')->nullable();
            $table->boolean('animation_required')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_requests');
    }
}
