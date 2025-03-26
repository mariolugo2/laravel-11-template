<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletos de la Rifa</title>
    <style>
        @page {
            size: letter landscape; /* Formato horizontal */
            margin: 20px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            text-align: center;
        }

        .container {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        .boleto {
            width: 45%;
            height: 200px;
            border: 2px solid black;
            border-radius: 10px;
            padding: 10px;
            margin: 10px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .boleto h3 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .boleto p {
            font-size: 14px;
            margin: 2px 0;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <h2>Boletos de la Rifa: {{ $rifa->lote }}</h2>

    @foreach ($rifa->boletos->chunk(5) as $pagina)
        <div class="container">
            @foreach ($pagina as $boleto)
                <div class="boleto">
                    <h3><strong>Boleto N° {{ $loop->iteration }}</strong></h3>
                    <p><strong>Código:</strong> {{ $boleto->codigo }}</p>
                    <p><strong>Números:</strong> {{ implode(', ', json_decode($boleto->numeros)) }}</p>
                    <p><strong>Rifa:</strong> {{ $rifa->lote }}</p>
                </div>
            @endforeach
        </div>
        <div class="page-break"></div> <!-- Corta la página después de 5 boletos -->
    @endforeach
</body>
</html>
