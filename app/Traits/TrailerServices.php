<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\ProductPromotion;
use App\Trailer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\AllServices;

trait TrailerServices {

    use AllServices;

    private function getTrailers($requester) {

        $data = collect();
        //Role Based Retrieve Done in Store
        $channels = $this->getChannels($requester);
        foreach($channels as $channel){
            $data = $data->merge($channel->trailers()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }


    private function filterTrailers($data , $params) {
        $data = $this->globalFilter($data, $params);
        $params = $this->checkUndefinedProperty($params , $this->trailerFilterCols());

        if($params->keyword){
            $keyword = $params->keyword;
            $data = $data->filter(function($item)use($keyword){
                //check string exist inside or not
                if(stristr($item->title, $keyword) == TRUE) {
                    return true;
                }else{
                    return false;
                }

            });
        }
        
        if($params->scope){
            error_log('Filtering trailers with scope....');
            $scope = $params->scope;
            if($scope == 'private'){
                $data = $data->filter(function ($item){
                    return $item->scope == 'private';
                });
            }else{
                $data = $data->filter(function ($item){
                    return $item->scope == 'public';
                });
            }
        }

        $data = $data->unique('id');

        return $data;
    }

    private function getTrailer($uid) {
        $data = Trailer::where('uid', $uid)->with('channel', 'comments.secondcomments')->where('status', 1)->first();
        return $data;
    }

    private function getTrailerById($id) {
        $data = Trailer::where('id', $id)->with('channel', 'comments.secondcomments')->where('status', 1)->first();
        return $data;
    }

    private function createTrailer($params) {

        $params = $this->checkUndefinedProperty($params , $this->trailerAllCols());

        $data = new Trailer();
        $data->uid = Carbon::now()->timestamp . Trailer::count();
        $data->title  = $params->title ;
        $data->desc = $params->desc;
        $data->videopath = $params->videopath;
        $data->videopublicid = $params->videopublicid;
        $data->totallength = $params->totallength;
        $data->imgpath = $params->imgpath;
        $data->imgpublicid = $params->imgpublicid;
        $data->agerestrict = false;
        $data->view = 0;
        
        if($params->scope == 'private'){
            $data->scope = $params->scope;
        }else{
            $data->scope = 'public';
        }
      
        $video = $this->getVideoById($params->video_id);
        if($this->isEmpty($video)){
            return null;
        }
        $data->video()->associate($video);

        if(!$this->saveModel($data)){
            return null;
        }

        return $data->refresh();
    }

    //Make Sure Trailer is not empty when calling this function
    private function updateTrailer($data,  $params) {
        
        $params = $this->checkUndefinedProperty($params , $this->trailerAllCols());

        $data->title  = $params->title ;
        $data->desc = $params->desc;
        $data->trailerpath = $params->trailerpath;
        $data->trailerpublicid = $params->trailerpublicid;
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

    private function deleteTrailer($data) {
        
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


    // Modifying Display Data
    // -----------------------------------------------------------------------------------------------------------------------------------------
    public function trailerAllCols() {

        return ['id','video_id', 'uid', 'title' , 'desc' , 'videopath', 'videopublicid'  , 
        'imgpublicid', 'imgpath' , 'totallength' , 'view' ,'scope','agerestrict','status'];

    }

    public function trailerDefaultCols() {

        return ['id','channel_id', 'playlist_id', 'uid', 
        'title' , 'desc' , 'trailerpath', 'trailerpublicid'  , 'imgpublicid', 'imgpath' , 'totallength' , 'view' , 
        'like' , 'dislike','price','discpctg','disc','discbyprice','free','salesqty','scope',
        'agerestrict','status'];

    }
    public function trailerFilterCols() {

        return ['keyword', 'scope'];

    }


}
