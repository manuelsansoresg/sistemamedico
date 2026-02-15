<?php

use App\Models\Paquete;
use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $owner = User::factory()->create();

    $paquete = Paquete::create([
        'user_id' => $owner->id,
        'nombre' => 'Paquete Test',
        'precio' => 1000,
        'porcentaje_ganancia' => 0,
        'activo' => true,
        'tipo' => 'clinica',
        'validar_cedula' => false,
    ]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'apellido_paterno' => 'Test',
        'apellido_materno' => 'User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'telefono' => '5555555555',
        'tipo_registro' => 'otro',
        'tipo_establecimiento' => 'clinica',
        'paquete_id' => $paquete->id,
        'terms_accepted' => '1',
        'payment_method' => 'transferencia',
    ]);

    $this->assertAuthenticated();
    $response->assertStatus(200);
    $response->assertViewIs('auth.register_success_transfer');
});
