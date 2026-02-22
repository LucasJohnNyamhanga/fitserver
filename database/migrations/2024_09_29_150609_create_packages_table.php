<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {

            /*
            |----------------------------------
            | Primary Key
            |----------------------------------
            */
            $table->bigIncrements('id');

            /*
            |----------------------------------
            | Package Content
            |----------------------------------
            */

            $table->string('title', 150)->index();

            $table->longText('description')->nullable();
            $table->longText('expectation')->nullable();

            $table->string('image', 255)->nullable();
            $table->string('target', 100)->index();

            /*
            |----------------------------------
            | Pricing & Rating
            |----------------------------------
            */

            // Use decimal precision for money
            $table->decimal('price', 10, 2)->default(0);

            // Rating precision (0 - 5 scale recommended)
            $table->decimal('rating', 3, 2)->default(0);

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
            | Timestamp (PostgreSQL Safe)
            |----------------------------------
            */

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};