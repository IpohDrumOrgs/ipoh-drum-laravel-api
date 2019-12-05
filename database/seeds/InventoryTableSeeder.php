<?php

use Illuminate\Database\Seeder;
use App\Inventory;
use App\Store;
use Faker\Factory as Faker;
use App\Company;
use App\Category;
use App\InventoryImage;
use App\Pattern;
use App\Type;
use App\ProductFeature;
use App\ProductReview;
use App\ProductCharacteristic;
use App\InventoryFamily;
use App\Warranty;
use App\Shipping;
use App\ProductPromotion;
use App\Batch;
use App\User;
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
        $imgs = [
            "https://res.cloudinary.com/dmtxkcmay/image/upload/v1573966125/Inventory/media_i1h1g9.jpg",
            "https://res.cloudinary.com/dmtxkcmay/image/upload/v1573966094/Inventory/media_tk8i6z.jpg",
            "https://res.cloudinary.com/dmtxkcmay/image/upload/v1573966064/Inventory/492_wthrw8.jpg",
            "https://res.cloudinary.com/dmtxkcmay/image/upload/v1573965837/Inventory/20170904004753_tt47au.jpg",
            "https://res.cloudinary.com/dmtxkcmay/image/upload/v1573965818/Inventory/d2729373_d5gdle.jpg",
            "https://res.cloudinary.com/dmtxkcmay/image/upload/v1573965780/Inventory/544a32fb-1559013872-4742b6b7d1b9e1f5a7fb351a52dc2b0d_fzig8a.jpg",
            "https://res.cloudinary.com/dmtxkcmay/image/upload/v1573965764/Inventory/3700811bde38eb4991174e373f6ea99464c5f124_tbli2q.jpg",
            "https://res.cloudinary.com/dmtxkcmay/image/upload/v1573965417/Inventory/white-pomeranian-long-1024x555_mrks2o.jpg",
            "https://res.cloudinary.com/dmtxkcmay/image/upload/v1573966034/Inventory/hqdefault_op3wyk.jpg",
            "https://res.cloudinary.com/dmtxkcmay/image/upload/v1573965858/Inventory/maxresdefault_imfbdp.jpg",
            "https://res.cloudinary.com/dmtxkcmay/image/upload/v1573965837/Inventory/20170904004753_tt47au.jpg",
            "https://res.cloudinary.com/dmtxkcmay/image/upload/v1573965780/Inventory/544a32fb-1559013872-4742b6b7d1b9e1f5a7fb351a52dc2b0d_fzig8a.jpg",
            "https://res.cloudinary.com/dmtxkcmay/image/upload/v1573965745/Inventory/DFEgU7QVwAA8NnG_noulzy.jpg",
        ];
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
            $inventory->imgpath = $imgs[$faker->randomElement([0,1,2,3,4,5,6,7,8,9,10,11,12])];
            $inventory->imgpublicid = Carbon::now()->timestamp . Inventory::count();
            $inventory->cost = $faker->numberBetween($min = 1, $max = 1000);
            $inventory->price = $faker->numberBetween($min = 1, $max = 1000);
            $inventory->rating = $faker->randomElement([0,1,2,3,4,5]);
            $inventory->desc = $faker->sentence;
            $inventory->qty = $faker->numberBetween($min = 1, $max = 1000);
            $inventory->stockthreshold = $faker->numberBetween($min = 1, $max = 1000);
            $inventory->salesqty = 0;

            $store = Store::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]));
            $inventory->store()->associate($store);
            
            $productpromotion = ProductPromotion::find($faker->randomElement([1,2,3,null]));
            $inventory->promotion()->associate($productpromotion);

            $shipping = Shipping::find($faker->randomElement([1,2,null]));
            $inventory->shipping()->associate($shipping);

            $warranty = Warranty::find($faker->randomElement([1,2,null]));
            $inventory->warranty()->associate($warranty);

            if($inventory->promotion != null){
                if($inventory->promotion->qty > 0){
                    $inventory->promoendqty = $inventory->salesqty + $inventory->promotion->qty;
                }
            }

            $inventory->save();

            $inventoryfamily = new InventoryFamily();
            $inventoryfamily->uid = Carbon::now()->timestamp . '-' . (InventoryFamily::count() + 1);
            $inventoryfamily->name = $faker->unique()->jobTitle;
            $inventoryfamily->desc = $faker->sentence;
            $inventoryfamily->cost = $faker->numberBetween($min = 1, $max = 1000);
            $inventoryfamily->price = $faker->numberBetween($min = 1, $max = 1000);
            $inventoryfamily->qty = $inventory->qty;
            $inventoryfamily->imgpath = $imgs[$faker->randomElement([0,1,2,3,4,5,6,7,8,9,10,11,12])];
            $inventoryfamily->imgpublicid = Carbon::now()->timestamp . '-' . (InventoryFamily::count() + 1);
            $inventoryfamily->inventory()->associate($inventory);
            $inventoryfamily->save();

            for($y = 0 ; $y < 4 ; $y++){
                $inventoryfamily = new InventoryFamily();
                $inventoryfamily->uid = Carbon::now()->timestamp . '-' . (InventoryFamily::count() + 1);
                $inventoryfamily->name = $faker->unique()->jobTitle;
                $inventoryfamily->desc = $faker->sentence;
                $inventoryfamily->cost = $faker->numberBetween($min = 1, $max = 1000);
                $inventoryfamily->price = $faker->numberBetween($min = 1, $max = 1000);
                $inventoryfamily->qty = 0;
                $inventoryfamily->imgpath = $imgs[$faker->randomElement([0,1,2,3,4,5,6,7,8,9,10,11,12])];
                $inventoryfamily->imgpublicid = Carbon::now()->timestamp . '-' . (InventoryFamily::count() + 1);
                $inventoryfamily->inventory()->associate($inventory);
                $inventoryfamily->save();

                for($z = 0 ; $z < $faker->randomElement([0,1,2,3,4,5]) ; $z++){
                    $pattern = new Pattern();
                    $pattern->uid = Carbon::now()->timestamp . '-' . (Pattern::count() + 1);
                    $pattern->name = $faker->unique()->jobTitle;
                    $pattern->desc = $faker->sentence;
                    $pattern->cost = $faker->numberBetween($min = 1, $max = 1000);
                    $pattern->price = $faker->numberBetween($min = 1, $max = 1000);
                    $pattern->qty = 0;
                    $pattern->imgpath = $imgs[$faker->randomElement([0,1,2,3,4,5,6,7,8,9,10,11,12])];
                    $pattern->inventoryfamily()->associate($inventoryfamily);
                    $pattern->save();
                }
            }

            $image = new InventoryImage();
            $image->uid = Carbon::now()->timestamp . '-' . (InventoryImage::count() + 1);
            $image->name = $faker->unique()->jobTitle;
            $image->imgpath = $inventory->imgpath;
            $image->imgpublicid = Carbon::now()->timestamp . '-' . (InventoryImage::count() + 1);
            $image->inventory()->associate($inventory);
            $image->save();

            for($y = 0 ; $y < 7 ; $y++){
                $image = new InventoryImage();
                $image->uid = Carbon::now()->timestamp . '-' . (InventoryImage::count() + 1);
                $image->name = $faker->jobTitle;
                $image->imgpath = $imgs[$faker->randomElement([0,1,2,3,4,5,6,7,8,9,10,11,12])];
                $image->imgpublicid = Carbon::now()->timestamp . '-' . (InventoryImage::count() + 1);
                $image->inventory()->associate($inventory);
                $image->save();
            }

            $category = Category::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]));
            $inventory->categories()->attach($category);

            $type = Type::find($faker->randomElement([1,2,3,4,5]));
            $inventory->types()->attach($type);

            $productfeature = ProductFeature::find($faker->randomElement([1,2,3,4]));
            $inventory->productfeatures()->attach($productfeature);
            
            $productcharacteristic = ProductCharacteristic::find($faker->randomElement([1,2,3,null]));
            $inventory->characteristics()->attach($productcharacteristic);

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
        
        for($x=0; $x<200; $x++){
            $productreview = new ProductReview();
            $productreview->uid =  Carbon::now()->timestamp . '-' . (ProductReview::count() + 1);
            $productreview->title =  $faker->jobTitle;
            $productreview->desc = $faker->sentence;
            $productreview->imgpath = $imgs[$faker->randomElement([0,1,2,3,4,5,6,7,8,9,10,11,12])];
            $productreview->imgpublicid = Carbon::now()->timestamp . '-' . (ProductReview::count() + 1);
            $productreview->rating = $faker->randomElement([0,1,2,3,4,5]);
            $productreview->like = $faker->numberBetween($min = 1, $max = 1000);
            $productreview->dislike = $faker->numberBetween($min = 1, $max = 1000);
            $productreview->status = true;
            $inventory = Inventory::find( $faker->numberBetween($min = 1, $max = 50));
            $productreview->inventory()->associate($inventory);
            $productreview->user()->associate(User::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11,12])));
            $productreview->save();
        }
    }
}
