<?php

use Illuminate\Database\Seeder;
use App\Blogger;
use App\Article;
use App\ArticleImage;
use App\Comment;
use App\SecondComment;
use Faker\Factory as Faker;
use App\Company;
use App\User;
use Carbon\Carbon;


class LiveBloggerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $blogger = new Blogger();

        $blogger->uid = Carbon::now()->timestamp. Blogger::count();
        $blogger->name = "Ipoh Drum Academy";
        $blogger->desc = "撼重奏鼓艺坊";
        $blogger->email = "";
        $blogger->tel1 =  "";
        $blogger->imgpath = "";
        $blogger->companyBelongings = true;

        $company = Company::find(1);
        $blogger->company()->associate($company);
        $blogger->save();

    }
}
