<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use App\Traits\StoreServices;

trait TicketServices {

    use GlobalFunctions, LogServices, StoreServices;

    private function getTicketListing($requester) {

        $data = collect();
        //Role Based Retrieve Done in Store
        $stores = $this->getStoreListing($requester);
        foreach($stores as $store){
            $data = $data->merge($store->tickets()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }


    private function pluckTicketIndex($cols) {

        $data = Ticket::where('status',true)->get($cols);
        return $data;

    }


    private function filterTicketListing($requester , $params) {

        error_log('Filtering tickets....');
        $data = $this->getTicketListing($requester);

        if($params->keyword){
            error_log('Filtering tickets with keyword....');
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
            error_log('Filtering tickets with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering tickets with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering tickets with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }

        if($params->onsale){
            error_log('Filtering tickets with on sale status....');
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


    private function pluckTicketFilter($cols , $params) {

        //Unauthorized users cannot access deleted data
        $data = Ticket::where('status',true)->get();

        if($params->keyword){
            error_log('Filtering tickets with keyword....');
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
            error_log('Filtering tickets with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering tickets with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->onsale){
            error_log('Filtering tickets with on sale status....');
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


    private function getTicket($requester , $uid) {
        $data = Ticket::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function pluckTicket($cols , $uid) {
        $data = Ticket::where('uid', $uid)->where('status', 1)->get($cols)->first();
        return $data;
    }

    private function createTicket($requester , $params) {

        $data = new Ticket();
        $data->uid = Carbon::now()->timestamp . Ticket::count();
        $data->name = $params->name;
        $data->code = $params->code;
        $data->sku = $params->sku;
        $data->desc = $params->desc;
        $data->price = $this->toDouble($params->price);
        $data->disc = $this->toDouble($params->disc);
        $data->discpctg = $this->toDouble($params->disc / $params->price);
        $data->promoprice = $this->toDouble($params->promoprice);
        $data->promostartdate = $this->toDate($params->promostartdate);
        $data->promoenddate = $this->toDate($params->promoenddate);
        $data->enddate = $this->toDate($params->enddate);
        $data->stock = $this->toInt($params->stock);
        $data->salesqty = 0;
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
            $this->createLog($requester->id , [$data->id], 'store', 'ticket');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    //Make Sure Ticket is not empty when calling this function
    private function updateTicket($requester, $data,  $params) {

        $data->name = $params->name;
        $data->code = $params->code;
        $data->sku = $params->sku;
        $data->desc = $params->desc;
        $data->price = $this->toDouble($params->price);
        $data->disc = $this->toDouble($params->disc);
        $data->discpctg = $this->toDouble($params->disc / $params->price);
        $data->promoprice = $this->toDouble($params->promoprice);
        $data->promostartdate = $this->toDate($params->promostartdate);
        $data->promoenddate = $this->toDate($params->promoenddate);
        $data->enddate = $this->toDate($params->enddate);
        $data->stock = $this->toInt($params->stock);
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
            $this->createLog($requester->id , [$data->id], 'update', 'ticket');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    private function deleteTicket($requester , $id) {
        $data = Ticket::find($id);
        $data->status = false;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'delete', 'ticket');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }


    public function ticketDefaultCols() {

        return ['id','uid' ,'onsale', 'onpromo', 'name' , 'desc' , 'price' , 'disc' , 'discpctg' , 'promoprice' , 'promostartdate' , 'promoenddate', 'enddate' , 'stock', 'salesqty' , 'warrantyperiod'];

    }

}
