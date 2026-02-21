<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
        $roleRoot = Role::create(['name' => 'root']);
        Role::create(['name' => 'doctor']);
        Role::create(['name' => 'asistente']);
        Role::create(['name' => 'secretaria']);
        Role::create(['name' => 'paciente']);

        // Crear usuario
        $user = User::create([
            'name' => 'manuel',
            'email' => 'manuelsansoresg@gmail.com',
            'password' => Hash::make('demor00txx'),
        ]);

        // Asignar rol
        $user->assignRole($roleRoot);
    }
}
