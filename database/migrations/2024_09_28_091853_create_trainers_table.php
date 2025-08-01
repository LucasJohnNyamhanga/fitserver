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
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->longText('bio');
            $table->longText('services');
            $table->boolean('active')->default(false);
            $table->boolean('is_super')->default(false);
            
            $table->unsignedBigInteger('user_id')->unique(); // define unique first
        
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade'); // define foreign key separately
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainers');
    }
};
