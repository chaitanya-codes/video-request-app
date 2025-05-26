<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkOrderStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_order_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_request_id')->constrained()->onDelete('cascade');
            $table->enum('stage', ['1', '2', '3', '4'])->default('1');
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_order_statuses');
    }
}
