<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Project;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    /**
     * Mostrar lista de reuniones
     */
    public function index(Request $request)
    {
        try {
            $query = Meeting::with(['project']);

            // Filtros
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('scheduled_time', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('scheduled_time', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            // Ordenamiento
            $sortBy = $request->get('sort', 'scheduled_time');
            $sortOrder = $request->get('order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $meetings = $query->paginate(15);
            $projects = Project::orderBy('name')->get();

            // Estadísticas
            $stats = [
                'total_meetings' => Meeting::count(),
                'scheduled_meetings' => Meeting::where('status', 'scheduled')->count(),
                'completed_meetings' => Meeting::where('status', 'completed')->count(),
                'upcoming_meetings' => Meeting::upcoming()->count(),
                'today_meetings' => Meeting::today()->count(),
            ];

            return view('meetings.index', compact('meetings', 'projects', 'stats'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar reuniones: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request)
    {
        $projects = Project::active()->orderBy('name')->get();
        $selectedProject = $request->get('project_id');
        $calendlyUrl = env('CALENDLY_EMBED_URL');

        return view('meetings.create', compact('projects', 'selectedProject', 'calendlyUrl'));
    }

    /**
     * Guardar nueva reunión
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'project_id' => 'nullable|exists:projects,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'scheduled_time' => 'required|date|after:now',
                'calendly_url' => 'nullable|url',
                'status' => 'required|in:scheduled,in_progress,completed,cancelled'
            ]);

            // Si no se proporciona URL de Calendly, usar la por defecto
            if (empty($validated['calendly_url'])) {
                $validated['calendly_url'] = env('CALENDLY_EMBED_URL');
            }

            $meeting = Meeting::create($validated);

            return redirect()->route('meetings.show', $meeting)
                ->with('success', 'Reunión creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear reunión: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar reunión específica
     */
    public function show(Meeting $meeting)
    {
        try {
            $meeting->load(['project']);

            return view('meetings.show', compact('meeting'));
        } catch (\Exception $e) {
            return redirect()->route('meetings.index')
                ->with('error', 'Error al cargar reunión: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Meeting $meeting)
    {
        $projects = Project::active()->orderBy('name')->get();

        return view('meetings.edit', compact('meeting', 'projects'));
    }

    /**
     * Actualizar reunión
     */
    public function update(Request $request, Meeting $meeting)
    {
        try {
            $validated = $request->validate([
                'project_id' => 'nullable|exists:projects,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'scheduled_time' => 'required|date',
                'calendly_url' => 'nullable|url',
                'status' => 'required|in:scheduled,in_progress,completed,cancelled'
            ]);

            // Si no se proporciona URL de Calendly, mantener la existente o usar la por defecto
            if (empty($validated['calendly_url'])) {
                $validated['calendly_url'] = $meeting->calendly_url ?: env('CALENDLY_EMBED_URL');
            }

            $meeting->update($validated);

            return redirect()->route('meetings.show', $meeting)
                ->with('success', 'Reunión actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar reunión: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar reunión
     */
    public function destroy(Meeting $meeting)
    {
        try {
            $meeting->delete();

            return redirect()->route('meetings.index')
                ->with('success', 'Reunión eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar reunión: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado de la reunión (AJAX)
     */
    public function changeStatus(Request $request, Meeting $meeting)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:scheduled,in_progress,completed,cancelled'
            ]);

            $meeting->update(['status' => $validated['status']]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Estado actualizado exitosamente',
                    'meeting' => $meeting->fresh()
                ]);
            }

            return redirect()->back()
                ->with('success', 'Estado de la reunión actualizado exitosamente.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar estado: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al actualizar estado: ' . $e->getMessage());
        }
    }

    /**
     * Vista de calendario de reuniones
     */
    public function calendar(Request $request)
    {
        try {
            $meetings = Meeting::with(['project'])
                ->when($request->filled('project_id'), function ($query) use ($request) {
                    return $query->where('project_id', $request->project_id);
                })
                ->when($request->filled('month'), function ($query) use ($request) {
                    $date = \Carbon\Carbon::createFromFormat('Y-m', $request->month);
                    return $query->whereYear('scheduled_time', $date->year)
                                 ->whereMonth('scheduled_time', $date->month);
                })
                ->orderBy('scheduled_time')
                ->get();

            $projects = Project::orderBy('name')->get();
            $currentMonth = $request->get('month', now()->format('Y-m'));

            // Formatear reuniones para el calendario
            $calendarEvents = $meetings->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'title' => $meeting->title,
                    'start' => $meeting->scheduled_time->toISOString(),
                    'backgroundColor' => $meeting->status_color,
                    'borderColor' => $meeting->status_color,
                    'url' => route('meetings.show', $meeting),
                    'extendedProps' => [
                        'project' => $meeting->project ? $meeting->project->name : null,
                        'status' => $meeting->status_text,
                        'description' => $meeting->description
                    ]
                ];
            });

            return view('meetings.calendar', compact('meetings', 'projects', 'currentMonth', 'calendarEvents'));
        } catch (\Exception $e) {
            return redirect()->route('meetings.index')
                ->with('error', 'Error al cargar calendario: ' . $e->getMessage());
        }
    }

    /**
     * Obtener reuniones para API (AJAX)
     */
    public function getMeetings(Request $request)
    {
        try {
            $query = Meeting::with(['project']);

            if ($request->filled('start') && $request->filled('end')) {
                $query->whereBetween('scheduled_time', [
                    $request->start,
                    $request->end
                ]);
            }

            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            $meetings = $query->get();

            $events = $meetings->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'title' => $meeting->title,
                    'start' => $meeting->scheduled_time->toISOString(),
                    'backgroundColor' => $meeting->status_color,
                    'borderColor' => $meeting->status_color,
                    'url' => route('meetings.show', $meeting),
                    'extendedProps' => [
                        'project' => $meeting->project ? $meeting->project->name : null,
                        'status' => $meeting->status_text,
                        'description' => $meeting->description
                    ]
                ];
            });

            return response()->json($events);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener reuniones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar reunión como completada
     */
    public function markCompleted(Meeting $meeting)
    {
        try {
            $meeting->update(['status' => 'completed']);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reunión marcada como completada'
                ]);
            }

            return redirect()->back()
                ->with('success', 'Reunión marcada como completada.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
