<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\Inventory;
use App\InventoryFamily;
use App\InventoryImage;
use App\ProductPromotion;
use App\Warranty;
use App\Shipping;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use App\Traits\StoreServices;
use App\Traits\ImageHostingServices;

trait InventoryServices {

    use GlobalFunctions, LogServices, StoreServices, ImageHostingServices;

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

        $data = Inventory::where('uid', $uid)->where('status', true)->with('store','promotion','warranty','shipping','inventoryfamilies.patterns','images','productreviews','characteristics')->first();
        return $data;

    }
    //Make Sure Inventory is not empty when calling this function
    private function createInventory($params) {

        $params = $this->checkUndefinedProperty($params , $this->inventoryAllCols());
        $data = new Inventory();

        $data->uid = Carbon::now()->timestamp . Inventory::count();
        $data->name = $params->name;
        $data->code = $params->code;
        $data->sku = $params->sku;
        $data->desc = $params->desc;
        $data->cost = $this->toDouble($params->cost);
        $data->price = $this->toDouble($params->price);
        $data->qty = $this->toInt($params->qty);
        $data->stockthreshold = $this->toInt($params->stockthreshold);
        $data->onsale = $params->onsale;

        $store = Store::find($params->store_id);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);
        
        $promotion = ProductPromotion::find($params->product_promotion_id);
        if($this->isEmpty($promotion)){
            return null;
        }else{
            if($promotion->qty > 0){
                $data->promoendqty = $data->salesqty + $promotion->qty;
            }
        }

        $data->promotion()->associate($promotion);
        
        $warranty = Warranty::find($params->warranty_id);
        if($this->isEmpty($warranty)){
            return null;
        }
        $data->warranty()->associate($warranty);

        $shipping = Shipping::find($params->shipping_id);
        if($this->isEmpty($shipping)){
            return null;
        }
        $data->shipping()->associate($shipping);

        $data->status = true;

        if(!$this->saveModel($data)){
            return null;
        }
        
      
        return $data->refresh();
    }

    //Make Sure Inventory is not empty when calling this function
    private function updateInventory($data,  $params) {

        $params = $this->checkUndefinedProperty($params , $this->inventoryAllCols());

        $data->name = $params->name;
        $data->code = $params->code;
        $data->sku = $params->sku;
        $data->desc = $params->desc;
        $data->cost = $this->toDouble($params->cost);
        $data->price = $this->toDouble($params->price);
        $data->qty = $this->toInt($params->qty);
        $data->stockthreshold = $this->toInt($params->stockthreshold);
        $data->onsale = $params->onsale;

        $store = Store::find($params->store_id);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);
        
        $promotion = ProductPromotion::find($params->product_promotion_id);
        if($this->isEmpty($promotion)){
            return null;
        }else{
            if($promotion->qty > 0){
                $data->promoendqty = $data->salesqty + $promotion->qty;
            }
        }

        $data->promotion()->associate($promotion);
        
        $warranty = Warranty::find($params->warranty_id);
        if($this->isEmpty($warranty)){
            return null;
        }
        $data->warranty()->associate($warranty);

        $shipping = Shipping::find($params->shipping_id);
        if($this->isEmpty($shipping)){
            return null;
        }
        $data->shipping()->associate($shipping);

        $data->status = true;

        if(!$this->saveModel($data)){
            return null;
        }
        
      
        return $data->refresh();

    }

    private function deleteInventory($data) {
        $data->status = false;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }
    }

    //Relationship Associating
    //===============================================================================================================================================================================
    public function associateImageWithInventory($data, $params)
    {
        $image = new InventoryImage();
        $image->uid = Carbon::now()->timestamp . InventoryImage::count();
        $image->name = $params->name;
        $image->imgpath = $params->imgurl;
        $image->imgpublicid = $params->publicid;
        $image->inventory()->associate($data);
        if($this->saveModel($image)){
            return true;
        }else{
            return false;
        }
    }

    public function associateInventoryFamilyWithInventory($data, $params)
    {
        
        $inventoryfamily = $this->createInventoryFamily($params);
        $inventoryfamily->inventory()->associate($data);
        if($this->saveModel($inventoryfamily)){
            return $inventoryfamily;
        }else{
            return null;
        }
    }

    
    //Relationship Deassociating
    //===============================================================================================================================================================================
    public function deleteInventoryImage($publicid)
    {
        $image =  InventoryImage::where('imgpublicid' , $publicid)->first();
        if(!$this->isEmpty($image)){
            $image->delete();
        }
    }
    

    //Modifying Display Data
    // -----------------------------------------------------------------------------------------------------------------------------------------
    public function inventoryDefaultCols() {

        return ['id','uid', 'imgpath', 'rating' ,'onsale', 'onpromo', 'name' , 'desc' , 'price'  , 'qty', 'salesqty' , 'promotion' , 'store' , 'warranty' , 'shipping' , 'productreviews','inventoryfamilies'];

    }
    

    public function inventoryAllCols() {

        return ['id','store_id', 'product_promotion_id', 'shipping_id' ,'warranty_id', 'uid', 'code' , 'sku' , 'name'  , 'imgpublicid', 'imgpath' , 'desc' , 'rating' , 'cost' , 'price' , 'qty','promoendqty','salesqty','stockthreshold','status','onsale'];

    }
    
    public function calculatePromotionPrice($data) {
        if(!$this->isEmpty($data->promotion)){
            if($data->promotion->discbyprice){
                $data->promoprice = $data->price - $data->promotion->disc;
            }else{
                $data->promoprice = $data->price - ($data->price * $data->promotion->discpctg);
            }
            
            if($data->price != 0){
                $data->promopctg = $this->toDouble($data->promoprice / $data->price ) * 100;
            }else{
                $data->promopctg = 0;
            }
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
