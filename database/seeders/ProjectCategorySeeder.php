<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProjectCategorySeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companyId)
    {
        $categories = ['Laravel', 'Vuejs', 'React', 'Zend', 'CakePhp'];

        foreach ($categories as $item) {
            $category = new \App\Models\ProjectCategory();
            $category->category_name = $item;
            $category->company_id = $companyId;
            $category->save();
        }

    }

}
