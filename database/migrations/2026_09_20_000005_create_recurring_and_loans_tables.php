<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->decimal('amount', 12, 2);
            $table->enum('frequency', ['weekly', 'monthly', 'yearly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('payment_method')->default('Cash');
            $table->boolean('is_active')->default(true);
            $table->date('last_generated_date')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('monthly_installment', 12, 2);
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['loan_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('recurring_expenses');
    }
};
