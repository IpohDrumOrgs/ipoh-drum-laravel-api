<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Store;
use App\Company;
use Carbon\Carbon;

class LiveStoreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $store = new Store();
        $store->uid =  Carbon::now()->timestamp . (Store::count() + 1);
        $store->name =  "IpohDrumAcademy";
        $store->contact =  "60167060361";
        $store->email =  "ipohdrum@outlook.com";
        $store->rating =  0;
        $store->address = "";
        $store->state = "Perak";
        $store->desc = "Hi We are Ipoh Drum Academy. Hi 我们是撼重奏噢~";
        $store->postcode = "31900";
        $store->country = "Malaysia";
        $store->status = true;
        $company = Company::find(1);
        $store->company()->associate($company);
        $store->save();

    }
}
