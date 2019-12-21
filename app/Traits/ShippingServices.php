<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\Shipping;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\AllServices;

trait ShippingServices {

    use AllServices;

    private function getShippings($requester) {

        $data = collect();

        //Role Based Retrieve Done in Store Services
        $stores = $this->getStores($requester);
        foreach($stores as $store){
            $data = $data->merge($store->shippings()->where('status',true)->get());
        }

        $data = $data->merge(Shipping::where('store_id',null)->get());

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }

    private function filterShippings($data , $params) {


        if($params->keyword){
            error_log('Filtering shippings with keyword....');
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
            error_log('Filtering shippings with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering shippings with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering shippings with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }



        $data = $data->unique('id');

        return $data;
    }

    private function getShipping($uid) {

        $data = Shipping::where('uid', $uid)->where('status', true)->first();
        return $data;

    }
    
    private function getShippingById($id) {

        $data = Shipping::where('id', $id)->where('status', true)->first();
        return $data;

    }

    //Make Sure Shipping is not empty when calling this function
    private function createShipping($params) {

        $params = $this->checkUndefinedProperty($params , $this->shippingAllCols());
        $data = new Shipping();
        
        $data->uid = Carbon::now()->timestamp . Shipping::count();
        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->price = $this->toDouble($params->price);
        $data->maxweight = $this->toDouble($params->maxweight);
        $data->maxdimension = $this->toDouble($params->maxdimension);

        if($params->store_id){
            $store = $this->getStoreById($params->store_id);
            if($this->isEmpty($store)){
                return null;
            }
            $data->store()->associate($store);

        }
        
        $data->status = true;

        if(!$this->saveModel($data)){
            return null;
        }
        
      
        return $data->refresh();
    }

    //Make Sure Shipping is not empty when calling this function
    private function updateShipping($data,  $params) {

        $params = $this->checkUndefinedProperty($params , $this->shippingAllCols());

        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->price = $this->toDouble($params->price);
        $data->maxweight = $this->toDouble($params->maxweight);
        $data->maxdimension = $this->toDouble($params->maxdimension);

        if($params->store_id){
            $store = $this->getStoreById($params->store_id);
            if($this->isEmpty($store)){
                return null;
            }
            $data->store()->associate($store);
        }
        
        $data->status = true;

        if(!$this->saveModel($data)){
            return null;
        }
        
      
        return $data->refresh();

    }

    private function deleteShipping($data) {
        $data->status = false;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }
    }

    //Modifying Display Data
    // -----------------------------------------------------------------------------------------------------------------------------------------
    public function shippingAllCols() {

        return ['id','uid', 'store_id', 'name' ,'desc', 'price', 'maxweight' , 'maxdimension' , 'status'];

    }
    
    
    


}
