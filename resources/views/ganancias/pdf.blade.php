<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Ganancias</title>
    <style>
        @page {
            margin: 22px;
        }
        html, body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #1E293B;
        }
        .header {
            padding-bottom: 12px;
            border-bottom: 2px solid #0061F5;
            margin-bottom: 16px;
        }
        .header-title {
            font-size: 18px;
            font-weight: 700;
            color: #0061F5;
            margin: 0 0 2px 0;
        }
        .header-subtitle {
            font-size: 11px;
            color: #64748B;
            margin: 0;
        }
        .header-periodo {
            font-size: 10px;
            color: #27ADFA;
            margin: 2px 0 0 0;
            font-weight: 600;
        }
        .summary {
            margin-bottom: 14px;
            width: 100%;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 10px 14px;
            border: 1px solid #E2E8F0;
            width: 50%;
        }
        .summary-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748B;
            font-weight: 600;
        }
        .summary-value {
            font-size: 18px;
            font-weight: 700;
            color: #1E293B;
        }
        .summary-accent {
            color: #0061F5;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
        }
        table.data thead {
            display: table-header-group;
        }
        table.data th {
            background: #F1F5F9;
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            padding: 7px 8px;
            text-align: left;
            border: 1px solid #E2E8F0;
        }
        table.data th.right {
            text-align: right;
        }
        table.data td {
            padding: 6px 8px;
            border: 1px solid #E2E8F0;
            vertical-align: middle;
        }
        table.data td.right {
            text-align: right;
        }
        table.data .ganancia-positiva {
            color: #059669;
            font-weight: 700;
        }
        .truncate {
            max-width: 100px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: 700;
        }
        .badge-compra {
            background: #DBEAFE;
            color: #1E40AF;
        }
        .badge-renovacion {
            background: #D1FAE5;
            color: #065F46;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #94A3B8;
            border-top: 1px solid #E2E8F0;
            padding-top: 6px;
        }
        .page-number:before {
            content: "Página " counter(page);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="header-title">{{ config('app.name', 'Sistema Médico') }}</h1>
        <p class="header-subtitle">Reporte de Ganancias</p>
        <p class="header-periodo">Periodo: {{ $periodoLabel }}</p>
    </div>

    <div class="summary">
        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-label">Total Ganancias</div>
                    <div class="summary-value summary-accent">${{ number_format($totalGanancias, 2) }}</div>
                </td>
                <td>
                    <div class="summary-label">Ventas Totales Generadas</div>
                    <div class="summary-value">${{ number_format($totalVentas, 2) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 70px;">Fecha</th>
                <th>Concepto</th>
                <th style="width: 85px;">Servicio</th>
                <th style="width: 75px;">Tipo</th>
                @if(!$user->hasRole('doctor'))
                    <th style="width: 100px;">Doctor</th>
                @endif
                <th class="right" style="width: 72px;">Monto Venta</th>
                <th class="right" style="width: 60px;">Ganancia %</th>
                <th class="right" style="width: 72px;">Ganancia $</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ganancias as $ganancia)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($ganancia->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $ganancia->concepto }}</td>
                    <td>{{ optional($ganancia->catalogo)->nombre ?? optional($ganancia->paquete)->nombre ?? 'N/A' }}</td>
                    <td>
                        @if($ganancia->tipo_ingreso === 'compra')
                            <span class="badge badge-compra">Compra</span>
                        @elseif($ganancia->tipo_ingreso === 'renovacion')
                            <span class="badge badge-renovacion">Renovación</span>
                        @else
                            —
                        @endif
                    </td>
                    @if(!$user->hasRole('doctor'))
                        <td>{{ $ganancia->user->name }}</td>
                    @endif
                    <td class="right">${{ number_format($ganancia->monto_total, 2) }}</td>
                    <td class="right">{{ $ganancia->porcentaje_aplicado }}%</td>
                    <td class="right ganancia-positiva">
                        @if(!$user->hasRole('doctor'))
                            + ${{ number_format($ganancia->monto_total - $ganancia->monto_ganancia_doctor, 2) }}
                        @else
                            + ${{ number_format($ganancia->monto_ganancia_doctor, 2) }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $user->hasRole('doctor') ? 6 : 8 }}" style="text-align: center; color: #94A3B8; padding: 20px;">
                        No hay registros de ganancias en este periodo.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <span class="page-number"></span> &mdash; Generado el {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
