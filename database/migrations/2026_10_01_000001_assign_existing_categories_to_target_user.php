<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Assign all existing categories to user nipunamarasinghe49@gmail.com
     */
    public function up(): void
    {
        $targetUser = User::where('email', 'nipunamarasinghe49@gmail.com')->first();

        if ($targetUser) {
            DB::table('categories')
                ->whereNull('user_id')
                ->update(['user_id' => $targetUser->id]);

            // Ensure any existing categories belong to this target user if they were unassigned or assigned to dummy users
            DB::table('categories')
                ->where('user_id', 1)
                ->update(['user_id' => $targetUser->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op or revert user_id to null for default categories
    }
};
