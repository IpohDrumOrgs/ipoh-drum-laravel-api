<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\ProductPromotion;
use App\Article;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\AllServices;

trait ArticleServices {

    use AllServices;

    private function getArticles($requester) {

        $data = collect();
        //Role Based Retrieve Done in Store
        $bloggers = $this->getBloggers($requester);
        foreach($bloggers as $blogger){
            $data = $data->merge($blogger->articles()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }


    private function filterArticles($data , $params) {

        $params = $this->checkUndefinedProperty($params , $this->articleFilterCols());
        error_log('Filtering articles....');

        if($params->keyword){
            error_log('Filtering articles with keyword....');
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
            error_log('Filtering articles with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering articles with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering articles with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }

        if($params->scope){
            error_log('Filtering articles with scope....');
            $data = $data->where('scope', $params->scope);
        }


        $data = $data->unique('id');

        return $data;
    }

    private function getArticle($uid) {
        $data = Article::where('uid', $uid)->with('blogger', 'comments.secondcomments', 'articleimages')->where('status', 1)->first();
        return $data;
    }

    private function getArticleById($id) {
        $data = Article::where('id', $id)->with('blogger', 'comments.secondcomments', 'articleimages')->where('status', 1)->first();
        return $data;
    }

    private function createArticle($params) {

        $params = $this->checkUndefinedProperty($params , $this->articleAllCols());

        $data = new Article();
        $data->uid = Carbon::now()->timestamp . Article::count();
        $data->name = $params->name;
        $data->code = $params->code;
        $data->sku = $params->sku;
        $data->desc = $params->desc;
        $data->price = $this->toDouble($params->price);
        $data->enddate = $this->toDate($params->enddate);
        $data->qty = $this->toInt($params->qty);
        $data->salesqty = 0;
        $data->stockthreshold = $this->toInt($params->stockthreshold);
        $data->onsale = $params->onsale;

        $store = $this->getStoreById($params->store_id);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);
           
        $promotion = $this->getProductPromotionById($params->product_promotion_id);
        if($this->isEmpty($promotion)){
            return null;
        }else{
            if($promotion->qty > 0){
                $data->promoendqty = $data->salesqty + $promotion->qty;
            }
        }

        $data->promotion()->associate($promotion);

        $data->status = true;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }

        return $data->refresh();
    }

    //Make Sure Article is not empty when calling this function
    private function updateArticle($data,  $params) {
        
        $params = $this->checkUndefinedProperty($params , $this->articleAllCols());

        $data->name = $params->name;
        $data->code = $params->code;
        $data->sku = $params->sku;
        $data->desc = $params->desc;
        $data->price = $this->toDouble($params->price);
        $data->enddate = $this->toDate($params->enddate);
        $data->qty = $this->toInt($params->qty);
        $data->salesqty = 0;
        $data->stockthreshold = $this->toInt($params->stockthreshold);
        $data->onsale = $params->onsale;

        $store = $this->getStoreById($params->store_id);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);
           
        $promotion = $this->getProductPromotionById($params->product_promotion_id);
        if($this->isEmpty($promotion)){
            return null;
        }else{
            if($promotion->qty > 0){
                $data->promoendqty = $data->salesqty + $promotion->qty;
            }
        }

        $data->promotion()->associate($promotion);

        $data->status = true;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }
        return $data->refresh();
    }

    private function deleteArticle($data) {
        $data->status = false;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }

        return $data->refresh();
    }

    private function getAllArticles() {
        
        $data = Article::where('status', true)->with('articleimages','blogger')->get();

        return $data;
    }

    
    private function setCommentCount($data) {
        
        $data->commentcount = $data->comments()->count();

        return $data;
    }

    // Modifying Display Data
    // -----------------------------------------------------------------------------------------------------------------------------------------
    public function articleAllCols() {

        return ['id','store_id', 'product_promotion_id', 'uid', 
        'code' , 'sku' , 'name'  , 'imgpublicid', 'imgpath' , 'desc' , 'rating' , 
        'price' , 'qty','promoendqty','salesqty','stockthreshold','status','onsale'];

    }

    public function articleDefaultCols() {

        return ['id','uid' ,'onsale', 'onpromo', 'name' , 'desc' , 'price' , 'disc' , 
        'discpctg' , 'promoprice' , 'promostartdate' , 'promoenddate', 'enddate' , 
        'stock', 'salesqty' ];

    }
    public function articleFilterCols() {

        return ['keyword','fromdate' ,'todate', 'status', 'scope'];

    }

}
