<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category')->default('Operasional'); // Operasional, Listrik & Air, Sewa Tempat, Plastik/Kemasan, Gaji, Transport, Lainnya
            $table->string('title');
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('expense_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
