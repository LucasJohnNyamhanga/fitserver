<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_package', function (Blueprint $table) {

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
            $table->unsignedBigInteger('customer_id')->index();

            /*
            |----------------------------------
            | Foreign Keys
            |----------------------------------
            */

            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
                ->onDelete('cascade');

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');

            /*
            |----------------------------------
            | Prevent Duplicate Subscription
            |----------------------------------
            */

            $table->unique(['package_id', 'customer_id']);

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
        Schema::dropIfExists('customer_package');
    }
};