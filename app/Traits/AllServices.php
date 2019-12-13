<?php

namespace App\Traits;

//Functionality Services
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\LogServices;
use App\Traits\AssignDatabaseRelationship;
use App\Traits\ImageHostingServices;
use App\Traits\VideoHostingServices;

//Model Services
use App\Traits\CategoryServices;
use App\Traits\CompanyServices;
use App\Traits\CompanyTypeServices;
use App\Traits\GroupServices;
use App\Traits\InventoryFamilyServices;
use App\Traits\InventoryServices;
use App\Traits\ModuleServices;
use App\Traits\PatternServices;
use App\Traits\ProductFeatureServices;
use App\Traits\ProductPromotionServices;
use App\Traits\ProductReviewServices;
use App\Traits\RoleServices;
use App\Traits\SaleItemServices;
use App\Traits\SaleServices;
use App\Traits\ShippingServices;
use App\Traits\StoreReviewServices;
use App\Traits\StoreServices;
use App\Traits\TicketServices;
use App\Traits\TypeServices;
use App\Traits\UserServices;
use App\Traits\VerificationCodeServices;
use App\Traits\VoucherServices;
use App\Traits\WarrantyServices;

trait AllServices {

    use 
    GlobalFunctions, 
    LogServices, 
    AssignDatabaseRelationship, 
    VideoHostingServices , 
    ImageHostingServices, 
    NotificationFunctions, 

    CategoryServices, 
    CompanyServices, 
    CompanyTypeServices, 
    GroupServices, 
    InventoryFamilyServices, 
    InventoryServices, 
    ModuleServices, 
    PatternServices, 
    ProductFeatureServices, 
    ProductPromotionServices, 
    ProductReviewServices, 
    RoleServices, 
    SaleItemServices, 
    SaleServices, 
    ShippingServices, 
    StoreReviewServices, 
    StoreServices, 
    TicketServices, 
    TypeServices, 
    UserServices, 
    VerificationCodeServices, 
    VoucherServices, 
    WarrantyServices;

}