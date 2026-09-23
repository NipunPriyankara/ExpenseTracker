<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('target_amount', 12, 2);
            $table->decimal('current_amount', 12, 2)->default(0.00);
            $table->date('target_date')->nullable();
            $table->string('icon')->default('bi-piggy-bank');
            $table->string('color', 20)->default('#10b981');
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('savings_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('savings_goal_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdraw'])->default('deposit');
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['savings_goal_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_transactions');
        Schema::dropIfExists('savings_goals');
    }
};
