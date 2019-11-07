<?php

use Illuminate\Database\Seeder;
use App\Inventory;
use App\Store;
use Faker\Factory as Faker;
use App\Company;
use App\Category;
use App\Type;
use App\ProductFeature;
use App\Batch;
use Carbon\Carbon;


class InventoryTableSeeder extends Seeder
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
            $inventory = new Inventory();
            $checkid = false;
            $uid = '';
            while(!$checkid){
                $uid = '4' . Carbon::now()->timestamp;
                if (!Inventory::where('uid', '=', $uid)->exists()) {
                    // user found
                    $checkid = true;
                }
            }

            $inventory->uid = $uid;
            $inventory->code = $faker->unique()->ean8;
            $inventory->name = $faker->unique()->jobTitle;
            $inventory->sku = $faker->unique()->ean8;
            $inventory->cost = $faker->randomDigit;
            $inventory->price = $faker->randomDigit;
            $inventory->desc = $faker->sentence;
            $inventory->stock = $faker->randomDigit;
            $inventory->stockthreshold = $faker->randomDigit;
            $inventory->salesqty = 0;
            $store = Store::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]));
            $inventory->store()->associate($store);

            $inventory->save();

            $category = Category::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]));
            $inventory->categories()->attach($category);
            
            $type = Type::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]));
            $inventory->types()->attach($type);

            $productfeature = ProductFeature::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]));
            $inventory->productfeatures()->attach($productfeature);

            // $batch = new Batch();
            // $batch->uid = $inventory->uid.'-'.($inventory->batches()->where('status','!=','cancel')->count() + 1);
            // $batch->cost = $inventory->cost;
            // $batch->price = $inventory->price;
            // $batch->stock = $inventory->stock;
            // $batch->salesqty = $inventory->salesqty;
            // $batch->batchno = $inventory->batches()->where('status', true)->count() + 1;
            // $batch->curbatch = true;
            // $batch->inventory()->associate($inventory);
            // $batch->save();
        }
    }
}
