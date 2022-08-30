<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
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
            if(str_contains($value->getName(),'admin.') || str_contains($value->getName(),'staff.') || str_contains($value->getName(),'student.')){
                $permissions[$value->getName()] = $value->getName();
            }
        }

        foreach ($permissions as $permission) {
             Permission::updateOrCreate(['name' => $permission], [ 
                'name' => $permission
            ]);
        }
        
        $user = User::updateOrCreate(['email' => 'admin@gmail.com', 'username' => 'admin'],
        [
            'name' => 'Super Admin', 
            'username' => 'admin', 
            'user_type' => 'super_admin', 
            'user_staus' => 'active', 
            'user_staus' => 'active', 
            'is_deleted' => 'no', 
            'role' => 1, 
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123456')
        ]);
        $role = Role::updateOrCreate(['name' => 'Super Admin'],['name' => 'Super Admin']);
        $permissions = Permission::pluck('id','id')->all();
        $role->syncPermissions($permissions);
        $user->assignRole([$role->id]);
        dd('Permission granted');
    }
}
