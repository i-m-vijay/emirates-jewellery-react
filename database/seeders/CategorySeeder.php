<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Gold',
            'Diamond',
            'All Jwelerry',
            'Collections',
            'Gifting',
        ];

        foreach ($categories as $name) {
            Category::create(['category_name' => $name]);
        }
    }
}
