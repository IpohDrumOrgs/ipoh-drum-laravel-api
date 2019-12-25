<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\InventoryImage;
use App\InventoryImageFamily;
use App\InventoryImageImage;
use App\ProductPromotion;
use App\Warranty;
use App\Shipping;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\AllServices;

trait InventoryImageServices {

    use AllServices;

    private function getInventoryImages($requester) {

        $data = collect();

        //Role Based Retrieve Done in Store Services
        $inventories = $this->getInventoryImages($requester);
        foreach($inventories as $inventory){
            $data = $data->merge($inventory->images()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }

    private function filterInventoryImages($data , $params) {


        if($params->keyword){
            error_log('Filtering inventoryimages with keyword....');
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
            error_log('Filtering inventoryimages with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering inventoryimages with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering inventoryimages with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }

        if($params->onsale){
            error_log('Filtering inventoryimages with on sale status....');
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

    private function getInventoryImage($uid) {

        $data = InventoryImage::where('uid', $uid)->where('status', true)->first();
        return $data;

    }
    
    private function getInventoryImageById($id) {

        $data = InventoryImage::where('id', $id)->where('status', true)->first();
        return $data;

    }
    
    //Make Sure InventoryImage is not empty when calling this function
    private function createInventoryImage($params) {

        $params = $this->checkUndefinedProperty($params , $this->inventoryImageDefaultCols());

        $data = new InventoryImage();
        $data->uid = Carbon::now()->timestamp . InventoryImage::count();
        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->imgpath = $params->imgurl;
        $data->imgpublicid = $params->publicid;

        $inventory = $this->getInventoryById($data->inventory_id);
        if($this->isEmpty($warranty)){
            return null;
        }
        $data->inventory()->associate($inventory);

        if(!$this->saveModel($data)){
            return null;
        }
        
        return $data->refresh();
    }

    //Make Sure InventoryImage is not empty when calling this function
    private function updateInventoryImage($data,  $params) {

        $params = $this->checkUndefinedProperty($params , $this->inventoryimageAllCols());

        $data->name = $params->name;
        $data->code = $params->code;
        $data->sku = $params->sku;
        $data->desc = $params->desc;
        $data->cost = $this->toDouble($params->cost);
        $data->price = $this->toDouble($params->price);
        $data->qty = $this->toInt($params->qty);
        $data->stockthreshold = $this->toInt($params->stockthreshold);
        $data->onsale = $params->onsale;

       
        $store = $this->getStoreById($params->store_id);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);
       
        if($params->product_promotion_id){
            $promotion = $this->getProductPromotionById($params->product_promotion_id);
            if($this->isEmpty($promotion)){
                return null;
            }else{
                if($promotion->qty > 0){
                    $data->promoendqty = $data->salesqty + $promotion->qty;
                }
            }
            $data->promotion()->associate($promotion);
        }

        
        if($params->warranty_id){
            $warranty = $this->getWarrantyById($params->warranty_id);
            if($this->isEmpty($warranty)){
                return null;
            }
            $data->warranty()->associate($warranty);
        }

        if($params->shipping_id){
            $shipping = $this->getShippingById($params->shipping_id);
            if($this->isEmpty($shipping)){
                return null;
            }
            $data->shipping()->associate($shipping);
        }

        $data->status = true;

        if(!$this->saveModel($data)){
            return null;
        }
        
      
        return $data->refresh();

    }

    private function deleteInventoryImage($data) {
        $data->status = false;

        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }
    }



    //Relationship Associating
    //===============================================================================================================================================================================
    public function associateImageWithInventoryImage($data, $params)
    {
        
        $params = $this->checkUndefinedProperty($params , $this->inventoryimageImageDefaultCols());

        $image = new InventoryImageImage();
        $image->uid = Carbon::now()->timestamp . InventoryImageImage::count();
        $image->name = $params->name;
        $image->desc = $params->desc;
        $image->imgpath = $params->imgurl;
        $image->imgpublicid = $params->publicid;
        $image->inventoryimage()->associate($data);
        if($this->saveModel($image)){
            return $image->refresh();
        }else{
            return null;
        }
    }

    public function associateInventoryImageFamilyWithInventoryImage($data, $params)
    {
        
        $inventoryimagefamily = $this->createInventoryImageFamily($params);
        $inventoryimagefamily->inventoryimage()->associate($data);
        if($this->saveModel($inventoryimagefamily)){
            return $inventoryimagefamily;
        }else{
            return null;
        }
    }

    
    //Relationship Deassociating
    //===============================================================================================================================================================================
    public function deleteInventoryImageImage($publicid)
    {
        $image =  InventoryImageImage::where('imgpublicid' , $publicid)->first();
        $this->deleteImage($publicid);
        if(!$this->isEmpty($image)){
            $image->delete();
            return true;
        }else{
            return false;
        }
    }
    

    //Modifying Display Data
    // -----------------------------------------------------------------------------------------------------------------------------------------
    public function inventoryimageDefaultCols() {

        return ['id','uid', 'imgpath', 'rating' ,'onsale', 'onpromo', 'name' , 'desc' , 'price'  , 'qty', 'salesqty' , 'promotion' , 'store' , 'warranty' , 'shipping' , 'reviews','inventoryimagefamilies'];

    }
    
    public function inventoryimageImageDefaultCols() {

        return ['id','uid', 'inventoryimage_id', 'name' ,'desc', 'imgpublicid', 'imgpath' , 'status'];

    }

    public function inventoryimageAllCols() {

        return ['id','store_id', 'product_promotion_id', 'shipping_id' ,'warranty_id', 'uid', 'code' , 'sku' , 'name'  , 'imgpublicid', 'imgpath' , 'desc' , 'rating' , 'cost' , 'price' , 'qty','promoendqty','salesqty','stockthreshold','status','onsale'];

    }
    
    public function calculatePromotionPrice($data) {
        if(isset($data->promotion)){
            if(!$this->isEmpty($data->promotion)){
                if($data->promotion->discbyprice){
                    $data->promoprice =  $this->toDouble($data->price - $data->promotion->disc);
                }else{
                    $data->promoprice =  $this->toDouble($data->price - ($data->price * $data->promotion->discpctg));
                }
                
                if($data->price != 0){
                    $data->promopctg = $this->toDouble($data->promoprice / $data->price ) * 100;
                }else{
                    $data->promopctg = 0;
                }
            }
    
        }

        return $data;
    }
    
    public function countProductReviews($data) {
        if(isset($data->reviews)){
            if(!$this->isEmpty($data->reviews)){
                $data->totalproductreview = collect($data->reviews)->count();
            }else{
                $data->totalproductreview = 0;
            }

        }

        return $data;
    }

    


}
