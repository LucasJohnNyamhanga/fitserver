<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('active_packages', function (Blueprint $table) {

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
            $table->unsignedBigInteger('user_id')->index();

            /*
            |----------------------------------
            | Foreign Keys
            |----------------------------------
            */

            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            /*
            |----------------------------------
            | Prevent Duplicate Active Access
            |----------------------------------
            */
            $table->unique(['package_id', 'user_id']);

            /*
            |----------------------------------
            | Subscription Control (Important ⭐)
            */
            $table->timestampTz('expires_at')->nullable()->index();

            /*
            |----------------------------------
            | Status Tracking
            */
            $table->string('status', 30)->default('active')->index();

            /*
            |----------------------------------
            | Timestamp (PostgreSQL Safe)
            */
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_packages');
    }
};