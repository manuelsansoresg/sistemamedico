<x-mail::message>
# Hola {{ $user->name }},

Gracias por registrarte en **Sistema Médico**. Has seleccionado el método de pago por **Transferencia Bancaria**.

Para activar tu suscripción al paquete **{{ $suscripcion->paquete->nombre }}**, por favor realiza una transferencia por la cantidad de:

# ${{ number_format($suscripcion->precio, 2) }} MXN

A la siguiente cuenta bancaria:

- **Banco:** BBVA
- **Beneficiario:** Sistema Médico S.A. de C.V.
- **CLABE:** 012 180 015544332211 5
- **Referencia:** {{ $user->email }}

### Paso Final: Subir Comprobante

Una vez realizado el pago, es necesario que subas tu comprobante (imagen o PDF) en el siguiente enlace seguro para que nuestro equipo pueda activar tu cuenta:

<x-mail::button :url="$urlSubirComprobante">
Subir Comprobante de Pago
</x-mail::button>

Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:
{{ $urlSubirComprobante }}

*Nota: Tu cuenta permanecerá inactiva hasta que validemos tu pago.*

Gracias,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
