@extends('layouts.app')

@section('title', 'Dashboard - Sistema de Gestión de Proyectos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Dashboard</h1>
    <div>
        <a href="{{ route('projects.create') }}" class="btn btn-primary me-2">
            Nuevo Proyecto
        </a>
        <a href="{{ route('tickets.create') }}" class="btn btn-outline-primary">
            Nuevo Ticket
        </a>
    </div>
</div>

@if(isset($error))
    <div class="alert alert-danger">
        {{ $error }}
    </div>
@else
    <!-- Estadísticas Principales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-50 small">Total Proyectos</div>
                            <h3>{{ $stats['total_projects'] ?? 0 }}</h3>
                        </div>
                        <div class="align-self-center">
                            <div class="h2 text-white-50">📊</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-50 small">Proyectos Activos</div>
                            <h3>{{ $stats['active_projects'] ?? 0 }}</h3>
                        </div>
                        <div class="align-self-center">
                            <div class="h2 text-white-50">✅</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-50 small">Tareas Pendientes</div>
                            <h3>{{ $stats['pending_tasks'] ?? 0 }}</h3>
                        </div>
                        <div class="align-self-center">
                            <div class="h2 text-white-50">⏳</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-white-50 small">Tickets Abiertos</div>
                            <h3>{{ $stats['open_tickets'] ?? 0 }}</h3>
                        </div>
                        <div class="align-self-center">
                            <div class="h2 text-white-50">🎫</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Proyectos Recientes -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Proyectos Recientes</h5>
                    <a href="{{ route('projects.index') }}" class="btn btn-sm btn-outline-primary">Ver Todos</a>
                </div>
                <div class="card-body">
                    @if(isset($recentProjects) && $recentProjects->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Proyecto</th>
                                        <th>Estado</th>
                                        <th>Tareas</th>
                                        <th>Tickets</th>
                                        <th>Progreso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentProjects as $project)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $project->name }}</strong>
                                                    @if($project->description)
                                                        <br><small class="text-muted">{{ Str::limit($project->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-status-{{ $project->status }}">
                                                    {{ ucfirst($project->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $project->tasks->count() }}</td>
                                            <td>{{ $project->tickets->count() }}</td>
                                            <td>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar" style="width: {{ $project->progress }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $project->progress }}%</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-primary">
                                                    Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="h1 text-muted">📋</div>
                            <p class="text-muted">No hay proyectos recientes</p>
                            <a href="{{ route('projects.create') }}" class="btn btn-primary">Crear Primer Proyecto</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actividad Reciente -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actividad Reciente</h5>
                </div>
                <div class="card-body">
                    @if(isset($recentActivity) && $recentActivity->count() > 0)
                        <div class="timeline">
                            @foreach($recentActivity as $activity)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            @if($activity['type'] === 'project')
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    📊
                                                </div>
                                            @elseif($activity['type'] === 'task')
                                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    ✓
                                                </div>
                                            @else
                                                <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    🎫
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="small text-muted">{{ $activity['created_at']->diffForHumans() }}</div>
                                            <div>
                                                <strong>{{ ucfirst($activity['type']) }}:</strong>
                                                <a href="{{ $activity['url'] }}" class="text-decoration-none">
                                                    {{ $activity['title'] }}
                                                </a>
                                            </div>
                                            @if(isset($activity['project']))
                                                <small class="text-muted">en {{ $activity['project'] }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="h3 text-muted">📝</div>
                            <p class="text-muted">No hay actividad reciente</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tareas Próximas a Vencer -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tareas Próximas a Vencer</h5>
                </div>
                <div class="card-body">
                    @if(isset($upcomingTasks) && $upcomingTasks->count() > 0)
                        @foreach($upcomingTasks as $task)
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                <div>
                                    <strong>{{ $task->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $task->project->name }}</small>
                                    <br>
                                    <span class="badge badge-priority-{{ $task->priority }}">
                                        {{ $task->priority_text }}
                                    </span>
                                </div>
                                <div class="text-end">
                                    <div class="small text-muted">Vence:</div>
                                    <strong class="{{ $task->is_overdue ? 'text-danger' : 'text-warning' }}">
                                        {{ $task->due_date->format('d/m/Y') }}
                                    </strong>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <div class="h3 text-muted">⏰</div>
                            <p class="text-muted">No hay tareas próximas a vencer</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tickets Críticos -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tickets Críticos</h5>
                </div>
                <div class="card-body">
                    @if(isset($criticalTickets) && $criticalTickets->count() > 0)
                        @foreach($criticalTickets as $ticket)
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                <div>
                                    <strong>{{ $ticket->title }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $ticket->project->name }}</small>
                                    <br>
                                    <span class="badge" style="background-color: {{ $ticket->status_color }}">
                                        {{ $ticket->status_text }}
                                    </span>
                                </div>
                                <div class="text-end">
                                    <span class="badge badge-priority-critical">
                                        {{ $ticket->priority_text }}
                                    </span>
                                    <br>
                                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary mt-2">
                                        Ver
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <div class="h3 text-muted">🚨</div>
                            <p class="text-muted">No hay tickets críticos</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reuniones Próximas -->
    @if(isset($upcomingMeetings) && $upcomingMeetings->count() > 0)
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Reuniones Próximas</h5>
                        <a href="{{ route('meetings.index') }}" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($upcomingMeetings as $meeting)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card border">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $meeting->title }}</h6>
                                            <p class="card-text small text-muted">
                                                @if($meeting->project)
                                                    {{ $meeting->project->name }}
                                                @endif
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    {{ $meeting->scheduled_time->format('d/m/Y H:i') }}
                                                </small>
                                                <span class="badge" style="background-color: {{ $meeting->status_color }}">
                                                    {{ $meeting->status_text }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif

@endsection

@push('styles')
<style>
    .timeline-item {
        position: relative;
    }

    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 15px;
        top: 40px;
        width: 2px;
        height: calc(100% - 20px);
        background-color: var(--gray);
    }

    .badge-priority-critical {
        background-color: var(--primary-red);
    }

    .stats-card h3 {
        font-size: 2rem;
    }
</style>
@endpush
