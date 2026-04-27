<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('recursos.pdf.title') }}</title>
    <style>
        @page {
            margin: 18px;
        }
        html, body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #111827;
        }
        h1 {
            font-size: 16px;
            margin: 0 0 4px 0;
        }
        .muted {
            color: #6B7280;
        }
        .header {
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #E5E7EB;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            display: table-header-group;
        }
        th, td {
            border: 1px solid #E5E7EB;
            padding: 6px 8px;
            vertical-align: top;
        }
        th {
            background: #F9FAFB;
            font-weight: 700;
            text-align: left;
        }
        tr {
            page-break-inside: avoid;
        }
        .nowrap {
            white-space: nowrap;
        }
        .notes {
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%; border-collapse: collapse;">
            <tr class="hover:bg-gray-50 transition-colors">
                <td style="width: 70px; vertical-align: top;">
                    @if(!empty($clinicaLogoPath))
                        <img src="{{ $clinicaLogoPath }}" style="height: 54px; width: 54px; object-fit: contain;">
                    @endif
                </td>
                <td style="vertical-align: top;">
                    <h1>{{ $clinicaNombre }}</h1>
                    <div class="muted">{{ __('recursos.pdf.subtitle') }}</div>
                    <div class="muted">{{ __('recursos.pdf.period', ['period' => $periodoTitulo]) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr class="hover:bg-gray-50 transition-colors">
                <th class="nowrap" style="width: 90px;">{{ __('recursos.pdf.columns.date') }}</th>
                <th class="nowrap" style="width: 90px;">{{ __('recursos.pdf.columns.time') }}</th>
                <th style="width: 160px;">{{ __('recursos.pdf.columns.resource') }}</th>
                <th style="width: 170px;">{{ __('recursos.pdf.columns.user') }}</th>
                <th>{{ __('recursos.pdf.columns.details') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservas as $reserva)
                @php
                    $usuario = $reserva->user;
                    $usuarioNombre = $usuario
                        ? trim(($usuario->name ?? '').' '.($usuario->apellido_paterno ?? '').' '.($usuario->apellido_materno ?? ''))
                        : __('common.system');
                    $detalles = trim(($reserva->titulo ?? '')."\n".($reserva->comentario ?? ''));
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="nowrap">{{ optional($reserva->inicio)->format('d/m/Y') }}</td>
                    <td class="nowrap">
                        {{ optional($reserva->inicio)->format('H:i') }}
                        -
                        {{ optional($reserva->fin)->format('H:i') }}
                    </td>
                    <td>{{ $reserva->recurso?->nombre }}</td>
                    <td>{{ $usuarioNombre }}</td>
                    <td class="notes">{{ $detalles }}</td>
                </tr>
            @empty
                <tr class="hover:bg-gray-50 transition-colors">
                    <td colspan="5" class="muted">{{ __('recursos.pdf.no_reservations') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
