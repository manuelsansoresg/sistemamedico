@extends('emails.layouts.default')

@section('content')
    <h2>{{ __('emails.common.greeting', ['name' => $user->name]) }}</h2>

    <p>{!! __('emails.card_welcome.welcome', ['app' => '<strong>'.e(config('app.name', 'Sistema Médico')).'</strong>']) !!}</p>

    <p>{!! __('emails.card_welcome.payment_processed', ['package' => '<strong>'.e($suscripcion->paquete->nombre).'</strong>']) !!}</p>

    <div class="info-box">
        <p style="margin-bottom: 0; font-weight: bold; color: #003366;">{{ __('emails.card_welcome.access_ready') }}</p>
    </div>

    <p>{{ __('emails.card_welcome.prepared') }}</p>

    <div style="text-align: center;">
        <a href="{{ route('login') }}" class="button">{{ __('emails.card_welcome.login_button') }}</a>
    </div>

    <p>{{ __('emails.card_welcome.subscription_details') }}</p>
    <ul>
        <li><strong>{{ __('emails.common.plan') }}</strong> {{ $suscripcion->paquete->nombre }}</li>
        <li><strong>{{ __('emails.common.start_date') }}</strong> {{ \Carbon\Carbon::parse($suscripcion->fecha_inicio)->format('d/m/Y') }}</li>
        <li><strong>{{ __('emails.common.price') }}</strong> ${{ number_format($suscripcion->precio, 2) }} MXN</li>
    </ul>

    <p>{{ __('emails.card_welcome.help') }}</p>

    <p>{{ __('emails.card_welcome.thanks') }}<br>
    {{ __('emails.common.team') }}</p>
@endsection
