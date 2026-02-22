<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {

            /*
            |----------------------------------
            | Primary Key
            |----------------------------------
            */
            $table->bigIncrements('id');

            /*
            |----------------------------------
            | Event Content
            |----------------------------------
            */

            $table->string('title', 150)->index();
            $table->longText('description')->nullable();
            $table->string('image', 255)->nullable();

            /*
            |----------------------------------
            | Relations
            |----------------------------------
            */

            $table->unsignedBigInteger('user_id')->index();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('events');
    }
};