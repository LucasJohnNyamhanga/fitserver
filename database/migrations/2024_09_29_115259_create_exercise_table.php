<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {

            /*
            |----------------------------------
            | Primary Key
            |----------------------------------
            */
            $table->bigIncrements('id');

            /*
            |----------------------------------
            | Exercise Basic Info
            |----------------------------------
            */

            $table->string('jina', 150)->index();

            $table->longText('maelezo')->nullable();

            $table->string('ugumu', 50)->index();
            $table->string('muda', 50)->index();

            /*
            |----------------------------------
            | Media
            |----------------------------------
            */

            $table->string('video')->default('empty');
            $table->string('picha', 255)->nullable();

            /*
            |----------------------------------
            | Training Metrics
            |----------------------------------
            */

            $table->integer('repetition')->default(0);
            $table->integer('seti')->default(0);

            $table->string('muscleName', 100)->index();

            $table->longText('instructions')->nullable();

            /*
            |----------------------------------
            | Status
            |----------------------------------
            */

            $table->boolean('active')->default(false)->index();

            /*
            |----------------------------------
            | Relations
            |----------------------------------
            */

            $table->unsignedBigInteger('trainer_id')->index();

            $table->foreign('trainer_id')
                ->references('id')
                ->on('trainers')
                ->onDelete('cascade');

            /*
            |----------------------------------
            | PostgreSQL Safe Timestamp
            |----------------------------------
            */

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};