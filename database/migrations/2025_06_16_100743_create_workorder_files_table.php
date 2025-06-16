<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkorderFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workorder_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_request_id')->constrained('video_requests')->onDelete('cascade');
            $table->string('file_path')->nullable();
            $table->enum('file_type', ['script', 'voiceover', 'segments', 'final_video'])->default('script');
            $table->boolean('is_rejected')->default(false);
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
        Schema::dropIfExists('workorder_files');
    }
}
