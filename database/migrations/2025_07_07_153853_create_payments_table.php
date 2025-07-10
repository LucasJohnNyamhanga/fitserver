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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->string('reference')->unique();         // Used as ZenoPay order_id
            $table->string('status')->default('pending');  // pending, completed, failed
            $table->string('transaction_id')->nullable()->index(); // ZenoPay transid
            $table->string('channel')->nullable();         // e.g., MPESA-TZ
            $table->string('phone')->nullable();           // Buyer mobile number
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
