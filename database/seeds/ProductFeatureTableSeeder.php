<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\ProductFeature;
use Carbon\Carbon;

class ProductFeatureTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $faker = Faker::create();

        for($x=0 ; $x<50 ; $x++){
            $productfeature = new ProductFeature();
            $productfeature->uid =  Carbon::now()->timestamp . '-' . (ProductFeature::count() + 1);
            $productfeature->name =  $faker->unique()->jobTitle;
            $productfeature->desc = $faker->sentence;
            $productfeature->status = true;
            $productfeature->save();
        }
        
    }
}
