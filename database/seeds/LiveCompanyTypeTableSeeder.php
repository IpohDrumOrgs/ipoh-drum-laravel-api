<?php

use Illuminate\Database\Seeder;
use App\CompanyType;
use Carbon\Carbon;

class LiveCompanyTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companytype = new CompanyType();
        $companytype->uid = Carbon::now()->timestamp . (CompanyType::count() + 1);;
        $companytype->name = 'management';
        $companytype->save();
        
    }
}
