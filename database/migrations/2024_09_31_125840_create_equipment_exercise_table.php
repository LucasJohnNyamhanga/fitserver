<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_exercise', function (Blueprint $table) {

            $table->id();

            /*
            |----------------------------------
            | Relations
            |----------------------------------
            */

            $table->foreignId('equipment_id')
                ->constrained('equipments')
                ->cascadeOnDelete();

            $table->foreignId('exercise_id')
                ->constrained('exercises')
                ->cascadeOnDelete();

            /*
            |----------------------------------
            | Prevent Duplicate Mapping
            |----------------------------------
            */

            $table->unique(
                ['equipment_id', 'exercise_id'],
                'equipment_exercise_unique'
            );

            /*
            |----------------------------------
            | PostgreSQL Safe Timestamps
            |----------------------------------
            */

            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_exercise');
    }
};