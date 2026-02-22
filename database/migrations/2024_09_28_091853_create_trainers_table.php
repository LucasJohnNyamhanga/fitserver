<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainers', function (Blueprint $table) {

            /*
            |----------------------------------
            | Primary Key
            |----------------------------------
            */
            $table->bigIncrements('id');

            /*
            |----------------------------------
            | Trainer Profile Data
            |----------------------------------
            */

            $table->string('location', 150)->index();

            // Long text fields (PostgreSQL handles this efficiently)
            $table->longText('bio')->nullable();
            $table->longText('services')->nullable();

            /*
            |----------------------------------
            | Status Flags
            |----------------------------------
            */

            $table->boolean('active')->default(false)->index();
            $table->boolean('is_super')->default(false)->index();

            /*
            |----------------------------------
            | Relations
            |----------------------------------
            */

            $table->unsignedBigInteger('user_id')->unique();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('trainers');
    }
};