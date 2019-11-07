<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Type;
use Carbon\Carbon;

class TypeTableSeeder extends Seeder
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
            $type = new Type();
            $type->uid =  Carbon::now()->timestamp . '-' . (Type::count() + 1);
            $type->name =  $faker->unique()->jobTitle;
            $type->desc = $faker->sentence;
            $type->status = true;
            $type->save();
        }
        
    }
}
