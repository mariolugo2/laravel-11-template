@extends('stisla.layouts.app')

@section('title', 'Imprimir Boletos')

@section('content')
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .page {
                page-break-after: always;
                width: 100%;
                height: 100%;
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                align-items: center;
            }

            .boleto {
                width: 45%;
                height: 200px;
                border: 2px solid black;
                border-radius: 10px;
                padding: 10px;
                margin: 5px;
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
        }
    </style>

    <div class="container">
        <button onclick="window.print()" class="btn btn-primary my-3">Imprimir</button>
        
        @foreach ($rifa->boletos->chunk(5) as $pagina)
            <div class="page">
                @foreach ($pagina as $boleto)
                    <div class="boleto">
                        <h3><strong>Boleto N° {{ $loop->index + 1 }}</strong></h3>
                        <p><strong>Código:</strong> {{ $boleto->codigo }}</p>
                        <p><strong>Números:</strong> {{ implode(', ', json_decode($boleto->numeros)) }}</p>
                        <p><strong>Rifa:</strong> {{ $rifa->lote }}</p>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
