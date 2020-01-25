<?php

use Illuminate\Database\Seeder;
use App\Company;
use App\CompanyType;
use App\Group;
use Illuminate\Support\Facades\Hash;
use App\User;
use Carbon\Carbon;

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
        $company->regno = null;
        $company->tel1 = null;
        $company->tel2 = null;
        $company->fax1 = null;
        $company->fax2 = null;
        $company->email1 = null;
        $company->email2 = null;
        $company->address1 = null;
        $company->address2 = null;
        $company->postcode = null;
        $company->city = null;
        $company->state = null;
        $company->country = null;
        $company->companytype()->associate(CompanyType::find(1));
        $company->save();

        $group = new Group();
        $group->uid = Carbon::now()->timestamp . (Group::count() + 1);
        $group->name = 'Main';
        $group->desc = 'Department Control Company All Resources';
        $group->company()->associate($company);
        $group->save();
        
        $user = new User();
        $user->uid = Carbon::now()->timestamp . (User::count() + 1);
        $user->name = 'Ipoh Drum Admin';
        $user->icno = null;
        $user->email = 'ipohdrum@outlook.com';
        $user->password = Hash::make('111111');
        $user->save();
        $user->roles()->attach([['role_id' => 2 , 'company_id'=> $company->refresh()->id]]);
        $user->groups()->attach($group->refresh()->id);
        
        $company = new Company();
        $company->uid = Carbon::now()->timestamp . (Company::count() + 1);;
        $company->name = 'The Asian Culture';
        $company->regno = null;
        $company->tel1 = null;
        $company->tel2 = null;
        $company->fax1 = null;
        $company->fax2 = null;
        $company->email1 = null;
        $company->email2 = null;
        $company->address1 = null;
        $company->address2 = null;
        $company->postcode = null;
        $company->city = null;
        $company->state = null;
        $company->country = null;
        $company->companytype()->associate(CompanyType::find(1));
        $company->save();

        $group = new Group();
        $group->uid = Carbon::now()->timestamp . (Group::count() + 1);
        $group->name = 'Main';
        $group->desc = 'Department Control Company All Resources';
        $group->company()->associate($company);
        $group->save();
        
        $user = new User();
        $user->uid = Carbon::now()->timestamp . (User::count() + 1);
        $user->name = 'TAC Admin';
        $user->icno = null;
        $user->email = 'tac.the.asian.culture@gmail.com';
        $user->password = Hash::make('111111');
        $user->save();
        $user->roles()->attach([['role_id' => 1 , 'company_id'=> $company->refresh()->id]]);
        $user->groups()->attach($group->refresh()->id);
        
    }
}
