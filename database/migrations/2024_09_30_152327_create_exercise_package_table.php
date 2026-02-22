<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_package', function (Blueprint $table) {

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

            $table->unsignedBigInteger('package_id')->index();
            $table->unsignedBigInteger('exercise_id')->index();

            /*
            |----------------------------------
            | Constraints
            |----------------------------------
            */

            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
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

            $table->unique(['package_id', 'exercise_id']);

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
        Schema::dropIfExists('exercise_package');
    }
};