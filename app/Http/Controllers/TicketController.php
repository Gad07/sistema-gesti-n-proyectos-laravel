<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Mostrar lista de tickets
     */
    public function index(Request $request)
    {
        try {
            $query = Ticket::with(['project']);

            // Filtros
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            // Ordenamiento
            $sortBy = $request->get('sort', 'created_at');
            $sortOrder = $request->get('order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $tickets = $query->paginate(15);
            $projects = Project::orderBy('name')->get();

            return view('tickets.index', compact('tickets', 'projects'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar tickets: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request)
    {
        $projects = Project::active()->orderBy('name')->get();
        $selectedProject = $request->get('project_id');

        return view('tickets.create', compact('projects', 'selectedProject'));
    }

    /**
     * Guardar nuevo ticket
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'priority' => 'required|in:low,medium,high,critical',
                'status' => 'required|in:open,in_progress,resolved,closed'
            ]);

            $ticket = Ticket::create($validated);

            return redirect()->route('tickets.show', $ticket)
                ->with('success', 'Ticket creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear ticket: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar ticket específico
     */
    public function show(Ticket $ticket)
    {
        try {
            $ticket->load(['project', 'media']);

            return view('tickets.show', compact('ticket'));
        } catch (\Exception $e) {
            return redirect()->route('tickets.index')
                ->with('error', 'Error al cargar ticket: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Ticket $ticket)
    {
        $projects = Project::active()->orderBy('name')->get();

        return view('tickets.edit', compact('ticket', 'projects'));
    }

    /**
     * Actualizar ticket
     */
    public function update(Request $request, Ticket $ticket)
    {
        try {
            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'priority' => 'required|in:low,medium,high,critical',
                'status' => 'required|in:open,in_progress,resolved,closed'
            ]);

            $ticket->update($validated);

            return redirect()->route('tickets.show', $ticket)
                ->with('success', 'Ticket actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar ticket: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar ticket
     */
    public function destroy(Ticket $ticket)
    {
        try {
            DB::transaction(function () use ($ticket) {
                // Eliminar archivos multimedia asociados
                foreach ($ticket->media as $media) {
                    if (file_exists(storage_path('app/public/' . $media->file_path))) {
                        unlink(storage_path('app/public/' . $media->file_path));
                    }
                }

                $ticket->delete();
            });

            return redirect()->route('tickets.index')
                ->with('success', 'Ticket eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar ticket: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado del ticket (AJAX)
     */
    public function changeStatus(Request $request, Ticket $ticket)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:open,in_progress,resolved,closed'
            ]);

            $ticket->update(['status' => $validated['status']]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Estado actualizado exitosamente',
                    'ticket' => $ticket->fresh()
                ]);
            }

            return redirect()->back()
                ->with('success', 'Estado del ticket actualizado exitosamente.');
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
}
