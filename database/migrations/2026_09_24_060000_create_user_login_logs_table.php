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
        Schema::create('user_login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device', 50)->nullable()->default('Desktop');
            $table->string('browser', 50)->nullable()->default('Unknown');
            $table->string('platform', 50)->nullable()->default('Unknown');
            $table->timestamp('login_at')->useCurrent();
            $table->string('status', 20)->default('success');
            $table->timestamps();

            $table->index(['user_id', 'login_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'last_login_ip']);
        });

        Schema::dropIfExists('user_login_logs');
    }
};
