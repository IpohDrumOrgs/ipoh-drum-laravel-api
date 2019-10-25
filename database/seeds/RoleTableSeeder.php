<?php

use Illuminate\Database\Seeder;
use App\Role;
use Carbon\Carbon;
use App\Module;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = new Role();  
        $checkid = false;
        $uid = '';
        while(!$checkid){
            $uid = '2' . Carbon::now()->timestamp;
            if (!Role::where('uid', '=', $uid)->exists()) {
                // user found
                $checkid = true;
            }
        }
        $role->uid = $uid;
        $role->name = 'Admin';
        $role->desc = 'The highest authority of the system';
        $role->save();
        
        $role = new Role();  
        $checkid = false;
        $uid = '';
        while(!$checkid){
            $uid = '2' . Carbon::now()->timestamp;
            if (!Role::where('uid', '=', $uid)->exists()) {
                // user found
                $checkid = true;
            }
        }
        $role->uid = $uid;
        $role->name = 'Admin Staff';
        $role->desc = 'The staff that help admin manages the system';
        $role->save();
        
        $role = new Role();  
        $checkid = false;
        $uid = '';
        while(!$checkid){
            $uid = '2' . Carbon::now()->timestamp;
            if (!Role::where('uid', '=', $uid)->exists()) {
                // user found
                $checkid = true;
            }
        }
        $role->uid = $uid;
        $role->name = 'Branch Manager';
        $role->desc = 'The manager of the branches';
        $role->save();
        
        $role = new Role();  
        $uid = '';
        while(!$checkid){
            $uid = '2' . Carbon::now()->timestamp;
            if (!Role::where('uid', '=', $uid)->exists()) {
                // user found
                $checkid = true;
            }
        }
        $role->uid = $uid;
        $role->name = 'Cashier';
        $role->desc = 'The user that create sale';
        $role->save();
    }
}
