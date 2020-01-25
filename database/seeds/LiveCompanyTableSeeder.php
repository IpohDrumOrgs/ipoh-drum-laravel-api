<?php

use Illuminate\Database\Seeder;
use App\Company;
use App\Group;

class LiveCompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = new Company();
        $company->uid = Carbon::now()->timestamp . (Company::count() + 1);;
        $company->name = 'Ipoh Drum Academy';
        $company->regno = '';
        $company->tel1 = '';
        $company->tel2 = '';
        $company->fax1 = '';
        $company->fax2 = '';
        $company->email1 = '';
        $company->email2 = '';
        $company->address1 = '';
        $company->address2 = '';
        $company->postcode = '';
        $company->city = '';
        $company->state = '';
        $company->country = '';
        $company->save();
    }
}
