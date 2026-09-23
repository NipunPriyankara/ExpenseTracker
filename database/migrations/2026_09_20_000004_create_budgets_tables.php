<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('month', 7); // e.g. 2026-09
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['user_id', 'month']);
        });

        Schema::create('budget_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['budget_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_categories');
        Schema::dropIfExists('budgets');
    }
};
