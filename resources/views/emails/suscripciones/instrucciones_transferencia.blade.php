@extends('emails.layouts.default')

@section('content')
    <h2>Hola {{ $user->name }},</h2>

    <p>Gracias por registrarte en <strong>Sistema Médico</strong>. Has seleccionado el método de pago por <strong>Transferencia Bancaria</strong>.</p>

    <p>Para activar tu suscripción al paquete <strong>{{ $suscripcion->paquete->nombre }}</strong>, por favor realiza una transferencia por la cantidad de:</p>

    <div style="text-align: center; margin: 30px 0;">
        <span style="font-size: 32px; font-weight: bold; color: #003366;">${{ number_format($suscripcion->precio, 2) }} MXN</span>
    </div>

    <div class="info-box">
        <p style="margin-bottom: 10px; font-weight: bold;">Datos Bancarios:</p>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="margin-bottom: 8px;"><strong>Banco:</strong> BBVA</li>
            <li style="margin-bottom: 8px;"><strong>Beneficiario:</strong> Sistema Médico S.A. de C.V.</li>
            <li style="margin-bottom: 8px;"><strong>CLABE:</strong> 012 180 015544332211 5</li>
            <li style="margin-bottom: 0;"><strong>Referencia:</strong> {{ $user->email }}</li>
        </ul>
    </div>

    <h3>Paso Final: Subir Comprobante</h3>

    <p>Una vez realizado el pago, es necesario que subas tu comprobante (imagen o PDF) en el siguiente enlace seguro para que nuestro equipo pueda activar tu cuenta:</p>

    <div style="text-align: center;">
        <a href="{{ $urlSubirComprobante }}" class="button">Subir Comprobante de Pago</a>
    </div>

    <p style="font-size: 14px; color: #6b7280; margin-top: 20px;">
        Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:<br>
        <a href="{{ $urlSubirComprobante }}" style="color: #0055aa;">{{ $urlSubirComprobante }}</a>
    </p>

    <p><em>Nota: Tu cuenta permanecerá inactiva hasta que validemos tu pago.</em></p>

    <p>Gracias,<br>
    El equipo de Sistema Médico</p>
@endsection
