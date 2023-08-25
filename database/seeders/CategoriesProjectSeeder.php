<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::truncate();

        $data = [
            'Business',
            'Music',
            'Sales',
        ];

        collect($data)->each(function($category) {
            Category::create([
                'name' => $category
            ]);
        });
    }
}
