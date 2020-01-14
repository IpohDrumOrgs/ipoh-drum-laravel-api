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
use App\Traits\AllServices;

trait InventoryServices {

    use AllServices;

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

        $data = Inventory::where('uid', $uid)->where('status', true)->with(['inventoryfamilies' => function($q){
            // Query the name field in status table
            $q->where('status', true); 
            $q->with(['patterns' => function($q){
                // Query the name field in status table
                $q->where('status', true);
            }]); 
        }])->with('store','promotion','warranty','shipping','images','reviews.user','characteristics')->first();
        return $data;

    }
    
    private function getInventoryById($id) {

        $data = Inventory::where('id', $id)->where('status', true)->with('store','promotion','warranty','shipping','inventoryfamilies.patterns','images','reviews.user','characteristics')->first();
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
        // $data->qty = $this->toInt($params->qty);
        $data->stockthreshold = $this->toInt($params->stockthreshold);
        // $data->onsale = $params->onsale;

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

    private function deleteInventory($data) {
        $data->status = false;

        $reviews = $data->reviews;   
        foreach($reviews as $review){
            if(!$this->deleteProductReview($review)){
                return null;
            }
        }
        
        $inventoryfamilies = $data->inventoryfamilies;
        foreach($inventoryfamilies as $inventoryfamily){
            if(!$this->deleteInventoryFamily($inventoryfamily)){
                return null;
            }
        }

        //Cancel Inventory Image
        $images = $data->images;
        foreach($images as $image){
            if(!$this->deleteInventoryImage($image)){
                error_log('deleting image');
                return null;
            }
        }

        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }
    }
    
    //Synchronize the data of inventory, inventory family, pattern
    private function syncInventoryById($id) {

        $inventory = $this->getInventoryById($id);
        if($this->isEmpty($inventory)){
            return false;
        }

        $inventorytotalqty = 0;
        $inventoryfamilies = $inventory->inventoryfamilies;
        foreach($inventoryfamilies as $inventoryfamily){

            $inventoryfamilytotalqty = 0;

            $patterns = $inventoryfamily->patterns;
            if(!$this->isEmpty($patterns)){
                foreach($patterns as $pattern){
                    $inventoryfamilytotalqty += $pattern->qty;

                    if($pattern->onsale){
                        $inventoryfamily->onsale = true;
                    }
                }
            }else{
                $inventoryfamilytotalqty = $inventoryfamily->qty;
            }

            $inventoryfamily->qty = $inventoryfamilytotalqty;
            $inventorytotalqty += $inventoryfamily->qty;

            if($inventoryfamily->onsale){
                $inventory->onsale = true;
            }

            if(!$this->saveModel($inventoryfamily)){
                return false;
            }

        }

        error_log("inventorytotalqty");
        error_log($inventorytotalqty);
        $inventory->qty = $inventorytotalqty;
        if(!$this->saveModel($inventory)){
            return false;
        }

        return true;
    }

     
    //Inventory Item Have been sold
    private function soldInventory($inventory) {

        if($inventory->qty <=0 || !$inventory->onsale){
            return null;
        }else{

            //Check Limited Promotion Qty
            if($inventory->promotion){
                if($inventory->promotion->qty > 0 && $inventory->promoendqty >= $inventory->salesqty){
                    $inventory->promotion()->dissociate();
                }
            }
            
            $inventory->qty -= 1;
            $inventory->salesqty += 1;
            
            if(!$this->saveModel($inventory)){
                return null;
            }

        }

        return $inventory->refresh();
    }

    //Relationship Associating
    //===============================================================================================================================================================================
    // public function associateImageWithInventory($data, $params)
    // {
        
    //     $params = $this->checkUndefinedProperty($params , $this->inventoryImageDefaultCols());

    //     $image = new InventoryImage();
    //     $image->uid = Carbon::now()->timestamp . InventoryImage::count();
    //     $image->name = $params->name;
    //     $image->desc = $params->desc;
    //     $image->imgpath = $params->imgurl;
    //     $image->imgpublicid = $params->publicid;
    //     $image->inventory()->associate($data);
    //     if($this->saveModel($image)){
    //         return $image->refresh();
    //     }else{
    //         return null;
    //     }
    // }

    // public function associateInventoryFamilyWithInventory($data, $params)
    // {
        
    //     $inventoryfamily = $this->createInventoryFamily($params);
    //     $inventoryfamily->inventory()->associate($data);
    //     if($this->saveModel($inventoryfamily)){
    //         return $inventoryfamily;
    //     }else{
    //         return null;
    //     }
    // }



    //Modifying Display Data
    // -----------------------------------------------------------------------------------------------------------------------------------------
    public function inventoryDefaultCols() {

        return ['id','uid', 'imgpath', 'rating' ,'onsale', 'onpromo', 'name' , 'desc' , 'price'  , 'qty', 'salesqty' , 'promotion' , 'store' , 'warranty' , 'shipping' , 'reviews','inventoryfamilies'];

    }

    public function inventoryAllCols() {

        return ['id','store_id', 'product_promotion_id', 'shipping_id' ,'warranty_id', 'uid', 'code' , 'sku' , 'name'  , 'imgpublicid', 'imgpath' , 'desc' , 'rating' , 'cost' , 'price' , 'qty','promoendqty','salesqty','stockthreshold','status','onsale'];

    }
    
    public function calculatePromotionPrice($data) {
        if(isset($data->promotion)){
            if(!$this->isEmpty($data->promotion)){
                if($this->withinTimeRange($data->promotion->promostartdate , $data->promotion->promoenddate)){
                    if($data->promotion->discbyprice){
                        $data->promoprice =  $this->toDouble($data->price - $data->promotion->disc);
                    }else{
                        $data->promoprice =  $this->toDouble($data->price - ($data->price * ($data->promotion->discpctg / 100)));
                    }
                    
                    if($data->price != 0){
                        $data->promopctg =  $this->toInt($this->toDouble($data->promoprice / $data->price ) * 100);
                    }else{
                        $data->promopctg = 0;
                    }
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
