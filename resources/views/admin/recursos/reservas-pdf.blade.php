<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte de Reservas</title>
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
            <tr>
                <td style="width: 70px; vertical-align: top;">
                    @if(!empty($clinicaLogoPath))
                        <img src="{{ $clinicaLogoPath }}" style="height: 54px; width: 54px; object-fit: contain;">
                    @endif
                </td>
                <td style="vertical-align: top;">
                    <h1>{{ $clinicaNombre }}</h1>
                    <div class="muted">Reporte de reservas de recursos</div>
                    <div class="muted">Periodo: {{ $periodoTitulo }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th class="nowrap" style="width: 90px;">Fecha</th>
                <th class="nowrap" style="width: 90px;">Hora</th>
                <th style="width: 160px;">Recurso</th>
                <th style="width: 170px;">Usuario</th>
                <th>Detalles / Notas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservas as $reserva)
                @php
                    $usuario = $reserva->user;
                    $usuarioNombre = $usuario
                        ? trim(($usuario->name ?? '').' '.($usuario->apellido_paterno ?? '').' '.($usuario->apellido_materno ?? ''))
                        : 'Sistema';
                    $detalles = trim(($reserva->titulo ?? '')."\n".($reserva->comentario ?? ''));
                @endphp
                <tr>
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
                <tr>
                    <td colspan="5" class="muted">No hay reservas para el periodo seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
