<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\ProductPromotion;
use App\ArticleImage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\AllServices;

trait ArticleImageServices {

    use AllServices;

    private function getArticleImages($requester) {

        $data = collect();
        //Role Based Retrieve Done in Store
        $articles = $this->getArticles($requester);
        foreach($articles as $article){
            $data = $data->merge($article->articleimages()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }


    private function filterArticleImages($data , $params) {

        $params = $this->checkUndefinedProperty($params , $this->articleimageFilterCols());
        error_log('Filtering articleimages....');

        if($params->keyword){
            error_log('Filtering articleimages with keyword....');
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
            error_log('Filtering articleimages with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering articleimages with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering articleimages with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }

        if($params->scope){
            error_log('Filtering articleimages with scope....');
            $data = $data->where('scope', $params->scope);
        }


        $data = $data->unique('id');

        return $data;
    }

    private function getArticleImage($uid) {
        $data = ArticleImage::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function getArticleImageById($id) {
        $data = ArticleImage::where('id', $id)->where('status', 1)->first();
        return $data;
    }

    private function createArticleImage($params) {

        $params = $this->checkUndefinedProperty($params , $this->articleimageAllCols());

        $article = $this->getArticleById($params->article_id);
        error_log( $params->article_id);
        error_log( $article);
        if($this->isEmpty($article)){
            error_log('hi');
            return null;
        }

        if($article->articleimages()->count() >= 6){
            error_log($article->articleimages()->count());
            error_log('hi1');
            return null;
        }

        $data = new ArticleImage();
        $data->uid = Carbon::now()->timestamp . ArticleImage::count();
        $data->title = $params->title;
        $data->desc = $params->desc;
        $data->like = 0;
        $data->dislike = 0;
        $data->coverimage = false;
        $data->imgpath = $params->imgpath;
        $data->imgpublicid = $params->imgpublicid;

        $data->article()->associate($article);

        if(!$this->saveModel($data)){
            return null;
        }
        
        return $data->refresh();
    }

    //Make Sure ArticleImage is not empty when calling this function
    private function updateArticleImage($data,  $params) {
   
      
    }

    private function deleteArticleImage($data) {
        $data->status = false;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }

        return $data->refresh();
    }

    private function getAllArticleImages() {
        
        $data = ArticleImage::where('status', true)->with('articleimageimages','blogger')->get();

        return $data;
    }

    // Modifying Display Data
    // -----------------------------------------------------------------------------------------------------------------------------------------
    public function articleimageAllCols() {

        return ['id','article_id', 'uid', 
        'title' , 'desc' , 'like'  , 'dislike', 'imgpath' , 'imgpublicpath' , 'coverimage'];
    }

    public function articleimageDefaultCols() {

        return ['id','uid' ,'onsale', 'onpromo', 'name' , 'desc' , 'price' , 'disc' , 
        'discpctg' , 'promoprice' , 'promostartdate' , 'promoenddate', 'enddate' , 
        'stock', 'salesqty' ];

    }
    public function articleimageFilterCols() {

        return ['keyword','fromdate' ,'todate', 'status', 'scope'];

    }

}
