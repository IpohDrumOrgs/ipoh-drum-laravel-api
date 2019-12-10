<?php

namespace App\Http\Controllers;
use JD\Cloudder\Facades\Cloudder;
use App\InventoryImage;
use App\Inventory;
use App\Traits\VideoHostingServices;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use VideoHostingServices;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $images = InventoryImage::all();
        return view('home', compact('images'));
    }

    public function home()
    {
        // add this
    }


    public function uploadImages(Request $request)
    {
        $this->validate($request,[
            'image_name'=>'required|mimes:jpeg,bmp,jpg,png|between:1, 6000',
        ]);

        $image = $request->file('image_name');

        $name = $request->file('image_name')->getClientOriginalName();

        $image_name = $request->file('image_name')->getRealPath();;

        Cloudder::upload($image_name, null , ['folder' => "/Inventory"]);

        list($width, $height) = getimagesize($image_name);

        $image_url= Cloudder::show(Cloudder::getPublicId(), ["width" => $width, "height"=>$height]);
        //save to uploads directory
        $image->move(public_path("uploads"), $name);

        //Save images
        $this->saveImages($request, $image_url);

        return redirect()->back()->with('status', 'Image Uploaded Successfully');
    }

    public function saveImages(Request $request, $image_url)
    {
        $image = new InventoryImage();
        $image->uid = InventoryImage::count()+1;
        $image->name = $request->file('image_name')->getClientOriginalName();
        $image->imgpath = $image_url;
        $image->inventory()->associate(Inventory::find(1));
        $image->save();
    }

    
    public function uploadVideo(Request $request)
    {
        
        
        $video = $request->file('image_name');

        $this->uploadVideos($video , "/Video");
        return redirect()->back()->with('status', 'Image Uploaded Successfully');
    }
}
