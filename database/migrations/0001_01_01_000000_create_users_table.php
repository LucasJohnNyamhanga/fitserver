<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * USERS TABLE
         */
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('fullname', 150);
            $table->string('mobile', 30)->index();
            $table->string('username', 100)->unique()->index();

            $table->boolean('active')->default(false)->index();
            $table->boolean('is_trainer')->default(false)->index();

            $table->string('password');

            $table->rememberToken();

            // PostgreSQL timezone safe timestamp
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable();
        });

        /**
         * PASSWORD RESET TABLE
         */
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 150)->primary();
            $table->string('token');
            $table->timestampTz('created_at')->nullable();

            $table->index('created_at');
        });

        /**
         * SESSIONS TABLE (PostgreSQL Optimized)
         */
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->index();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->longText('payload');

            $table->integer('last_activity')->index();
        });

        /**
         * PostgreSQL performance tuning (important ⭐)
         */
        DB::statement('SET default_statistics_target = 100');
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};