<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Category;
use Carbon\Carbon;

class LiveCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category = new Category();
        $category->uid =  Carbon::now()->timestamp .  (Category::count() + 1);
        $category->name =  '24 Festive Drums';
        $category->desc = "24 Festive Drums";
        $category->status = true;
        $category->save();
        
    }
}
