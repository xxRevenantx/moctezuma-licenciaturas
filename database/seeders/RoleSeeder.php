<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Profesor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Estudiante', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Invitado', 'guard_name' => 'web']);

        $permisos = [
            'admin.usuarios' => [$superAdmin, $admin],
            'admin.usuarios.acciones' => [$superAdmin],
            'admin.administracion' => [$superAdmin],
            'admin.generaciones' => [$superAdmin],
            'admin.asignar.generacion' => [$superAdmin],
            'admin.asignacion.licenciaturas' => [$superAdmin, $admin],
            'admin.licenciaturas' => [$superAdmin, $admin],
            'exportar.licenciaturas' => [$superAdmin],
            'exportar.directivos' => [$superAdmin],
            'documentos-identidad.ver' => [$superAdmin],
            'documentos-identidad.subir' => [$superAdmin],
            'documentos-identidad.reemplazar' => [$superAdmin],
            'documentos-identidad.eliminar' => [$superAdmin],
            'documentos-identidad.descargar' => [$superAdmin],
            'documentos-identidad.auditar' => [$superAdmin],
        ];

        foreach ($permisos as $nombre => $roles) {
            $permission = Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);

            foreach ($roles as $role) {
                $role->givePermissionTo($permission);
            }
        }
    }
}
