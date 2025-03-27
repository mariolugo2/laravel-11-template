<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletos de la Rifa</title>
    <style>
        @page {
            size: letter portrait; /* Hoja carta horizontal */
            margin: 20px; /* Márgenes uniformes */
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            text-align: center;
        }
        .contenedor {
            padding-top: 20px; /* Espaciado superior uniforme */
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            width: 100%;
        }
        .boleto {
            width: 97%; /* 5 boletos por fila */
            height: 150px; /* Ajuste para 5 filas por hoja */
            border: 2px solid black;
            border-radius: 10px;
            padding: 5px;
            margin: 3px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            font-size: 10px;
            background-color: #f8f9fa;
        }
        .boleto h3 {
            font-size: 14px;
            margin-bottom: 5px;
            color: #d9534f;
        }
        .boleto p {
            font-size: 10px;
            margin: 2px 0;
        }
        .logo {
            width: 50px;
            height: auto;
            margin-bottom: 5px;
        }
        .social {
            font-size: 8px;
            color: #007bff;
        }
        .titulo {
            text-align: center;
            margin-bottom: 20px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        {{-- Primera Hoja: Boletos Frontales --}}
        @foreach ($rifa->boletos->chunk(5) as $grupoBoletos)
            <div class="titulo">
                <h2>Boletos de la Rifa: {{ $rifa->lote }}</h2>
            </div>
            <div class="container">
                @foreach ($grupoBoletos as $boleto)
                    <div class="boleto">
                        <img src="{{ asset('images/logo.png') }}" class="logo">
                        <h3><strong>Boleto N° {{ $loop->parent->index * 5 + $loop->index + 1 }}</strong></h3>
                        <p><strong>Código:</strong> {{ $boleto->codigo }}</p>
                        <p><strong>Fecha de la rifa:</strong> {{ $rifa->fecha }}</p>
                        <p class="social">Síguenos en @TuRedSocial | Tel: 123-456-7890</p>
                    </div>
                @endforeach
            </div>
            <div class="page-break"></div>

            {{-- Segunda Hoja: Boletos Traseros --}}
            <div class="titulo">
                <h2>Boletos de la Rifa: {{ $rifa->lote }}</h2>
            </div>
            <div class="container">
                @foreach ($grupoBoletos as $boleto)
                    <div class="boleto">
                        <h3><strong>Números del Boleto</strong></h3>
                        <p>{{ implode(', ', json_decode($boleto->numeros)) }}</p>
                    </div>
                @endforeach
            </div>
            <div class="page-break"></div>
        @endforeach
    </div>
</body>
</html>
