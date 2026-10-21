<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['in', 'out']);
            $table->string('source'); // sale, purchase, expense, capital_in, capital_out, adjustment
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->string('description');
            $table->dateTime('transaction_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
