<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Income;
use Illuminate\Database\Seeder;

class IncomeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categoriesData = [
            [
                'name' => 'Salary & Wages',
                'icon' => 'bi-cash-stack',
                'color' => '#10b981',
                'subcategories' => [
                    ['name' => 'Basic Salary', 'icon' => 'bi-cash', 'color' => '#10b981'],
                    ['name' => 'Allowance', 'icon' => 'bi-award', 'color' => '#10b981'],
                    ['name' => 'Overtime (OT)', 'icon' => 'bi-clock-history', 'color' => '#10b981'],
                    ['name' => 'Bonus / Incentives', 'icon' => 'bi-stars', 'color' => '#10b981'],
                ],
            ],
            [
                'name' => 'Freelance & Services',
                'icon' => 'bi-laptop',
                'color' => '#06b6d4',
                'subcategories' => [
                    ['name' => 'Web Development', 'icon' => 'bi-code-slash', 'color' => '#06b6d4'],
                    ['name' => 'Consulting', 'icon' => 'bi-briefcase', 'color' => '#06b6d4'],
                    ['name' => 'Content & Design', 'icon' => 'bi-palette', 'color' => '#06b6d4'],
                ],
            ],
            [
                'name' => 'Business & Sales',
                'icon' => 'bi-shop',
                'color' => '#3b82f6',
                'subcategories' => [
                    ['name' => 'Product Sales', 'icon' => 'bi-box-seam', 'color' => '#3b82f6'],
                    ['name' => 'Service Fees', 'icon' => 'bi-receipt', 'color' => '#3b82f6'],
                    ['name' => 'Commissions', 'icon' => 'bi-percent', 'color' => '#3b82f6'],
                ],
            ],
            [
                'name' => 'Investments & Passive',
                'icon' => 'bi-graph-up-arrow',
                'color' => '#8b5cf6',
                'subcategories' => [
                    ['name' => 'Bank Interest', 'icon' => 'bi-bank', 'color' => '#8b5cf6'],
                    ['name' => 'Dividends', 'icon' => 'bi-piggy-bank', 'color' => '#8b5cf6'],
                    ['name' => 'Rental Income', 'icon' => 'bi-house-door', 'color' => '#8b5cf6'],
                    ['name' => 'Capital Gains', 'icon' => 'bi-arrow-up-right-circle', 'color' => '#8b5cf6'],
                ],
            ],
            [
                'name' => 'Gifts & Grants',
                'icon' => 'bi-gift',
                'color' => '#ec4899',
                'subcategories' => [],
            ],
            [
                'name' => 'Other Income',
                'icon' => 'bi-three-dots',
                'color' => '#64748b',
                'subcategories' => [],
            ],
        ];

        foreach ($categoriesData as $catData) {
            $parent = Category::updateOrCreate(
                [
                    'name' => $catData['name'],
                    'user_id' => null,
                    'type' => 'income',
                ],
                [
                    'icon' => $catData['icon'],
                    'color' => $catData['color'],
                    'parent_id' => null,
                    'is_default' => true,
                ]
            );

            foreach ($catData['subcategories'] as $subData) {
                Category::updateOrCreate(
                    [
                        'name' => $subData['name'],
                        'parent_id' => $parent->id,
                        'user_id' => null,
                        'type' => 'income',
                    ],
                    [
                        'icon' => $subData['icon'],
                        'color' => $subData['color'],
                        'is_default' => true,
                    ]
                );
            }
        }

        // Backfill existing Income records without category_id
        $salaryCategory = Category::where('type', 'income')->where('name', 'Salary & Wages')->first();
        $otherCategory = Category::where('type', 'income')->where('name', 'Other Income')->first();
        $freelanceCategory = Category::where('type', 'income')->where('name', 'Freelance & Services')->first();
        $businessCategory = Category::where('type', 'income')->where('name', 'Business & Sales')->first();
        $investmentCategory = Category::where('type', 'income')->where('name', 'Investments & Passive')->first();

        Income::whereNull('category_id')->get()->each(function ($income) use (
            $salaryCategory, $otherCategory, $freelanceCategory, $businessCategory, $investmentCategory
        ) {
            $src = strtolower($income->source ?? '');
            if (str_contains($src, 'sal')) {
                $income->category_id = $salaryCategory?->id;
            } elseif (str_contains($src, 'free')) {
                $income->category_id = $freelanceCategory?->id;
            } elseif (str_contains($src, 'bus')) {
                $income->category_id = $businessCategory?->id;
            } elseif (str_contains($src, 'inv')) {
                $income->category_id = $investmentCategory?->id;
            } else {
                $income->category_id = $otherCategory?->id;
            }
            $income->save();
        });
    }
}
