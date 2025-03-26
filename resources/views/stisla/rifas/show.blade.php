@extends('stisla.layouts.app')

@section('title')
Rifa Detalles
@endsection

@section('content')
<div class="section-header">
    <h1>Rifa: {{ $rifa->lote }}</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active">
            <a href="{{ route('dashboard.index') }}">Dashboard</a>
        </div>
        <div class="breadcrumb-item"><a href="{{ route('rifas.index') }}">Rifas</a></div>
        <div class="breadcrumb-item">{{ $rifa->lote }}</div>
    </div>
</div>

<div class="section-body">
    <h2 class="section-title">Detalles de la Rifa</h2>
    <p class="section-lead">Información detallada sobre la rifa {{ $rifa->lote }}.</p>

    <div class="card">
        <div class="card-header">
            <h4>Detalles</h4>
        </div>
        <div class="card-body">
            <p><strong>Lote:</strong> {{ $rifa->lote }}</p>
            <p><strong>Fecha:</strong> {{ $rifa->fecha }}</p>
            <p><strong>Generado por:</strong> {{ $rifa->generado_por }}</p>
            <p><strong>Cantidad de boletos:</strong> {{ $rifa->cantidad_boletos }}</p>

            <h5>Boletos:</h5>
            <div class="row">
                @foreach ($boletos as $boleto)
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <strong>Código: {{ $boleto->codigo }}</strong>
                        </div>
                        <div class="card-body">
                            <p><strong>Números:</strong></p>
                            <div class="badge-list">
                                @foreach (json_decode($boleto->numeros) as $numero)
                                <span class="badge badge-success">{{ $numero }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-center">
                {{ $boletos->links('pagination::bootstrap-4', ['class' => 'pagination-sm']) }}
            </div>
        </div>
    </div>
</div>
@endsection