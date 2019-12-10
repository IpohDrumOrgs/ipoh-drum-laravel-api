<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\Pattern;
use App\InventoryFamily;
use App\ProductPromotion;
use App\Warranty;
use App\Shipping;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use App\Traits\InventoryServices;
use App\Traits\ImageHostingServices;

trait PatternServices {

    use GlobalFunctions, LogServices, InventoryServices, ImageHostingServices;

    private function getPatterns($paramser) {

        $data = collect();
        //Role Based Retrieve Done in Store Services
        $inventoryfamilies = $this->getInventoryFamilies($paramser);
        foreach($inventoryfamilies as $inventoryfamily){
            $data = $data->merge($inventoryfamily->patterns()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }

    private function filterPatterns($data , $params) {


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

    private function getPattern($uid) {

        $data = Pattern::where('uid', $uid)->where('status', true)->with('inventory')->first();
        return $data;

    }
    //Make Sure Pattern is not empty when calling this function
    private function createPattern($params) {

        $data = new Pattern();

        $data->uid = Carbon::now()->timestamp . Pattern::count();
        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->cost = $this->toDouble($params->cost);
        $data->price = $this->toDouble($params->price);
        $data->qty = $this->toInt($params->qty);
        $data->onsale = $params->onsale;

        $inventoryfamily = InventoryFamily::find($params->inventory_family_id);
        if($this->isEmpty($inventoryfamily)){
            return null;
        }
        $data->inventoryfamily()->associate($inventoryfamily);
        
        return $data->refresh();
    }

    //Make Sure Pattern is not empty when calling this function
    private function updatePattern($data,  $params) {

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

    private function deletePattern($data) {
        $data->status = false;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }
    }


}
