@extends('layouts.app')

@section('title', 'Crear Proyecto - Sistema de Gestión')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Crear Nuevo Proyecto</h1>
    <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
        Volver a Proyectos
    </a>
</div>

<div class="row">
    <div class="col-lg-8 col-md-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Proyecto</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('projects.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre del Proyecto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Describe los objetivos y alcance del proyecto...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                       id="start_date" name="start_date" value="{{ old('start_date') }}">
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">Fecha de Finalización</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                       id="end_date" name="end_date" value="{{ old('end_date') }}">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label">Estado <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="">Seleccionar estado...</option>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Activo</option>
                            <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completado</option>
                            <option value="on_hold" {{ old('status') === 'on_hold' ? 'selected' : '' }}>En Pausa</option>
                            <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Crear Proyecto
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">💡 Consejos para crear un proyecto exitoso</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li><strong>Nombre claro:</strong> Usa un nombre descriptivo que identifique fácilmente el proyecto.</li>
                    <li><strong>Descripción detallada:</strong> Incluye objetivos, alcance y entregables esperados.</li>
                    <li><strong>Fechas realistas:</strong> Establece fechas alcanzables considerando la complejidad del proyecto.</li>
                    <li><strong>Estado apropiado:</strong> Comienza con "Activo" para proyectos en desarrollo.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Validación de fechas
        $('#start_date, #end_date').on('change', function() {
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            
            if (startDate && endDate && startDate > endDate) {
                alert('La fecha de finalización debe ser posterior a la fecha de inicio.');
                $('#end_date').val('');
            }
        });

        // Auto-focus en el primer campo
        $('#name').focus();
    });
</script>
@endpush
