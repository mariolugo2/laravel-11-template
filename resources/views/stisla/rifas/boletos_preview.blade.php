@extends('stisla.layouts.app')

@section('title', 'Vista Previa de Boletos')

@section('content')
  <div class="section-header">
    <h1>Vista Previa de Boletos - Rifa {{ $rifa->lote }}</h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></div>
      <div class="breadcrumb-item"><a href="{{ route('rifas.index') }}">Rifas</a></div>
      <div class="breadcrumb-item">Vista Previa</div>
    </div>
  </div>

  <div class="section-body">
    <div class="row">
      @foreach ($rifa->boletos as $boleto)
        <div class="col-md-6 col-lg-4">
          <div class="card">
            <div class="card-body text-center">
              <h5 class="card-title"><strong>Boleto N° {{ $loop->iteration }}</strong></h5>
              <p><strong>Código:</strong> {{ $boleto->codigo }}</p>
              <p><strong>Números:</strong> {{ implode(', ', json_decode($boleto->numeros)) }}</p>
              <p><strong>Rifa:</strong> {{ $rifa->lote }}</p>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="text-center mt-4">
      <a href="{{ route('rifas.imprimir.pdf', $rifa) }}" class="btn btn-primary">
        <i class="fas fa-download"></i> Descargar PDF
      </a>
      <button onclick="window.print()" class="btn btn-success">
        <i class="fas fa-print"></i> Imprimir
      </button>
    </div>
  </div>
@endsection
