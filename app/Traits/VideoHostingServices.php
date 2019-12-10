<?php

namespace App\Traits;
use Carbon\Carbon;
use JD\Cloudder\Facades\Cloudder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;

trait VideoHostingServices {

    use GlobalFunctions, LogServices;

    public function uploadVideos($video , $folder)
    {
        if($video && $folder){
            try{
    
            $name = $video->getClientOriginalName();
    
            $realpath = $video->getRealPath();
            error_log($realpath);
            $uploadresponse = Cloudder::uploadVideo($realpath, null , array( "folder" => $folder , "chunk_size" => 100000000));
            error_log(collect($uploadresponse));
            $imgurl= Cloudder::show(Cloudder::getPublicId());
            //save to uploads directory
            $data['imgurl'] = $imgurl;
            $data['publicid'] = Cloudder::getPublicId();
            $data['name'] = $name;
            return (object) $data;

            }catch(Exception $e){
                $this->createErrorLog('VideoHostingServices' , 'deleteVideos', 'error when uploading video ' , $e->getMessage());
                return null;
            }
        }

    }

    public function deleteVideos($ids)
    {
            foreach($ids as $id){
                $this->deleteVideo($id);
            }
    } 

    
    public function deleteVideo($id)
    {
        
        if($id){
            try{
                Cloudder::destroy($id);
                return true;
            }catch(Exxception $e){
                $this->createErrorLog('VideoHostingServices' , 'deleteVideo', 'error when deleting video '. $id , $e->getMessage());
                return false;
            }
        }
    } 


}
