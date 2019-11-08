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
    
    public function getCreateSuccessMsg($provider){
        return $provider. ' created successfully.';
    }

    public function getUpdateSuccessMsg($provider){
        return $provider. ' updated successfully.';
    }
    
    public function getDeleteSuccessMsg($provider){
        return $provider. ' deleted successfully.';
    }

    public function getErrorMsg($provider){
        return 'Something went wrong. Please Try Again Later.';
    }
}