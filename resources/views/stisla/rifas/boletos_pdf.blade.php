<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Boletos de la Rifa</title>
  <style>
    @page {
      size: letter portrait;
      margin: 20px;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      text-align: center;
    }

    .contenedor {
      padding-top: 20px;
    }

    .container {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-around;
      width: 100%;
    }

    .boleto {
      width: 97%;
      height: 150px;
      border: 2px solid black;
      border-radius: 10px;
      padding: 5px;
      margin: 3px;
      text-align: center;
      display: flex;
      flex-direction: column;
      justify-content: center;
      font-size: 10px;
      color: white;
      background-image: url("{{ asset('storage/firmas/boleto.png') }}");
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }

    .boleto h3 {
      font-size: 14px;
      margin-bottom: 5px;
      color: #fff; /* Aseguramos que el color sea blanco */
      font-weight: bold;
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

    /* Estilo para los números en la parte trasera */
    .numeros-boleto {
      display: inline-block;
      background-color: #d9534f; /* Rojo llamativo */
      color: white;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: bold;
      margin: 5px 0;
      box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.2);
    }

    .numeros-boleto.highlight {
      background-color: #f0ad4e; /* Amarillo para resaltar */
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
            <!-- El fondo del boleto ahora será la imagen especificada en el CSS -->
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
          <div class="boleto" style="background: none; color: black; border: 2px solid black;">
            <h3 style="color: black;"><strong>Números del Boleto</strong></h3>
            <p>
              @php
                // Asegurarnos de que los números se estén decodificando correctamente
                $numeros = json_decode($boleto->numeros);
              @endphp
              @if ($numeros)
                @foreach ($numeros as $numero)
                  <span class="numeros-boleto">{{ $numero }}</span>
                @endforeach
              @else
                <span class="numeros-boleto">No hay números disponibles</span>
              @endif
            </p>
          </div>
        @endforeach
      </div>
      <div class="page-break"></div>
    @endforeach
  </div>
</body>

</html>
