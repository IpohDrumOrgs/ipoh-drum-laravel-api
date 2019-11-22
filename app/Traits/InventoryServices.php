<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\Inventory;
use App\ProductPromotion;
use App\Warranty;
use App\Shipping;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use App\Traits\StoreServices;

trait InventoryServices {

    use GlobalFunctions, LogServices, StoreServices;

    private function getInventories($requester) {

        $data = collect();

        //Role Based Retrieve Done in Store Services
        $stores = $this->getStores($requester);
        foreach($stores as $store){
            $data = $data->merge($store->inventories()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }

    private function filterInventories($data , $params) {


        if($params->keyword){
            error_log('Filtering inventories with keyword....');
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
            error_log('Filtering inventories with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering inventories with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering inventories with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }

        if($params->onsale){
            error_log('Filtering inventories with on sale status....');
            if($params->onsale == 'true'){
                $data = $data->where('onsale', true);
            }else if($params->onsale == 'false'){
                $data = $data->where('onsale', false);
            }else{
                $data = $data->where('onsale', '!=', null);
            }
        }


        $data = $data->unique('id');

        return $data;
    }

    private function getInventory($uid) {

        $data = Inventory::where('uid', $uid)->where('status', true)->with('store','promotion','warranty','shipping','inventoryfamilies','images','productreviews','characteristics')->first();
        return $data;

    }
    //Make Sure Inventory is not empty when calling this function
    private function createInventory($params) {

        $data = new Inventory();

        $data->uid = Carbon::now()->timestamp . Inventory::count();
        $data->name = $params->name;
        $data->code = $params->code;
        $data->sku = $params->sku;
        $data->desc = $params->desc;
        $data->imgpath = $params->imgpath;
        $data->cost = $this->toDouble($params->cost);
        $data->price = $this->toDouble($params->price);
        $data->qty = $this->toInt($params->qty);
        $data->stockthreshold = $this->toInt($params->stockthreshold);
        $data->onsale = $params->onsale;

        $store = Store::find($params->storeid);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);
        
        $promotion = ProductPromotion::find($params->promotionid);
        if($this->isEmpty($promotion)){
            return null;
        }else{
            if($promotion->qty > 0){
                $data->promoendqty = $data->salesqty + $promotion->qty;
            }
        }

        $data->promotion()->associate($promotion);
        
        $warranty = Warranty::find($params->warrantyid);
        if($this->isEmpty($warranty)){
            return null;
        }
        $data->warranty()->associate($warranty);

        $shipping = Shipping::find($params->shippingid);
        if($this->isEmpty($shipping)){
            return null;
        }
        $data->shipping()->associate($shipping);

        $data->status = true;
        try {
            $data->save();
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    //Make Sure Inventory is not empty when calling this function
    private function updateInventory($data,  $params) {

        $data->name = $params->name;
        $data->code = $params->code;
        $data->sku = $params->sku;
        $data->desc = $params->desc;
        $data->imgpath = $params->imgpath;
        $data->cost = $this->toDouble($params->cost);
        $data->price = $this->toDouble($params->price);
        $data->qty = $this->toInt($params->qty);
        $data->stockthreshold = $this->toInt($params->stockthreshold);
        $data->onsale = $params->onsale;

        $store = Store::find($params->storeid);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);
        error_log('here');
        $promotion = ProductPromotion::find($params->promotionid);
        if($this->isEmpty($promotion)){
            return null;
        }else{
            if($promotion->qty > 0){
                $data->promoendqty = $data->salesqty + $promotion->qty;
            }
        }

        $data->promotion()->associate($promotion);
        
        $warranty = Warranty::find($params->warrantyid);
        if($this->isEmpty($warranty)){
            return null;
        }
        $data->warranty()->associate($warranty);

        $shipping = Shipping::find($params->shippingid);
        if($this->isEmpty($shipping)){
            return null;
        }
        $data->shipping()->associate($shipping);

        $data->status = true;
        try {
            $data->save();
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    private function deleteInventory($data) {
        $data->status = false;
        try {
            $data->save();
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    public function inventoryDefaultCols() {

        return ['id','uid', 'imgpath', 'rating' ,'onsale', 'onpromo', 'name' , 'desc' , 'price'  , 'qty', 'salesqty' , 'promotion' , 'store' , 'warranty' , 'shipping' , 'productreviews'];

    }
    
    public function calculatePromotionPrice($data) {
        if(!$this->isEmpty($data->promotion)){
            if($data->promotion->discbyprice){
                $data->promoprice = $data->price - $data->promotion->disc;
            }else{
                $data->promoprice = $data->price - ($data->price * $data->promotion->discpctg);
            }
            
            $data->promopctg = $this->toDouble($data->promoprice / $data->price);
        }

        return $data;

    }
    
    public function countProductReviews($data) {
        if(!$this->isEmpty($data->productreviews)){
            $data->totalproductreview = collect($data->productreviews)->count();
        }else{
            $data->totalproductreview = 0;
        }

        return $data;

    }


}
