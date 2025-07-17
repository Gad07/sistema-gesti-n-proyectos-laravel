@extends('layouts.app')

@section('title', 'Proyectos - Sistema de Gestión')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Proyectos</h1>
    <a href="{{ route('projects.create') }}" class="btn btn-primary">
        Nuevo Proyecto
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Nombre del proyecto...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="status">
                    <option value="">Todos los estados</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activo</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completado</option>
                    <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>En Pausa</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Ordenar por</label>
                <select class="form-select" name="sort">
                    <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Fecha de creación</option>
                    <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Nombre</option>
                    <option value="end_date" {{ request('sort') === 'end_date' ? 'selected' : '' }}>Fecha de finalización</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-primary me-2">Filtrar</button>
                <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </form>
    </div>
</div>

@if($projects->count() > 0)
    <!-- Vista de Tarjetas -->
    <div class="row">
        @foreach($projects as $project)
            <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $project->name }}</h5>
                        <span class="badge badge-status-{{ $project->status }}">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                    </div>
                    <div class="card-body">
                        @if($project->description)
                            <p class="card-text text-muted">{{ Str::limit($project->description, 100) }}</p>
                        @endif
                        
                        <!-- Estadísticas del proyecto -->
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="h5 mb-0 text-primary">{{ $project->tasks_count }}</div>
                                <small class="text-muted">Tareas</small>
                            </div>
                            <div class="col-4">
                                <div class="h5 mb-0 text-warning">{{ $project->tickets_count }}</div>
                                <small class="text-muted">Tickets</small>
                            </div>
                            <div class="col-4">
                                <div class="h5 mb-0 text-success">{{ $project->progress }}%</div>
                                <small class="text-muted">Progreso</small>
                            </div>
                        </div>

                        <!-- Barra de progreso -->
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar" style="width: {{ $project->progress }}%"></div>
                        </div>

                        <!-- Fechas -->
                        <div class="row small text-muted">
                            @if($project->start_date)
                                <div class="col-6">
                                    <strong>Inicio:</strong><br>
                                    {{ $project->start_date->format('d/m/Y') }}
                                </div>
                            @endif
                            @if($project->end_date)
                                <div class="col-6">
                                    <strong>Fin:</strong><br>
                                    {{ $project->end_date->format('d/m/Y') }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary btn-sm">
                                Ver
                            </a>
                            <a href="{{ route('projects.kanban', $project) }}" class="btn btn-outline-success btn-sm">
                                Kanban
                            </a>
                            <a href="{{ route('projects.gantt', $project) }}" class="btn btn-outline-info btn-sm">
                                Gantt
                            </a>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                    Más
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('projects.edit', $project) }}">Editar</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger btn-delete">
                                                Eliminar
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Paginación -->
    <div class="d-flex justify-content-center">
        {{ $projects->links() }}
    </div>
@else
    <!-- Estado vacío -->
    <div class="card">
        <div class="card-body text-center py-5">
            <div class="h1 text-muted mb-4">📋</div>
            <h3>No hay proyectos</h3>
            <p class="text-muted mb-4">
                @if(request()->hasAny(['search', 'status', 'sort']))
                    No se encontraron proyectos que coincidan con los filtros aplicados.
                @else
                    Comienza creando tu primer proyecto para organizar tus tareas y tickets.
                @endif
            </p>
            @if(request()->hasAny(['search', 'status', 'sort']))
                <a href="{{ route('projects.index') }}" class="btn btn-outline-primary me-2">
                    Limpiar Filtros
                </a>
            @endif
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
                Crear Primer Proyecto
            </a>
        </div>
    </div>
@endif

@endsection

@push('styles')
<style>
    .badge-status-active {
        background-color: #28A745;
    }
    .badge-status-completed {
        background-color: #6C757D;
    }
    .badge-status-on_hold {
        background-color: #FFC107;
        color: #000;
    }
    .badge-status-cancelled {
        background-color: var(--primary-red);
    }
    
    .card-footer .btn-group {
        display: flex;
    }
    
    .card-footer .btn-group .btn {
        flex: 1;
    }
    
    .progress-bar {
        transition: width 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-submit form on filter change
    $(document).ready(function() {
        $('select[name="status"], select[name="sort"]').on('change', function() {
            $(this).closest('form').submit();
        });
    });
</script>
@endpush
