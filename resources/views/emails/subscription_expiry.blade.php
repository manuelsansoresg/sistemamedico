@extends('emails.layouts.default')

@section('content')
    <h2>Hola, {{ $suscripcion->user->name }}</h2>
    
    <p>Le informamos que su suscripción <strong>{{ $suscripcion->tipo == 'paquete' ? ($suscripcion->paquete->nombre ?? 'Paquete') : ($suscripcion->catalogo->nombre ?? 'Servicio Extra') }}</strong> está próxima a vencer.</p>
    
    <div class="info-box">
        <p><strong>Fecha de Vencimiento:</strong> {{ \Carbon\Carbon::parse($suscripcion->fecha_fin)->format('d/m/Y') }}</p>
        <p><strong>Días restantes:</strong> {{ floor(\Carbon\Carbon::now()->diffInDays($suscripcion->fecha_fin, false)) }}</p>
    </div>

    @if($suscripcion->tipo == 'paquete')
    <p>Recuerde que al vencer su paquete, se limitará el acceso a las funcionalidades incluidas hasta que renueve su suscripción.</p>
    @else
    <p>Recuerde que al vencer su suscripción extra, no podrá crear nuevos recursos asociados a este servicio.</p>
    @endif

    <a href="{{ route('admin.suscripciones.index') }}" class="button">Renovar Suscripción</a>
    
    <p>Gracias por confiar en nosotros.</p>
@endsection
