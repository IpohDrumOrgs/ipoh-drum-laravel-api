<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Slider;
use Carbon\Carbon;

class SliderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $slider = new Slider();
        $slider->uid =  Carbon::now()->timestamp . '-' . (Slider::count() + 1);
        $slider->name =  'Slider 1';
        $slider->page =  'shop';
        $slider->imgpath =  '';
        $slider->imgpublicid =  $slider->uid;
        $slider->link =  '#';
        $slider->hidden = true;
        $slider->save();


        $slider = new Slider();
        $slider->uid =  Carbon::now()->timestamp . '-' . (Slider::count() + 1);
        $slider->name =  'Slider 2';
        $slider->page =  'shop';
        $slider->imgpath =  '';
        $slider->imgpublicid =  $slider->uid;
        $slider->link =  '#';
        $slider->hidden = true;
        $slider->save();
        

        $slider = new Slider();
        $slider->uid =  Carbon::now()->timestamp . '-' . (Slider::count() + 1);
        $slider->name =  'Slider 3';
        $slider->page =  'shop';
        $slider->imgpath =  '';
        $slider->imgpublicid =  $slider->uid;
        $slider->link =  '#';
        $slider->hidden = true;
        $slider->save();
        

        $slider = new Slider();
        $slider->uid =  Carbon::now()->timestamp . '-' . (Slider::count() + 1);
        $slider->name =  'Slider 4';
        $slider->page =  'shop';
        $slider->imgpath =  '';
        $slider->imgpublicid =  $slider->uid;
        $slider->link =  '#';
        $slider->hidden = true;
        $slider->save();

        $slider = new Slider();
        $slider->uid =  Carbon::now()->timestamp . '-' . (Slider::count() + 1);
        $slider->name =  'Slider 5';
        $slider->page =  'shop';
        $slider->imgpath =  '';
        $slider->imgpublicid =  $slider->uid;
        $slider->link =  '#';
        $slider->hidden = true;
        $slider->save();
    }
}
