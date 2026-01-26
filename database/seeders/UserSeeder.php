<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

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
