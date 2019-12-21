<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\Warranty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\AllServices;

trait WarrantyServices {

    use AllServices;

    private function getWarranties($requester) {

        $data = collect();

        //Role Based Retrieve Done in Store Services
        $stores = $this->getStores($requester);
        foreach($stores as $store){
            $data = $data->merge($store->warranties()->where('status',true)->get());
        }

        $data = $data->merge(Warranty::where('store_id',null)->get());

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }

    private function filterWarranties($data , $params) {


        if($params->keyword){
            error_log('Filtering warranties with keyword....');
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
            error_log('Filtering warranties with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering warranties with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering warranties with status....');
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

    private function getWarranty($uid) {

        $data = Warranty::where('uid', $uid)->where('status', true)->first();
        return $data;

    }

    private function getWarrantyById($id) {

        $data = Warranty::where('id', $id)->where('status', true)->first();
        return $data;

    }

    //Make Sure Warranty is not empty when calling this function
    private function createWarranty($params) {

        $params = $this->checkUndefinedProperty($params , $this->warrantyAllCols());
        $data = new Warranty();
        
        $data->uid = Carbon::now()->timestamp . Warranty::count();
        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->period = $this->toInt($params->period);
        $data->policy =  $params->policy;

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

    //Make Sure Warranty is not empty when calling this function
    private function updateWarranty($data,  $params) {

        $params = $this->checkUndefinedProperty($params , $this->warrantyAllCols());

        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->period = $this->toInt($params->period);
        $data->policy =  $params->policy;

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

    private function deleteWarranty($data) {
        $data->status = false;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }
    }

    //Modifying Display Data
    // -----------------------------------------------------------------------------------------------------------------------------------------
    public function warrantyAllCols() {

        return ['id','uid', 'store_id', 'name' ,'desc', 'period', 'policy' , 'status'];

    }
    
    


}
