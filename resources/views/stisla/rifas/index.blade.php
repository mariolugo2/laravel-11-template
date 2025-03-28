@extends('stisla.layouts.app-table')

@section('title')
  {{ $title }}
@endsection

@section('content')
  <div class="section-header">
    <h1><i class="fa fa-ticket"></i> {{ $title }}</h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item active">
        <a href="{{ route('dashboard.index') }}">{{ __('Dashboard') }}</a>
      </div>
      <div class="breadcrumb-item">{{ $title }}</div>
    </div>
  </div>

  <div class="section-body">
    <h2 class="section-title">{{ $title }}</h2>
    <p class="section-lead">{{ __('Lista de rifas generadas en el sistema.') }}</p>

    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4>Listado de Rifas</h4>
            <div class="card-header-action">
              <a href="{{ route('rifas.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Nueva Rifa
              </a>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped" id="datatable">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Lote</th>
                    <th>Generado por</th>
                    <th>Boletos</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($rifas as $rifa)
                    <tr>
                      <td>{{ $rifa->id }}</td>
                      <td>{{ $rifa->fecha }}</td>
                      <td>{{ $rifa->lote }}</td>
                      <td>{{ $rifa->usuario->name }}</td>
                      <td>{{ $rifa->cantidad_boletos }}</td>
                      <td>{{ $rifa->estado }}</td>
                      <td>
                        <a href="{{ route('rifas.show', $rifa->id) }}" class="btn btn-info btn-sm">
                          <i class="fa fa-eye"></i> Ver
                        </a>
                        <a href="{{ route('rifas.imprimir.vista', $rifa->id) }}" class="btn btn-success btn-sm">
                          <i class="fa fa-print"></i> Imprimir
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>

</script>
@endpush
