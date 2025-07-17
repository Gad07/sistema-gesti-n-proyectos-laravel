<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema de Gestión de Proyectos')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-red: #FB0009;
            --white: #FFFFFF;
            --gray: #BBBBBB;
            --dark-gray: #6C757D;
            --light-gray: #F8F9FA;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-gray);
            color: #333;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-red) !important;
        }

        .btn-primary {
            background-color: var(--primary-red);
            border-color: var(--primary-red);
        }

        .btn-primary:hover {
            background-color: #d40008;
            border-color: #d40008;
        }

        .btn-outline-primary {
            color: var(--primary-red);
            border-color: var(--primary-red);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-red);
            border-color: var(--primary-red);
        }

        .text-primary {
            color: var(--primary-red) !important;
        }

        .bg-primary {
            background-color: var(--primary-red) !important;
        }

        .border-primary {
            border-color: var(--primary-red) !important;
        }

        .navbar {
            background-color: var(--white) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .nav-link {
            color: #333 !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-red) !important;
        }

        .nav-link.active {
            color: var(--primary-red) !important;
        }

        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .card-header {
            background-color: var(--white);
            border-bottom: 2px solid var(--light-gray);
            font-weight: 600;
        }

        .badge-priority-high {
            background-color: var(--primary-red);
        }

        .badge-priority-medium {
            background-color: #FFA500;
        }

        .badge-priority-low {
            background-color: #28A745;
        }

        .badge-status-active {
            background-color: #28A745;
        }

        .badge-status-completed {
            background-color: var(--dark-gray);
        }

        .sidebar {
            background-color: var(--white);
            min-height: calc(100vh - 76px);
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 8px;
        }

        .sidebar .nav-link:hover {
            background-color: var(--light-gray);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-red);
            color: var(--white) !important;
        }

        .main-content {
            padding: 20px;
        }

        .stats-card {
            background: linear-gradient(135deg, var(--primary-red), #ff3333);
            color: var(--white);
            border-radius: 12px;
        }

        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0;
        }

        .kanban-column {
            background-color: var(--light-gray);
            border-radius: 8px;
            min-height: 500px;
        }

        .kanban-card {
            cursor: move;
            transition: all 0.2s ease;
        }

        .kanban-card:hover {
            transform: scale(1.02);
        }

        .gantt-timeline {
            background-color: var(--white);
            border: 1px solid var(--gray);
        }

        .alert-primary {
            background-color: rgba(251, 0, 9, 0.1);
            border-color: var(--primary-red);
            color: var(--primary-red);
        }

        .form-control:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 0.2rem rgba(251, 0, 9, 0.25);
        }

        .form-select:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 0.2rem rgba(251, 0, 9, 0.25);
        }

        .page-link {
            color: var(--primary-red);
        }

        .page-link:hover {
            color: #d40008;
            background-color: var(--light-gray);
            border-color: var(--gray);
        }

        .page-item.active .page-link {
            background-color: var(--primary-red);
            border-color: var(--primary-red);
        }

        .dropdown-menu {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border: none;
        }

        .dropdown-item:hover {
            background-color: var(--light-gray);
            color: var(--primary-red);
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #333;
        }

        .progress-bar {
            background-color: var(--primary-red);
        }

        .spinner-border-sm {
            color: var(--primary-red);
        }

        .text-muted {
            color: var(--dark-gray) !important;
        }

        .border {
            border-color: var(--gray) !important;
        }

        .modal-header {
            border-bottom: 2px solid var(--light-gray);
        }

        .modal-footer {
            border-top: 2px solid var(--light-gray);
        }

        .toast {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .toast-header {
            background-color: var(--primary-red);
            color: var(--white);
        }

        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .main-content {
                padding: 15px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <strong>Sistema de Gestión</strong>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}" href="{{ route('projects.index') }}">
                            Proyectos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tickets.*') ? 'active' : '' }}" href="{{ route('tickets.index') }}">
                            Tickets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('meetings.*') ? 'active' : '' }}" href="{{ route('meetings.index') }}">
                            Reuniones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contents.*') ? 'active' : '' }}" href="{{ route('contents.index') }}">
                            Contenido
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Configuración
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Perfil</a></li>
                            <li><a class="dropdown-item" href="#">Configuración</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            @hasSection('sidebar')
                <div class="col-md-3 col-lg-2 px-0">
                    <div class="sidebar">
                        @yield('sidebar')
                    </div>
                </div>
                <div class="col-md-9 col-lg-10">
                    <div class="main-content">
                        @yield('content')
                    </div>
                </div>
            @else
                <div class="col-12">
                    <div class="main-content">
                        @yield('content')
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        @if(session('success'))
            <div class="toast show" role="alert">
                <div class="toast-header">
                    <strong class="me-auto">Éxito</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="toast show" role="alert">
                <div class="toast-header bg-danger">
                    <strong class="me-auto">Error</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    {{ session('error') }}
                </div>
            </div>
        @endif
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // CSRF Token setup for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Auto-hide toasts after 5 seconds
        setTimeout(function() {
            $('.toast').toast('hide');
        }, 5000);

        // Confirm delete actions
        $(document).on('click', '.btn-delete', function(e) {
            if (!confirm('¿Estás seguro de que deseas eliminar este elemento?')) {
                e.preventDefault();
            }
        });

        // Loading state for forms
        $(document).on('submit', 'form', function() {
            $(this).find('button[type="submit"]').prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...'
            );
        });
    </script>

    @stack('scripts')
</body>
</html>
