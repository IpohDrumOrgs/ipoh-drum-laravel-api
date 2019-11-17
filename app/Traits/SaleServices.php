<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use App\Traits\StoreServices;

trait SaleServices {

    use GlobalFunctions, LogServices , StoreServices;

    private function getSaleListing($requester) {

        $data = collect();

        //Role Based Retrieve Done in Store Services
        $stores = $this->getStoreListing($requester);
        foreach($stores as $store){
            $data = $data->merge($store->sales()->where('status',true)->get());
        }


        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }


    private function pluckSaleIndex($cols) {

        $data = Sale::where('status',true)->get($cols);
        return $data;

    }


    private function filterSaleListing($requester , $params) {

        error_log('Filtering sales....');
        $data = $this->getSaleListing($requester);

        if($params->keyword){
            error_log('Filtering sales with keyword....');
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
            error_log('Filtering sales with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering sales with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering sales with status....');
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


    private function pluckSaleFilter($cols , $params) {

        //Unauthorized users cannot access deleted data
        $data = Sale::where('status',true)->get();

        if($params->keyword){
            error_log('Filtering sales with keyword....');
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
            error_log('Filtering sales with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering sales with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        $data = $data->unique('id');

        //Pluck Columns
        $data = $data->map(function($item)use($cols){
            return $item->only($cols);
        });

        return $data;

    }


    private function getSale($requester , $uid) {
        $data = Sale::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function pluckSale($cols , $uid) {
        $data = Sale::where('uid', $uid)->where('status', 1)->get($cols)->first();
        return $data;
    }

    private function createSale($requester , $params) {

        $data = new Sale();
        $data->uid = Carbon::now()->timestamp . Sale::count();
        $data->sono = $params->sono;
        $data->totalqty = $this->toInt($params->totalqty);
        $data->totalcost = $this->toDouble($params->totalcost);
        $data->linetotal = $this->toDouble($params->linetotal);
        $data->totaldisc = $this->toDouble($params->totaldisc);
        $data->discpctg = $this->toDouble($data->totaldisc / $data->linetotal);
        // $data->charge = $this->toDouble($params->price);
        $data->grandtotal = $this->toDouble($data->linetotal - $data->totaldisc);
        $data->payment = $this->toDouble($params->payment);
        $data->outstanding = $this->toDouble($params->outstanding);
        $data->docdate = $this->toDate($params->docdate);
        $data->remark = $params->remark;

        if(!$this->isEmpty($params->userid)){
            $data->pos = false;
            $user = User::find($params->userid);
            if($this->isEmpty($user)){
                return null;
            }
            $data->user()->associate($user);
        }else{
            $data->pos = true;
        }

        $store = Store::find($params->storeid);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);


        $data->status = true;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'store', 'sale');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    //Make Sure Sale is not empty when calling this function
    private function updateSale($requester, $data,  $params) {

        $data->sono = $params->sono;
        $data->totalqty = $this->toInt($params->totalqty);
        $data->totalcost = $this->toDouble($params->totalcost);
        $data->linetotal = $this->toDouble($params->linetotal);
        $data->totaldisc = $this->toDouble($params->totaldisc);
        $data->discpctg = $this->toDouble($data->totaldisc / $data->linetotal);
        // $data->charge = $this->toDouble($params->price);
        $data->grandtotal = $this->toDouble($data->linetotal - $data->totaldisc);
        $data->payment = $this->toDouble($params->payment);
        $data->outstanding = $this->toDouble($params->outstanding);
        $data->docdate = $this->toDate($params->docdate);
        $data->remark = $params->remark;

        if(!$this->isEmpty($params->userid)){
            $data->pos = false;
            $user = User::find($params->userid);
            if($this->isEmpty($user)){
                return null;
            }
            $data->user()->associate($user);
        }else{
            $data->pos = true;
        }

        $store = Store::find($params->storeid);
        if($this->isEmpty($store)){
            return null;
        }
        $data->store()->associate($store);

        $data->status = true;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'update', 'sale');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    private function deleteSale($requester , $id) {
        $data = Sale::find($id);
        $data->status = false;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'delete', 'sale');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }


}
