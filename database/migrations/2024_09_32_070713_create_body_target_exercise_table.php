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
        Schema::create('body_target_exercise', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('body_target_id');
            $table->foreign('body_target_id')->references('id')->on('body_targets')->onDelete('cascade');
            $table->unsignedBigInteger('exercise_id');
            $table->foreign('exercise_id')->references('id')->on('exercises')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['body_target_id', 'exercise_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('body_target_exercise');
    }
};
