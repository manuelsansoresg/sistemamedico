@extends('emails.layouts.default')

@section('content')
    <h2>{{ __('emails.common.greeting', ['name' => $suscripcion->user->name]) }}</h2>
    
    <p>{!! __('emails.subscription_expiry.notice', ['subscription' => '<strong>'.e($suscripcion->tipo == 'paquete' ? ($suscripcion->paquete->nombre ?? __('emails.subscription_expiry.package_fallback')) : ($suscripcion->catalogo->nombre ?? __('emails.subscription_expiry.extra_fallback'))).'</strong>']) !!}</p>
    
    <div class="info-box">
        <p><strong>{{ __('emails.subscription_expiry.expiry_date') }}</strong> {{ \Carbon\Carbon::parse($suscripcion->fecha_fin)->format('d/m/Y') }}</p>
        <p><strong>{{ __('emails.subscription_expiry.days_remaining') }}</strong> {{ floor(\Carbon\Carbon::now()->diffInDays($suscripcion->fecha_fin, false)) }}</p>
    </div>

    @if($suscripcion->tipo == 'paquete')
    <p>{{ __('emails.subscription_expiry.package_reminder') }}</p>
    @else
    <p>{{ __('emails.subscription_expiry.extra_reminder') }}</p>
    @endif

    <a href="{{ route('compras.index') }}" class="button">{{ __('emails.subscription_expiry.renew') }}</a>
    
    <p>{{ __('emails.common.thanks') }}</p>
@endsection
