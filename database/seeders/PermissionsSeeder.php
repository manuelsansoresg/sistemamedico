<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'descargar expedientes',
            'descargar consultas',
            'descargar estudios', // Base permission for studies
            'descargar estudios con imagenes',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $roleRoot = Role::where('name', 'root')->first();
        if ($roleRoot) {
            $roleRoot->givePermissionTo(Permission::all());
        }

        $roleDoctor = Role::where('name', 'doctor')->first();
        if ($roleDoctor) {
            $roleDoctor->givePermissionTo(Permission::all());
        }

        // Asistente and Secretaria start with no permissions by default
    }
}
