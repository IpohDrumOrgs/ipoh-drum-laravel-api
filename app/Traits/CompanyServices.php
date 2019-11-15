<?php

namespace App\Traits;
use App\User;
use App\Company;
use App\Role;
use App\Inventory;
use App\InventoryBatch;
use App\Module;
use App\PurchaseItem;
use App\SaleItem;
use App\Sale;
use App\Article;
use App\Category;
use App\CompanyType;
use App\Group;
use App\Log;
use App\Payment;
use App\Video;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;

trait CompanyServices {

    use GlobalFunctions, LogServices;

    private function getCompanyListing($requester) {

        $data = collect();
        $companies = $requester->companies;
        foreach($companies as $company){
            $clearance = $this->checkClearance($requester, $company ,  $this->checkModule('company','index'));
            switch ($clearance) {
                //System Wide
                case 1:
                error_log('break');
                    $temp = Company::where('status', true)->get();
                    $data = $data->merge($temp);
                    break 2;
                //Company Wide
                case 2:
                //Group Wide
                case 3:
                //Own Wide
                case 4:
                    $temp = $requester->companies()->where('status',true)->get();
                    $data = $data->merge($temp);
                    break;
                default:
                    break;
            }
    
        }
        
        $data = $data->unique('id')->sortBy('id');

        return $data;
    
    }

    
    private function pluckCompanyIndex($cols) {

        $data = Company::where('status',true)->get($cols);
        return $data;
    
    }


    private function filterCompanyListing($requester , $params) {

        error_log('Filtering companies....');
        $data = $this->getCompanyListing($requester);

        if($params->keyword){
            error_log('Filtering companies with keyword....');
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
            error_log('Filtering companies with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering companies with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });
            
        } 

        if($params->status){
            error_log('Filtering companies with status....');
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

    
    private function pluckCompanyFilter($cols , $params) {

        //Unauthorized users cannot access deleted data
        $data = Company::where('status',true)->get();

        if($params->keyword){
            error_log('Filtering companies with keyword....');
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
            error_log('Filtering companies with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering companies with todate....');
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


    private function getCompany($requester , $uid) {
        $data = Company::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function pluckCompany($cols , $uid) {
        $data = Company::where('uid', $uid)->where('status', 1)->get($cols)->first();
        return $data;
    }

    private function createCompany($requester , $params) {

        $data = new Company();
        $data->uid = Carbon::now()->timestamp . Company::count();
        $data->name = $params->name;
        $data->email1 = $params->email1;
        $data->email2 = $params->email2;
        $data->regno = $params->regno;
        $data->tel1 = $params->tel1;
        $data->tel2 = $params->tel2;
        $data->fax1 = $params->fax1;
        $data->fax2 = $params->fax2;
        $data->address1 = $params->address1;
        $data->address2 = $params->address2;
        $data->postcode = $params->postcode;
        $data->city = $params->city;
        $data->state = $params->state;
        $data->country = $params->country;
        $companytype = CompanyType::find($params->companytypeid);
        if($this->isEmpty($companytype)){
            return null;
        }
        $data->companytype()->associate($companytype);
        $data->status = true;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'store', 'company');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    //Make Sure Company is not empty when calling this function
    private function updateCompany($requester, $data,  $params) {
        
        $data->name = $params->name;
        $data->email1 = $params->email1;
        $data->email2 = $params->email2;
        $data->regno = $params->regno;
        $data->tel1 = $params->tel1;
        $data->tel2 = $params->tel2;
        $data->fax1 = $params->fax1;
        $data->fax2 = $params->fax2;
        $data->address1 = $params->address1;
        $data->address2 = $params->address2;
        $data->postcode = $params->postcode;
        $data->city = $params->city;
        $data->state = $params->state;
        $data->country = $params->country;
        $companytype = CompanyType::find($params->companytypeid);
        if($this->isEmpty($companytype)){
            return null;
        }
        $data->companytype()->associate($companytype);
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'update', 'company');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    private function deleteCompany($requester , $id) {
        $data = Company::find($id);
        $data->status = false;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'delete', 'company');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    
}