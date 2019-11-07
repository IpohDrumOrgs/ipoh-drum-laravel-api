<?php

use Illuminate\Database\Seeder;
use App\Ticket;
use App\Store;
use Faker\Factory as Faker;
use App\Company;
use App\Category;
use App\Type;
use App\ProductFeature;
use App\Batch;
use Carbon\Carbon;


class TicketTableSeeder extends Seeder
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
            $ticket = new Ticket();
            $checkid = false;
            $uid = '';
            while(!$checkid){
                $uid = '4' . Carbon::now()->timestamp;
                if (!Ticket::where('uid', '=', $uid)->exists()) {
                    // user found
                    $checkid = true;
                }
            }

            $ticket->uid = $uid;
            $ticket->code = $faker->unique()->ean8;
            $ticket->name = $faker->unique()->jobTitle;
            $ticket->sku = $faker->unique()->ean8;
            $ticket->price = $faker->randomDigit;
            $ticket->desc = $faker->sentence;
            $ticket->stock = $faker->randomDigit;
            $ticket->stockthreshold = $faker->randomDigit;
            $ticket->salesqty = 0;
            $ticket->enddate = Carbon::now()->addDays(2);
            $store = Store::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]));
            $ticket->store()->associate($store);

            $ticket->save();

            $category = Category::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]));
            $ticket->categories()->attach($category);
            
            $type = Type::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]));
            $ticket->types()->attach($type);

            $productfeature = ProductFeature::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]));
            $ticket->productfeatures()->attach($productfeature);

        }
    }
}
