<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
    }
}
