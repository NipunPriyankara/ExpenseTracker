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
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('transport_mode', 50)->nullable()->after('payment_method');
            $table->string('vehicle_type', 50)->nullable()->after('transport_mode');

            $table->index(['user_id', 'transport_mode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'transport_mode']);
            $table->dropColumn(['transport_mode', 'vehicle_type']);
        });
    }
};
