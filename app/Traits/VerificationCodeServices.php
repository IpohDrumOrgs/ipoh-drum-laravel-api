<?php

namespace App\Traits;
use App\User;
use App\VerificationCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use App\Traits\TicketServices;

trait VerificationCodeServices {

    use GlobalFunctions, LogServices ,TicketServices;

    private function getVerificationCodeListing($requester) {

        $data = collect();
        //Role Based Retrieve Done in TicketService
        $tickets = $this->getTicketListing($requester);
        foreach($tickets as $ticket){
            $data = $data->merge($ticket->verificationcodes()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id');

        return $data;
    
    }

    
    private function pluckVerificationCodeIndex($cols) {

        $data = VerificationCode::where('status',true)->get($cols);
        return $data;
    
    }


    private function filterVerificationCodeListing($requester , $params) {

        error_log('Filtering verificationcodes....');
        $data = $this->getVerificationCodeListing($requester);

        if($params->keyword){
            error_log('Filtering verificationcodes with keyword....');
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
            error_log('Filtering verificationcodes with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering verificationcodes with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });
            
        } 

        if($params->status){
            error_log('Filtering verificationcodes with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }
        
        if($params->onsale){
            error_log('Filtering verificationcodes with on sale status....');
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

    
    private function pluckVerificationCodeFilter($cols , $params) {

        //Unauthorized users cannot access deleted data
        $data = VerificationCode::where('status',true)->get();

        if($params->keyword){
            error_log('Filtering verificationcodes with keyword....');
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
            error_log('Filtering verificationcodes with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering verificationcodes with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });
            
        } 
        
        if($params->onsale){
            error_log('Filtering verificationcodes with on sale status....');
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


    private function getVerificationCode($requester , $uid) {
        $data = VerificationCode::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function pluckVerificationCode($cols , $uid) {
        $data = VerificationCode::where('uid', $uid)->where('status', 1)->get($cols)->first();
        return $data;
    }

    private function createVerificationCode($requester , $params) {

        $data = new VerificationCode();
        $data->uid = Carbon::now()->timestamp . VerificationCode::count();
        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->status = true;

        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'store', 'verificationcode');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    //Make Sure VerificationCode is not empty when calling this function
    private function updateVerificationCode($requester, $data,  $params) {
        
        $data->name = $params->name;
        $data->desc = $params->desc;

        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'update', 'verificationcode');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    private function deleteVerificationCode($requester , $id) {
        $data = VerificationCode::find($id);
        $data->status = false;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'delete', 'verificationcode');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    
}