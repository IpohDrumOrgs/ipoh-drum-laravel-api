<?php

namespace App\Traits;
use App\User;
use App\Store;
use App\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\AllServices;

trait SaleItemServices {

    use AllServices;

    private function getSaleItems($requester) {

        $data = collect();

        //Role Based Retrieve Done in Store Services
        $sales = $this->getSales($requester);
        foreach($sales as $sale){
            $data = $data->merge($sale->saleitems()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }




    private function filterSaleItems($data , $params) {

        error_log('Filtering saleitems....');

        if($params->keyword){
            error_log('Filtering saleitems with keyword....');
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
            error_log('Filtering saleitems with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering saleitems with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering saleitems with status....');
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

    private function getSaleItem($uid) {
        $data = SaleItem::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function getSaleItemById($id) {
        $data = SaleItem::where('id', $id)->where('status', 1)->first();
        return $data;
    }

    private function createSaleItem($params) {

        $params = $this->checkUndefinedProperty($params , $this->saleItemAllCols());

        $data = new SaleItem();
        $data->uid = Carbon::now()->timestamp . SaleItem::count();
        $data->name = $params->name;
        $data->qty = $this->toDouble($params->totalcost);
        $data->desc = $params->desc;
        $data->cost = $this->toDouble($params->cost);
        $data->price = $this->toDouble($params->price);
        $data->totaldisc = $this->toDouble($params->totaldisc);
        $data->linetotal = $this->toDouble($linetotal);
        $data->totalcost = $this->toDouble($params->totalcost);
        $data->payment = $this->toDouble($params->payment);
        $data->outstanding = $this->toDouble($params->outstanding);
        $data->type = $params->type;
        $data->type = $this->toDate($params->docdate);

        if($data->type == 'ticket'){
            $ticket = $this->getTicketById($params->ticket_id);
            if($this->isEmpty($ticket)){
                return null;
            }
            $data->ticket()->associate($ticket);
        }else if($data->type == 'inventory'){
            $inventory = $this->getInventoryById($params->inventory_id);
            if($this->isEmpty($inventory)){
                return null;
            }
            $data->inventory()->associate($inventory);
        }else{
            return null;
        }


        $data->status = true;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }

        return $data->refresh();
    }

    //Make Sure SaleItem is not empty when calling this function
    private function updateSaleItem($data,  $params) {

        $params = $this->checkUndefinedProperty($params , $this->saleItemAllCols());

        $data->name = $params->name;
        $data->qty = $this->toDouble($params->totalcost);
        $data->desc = $params->desc;
        $data->cost = $this->toDouble($params->cost);
        $data->price = $this->toDouble($params->price);
        $data->totaldisc = $this->toDouble($params->totaldisc);
        $data->linetotal = $this->toDouble($linetotal);
        $data->totalcost = $this->toDouble($params->totalcost);
        $data->payment = $this->toDouble($params->payment);
        $data->outstanding = $this->toDouble($params->outstanding);
        $data->type = $params->type;
        $data->type = $this->toDate($params->docdate);

        if($data->type == 'ticket'){
            $ticket = $this->getTicketById($params->ticket_id);
            if($this->isEmpty($ticket)){
                return null;
            }
            $data->ticket()->associate($ticket);
        }else if($data->type == 'inventory'){
            $inventory = $this->getInventoryById($params->inventory_id);
            if($this->isEmpty($inventory)){
                return null;
            }
            $data->inventory()->associate($inventory);
        }else{
            return null;
        }

        $data->status = true;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }
        return $data->refresh();
    }

    private function deleteSaleItem($data) {
        $data->status = false;
        if($this->saveModel($data)){
            return $data->refresh();
        }else{
            return null;
        }

        return $data->refresh();
    }

    //Modifying Display Data
    // -----------------------------------------------------------------------------------------------------------------------------------------
    public function saleItemAllCols() {

        return ['id','uid', 'sale_id' ,'inventory_id', 'inventory_family_id' , 
        'pattern_id' , 'ticket_id' , 'name' , 'qty' , 'desc' , 'cost' , 'price' , 'totaldisc' , 
        'linetotal' , 'totalcost', 'payment', 'outstanding', 'type', 'docdate', 'status' ];

    }

}
