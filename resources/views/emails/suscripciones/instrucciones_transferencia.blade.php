@extends('emails.layouts.default')

@section('content')
    <h2>{{ __('emails.common.greeting', ['name' => $user->name]) }}</h2>

    <p>{!! __('emails.transfer_instructions.intro', ['app' => '<strong>'.e(config('app.name', 'Sistema Médico')).'</strong>', 'method' => '<strong>'.e(__('emails.transfer_instructions.method')).'</strong>']) !!}</p>

    <p>{!! __('emails.transfer_instructions.amount_instructions', ['package' => '<strong>'.e($suscripcion->paquete->nombre).'</strong>']) !!}</p>

    <div style="text-align: center; margin: 30px 0;">
        <span style="font-size: 32px; font-weight: bold; color: #003366;">${{ number_format($suscripcion->precio, 2) }} MXN</span>
    </div>

    <div class="info-box">
        <p style="margin-bottom: 10px; font-weight: bold;">{{ __('emails.common.bank_data') }}</p>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="margin-bottom: 8px;"><strong>{{ __('emails.common.bank') }}</strong> BBVA</li>
            <li style="margin-bottom: 8px;"><strong>{{ __('emails.common.beneficiary') }}</strong> Sistema Médico S.A. de C.V.</li>
            <li style="margin-bottom: 8px;"><strong>CLABE:</strong> 012 180 015544332211 5</li>
            <li style="margin-bottom: 0;"><strong>{{ __('emails.common.reference') }}</strong> {{ $user->email }}</li>
        </ul>
    </div>

    <h3>{{ __('emails.transfer_instructions.final_step') }}</h3>

    <p>{{ __('emails.transfer_instructions.upload_instructions') }}</p>

    <div style="text-align: center;">
        <a href="{{ $urlSubirComprobante }}" class="button">{{ __('emails.transfer_instructions.upload_button') }}</a>
    </div>

    <p style="font-size: 14px; color: #6b7280; margin-top: 20px;">
        {{ __('emails.transfer_instructions.fallback_link') }}<br>
        <a href="{{ $urlSubirComprobante }}" style="color: #0055aa;">{{ $urlSubirComprobante }}</a>
    </p>

    <p><em>{{ __('emails.transfer_instructions.inactive_note') }}</em></p>

    <p>{{ __('emails.transfer_instructions.thanks') }}<br>
    {{ __('emails.common.team') }}</p>
@endsection
