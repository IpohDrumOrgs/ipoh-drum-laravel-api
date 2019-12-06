<?php

namespace App\Traits;
use App\InventoryFamily;
use App\Inventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use App\Traits\PatternServices;
use App\Traits\ImageHostingServices;

trait InventoryFamilyServices {

    use GlobalFunctions, LogServices, PatternServices, ImageHostingServices;

    private function getInventoryFamilies($paramser) {

        $data = collect();
        //Role Based Retrieve Done in Store Services
        $inventories = $this->getInventories($paramser);
        foreach($inventories as $inventory){
            $data = $data->merge($inventory->inventoryfamilies()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }

    private function filterInventoryFamilies($data , $params) {


        if($params->keyword){
            error_log('Filtering inventoryfamilies with keyword....');
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
            error_log('Filtering inventoryfamilies with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering inventoryfamilies with todate....');
            $date = Carbon::parse($params->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering inventoryfamilies with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }

        if($params->onsale){
            error_log('Filtering inventoryfamilies with on sale status....');
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

    private function getInventoryFamily($uid) {

        $data = InventoryFamily::where('uid', $uid)->where('status', true)->with('inventory')->first();
        return $data;

    }
    
    private function getInventoryFamilyById($id) {

        $data = InventoryFamily::where('id', $id)->where('status', true)->with('inventory')->first();
        return $data;

    }
    //Make Sure InventoryFamily is not empty when calling this function
    private function createInventoryFamily($params) {

        $data = new InventoryFamily();
        error_log(collect($params));
        $data->uid = Carbon::now()->timestamp . InventoryFamily::count();
        $data->name = $params->name;
        $data->code = $params->code;
        $data->sku = $params->sku;
        $data->desc = $params->desc;
        $data->cost = $this->toDouble($params->cost);
        $data->price = $this->toDouble($params->price);
        $data->qty = $this->toInt($params->qty);
        $data->onsale = $params->onsale;

        $inventory = Inventory::find($params->inventoryid);
        if($this->isEmpty($inventory)){
            return null;
        }
        $data->inventory()->associate($inventory);
        
        return $data->refresh();
    }

    //Make Sure InventoryFamily is not empty when calling this function
    private function updateInventoryFamily($data,  $params) {

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
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }

    }

    private function deleteInventoryFamily($data) {
        $patterns = $data->patterns;
        foreach($patterns as $pattern){
            if(!$this->deletePattern($pattern)){
                return null;
            }
        }
        $data->status = false;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }
    }

    
    //Relationship Associating
    //===============================================================================================================================================================================
    public function associatePatternWithInventoryFamily($data, $params)
    {
        $pattern = $this->createPattern($params);
        $pattern->inventoryfamily()->associate($data);
        if($this->saveModel($pattern)){
            return $pattern;
        }else{
            return null;
        }
    }


}
