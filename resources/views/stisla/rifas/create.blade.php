@extends('stisla.layouts.app')

@section('title')
  {{ __('Crear Rifa') }}
@endsection

@section('content')
  <div class="section-header">
    <h1><i class="fa fa-plus"></i> {{ __('Crear Rifa') }}</h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item active">
        <a href="{{ route('dashboard.index') }}">{{ __('Dashboard') }}</a>
      </div>
      <div class="breadcrumb-item">
        <a href="{{ route('rifas.index') }}">{{ __('Rifas') }}</a>
      </div>
      <div class="breadcrumb-item">{{ __('Crear') }}</div>
    </div>
  </div>

  <div class="section-body">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>{{ __('Nueva Rifa') }}</h4>
          </div>
          <div class="card-body">
            <form action="{{ route('rifas.store') }}" method="POST">
              @csrf
              <div class="form-group">
                <label>{{ __('Fecha de la Rifa') }}</label>
                <input 
                    type="date" 
                    name="fecha" 
                    class="form-control" 
                    value="{{ now()->toDateString() }}"  
                    min="{{ now()->toDateString() }}"    
                    required
                >
            </div>
            <div class="form-group">
              <label>{{ __('Lote') }}</label>
              <input 
                  type="text" 
                  name="lote" 
                  class="form-control" 
                  value="{{ $loteAuto ?? old('lote') }}" 
                  pattern="R-\d{4}-\d{6}" 
                  title="Formato: R-0001-240328 (R-Número-Fecha)" 
                  required
                  readonly
              >
              <small class="form-text text-muted">
                  Formato automático: R-0001-{{ now()->format('ymd') }}
              </small>
          </div>
              <div class="form-group">
                <label>{{ __('Cantidad de Boletos') }}</label>
                <input type="number" name="cantidad_boletos" class="form-control" required>
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-primary">
                  <i class="fa fa-save"></i> {{ __('Guardar Rifa') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
