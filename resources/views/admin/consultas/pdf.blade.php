<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receta Médica - {{ $consulta->paciente->name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; color: #2c3e50; }
        .header p { margin: 2px 0; color: #7f8c8d; }
        .info-section { margin-bottom: 20px; width: 100%; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; vertical-align: top; }
        .label { font-weight: bold; color: #34495e; width: 120px; }
        .content-section { margin-top: 20px; }
        .field-group { margin-bottom: 15px; }
        .field-label { font-weight: bold; display: block; margin-bottom: 5px; color: #0061F5; }
        .field-value { background: #f9f9f9; padding: 10px; border-radius: 5px; border: 1px solid #eee; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #95a5a6; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dr. {{ $consulta->doctor->name }} {{ $consulta->doctor->apellido_paterno }} {{ $consulta->doctor->apellido_materno }}</h1>
        <p>{{ $consulta->doctor->especialidad->nombre ?? 'Médico General' }}</p>
        <p>Ced. Prof: {{ $consulta->doctor->cedula_profesional }}</p>
        <p>{{ $consulta->cita->consultorio->clinica->nombre ?? '' }} - {{ $consulta->cita->consultorio->nombre ?? '' }}</p>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Paciente:</td>
                <td>{{ $consulta->paciente->name }} {{ $consulta->paciente->apellido_paterno }} {{ $consulta->paciente->apellido_materno }}</td>
                <td class="label">Fecha:</td>
                <td>{{ $consulta->created_at->format('d/m/Y h:i A') }}</td>
            </tr>
            <tr>
                <td class="label">Edad:</td>
                <td>{{ $consulta->paciente->fecha_nacimiento ? $consulta->paciente->fecha_nacimiento->age . ' años' : 'N/A' }}</td>
                <td class="label">Peso/Talla:</td>
                <td>{{ $consulta->peso }} kg / {{ $consulta->estatura }} m</td>
            </tr>
            <tr>
                <td class="label">Alergias:</td>
                <td colspan="3">{{ $consulta->alergias ?? 'Negadas' }}</td>
            </tr>
        </table>
    </div>

    <div class="content-section">
        @foreach($consulta->valores as $valor)
            <div class="field-group">
                <span class="field-label">{{ $valor->campo->nombre }}:</span>
                <div class="field-value">
                    {!! nl2br(e($valor->valor)) !!}
                </div>
            </div>
        @endforeach
    </div>

    <div class="footer">
        <p>Este documento es un resumen de la consulta médica.</p>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
