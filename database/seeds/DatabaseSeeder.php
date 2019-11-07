<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(ModuleTableSeeder::class);
        
        // $this->call(AccountModuleTableSeeder::class);
        // $this->call(BatchModuleTableSeeder::class);
        // $this->call(CompanyModuleTableSeeder::class);
        // // $this->call(GroupModuleTableSeeder::class);
        // $this->call(InventoryModuleTableSeeder::class);
        // // $this->call(PaymentModuleTableSeeder::class);
        // $this->call(PurchaseModuleTableSeeder::class);
        // $this->call(SaleModuleTableSeeder::class);
        // $this->call(StockTransferModuleTableSeeder::class);
        // $this->call(UserModuleTableSeeder::class);
        // // $this->call(CompanyTypeTableModuleTableSeeder::class);
        // $this->call(PriceListModuleTableSeeder::class);


        $this->call(ModuleTableSeeder::class);
        $this->call(ModuleUserSeeder::class);
        $this->call(ModuleCompanySeeder::class);
        $this->call(ModuleCompanyTypeSeeder::class);
        $this->call(ModuleGroupSeeder::class);
        $this->call(ModuleInventorySeeder::class);
        $this->call(ModulePaymentSeeder::class);
        $this->call(ModulePurchaseSeeder::class);
        $this->call(ModuleRoleSeeder::class);
        $this->call(ModuleSaleSeeder::class);

        $this->call(RoleTableSeeder::class);
        $this->call(RoleAdminSeeder::class);
        $this->call(RoleSuperAdminSeeder::class);


        //Testing Data
        $this->call(CompanyTypeTableSeeder::class);
        $this->call(GroupTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(CategoryTableSeeder::class);
        $this->call(TypeTableSeeder::class);
        $this->call(ProductFeatureTableSeeder::class);
        $this->call(StoreTableSeeder::class);
        $this->call(TicketTableSeeder::class);
        // $this->call(AccountTableSeeder::class);
        $this->call(InventoryTableSeeder::class);
    }
}
