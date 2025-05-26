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
        Schema::create('workorder_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_request_id')->constrained()->onDelete('cascade');
            $table->enum('stage', ['1', '2', '3', '4', '5'])->default('1');
            $table->string('script_path')->nullable();
            $table->string('voiceover_path')->nullable();
            $table->json('segments_path')->nullable();
            $table->string('final_video_path')->nullable();
            $table->json('approved')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workorder_status');
    }
};
