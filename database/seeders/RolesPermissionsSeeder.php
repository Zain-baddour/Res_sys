<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Create Roles
        $adminRole = Role::create(['name' => 'admin']);
        $ownerRole = Role::create(['name' => 'owner']);
        $assistantRole = Role::create(['name' => 'assistant']);
        $clientRole = Role::create(['name' => 'client']);
        $carSerRole = Role::create(['name' => 'carSer']);

        //define permissions
        $permissions = [
            'request_hall' , 'add_hall' , 'get_halls'
        ];
        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName , 'web');
        }

        //Assign Permissions to Roles
        $adminRole->syncPermissions($permissions);
        $ownerRole->givePermissionTo(['request_hall' , 'get_halls']);
        $assistantRole->givePermissionTo(['request_hall' , 'get_halls']);
        $clientRole->givePermissionTo(['get_halls']);

        //create admin user and assign role
        $adminUser = User::factory()->create([
           'name' => 'Admin User',
           'email' => 'example@gmail.com',
           'password' => bcrypt('passwordAdmin'),
            'location' => 'Damscus',
            'number' => '0988022701',
            'role' => 'Admin',
        ]);

        $adminUser->assignRole($adminRole);

        //assign permission associated with role to the admin user
        $permissions = $adminRole->permissions()->pluck('name')->toArray();
        $adminUser->givePermissionTo($permissions);


        $carSerUser = User::factory()->create([
            'name' => 'yallaGo',
            'email' => 'Go@gmail.com',
            'password' => bcrypt('yallaGo'),
            'location' => 'Damscus-allmazzah',
            'number' => '0988254754',
            'role' => 'carSer',
        ]);

        $carSerUser->assignRole($carSerRole);












//        //create hallManeger user and assign role
//        $hallManagerUser = User::factory()->create([
//            'name' => 'Maneger1',
//            'email' => 'man1@gmail.com',
//            'password' => bcrypt('passwordMan1'),
//        ]);
//
//        $hallManagerUser->assignRole($hallAdminRole);
//
//        //assign permission associated with role to the admin user
//        $permissions = $hallAdminRole->permissions()->pluck('name')->toArray();
//        $hallAdminRole->givePermissionTo($permissions);

    }
}
