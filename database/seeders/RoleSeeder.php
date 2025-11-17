<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;



class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role1 = Role::create(['name' => 'SuperAdmin']);
        $role2 = Role::create(['name' => 'Admin']);
        $role3 = Role::create(['name' => 'Profesor']);
        $role4 = Role::create(['name' => 'Estudiante']);
        $role5 = Role::create(['name' => 'Invitado']);


        Permission::create(['name' => 'admin.usuarios'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'admin.usuarios.acciones'])->syncRoles([$role1]);


        Permission::create(['name' => 'admin.administracion'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.generaciones'])->syncRoles([$role1]);

        Permission::create(['name' => 'admin.asignar.generacion'])->syncRoles([$role1]);

        // ROL la asignacion de licenciaturas
        Permission::create(['name' => 'admin.asignacion.licenciaturas'])->syncRoles([$role1, $role2]);


        // ROL PARA LICENCIATURAS
        Permission::create(['name' => 'admin.licenciaturas'])->syncRoles([$role1, $role2]);


        Permission::create(['name' => 'exportar.licenciaturas'])->syncRoles([$role1]);


        Permission::create(['name' => 'exportar.directivos'])->syncRoles([$role1]);




    }
}
