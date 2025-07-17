<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Mostrar formulario de creación de tarea
     */
    public function create(Project $project)
    {
        return view('tasks.create', compact('project'));
    }

    /**
     * Guardar nueva tarea
     */
    public function store(Request $request, Project $project)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'due_date' => 'nullable|date|after_or_equal:start_date',
                'priority' => 'required|in:low,medium,high',
                'kanban_column' => 'required|string'
            ]);

            // Obtener la siguiente posición en la columna
            $nextPosition = Task::where('project_id', $project->id)
                ->where('kanban_column', $validated['kanban_column'])
                ->max('position') + 1;

            $validated['project_id'] = $project->id;
            $validated['position'] = $nextPosition;

            $task = Task::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tarea creada exitosamente',
                    'task' => $task->load('project')
                ]);
            }

            return redirect()->route('projects.show', $project)
                ->with('success', 'Tarea creada exitosamente.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear tarea: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear tarea: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Project $project, Task $task)
    {
        return view('tasks.edit', compact('project', 'task'));
    }

    /**
     * Actualizar tarea
     */
    public function update(Request $request, Project $project, Task $task)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'due_date' => 'nullable|date|after_or_equal:start_date',
                'priority' => 'required|in:low,medium,high',
                'kanban_column' => 'required|string'
            ]);

            // Si cambió de columna, actualizar posición
            if ($task->kanban_column !== $validated['kanban_column']) {
                $nextPosition = Task::where('project_id', $project->id)
                    ->where('kanban_column', $validated['kanban_column'])
                    ->max('position') + 1;
                $validated['position'] = $nextPosition;
            }

            $task->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tarea actualizada exitosamente',
                    'task' => $task->fresh()->load('project')
                ]);
            }

            return redirect()->route('projects.show', $project)
                ->with('success', 'Tarea actualizada exitosamente.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar tarea: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar tarea: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar tarea
     */
    public function destroy(Project $project, Task $task)
    {
        try {
            DB::transaction(function () use ($task) {
                // Eliminar archivos multimedia asociados
                foreach ($task->media as $media) {
                    if (file_exists(storage_path('app/public/' . $media->file_path))) {
                        unlink(storage_path('app/public/' . $media->file_path));
                    }
                }

                $task->delete();
            });

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tarea eliminada exitosamente'
                ]);
            }

            return redirect()->route('projects.show', $project)
                ->with('success', 'Tarea eliminada exitosamente.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar tarea: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al eliminar tarea: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar columna de Kanban (AJAX)
     */
    public function updateColumn(Request $request, Task $task)
    {
        try {
            $validated = $request->validate([
                'kanban_column' => 'required|string',
                'position' => 'required|integer|min:0'
            ]);

            DB::transaction(function () use ($task, $validated) {
                $oldColumn = $task->kanban_column;
                $newColumn = $validated['kanban_column'];
                $newPosition = $validated['position'];

                // Si cambió de columna
                if ($oldColumn !== $newColumn) {
                    // Reordenar tareas en la columna anterior
                    Task::where('project_id', $task->project_id)
                        ->where('kanban_column', $oldColumn)
                        ->where('position', '>', $task->position)
                        ->decrement('position');

                    // Hacer espacio en la nueva columna
                    Task::where('project_id', $task->project_id)
                        ->where('kanban_column', $newColumn)
                        ->where('position', '>=', $newPosition)
                        ->increment('position');
                } else {
                    // Reordenar dentro de la misma columna
                    if ($newPosition > $task->position) {
                        Task::where('project_id', $task->project_id)
                            ->where('kanban_column', $newColumn)
                            ->whereBetween('position', [$task->position + 1, $newPosition])
                            ->decrement('position');
                    } else {
                        Task::where('project_id', $task->project_id)
                            ->where('kanban_column', $newColumn)
                            ->whereBetween('position', [$newPosition, $task->position - 1])
                            ->increment('position');
                    }
                }

                // Actualizar la tarea
                $task->update([
                    'kanban_column' => $newColumn,
                    'position' => $newPosition
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Tarea movida exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mover tarea: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar posición de tarea (AJAX)
     */
    public function updatePosition(Request $request, Task $task)
    {
        try {
            $validated = $request->validate([
                'position' => 'required|integer|min:0'
            ]);

            $newPosition = $validated['position'];
            $oldPosition = $task->position;

            DB::transaction(function () use ($task, $newPosition, $oldPosition) {
                if ($newPosition > $oldPosition) {
                    // Mover hacia abajo
                    Task::where('project_id', $task->project_id)
                        ->where('kanban_column', $task->kanban_column)
                        ->whereBetween('position', [$oldPosition + 1, $newPosition])
                        ->decrement('position');
                } else {
                    // Mover hacia arriba
                    Task::where('project_id', $task->project_id)
                        ->where('kanban_column', $task->kanban_column)
                        ->whereBetween('position', [$newPosition, $oldPosition - 1])
                        ->increment('position');
                }

                $task->update(['position' => $newPosition]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Posición actualizada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar posición: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reordenar tareas (AJAX)
     */
    public function reorder(Request $request)
    {
        try {
            $validated = $request->validate([
                'tasks' => 'required|array',
                'tasks.*.id' => 'required|exists:tasks,id',
                'tasks.*.kanban_column' => 'required|string',
                'tasks.*.position' => 'required|integer|min:0'
            ]);

            DB::transaction(function () use ($validated) {
                foreach ($validated['tasks'] as $taskData) {
                    Task::where('id', $taskData['id'])
                        ->update([
                            'kanban_column' => $taskData['kanban_column'],
                            'position' => $taskData['position']
                        ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Tareas reordenadas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reordenar tareas: ' . $e->getMessage()
            ], 500);
        }
    }
}
