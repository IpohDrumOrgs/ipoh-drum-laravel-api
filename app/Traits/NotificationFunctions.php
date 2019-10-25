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
 
}