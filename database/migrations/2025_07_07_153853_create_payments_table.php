<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {

            $table->id();

            /*
            |----------------------------------
            | Relations
            |----------------------------------
            */

            $table->foreignId('package_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |----------------------------------
            | Payment Identifiers
            |----------------------------------
            */

            $table->string('reference', 100)
                ->unique()
                ->comment('ZenoPay order_id');

            $table->string('transaction_id', 150)
                ->nullable()
                ->index()
                ->comment('ZenoPay transaction id');

            /*
            |----------------------------------
            | Payment Channel Info
            |----------------------------------
            */

            $table->string('channel', 50)->nullable();
            $table->string('phone', 30)->nullable()->index();

            /*
            |----------------------------------
            | Financial Data
            |----------------------------------
            */

            $table->decimal('amount', 12, 2);

            /*
            |----------------------------------
            | Payment Workflow Control
            |----------------------------------
            */

            $table->string('status', 20)
                ->default('pending')
                ->index();

            $table->unsignedTinyInteger('retries_count')
                ->default(0);

            $table->timestampTz('next_check_at')
                ->nullable()
                ->index();

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
        Schema::dropIfExists('payments');
    }
};