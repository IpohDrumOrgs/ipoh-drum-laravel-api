<?php

namespace App\Traits;
use App\User;
use App\Role;
use App\Inventory;
use App\InventoryBatch;
use App\Module;
use App\Batch;
use App\PurchaseItem;
use App\SaleItem;
use App\Sale;
use Carbon\Carbon;
use DB;

trait NotificationFunctions {

    public function getRetrievedSuccessMsg($provider){
        return $provider. ' retrieved successfully.';
    }
 
    public function getNotFoundMsg($provider){
        return $provider. ' Not Found. Please Try Again Later';
    }
    
    public function getCreatedSuccessMsg($provider){
        return $provider. ' created successfully.';
    }

    public function getUpdatedSuccessMsg($provider){
        return $provider. ' updated successfully.';
    }
    
    public function getDeletedSuccessMsg($provider){
        return $provider. ' deleted successfully.';
    }

    public function getErrorMsg(){
        return 'Something went wrong. Please Try Again Later.';
    }
    
    
    public function errorResponse(){
        $data['data'] = null;
        $data['status'] = 'error';
        $data['msg'] = $this->getErrorMsg();
        $data['code'] = 404;
        return response()->json($data, 404);
    }
    
    public function notFoundResponse($provider){
        $data['data'] = null;
        $data['status'] = 'error';
        $data['msg'] = $this->getNotFoundMsg($provider);
        $data['code'] = 404;
        return response()->json($data, 404);
    }
    
    public function successResponse($provider , $data , $type){

        switch($type){
            case 'create' : 
                $data['status'] = 'success';
                $data['msg'] = $this->getUpdatedSuccessMsg($provider);
                $data['data'] = $data;
                $data['code'] = 200;
                break;

            case 'update' : 
                $data['status'] = 'success';
                $data['msg'] = $this->getCreatedSuccessMsg($provider);
                $data['data'] = $data;
                $data['code'] = 200;
                break;

            case 'retrieve' : 
                $data['status'] = 'success';
                $data['msg'] = $this->getRetrievedSuccessMsg($provider);
                $data['data'] = $data;
                $data['code'] = 200;
                break;

            case 'delete' : 
                $data['status'] = 'success';
                $data['msg'] = $this->getDeletedSuccessMsg($provider);
                $data['data'] = $data;
                $data['code'] = 200;
                break;

            default :
                $data['status'] = 'success';
                $data['msg'] = 'Operation success';
                $data['data'] = $data;
                $data['code'] = 200;
                break;

        }

        return response()->json($data, 200);
    }
}