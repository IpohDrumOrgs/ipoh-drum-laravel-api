<?php

use Illuminate\Database\Seeder;
use App\Channel;
use App\Video;
use App\Comment;
use App\SecondComment;
use Faker\Factory as Faker;
use App\Company;
use App\User;
use Carbon\Carbon;


class LiveChannelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $channel = new Channel();

        $channel->uid = Carbon::now()->timestamp. Channel::count();
        $channel->name = "Ipoh Drum Academy";
        $channel->desc = "我们是撼重奏哟~";
        $channel->email = "";
        $channel->tel1 =  "";
        $channel->companyBelongings = true;

        $company = Company::find(1);
        $channel->company()->associate($company);

        $channel->save();
    }
}
