<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\ProductPromotion;
use App\Video;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\AllServices;

trait VideoServices {

    use AllServices;

    private function getVideos($requester) {

        $data = collect();
        //Role Based Retrieve Done in Store
        $channels = $this->getChannels($requester);
        foreach($channels as $channel){
            $data = $data->merge($channel->videos()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }


    private function filterVideos($data , $params) {

        $params = $this->checkUndefinedProperty($params , $this->videoFilterCols());
        error_log('Filtering videos....');

        if($params->keyword){
            error_log('Filtering videos with keyword....');
            $keyword = $params->keyword;
            $data = $data->filter(function($item)use($keyword){
                //check string exist inside or not
                if(stristr($item->name, $keyword) == TRUE || stristr($item->uid, $keyword) == TRUE ) {
                    return true;
                }else{
                    return false;
                }

            });
        }


        if($params->fromdate){
            error_log('Filtering videos with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering videos with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering videos with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }

        if($params->scope){
            error_log('Filtering videos with scope....');
            $data = $data->where('scope', $params->scope);
        }


        $data = $data->unique('id');

        return $data;
    }

    private function getVideo($uid) {
        $data = Video::where('uid', $uid)->with('channel', 'comments.secondcomments')->where('status', 1)->first();
        return $data;
    }

    private function getVideoById($id) {
        $data = Video::where('id', $id)->with('channel', 'comments.secondcomments')->where('status', 1)->first();
        return $data;
    }

    private function createVideo($params) {

        $params = $this->checkUndefinedProperty($params , $this->videoAllCols());

        $data = new Video();
        $data->uid = Carbon::now()->timestamp . Video::count();
        $data->title  = $params->title ;
        $data->desc = $params->desc;
        $data->videopath = $params->videopath;
        $data->videopublicid = $params->videopublicid;
        $data->totallength = $params->totallength;
        $data->agerestrict = false;
        $data->like = 0;
        $data->dislike = 0;
        $data->view = 0;
        
        if($params->scope == 'private'){
            $data->scope = $params->scope;
        }else{
            $data->scope = 'public';
        }
        
        if($this->isEmpty( $params->free)){
            return null;
        }else{
            $data->free = $params->free;
            if($data->free){
                $data->price = 0;
                $data->disc = 0;
                $data->discpctg = 0;
            }else{
                $data->price = $this->toDouble($params->price);
                if($this->isEmpty( $params->discbyprice)){
                    return null;
                }else{
                    if($data->discbyprice){
                        $data->disc = $this->toDouble($params->disc);
                        $data->discpctg = $this->toInt($this->toDouble($data->disc / $data->price) * 100 );
                    }else{
                        $data->discpctg = $this->toInt($params->discpctg);
                        $data->disc = $this->toDouble($data->price * ($data->discpctg / 100));
                    }
                }
            }
        }

        $channel = $this->getChannelById($params->channel_id);
        if($this->isEmpty($channel)){
            error_log('here');
            return null;
        }
        $data->channel()->associate($channel);

        return $data->refresh();
    }

    //Make Sure Video is not empty when calling this function
    private function updateVideo($data,  $params) {
        
        $params = $this->checkUndefinedProperty($params , $this->videoAllCols());

        $data->title  = $params->title ;
        $data->desc = $params->desc;
        $data->videopath = $params->videopath;
        $data->videopublicid = $params->videopublicid;
        $data->totallength = $params->totallength;
        $data->agerestrict = false;
        
        if($params->scope == 'private'){
            $data->scope = $params->scope;
        }else{
            $data->scope = 'public';
        }
        
        if($this->isEmpty($params->free)){
            return null;
        }else{
            $data->free = $params->free;
            if($data->free){
                $data->price = 0;
                $data->disc = 0;
                $data->discpctg = 0;
            }else{
                $data->price = $this->toDouble($params->price);
                if($this->isEmpty( $params->discbyprice)){
                    return null;
                }else{
                    if($data->discbyprice){
                        $data->disc = $this->toDouble($params->disc);
                        $data->discpctg = $this->toInt($this->toDouble($data->disc / $data->price) * 100 );
                    }else{
                        $data->discpctg = $this->toInt($params->discpctg);
                        $data->disc = $this->toDouble($data->price * ($data->discpctg / 100));
                    }
                }
            }
        }

        $channel = $this->getChannelById($params->channel_id);
        if($this->isEmpty($channel)){
            error_log('here');
            return null;
        }
        $data->channel()->associate($channel);

        return $data->refresh();
    }

    private function deleteVideo($data) {
        
        $comments = $data->comments;
        foreach($comments as $comment){
            if(!$this->deleteComment($comment)){
                return null;
            }
        }
        
        $data->status = false;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }

        return $data->refresh();
    }

    private function getAllVideos() {
        
        $data = Video::where('status', true)->get();

        return $data;
    }

    private function likeVideo($video) {
        
        $video->like += 1;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }
        

        return true;
    }

    // Modifying Display Data
    // -----------------------------------------------------------------------------------------------------------------------------------------
    public function videoAllCols() {

        return ['id','channel_id', 'playlist_id', 'uid', 
        'title' , 'desc' , 'videopath', 'videopublicid'  , 'imgpublicid', 'imgpath' , 'totallength' , 'view' , 
        'like' , 'dislike','price','discpctg','disc','discbyprice','free','salesqty','scope',
        'agerestrict','status'];

    }

    public function videoDefaultCols() {

        return ['id','channel_id', 'playlist_id', 'uid', 
        'title' , 'desc' , 'videopath', 'videopublicid'  , 'imgpublicid', 'imgpath' , 'totallength' , 'view' , 
        'like' , 'dislike','price','discpctg','disc','discbyprice','free','salesqty','scope',
        'agerestrict','status'];

    }
    public function videoFilterCols() {

        return ['keyword','fromdate' ,'todate', 'status', 'scope'];

    }

}
