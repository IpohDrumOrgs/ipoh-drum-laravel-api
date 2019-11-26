<?php

namespace App\Traits;
use Carbon\Carbon;
use JD\Cloudder\Facades\Cloudder;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;

trait GroupServices {

    use GlobalFunctions, LogServices;

    public function uploadImages(Request $request)
    {
        $this->validate($request,[
            'image_name'=>'required|mimes:jpeg,bmp,jpg,png|between:1, 6000',
        ]);

        $image = $request->file('image_name');

        $name = $request->file('image_name')->getClientOriginalName();

        $image_name = $request->file('image_name')->getRealPath();;

        Cloudder::upload($image_name, null);

        list($width, $height) = getimagesize($image_name);

        $image_url= Cloudder::show(Cloudder::getPublicId(), ["width" => $width, "height"=>$height]);

        //save to uploads directory
        $image->move(public_path("uploads/Inventory"), $name);

        //Save images
        $this->saveImages($request, $image_url);

        return redirect()->back()->with('status', 'Image Uploaded Successfully');
    }

    public function saveImages(Request $request, $image_url)
    {
        $image = new InventoryImage();
        $image->name = $request->file('image_name')->getClientOriginalName();
        $image->imgpath = $image_url;
        $image->inventory()->associate(Inventory::find(1));
        $image->save();
    }

}
