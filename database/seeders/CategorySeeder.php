<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaultCategories = [
            ['name' => 'Food',          'icon' => 'bi-cup-hot',      'color' => '#f97316', 'type' => 'expense'],
            ['name' => 'Transport',     'icon' => 'bi-bus-front',    'color' => '#0ea5e9', 'type' => 'expense'],
            ['name' => 'Fuel',          'icon' => 'bi-fuel-pump',    'color' => '#eab308', 'type' => 'expense'],
            ['name' => 'Bills',         'icon' => 'bi-receipt',      'color' => '#ef4444', 'type' => 'expense'],
            ['name' => 'Mobile',        'icon' => 'bi-phone',        'color' => '#8b5cf6', 'type' => 'expense'],
            ['name' => 'Internet',      'icon' => 'bi-wifi',         'color' => '#06b6d4', 'type' => 'expense'],
            ['name' => 'Shopping',      'icon' => 'bi-bag',          'color' => '#ec4899', 'type' => 'expense'],
            ['name' => 'Entertainment', 'icon' => 'bi-film',         'color' => '#a855f7', 'type' => 'expense'],
            ['name' => 'Education',     'icon' => 'bi-book',         'color' => '#3b82f6', 'type' => 'expense'],
            ['name' => 'Health',        'icon' => 'bi-heart-pulse',  'color' => '#10b981', 'type' => 'expense'],
            ['name' => 'Rent',          'icon' => 'bi-house-door',   'color' => '#6366f1', 'type' => 'expense'],
            ['name' => 'Loan',          'icon' => 'bi-bank',         'color' => '#64748b', 'type' => 'expense'],
            ['name' => 'Vehicle',       'icon' => 'bi-car-front',    'color' => '#14b8a6', 'type' => 'expense'],
            ['name' => 'Family',        'icon' => 'bi-people',       'color' => '#f43f5e', 'type' => 'expense'],
            ['name' => 'Other',         'icon' => 'bi-three-dots',   'color' => '#94a3b8', 'type' => 'expense'],
        ];

        foreach ($defaultCategories as $cat) {
            Category::updateOrCreate(
                ['name' => $cat['name'], 'user_id' => null, 'type' => $cat['type']],
                [
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'is_default' => true,
                ]
            );
        }
    }
}
