<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Sistema Médico' }}</title>
    <style>
        /* Base styles */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            color: #374151;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .header {
            background-color: #003366; /* Azul elegante corporativo */
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .button {
            display: inline-block;
            background-color: #0055aa; /* Azul un poco más claro para el botón */
            color: #ffffff !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .button:hover {
            background-color: #004488;
        }
        h2 {
            color: #111827;
            font-size: 20px;
            margin-top: 0;
        }
        p {
            margin-bottom: 15px;
        }
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #0055aa;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .highlight {
            color: #003366;
            font-weight: bold;
        }
        ul {
            padding-left: 20px;
            margin-bottom: 15px;
        }
        li {
            margin-bottom: 5px;
        }
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                border-radius: 0;
                margin-top: 0;
                margin-bottom: 0;
            }
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Sistema Médico</h1>
        </div>

        <!-- Body -->
        <div class="content">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} Sistema Médico. Todos los derechos reservados.</p>
            <p>Si tienes dudas, contáctanos a soporte@sistemamedico.com</p>
        </div>
    </div>
</body>
</html>
