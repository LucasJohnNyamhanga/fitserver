<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('body_target_exercise', function (Blueprint $table) {

            /*
            |----------------------------------
            | Primary Key
            |----------------------------------
            */
            $table->bigIncrements('id');

            /*
            |----------------------------------
            | Relations
            |----------------------------------
            */

            $table->unsignedBigInteger('body_target_id')->index();
            $table->unsignedBigInteger('exercise_id')->index();

            /*
            |----------------------------------
            | Foreign Keys
            |----------------------------------
            */

            $table->foreign('body_target_id')
                ->references('id')
                ->on('body_targets')
                ->onDelete('cascade');

            $table->foreign('exercise_id')
                ->references('id')
                ->on('exercises')
                ->onDelete('cascade');

            /*
            |----------------------------------
            | Prevent Duplicate Mapping
            |----------------------------------
            */

            $table->unique(['body_target_id', 'exercise_id']);

            /*
            |----------------------------------
            | Timestamp (PostgreSQL Safe)
            |----------------------------------
            */

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('body_target_exercise');
    }
};