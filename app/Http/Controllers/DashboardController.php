<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\Meeting;
use App\Models\Content;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard principal
     */
    public function index()
    {
        try {
            // Estadísticas generales
            $stats = [
                'total_projects' => Project::count(),
                'active_projects' => Project::where('status', 'active')->count(),
                'total_tasks' => Task::count(),
                'pending_tasks' => Task::where('kanban_column', '!=', 'Done')->count(),
                'total_tickets' => Ticket::count(),
                'open_tickets' => Ticket::where('status', 'open')->count(),
                'upcoming_meetings' => Meeting::upcoming()->count(),
            ];

            // Proyectos recientes
            $recentProjects = Project::with(['tasks', 'tickets'])
                ->latest()
                ->take(5)
                ->get();

            // Tareas próximas a vencer
            $upcomingTasks = Task::with('project')
                ->where('due_date', '>=', now())
                ->where('due_date', '<=', now()->addDays(7))
                ->where('kanban_column', '!=', 'Done')
                ->orderBy('due_date')
                ->take(10)
                ->get();

            // Tickets críticos
            $criticalTickets = Ticket::with('project')
                ->where('priority', 'critical')
                ->where('status', '!=', 'closed')
                ->latest()
                ->take(5)
                ->get();

            // Reuniones próximas
            $upcomingMeetings = Meeting::with('project')
                ->upcoming()
                ->orderBy('scheduled_time')
                ->take(5)
                ->get();

            // Actividad reciente (últimos proyectos, tareas y tickets creados)
            $recentActivity = collect()
                ->merge(
                    Project::latest()->take(3)->get()->map(function ($item) {
                        return [
                            'type' => 'project',
                            'title' => $item->name,
                            'created_at' => $item->created_at,
                            'url' => route('projects.show', $item)
                        ];
                    })
                )
                ->merge(
                    Task::with('project')->latest()->take(3)->get()->map(function ($item) {
                        return [
                            'type' => 'task',
                            'title' => $item->name,
                            'project' => $item->project->name,
                            'created_at' => $item->created_at,
                            'url' => route('projects.show', $item->project)
                        ];
                    })
                )
                ->merge(
                    Ticket::with('project')->latest()->take(3)->get()->map(function ($item) {
                        return [
                            'type' => 'ticket',
                            'title' => $item->title,
                            'project' => $item->project->name,
                            'created_at' => $item->created_at,
                            'url' => route('tickets.show', $item)
                        ];
                    })
                )
                ->sortByDesc('created_at')
                ->take(10);

            return view('dashboard', compact(
                'stats',
                'recentProjects',
                'upcomingTasks',
                'criticalTickets',
                'upcomingMeetings',
                'recentActivity'
            ));

        } catch (\Exception $e) {
            return view('dashboard')->with('error', 'Error al cargar el dashboard: ' . $e->getMessage());
        }
    }
}
