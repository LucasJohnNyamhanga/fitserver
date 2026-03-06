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
        Schema::create('body_targets', function (Blueprint $table) {

            /*
            |----------------------------------
            | Primary Key
            |----------------------------------
            */
            $table->bigIncrements('id');

            /*
            |----------------------------------
            | Body Target Fields
            |----------------------------------
            */

            $table->string('name', 150);
            $table->longText('description')->nullable();
            $table->string('image');
            $table->boolean('active')->default(true);

            /*
            |----------------------------------
            | Explicit Index (PostgreSQL Safe)
            |----------------------------------
            */
            $table->index('active', 'bt_active_idx');
            $table->index('name', 'bt_name_idx');

            /*
            |----------------------------------
            | Timestamp (Timezone Safe)
            |----------------------------------
            */
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('body_targets');
    }
};