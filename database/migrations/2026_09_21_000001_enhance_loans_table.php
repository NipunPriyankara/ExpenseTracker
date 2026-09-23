<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->enum('type', ['borrowed', 'lent'])->default('borrowed')->after('user_id');
            $table->string('person_name')->nullable()->after('type');
            $table->enum('status', ['active', 'settled'])->default('active')->after('paid_amount');
            $table->decimal('monthly_installment', 12, 2)->nullable()->change();
            
            $table->index(['user_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'type', 'status']);
            $table->dropColumn(['type', 'person_name', 'status']);
            $table->decimal('monthly_installment', 12, 2)->nullable(false)->change();
        });
    }
};
