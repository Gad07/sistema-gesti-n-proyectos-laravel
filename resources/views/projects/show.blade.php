@extends('layouts.app')

@section('title', $project->name . ' - Sistema de Gestión')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-0">{{ $project->name }}</h1>
        <span class="badge badge-status-{{ $project->status }} fs-6 mt-2">
            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
        </span>
    </div>
    <div>
        <a href="{{ route('projects.kanban', $project) }}" class="btn btn-success me-2">
            Vista Kanban
        </a>
        <a href="{{ route('projects.gantt', $project) }}" class="btn btn-info me-2">
            Vista Gantt
        </a>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                Acciones
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('projects.edit', $project) }}">Editar Proyecto</a></li>
                <li><a class="dropdown-item" href="{{ route('projects.tasks.create', $project) }}">Nueva Tarea</a></li>
                <li><a class="dropdown-item" href="{{ route('tickets.create') }}?project_id={{ $project->id }}">Nuevo Ticket</a></li>
                <li><a class="dropdown-item" href="{{ route('meetings.create') }}?project_id={{ $project->id }}">Nueva Reunión</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger btn-delete">
                            Eliminar Proyecto
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Estadísticas del Proyecto -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Total Tareas</div>
                        <h4>{{ $stats['total_tasks'] }}</h4>
                    </div>
                    <div class="align-self-center">
                        <div class="h3 text-white-50">📋</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Completadas</div>
                        <h4>{{ $stats['completed_tasks'] }}</h4>
                    </div>
                    <div class="align-self-center">
                        <div class="h3 text-white-50">✅</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Tickets</div>
                        <h4>{{ $stats['total_tickets'] }}</h4>
                    </div>
                    <div class="align-self-center">
                        <div class="h3 text-white-50">🎫</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-50 small">Progreso</div>
                        <h4>{{ $stats['progress'] }}%</h4>
                    </div>
                    <div class="align-self-center">
                        <div class="h3 text-white-50">📊</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Información del Proyecto -->
<div class="row">
    <div class="col-lg-8">
        <!-- Pestañas -->
        <ul class="nav nav-tabs" id="projectTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                    Resumen
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tasks-tab" data-bs-toggle="tab" data-bs-target="#tasks" type="button" role="tab">
                    Tareas ({{ $stats['total_tasks'] }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tickets-tab" data-bs-toggle="tab" data-bs-target="#tickets" type="button" role="tab">
                    Tickets ({{ $stats['total_tickets'] }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media" type="button" role="tab">
                    Archivos
                </button>
            </li>
        </ul>

        <div class="tab-content" id="projectTabsContent">
            <!-- Pestaña Resumen -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        @if($project->description)
                            <h5>Descripción</h5>
                            <p class="text-muted">{{ $project->description }}</p>
                        @endif

                        <div class="row">
                            @if($project->start_date)
                                <div class="col-md-6">
                                    <h6>Fecha de Inicio</h6>
                                    <p>{{ $project->start_date->format('d/m/Y') }}</p>
                                </div>
                            @endif
                            @if($project->end_date)
                                <div class="col-md-6">
                                    <h6>Fecha de Finalización</h6>
                                    <p>{{ $project->end_date->format('d/m/Y') }}</p>
                                </div>
                            @endif
                        </div>

                        <h6>Progreso General</h6>
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar" style="width: {{ $stats['progress'] }}%">
                                {{ $stats['progress'] }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pestaña Tareas -->
            <div class="tab-pane fade" id="tasks" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Tareas Recientes</h5>
                        <a href="{{ route('projects.tasks.create', $project) }}" class="btn btn-sm btn-primary">
                            Nueva Tarea
                        </a>
                    </div>
                    <div class="card-body">
                        @if($recentTasks->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tarea</th>
                                            <th>Estado</th>
                                            <th>Prioridad</th>
                                            <th>Fecha Límite</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentTasks as $task)
                                            <tr>
                                                <td>
                                                    <strong>{{ $task->name }}</strong>
                                                    @if($task->description)
                                                        <br><small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $task->kanban_column }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-priority-{{ $task->priority }}">
                                                        {{ $task->priority_text }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($task->due_date)
                                                        <span class="{{ $task->is_overdue ? 'text-danger' : '' }}">
                                                            {{ $task->due_date->format('d/m/Y') }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">Sin fecha</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="btn btn-sm btn-outline-primary">
                                                        Editar
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="{{ route('projects.kanban', $project) }}" class="btn btn-outline-primary">
                                    Ver Todas las Tareas en Kanban
                                </a>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="h3 text-muted">📝</div>
                                <p class="text-muted">No hay tareas en este proyecto</p>
                                <a href="{{ route('projects.tasks.create', $project) }}" class="btn btn-primary">
                                    Crear Primera Tarea
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Pestaña Tickets -->
            <div class="tab-pane fade" id="tickets" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Tickets Recientes</h5>
                        <a href="{{ route('tickets.create') }}?project_id={{ $project->id }}" class="btn btn-sm btn-primary">
                            Nuevo Ticket
                        </a>
                    </div>
                    <div class="card-body">
                        @if($recentTickets->count() > 0)
                            @foreach($recentTickets as $ticket)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="card-title">{{ $ticket->title }}</h6>
                                                <p class="card-text text-muted">{{ Str::limit($ticket->description, 100) }}</p>
                                                <div>
                                                    <span class="badge" style="background-color: {{ $ticket->priority_color }}">
                                                        {{ $ticket->priority_text }}
                                                    </span>
                                                    <span class="badge" style="background-color: {{ $ticket->status_color }}">
                                                        {{ $ticket->status_text }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                                    Ver
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="text-center">
                                <a href="{{ route('tickets.index') }}?project_id={{ $project->id }}" class="btn btn-outline-primary">
                                    Ver Todos los Tickets
                                </a>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="h3 text-muted">🎫</div>
                                <p class="text-muted">No hay tickets en este proyecto</p>
                                <a href="{{ route('tickets.create') }}?project_id={{ $project->id }}" class="btn btn-primary">
                                    Crear Primer Ticket
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Pestaña Archivos -->
            <div class="tab-pane fade" id="media" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Archivos del Proyecto</h5>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            Subir Archivo
                        </button>
                    </div>
                    <div class="card-body">
                        @if($project->media->count() > 0)
                            <div class="row">
                                @foreach($project->media as $media)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <div class="h2 mb-2">{{ $media->icon }}</div>
                                                <h6 class="card-title">{{ $media->file_name }}</h6>
                                                <p class="card-text small text-muted">{{ $media->formatted_size }}</p>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ $media->url }}" class="btn btn-outline-primary" target="_blank">
                                                        Ver
                                                    </a>
                                                    <form action="{{ route('media.destroy', $media) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-delete">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <div class="h3 text-muted">📎</div>
                                <p class="text-muted">No hay archivos en este proyecto</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                    Subir Primer Archivo
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Información del Proyecto</h6>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-5">Estado:</dt>
                    <dd class="col-sm-7">
                        <span class="badge badge-status-{{ $project->status }}">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                    </dd>

                    @if($project->start_date)
                        <dt class="col-sm-5">Inicio:</dt>
                        <dd class="col-sm-7">{{ $project->start_date->format('d/m/Y') }}</dd>
                    @endif

                    @if($project->end_date)
                        <dt class="col-sm-5">Fin:</dt>
                        <dd class="col-sm-7">{{ $project->end_date->format('d/m/Y') }}</dd>
                    @endif

                    <dt class="col-sm-5">Creado:</dt>
                    <dd class="col-sm-7">{{ $project->created_at->format('d/m/Y') }}</dd>

                    <dt class="col-sm-5">Actualizado:</dt>
                    <dd class="col-sm-7">{{ $project->updated_at->diffForHumans() }}</dd>
                </dl>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Acciones Rápidas</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('projects.kanban', $project) }}" class="btn btn-outline-success">
                        📋 Vista Kanban
                    </a>
                    <a href="{{ route('projects.gantt', $project) }}" class="btn btn-outline-info">
                        📊 Vista Gantt
                    </a>
                    <a href="{{ route('projects.tasks.create', $project) }}" class="btn btn-outline-primary">
                        ➕ Nueva Tarea
                    </a>
                    <a href="{{ route('tickets.create') }}?project_id={{ $project->id }}" class="btn btn-outline-warning">
                        🎫 Nuevo Ticket
                    </a>
                    <a href="{{ route('meetings.create') }}?project_id={{ $project->id }}" class="btn btn-outline-secondary">
                        📅 Nueva Reunión
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para subir archivos -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subir Archivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('media.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="model_type" value="App\Models\Project">
                <input type="hidden" name="model_id" value="{{ $project->id }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Seleccionar Archivo</label>
                        <input type="file" class="form-control" id="file" name="file" required>
                        <div class="form-text">Máximo 10MB. Formatos permitidos: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, GIF</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Subir Archivo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
