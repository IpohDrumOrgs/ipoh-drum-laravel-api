<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\Inventory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use App\Traits\StoreServices;

trait InventoryServices {

    use GlobalFunctions, LogServices, StoreServices;

    private function getInventoryListing($requester) {

        $data = collect();

        //Role Based Retrieve Done in Store Services
        $stores = $this->getStoreListing($requester);
        foreach($stores as $store){
            $data = $data->merge($store->inventories()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }


    private function pluckInventoryIndex($cols) {

        $data = Inventory::where('status',true)->get($cols);
        return $data;

    }


    private function filterInventoryListing($requester , $params) {

        error_log('Filtering inventories....');
        $data = $this->getInventoryListing($requester);

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


    private function pluckInventoryFilter($cols , $params) {

        //Unauthorized users cannot access deleted data
        $data = Inventory::where('status',true)->get();

        if($params->keyword){
            error_log('Filtering inventories with keyword....');
            $keyword = $params->keyword;
            $data = $data->filter(function($item)use($keyword){
                //check string exist inside or not
                if(stristr($item->name, $keyword) == TRUE || stristr($item->regno, $keyword) == TRUE || stristr($item->uid, $keyword) == TRUE ) {
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

        //Pluck Columns
        $data = $data->map(function($item)use($cols){
            return $item->only($cols);
        });

        return $data;

    }


    private function getInventory($requester , $uid) {
        $data = Inventory::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function pluckInventory($cols , $uid) {
        $data = Inventory::where('uid', $uid)->where('status', 1)->get($cols)->first();
        return $data;
    }

    private function createInventory($requester , $params) {

        $data = new Inventory();
        $data->uid = Carbon::now()->timestamp . Inventory::count();
        $data->name = $params->name;
        $data->code = $params->code;
        $data->sku = $params->sku;
        $data->desc = $params->desc;
        $data->cost = $this->toDouble($params->cost);
        $data->price = $this->toDouble($params->price);
        $data->disc = $this->toDouble($params->disc);
        $data->discpctg = $this->toDouble($params->disc / $params->price);
        $data->promoprice = $this->toDouble($params->promoprice);
        $data->promostartdate = $this->toDate($params->promostartdate);
        $data->promoenddate = $this->toDate($params->promoenddate);
        $data->stock = $this->toInt($params->stock);
        $data->salesqty = 0;
        $data->warrantyperiod = $this->toInt($params->warrantyperiod);
        $data->stockthreshold = $this->toInt($params->stockthreshold);
        $data->onsale = $params->onsale;

        if(!$this->isEmpty($data->promostartdate) || !$this->isEmpty($data->promoenddate)){
            $data->onpromo = true;
        }else{
            $data->onpromo = false;
        }

        $store = Store::find($params->storeid);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);
        $data->status = true;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'store', 'inventory');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    //Make Sure Inventory is not empty when calling this function
    private function updateInventory($requester, $data,  $params) {

        $data->name = $params->name;
        $data->code = $params->code;
        $data->sku = $params->sku;
        $data->desc = $params->desc;
        $data->cost = $this->toDouble($params->cost);
        $data->price = $this->toDouble($params->price);
        $data->disc = $this->toDouble($params->disc);
        $data->discpctg = $this->toDouble($params->disc / $params->price);
        $data->promoprice = $this->toDouble($params->promoprice);
        $data->promostartdate = $this->toDate($params->promostartdate);
        $data->promoenddate = $this->toDate($params->promoenddate);
        $data->stock = $this->toInt($params->stock);
        $data->warrantyperiod = $this->toInt($params->warrantyperiod);
        $data->stockthreshold = $this->toInt($params->stockthreshold);
        $data->onsale = $params->onsale;

        if(!$this->isEmpty($data->promostartdate) || !$this->isEmpty($data->promoenddate)){
            $data->onpromo = true;
        }else{
            $data->onpromo = false;
        }

        $store = Store::find($params->storeid);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);
        $data->status = true;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'update', 'inventory');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    private function deleteInventory($requester , $id) {
        $data = Inventory::find($id);
        $data->status = false;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'delete', 'inventory');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    public function inventoryDefaultCols() {

        return ['id','uid' ,'onsale', 'onpromo', 'name' , 'desc' , 'price' , 'disc' , 'discpctg' , 'promoprice' , 'promostartdate' , 'promoenddate' , 'stock', 'salesqty' , 'warrantyperiod'];

    }

}
