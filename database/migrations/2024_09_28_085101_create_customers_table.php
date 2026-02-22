<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {

            $table->id();

            /*
            |----------------------------------
            | Profile Attributes
            |----------------------------------
            */

            $table->string('gender', 20);
            $table->string('goal', 100);

            $table->integer('age');
            $table->integer('height');
            $table->integer('weight');
            $table->integer('targetWeight');

            $table->string('health', 100)->nullable();
            $table->string('fitnessLevel', 100)->nullable();
            $table->string('strength', 100)->nullable();
            $table->string('fatStatus', 100)->nullable();

            /*
            |----------------------------------
            | Media
            |----------------------------------
            */
            $table->string('image')->nullable();

            /*
            |----------------------------------
            | Relations (One-to-One with users)
            |----------------------------------
            */
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            /*
            |----------------------------------
            | Index Strategy (Clean & Efficient)
            |----------------------------------
            */

            // Composite index for filtering by gender + goal
            $table->index(['gender', 'goal'], 'customers_gender_goal_idx');

            // Age index only once
            $table->index('age', 'customers_age_idx');

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
        Schema::dropIfExists('customers');
    }
};