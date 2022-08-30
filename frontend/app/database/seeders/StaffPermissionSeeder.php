<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Spatie\Permission\Models\Role;

class StaffPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $routeCollection = Route::getRoutes();
        $permissionList = array();
        foreach ($routeCollection as $value) {
            if(str_contains($value->getName(),'staff.')){
                $permissions[$value->getName()] = $value->getName();
            }
        }

        foreach ($permissions as $permission) {
             Permission::updateOrCreate(['name' => $permission], [ 
                'name' => $permission
            ]);
        }
        dd('Permission routes added to staff');
    }
}
