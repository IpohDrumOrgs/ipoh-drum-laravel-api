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


class ChannelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $faker = Faker::create();
        $videos = [
            "https://res.cloudinary.com/dmtxkcmay/video/upload/v1575879185/y2mate.com_-_314_1_6QMk-GBOta0_360p_jxkxiq.mp4",
            "https://res.cloudinary.com/dmtxkcmay/video/upload/v1575879167/y2mate.com_-_3142_R9sgsZe5XZ0_240p_rgh5wd.mp4",
            "https://res.cloudinary.com/dmtxkcmay/video/upload/v1575879154/y2mate.com_-_2019_hd_2019_XbYTjVfjDeU_360p_u3tfnx.mp4",
            "https://res.cloudinary.com/dmtxkcmay/video/upload/v1575879152/y2mate.com_-_047__IW-vXpT0K-k_360p_tjgfuu.mp4",
            "https://res.cloudinary.com/dmtxkcmay/video/upload/v1574954391/1_Minute_Timer_kzlp4e.mp4",
        ];
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

        for($x=0 ; $x<10 ; $x++){
            $channel = new Channel();

            $channel->uid = Carbon::now()->timestamp. Channel::count();
            $channel->name = $faker->unique()->jobTitle;
            $channel->desc = $faker->sentence;
            $channel->email = $faker->unique()->safeEmail;
            $channel->tel1 =  $faker->ean8;
            $channel->imgpath = $imgs[$faker->randomElement([0,1,2,3,4,5,6,7,8,9,10,11,12])];
            $channel->companyBelongings = $faker->boolean();

            if($channel->companyBelongings){
                $company = Company::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]));
                $channel->company()->associate($company);
            }else{
                $user = User::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11]));
                $channel->user()->associate($user);
            }

            $channel->save();

        }

        for($y = 0 ; $y < 50 ; $y++){
            $video = new Video();
            $video->uid = Carbon::now()->timestamp . Video::count();
            $video->title = $faker->jobTitle;
            $video->desc = $faker->sentence;
            $video->videopath = $videos[$faker->randomElement([0,1,2,3,4])];
            $video->videopublicid = Carbon::now()->timestamp;
            $video->totallength = "10:00";
            $video->view = $faker->numberBetween($min = 1000, $max = 100000);
            $video->like =  $faker->numberBetween($min = 1000, $max = 100000);
            $video->dislike =  $faker->numberBetween($min = 1000, $max = 100000);
            $video->scope = $faker->randomElement(['public', 'private']);
            $video->free = $faker->boolean();
            $video->agerestrict = false;

            if(!$video->free){
                
                $video->price = $faker->numberBetween($min = 1, $max = 1000);
                $video->discpctg = $faker->boolean($min = 0, $max = 1);
                $video->disc = $faker->numberBetween($min = 1, $max = 1000);
                $video->discbyprice = $faker->boolean();
            }
            $video->channel()->associate(Channel::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11])));
            $video->save();

        }

        for($z = 0 ; $z < 50 ; $z++){
            $comment = new Comment();
            $comment->uid = Carbon::now()->timestamp . Comment::count();
            $comment->text = $faker->sentence;
            $comment->type = 'video';
            $comment->like =  $faker->numberBetween($min = 1000, $max = 100000);
            $comment->dislike =  $faker->numberBetween($min = 1000, $max = 100000);

            $comment->video()->associate(Video::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11])));
            $comment->save();
            
        }
        
        for($a = 0 ; $a < 50 ; $a++){
            $scomment = new SecondComment();
            $scomment->uid = Carbon::now()->timestamp . SecondComment::count();
            $scomment->text = $faker->sentence;
            $scomment->like =  $faker->numberBetween($min = 1000, $max = 100000);
            $scomment->dislike =  $faker->numberBetween($min = 1000, $max = 100000);

            $scomment->comment()->associate(Comment::find($faker->randomElement([1,2,3,4,5,6,7,8,9,10,11])));
            $scomment->save();
        }
    }
}
