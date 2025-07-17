<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Mostrar lista de proyectos
     */
    public function index()
    {
        try {
            $projects = Project::with(['tasks', 'tickets'])
                ->withCount(['tasks', 'tickets'])
                ->latest()
                ->paginate(12);

            return view('projects.index', compact('projects'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar proyectos: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Guardar nuevo proyecto
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'status' => 'required|in:active,completed,on_hold,cancelled'
            ]);

            $project = Project::create($validated);

            return redirect()->route('projects.show', $project)
                ->with('success', 'Proyecto creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear proyecto: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar proyecto específico
     */
    public function show(Project $project)
    {
        try {
            $project->load(['tasks', 'tickets', 'meetings', 'media']);

            // Estadísticas del proyecto
            $stats = [
                'total_tasks' => $project->tasks->count(),
                'completed_tasks' => $project->tasks->where('kanban_column', 'Done')->count(),
                'pending_tasks' => $project->tasks->where('kanban_column', '!=', 'Done')->count(),
                'total_tickets' => $project->tickets->count(),
                'open_tickets' => $project->tickets->where('status', 'open')->count(),
                'progress' => $project->progress
            ];

            // Tareas recientes
            $recentTasks = $project->tasks()
                ->latest()
                ->take(5)
                ->get();

            // Tickets recientes
            $recentTickets = $project->tickets()
                ->latest()
                ->take(5)
                ->get();

            return view('projects.show', compact('project', 'stats', 'recentTasks', 'recentTickets'));
        } catch (\Exception $e) {
            return redirect()->route('projects.index')
                ->with('error', 'Error al cargar proyecto: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    /**
     * Actualizar proyecto
     */
    public function update(Request $request, Project $project)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'status' => 'required|in:active,completed,on_hold,cancelled'
            ]);

            $project->update($validated);

            return redirect()->route('projects.show', $project)
                ->with('success', 'Proyecto actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar proyecto: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar proyecto
     */
    public function destroy(Project $project)
    {
        try {
            DB::transaction(function () use ($project) {
                // Eliminar archivos multimedia asociados
                foreach ($project->media as $media) {
                    if (file_exists(storage_path('app/public/' . $media->file_path))) {
                        unlink(storage_path('app/public/' . $media->file_path));
                    }
                }

                $project->delete();
            });

            return redirect()->route('projects.index')
                ->with('success', 'Proyecto eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar proyecto: ' . $e->getMessage());
        }
    }

    /**
     * Vista Kanban del proyecto
     */
    public function kanban(Project $project)
    {
        try {
            $project->load('tasks');

            $kanbanColumns = [
                'To Do' => $project->getTasksByColumn('To Do'),
                'In Progress' => $project->getTasksByColumn('In Progress'),
                'Review' => $project->getTasksByColumn('Review'),
                'Done' => $project->getTasksByColumn('Done')
            ];

            return view('projects.kanban', compact('project', 'kanbanColumns'));
        } catch (\Exception $e) {
            return redirect()->route('projects.show', $project)
                ->with('error', 'Error al cargar vista Kanban: ' . $e->getMessage());
        }
    }

    /**
     * Vista Gantt del proyecto
     */
    public function gantt(Project $project)
    {
        try {
            $tasks = $project->tasks()
                ->whereNotNull('start_date')
                ->whereNotNull('due_date')
                ->orderBy('start_date')
                ->get();

            // Calcular rango de fechas para el gráfico
            $startDate = $tasks->min('start_date') ?: now();
            $endDate = $tasks->max('due_date') ?: now()->addMonth();

            return view('projects.gantt', compact('project', 'tasks', 'startDate', 'endDate'));
        } catch (\Exception $e) {
            return redirect()->route('projects.show', $project)
                ->with('error', 'Error al cargar vista Gantt: ' . $e->getMessage());
        }
    }

    /**
     * API: Obtener datos de tareas para gráficos
     */
    public function getTasksData(Project $project)
    {
        try {
            $tasks = $project->tasks()
                ->select('id', 'name', 'start_date', 'due_date', 'kanban_column', 'priority')
                ->get();

            return response()->json([
                'success' => true,
                'tasks' => $tasks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos: ' . $e->getMessage()
            ], 500);
        }
    }
}
