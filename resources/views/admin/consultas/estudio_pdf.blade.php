<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orden de Estudios - {{ $estudio->consulta->paciente->name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; color: #2c3e50; }
        .header p { margin: 2px 0; color: #7f8c8d; }
        .info-section { margin-bottom: 20px; width: 100%; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; vertical-align: top; }
        .label { font-weight: bold; color: #34495e; width: 120px; }
        .content-section { margin-top: 20px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #95a5a6; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dr. {{ $estudio->consulta->doctor->name }} {{ $estudio->consulta->doctor->apellido_paterno }}</h1>
        <p>Orden de Estudios de Laboratorio / Gabinete</p>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Paciente:</td>
                <td>{{ $estudio->consulta->paciente->name }} {{ $estudio->consulta->paciente->apellido_paterno }} {{ $estudio->consulta->paciente->apellido_materno }}</td>
            </tr>
            <tr>
                <td class="label">Fecha:</td>
                <td>{{ $estudio->created_at->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="content-section">
        <h3 style="margin-top: 0; color: #0061F5;">Estudios Solicitados:</h3>
        <p>{!! nl2br(e($estudio->orden)) !!}</p>
        
        @if($estudio->observacion)
            <br>
            <h4 style="margin-bottom: 5px; color: #7f8c8d;">Observaciones:</h4>
            <p>{!! nl2br(e($estudio->observacion)) !!}</p>
        @endif
    </div>

    <div class="footer">
        <p>Firma del Médico</p>
        <br><br>
        <p>_____________________________________</p>
    </div>
</body>
</html>
