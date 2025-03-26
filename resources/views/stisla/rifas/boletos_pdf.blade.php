<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletos de la Rifa</title>
    <style>
        @page {
            size: letter portrait; /* Hoja carta en vertical */
            margin: 20px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            text-align: center;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
        }
        .boleto {
            width: 97%; /* 5 boletos por fila */
            height: 160px; /* Ajuste para 5 filas por hoja */
            border: 2px solid black;
            border-radius: 10px;
            padding: 5px;
            margin: 3px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            font-size: 10px;
        }
        .boleto h3 {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .boleto p {
            font-size: 10px;
            margin: 2px 0;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <h2>Boletos de la Rifa: {{ $rifa->lote }}</h2>
    @php $contador = 1; @endphp  {{-- Variable para numeración global --}}
    @foreach ($rifa->boletos->chunk(5) as $pagina) <!-- 5 boletos por hoja -->
        <div class="container">
            @foreach ($pagina as $boleto)
                <div class="boleto">
                    <h3><strong>Boleto N° {{ $contador }}</strong></h3>
                    <p><strong>Código:</strong> {{ $boleto->codigo }}</p>
                    <p><strong>Números:</strong> {{ implode(', ', json_decode($boleto->numeros)) }}</p>
                    <p><strong>Rifa:</strong> {{ $rifa->lote }}</p>
                </div>
                @php $contador++; @endphp {{-- Incrementa el número global --}}
            @endforeach
        </div>
        <div class="page-break"></div>
    @endforeach
</body>
</html>
