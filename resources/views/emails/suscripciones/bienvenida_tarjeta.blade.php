@extends('emails.layouts.default')

@section('content')
    <h2>Hola {{ $user->name }},</h2>

    <p>¡Bienvenido a <strong>Sistema Médico</strong>!</p>

    <p>Tu pago con tarjeta ha sido procesado exitosamente y tu suscripción al paquete <strong>{{ $suscripcion->paquete->nombre }}</strong> está activa.</p>

    <div class="info-box">
        <p style="margin-bottom: 0; font-weight: bold; color: #003366;">¡Ya puedes acceder a todas las funcionalidades de tu plan!</p>
    </div>

    <p>Hemos preparado todo para que puedas comenzar a gestionar tus pacientes y consultas de inmediato.</p>

    <div style="text-align: center;">
        <a href="{{ route('login') }}" class="button">Ingresar al Sistema</a>
    </div>

    <p>Detalles de tu suscripción:</p>
    <ul>
        <li><strong>Plan:</strong> {{ $suscripcion->paquete->nombre }}</li>
        <li><strong>Fecha de Inicio:</strong> {{ \Carbon\Carbon::parse($suscripcion->fecha_inicio)->format('d/m/Y') }}</li>
        <li><strong>Precio:</strong> ${{ number_format($suscripcion->precio, 2) }} MXN</li>
    </ul>

    <p>Si tienes alguna pregunta o necesitas ayuda para configurar tu cuenta, no dudes en contactarnos.</p>

    <p>Gracias por confiar en nosotros,<br>
    El equipo de Sistema Médico</p>
@endsection
