<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\ProductPromotion;
use App\Slider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\AllServices;

trait SliderServices {

    use AllServices;

    private function getSliders($requester) {

        $data = collect();
        //Role Based Retrieve Done in Store
        $bloggers = $this->getBloggers($requester);
        foreach($bloggers as $blogger){
            $data = $data->merge($blogger->sliders()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }


    private function filterSliders($data , $params) {

        $data = $this->globalFilter($data, $params);
        $params = $this->checkUndefinedProperty($params , $this->sliderFilterCols());

        if($params->keyword){
            $keyword = $params->keyword;
            $data = $data->filter(function($item)use($keyword){
                //check string exist inside or not
                if(stristr($item->title, $keyword) == TRUE ) {
                    return true;
                }else{
                    return false;
                }

            });
        }

        if($params->scope){
            error_log('Filtering sliders with scope....');
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

    private function getSlider($uid) {
        $data = Slider::where('uid', $uid)->with('blogger', 'sliderimages')->where('status', 1)->first();
        return $data;
    }

    private function getSliderById($id) {
        $data = Slider::where('id', $id)->with('blogger', 'sliderimages')->where('status', 1)->first();
        return $data;
    }

    private function createSlider($params) {

        $params = $this->checkUndefinedProperty($params , $this->sliderAllCols());

        $data = new Slider();
        $data->uid = Carbon::now()->timestamp . Slider::count();
        $data->title = $params->title;
        $data->desc = $params->desc;
        $data->view = 0;
        $data->like = 0;
        $data->dislike = 0;
        if($params->scope == 'private'){
            $data->scope = $params->scope;
        }else{
            $data->scope = 'public';
        }
        $data->agerestrict = false;

        $blogger = $this->getBloggerById($params->blogger_id);
        if($this->isEmpty($blogger)){
            return null;
        }
        $data->blogger()->associate($blogger);

        $data->status = true;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }

        return $data->refresh();
    }

    //Make Sure Slider is not empty when calling this function
    private function updateSlider($data,  $params) {
        
        $params = $this->checkUndefinedProperty($params , $this->sliderAllCols());
        $data->title = $params->title;
        $data->desc = $params->desc;
        if($params->scope == 'private'){
            $data->scope = $params->scope;
        }else{
            $data->scope = 'public';
        }
        $data->agerestrict = false;

        $blogger = $this->getBloggerById($params->blogger_id);
        if($this->isEmpty($blogger)){
            return null;
        }
        $data->blogger()->associate($blogger);

        $data->status = true;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }

        return $data->refresh();
    }

    private function deleteSlider($data) {
        $sliderimages = $data->sliderimages;
        foreach($sliderimages as $sliderimage){
            if(!$this->deleteSliderImage($sliderimage)){
                return null;
            }
        }
        
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

    private function getAllPublicSliders() {
        
        $data = Slider::where('status', true)->where('hidden', false)->get();

        return $data;
    }
    

    private function parseToReadableSliderStructure($slider) {
        
        $slider->image = $slider->imgpath;
        $slider->thumbImage = $slider->imgpath;
        $slider->alt = $slider->desc;
        $slider->title = $slider->name;

        return $slider;
    }
    // Modifying Display Data
    // -----------------------------------------------------------------------------------------------------------------------------------------
    public function sliderAllCols() {

        return ['id','blogger_id', 'title', 'desc', 
        'view' , 'like' , 'dislike'  , 'scope', 'agerestrict' , 'status'];

    }

    public function sliderDefaultCols() {

        return ['id','uid' ,'onsale', 'onpromo', 'name' , 'desc' , 'price' , 'disc' , 
        'discpctg' , 'promoprice' , 'promostartdate' , 'promoenddate', 'enddate' , 
        'stock', 'salesqty' ];

    }
    public function sliderFilterCols() {

        return ['keyword' , 'scope'];

    }

}
