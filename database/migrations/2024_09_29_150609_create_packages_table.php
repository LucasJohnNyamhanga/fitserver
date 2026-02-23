<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {

            $table->bigIncrements('id');

            // Core
            $table->string('title', 150);
            $table->longText('description')->nullable();
            $table->longText('expectation')->nullable();
            $table->string('image', 255)->nullable();
            $table->string('target', 100);

            // Pricing
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('rating', 3, 2)->default(0);

            // Status
            $table->boolean('active')->default(true);

            // Relations
            $table->unsignedBigInteger('trainer_id');
            $table->foreign('trainer_id')
                ->references('id')
                ->on('trainers')
                ->onDelete('cascade');

            $table->timestampsTz();
        });

        /*
        |--------------------------------------------------------------------------
        | GENERATED SEARCH VECTOR (PostgreSQL 12+)
        |--------------------------------------------------------------------------
        | Weighted search:
        |  - Title weight A (highest)
        |  - Description weight B
        */

        DB::statement("
            ALTER TABLE packages
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (
                setweight(to_tsvector('simple', coalesce(title, '')), 'A') ||
                setweight(to_tsvector('simple', coalesce(description, '')), 'B')
            ) STORED;
        ");

        /*
        |--------------------------------------------------------------------------
        | INDEXES
        |--------------------------------------------------------------------------
        */

        DB::statement('CREATE INDEX packages_trainer_idx ON packages (trainer_id)');
        DB::statement('CREATE INDEX packages_price_idx ON packages (price)');
        DB::statement('CREATE INDEX packages_target_idx ON packages (target)');
        DB::statement('CREATE INDEX packages_active_idx ON packages (active)');

        /*
        |--------------------------------------------------------------------------
        | PARTIAL GIN INDEX (Optimized for active packages)
        |--------------------------------------------------------------------------
        */

        DB::statement("
            CREATE INDEX packages_search_active_idx
            ON packages
            USING GIN (search_vector)
            WHERE active = true;
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS packages_search_active_idx');
        DB::statement('DROP INDEX IF EXISTS packages_trainer_idx');
        DB::statement('DROP INDEX IF EXISTS packages_price_idx');
        DB::statement('DROP INDEX IF EXISTS packages_target_idx');
        DB::statement('DROP INDEX IF EXISTS packages_active_idx');

        Schema::dropIfExists('packages');
    }
};