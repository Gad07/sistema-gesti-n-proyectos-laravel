<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    /**
     * Mostrar lista de contenido
     */
    public function index(Request $request)
    {
        try {
            $query = Content::query();

            // Filtros
            if ($request->filled('content_type')) {
                $query->where('content_type', $request->content_type);
            }

            if ($request->filled('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            // Solo contenido público por defecto
            if (!$request->filled('show_all')) {
                $query->public();
            }

            // Ordenamiento
            $sortBy = $request->get('sort', 'created_at');
            $sortOrder = $request->get('order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $contents = $query->paginate(12);

            // Estadísticas
            $stats = [
                'total_contents' => Content::count(),
                'public_contents' => Content::public()->count(),
                'articles' => Content::byType('article')->count(),
                'guides' => Content::byType('guide')->count(),
                'tutorials' => Content::byType('tutorial')->count(),
            ];

            return view('contents.index', compact('contents', 'stats'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar contenido: ' . $e->getMessage());
        }
    }

    /**
     * Vista pública de contenido
     */
    public function publicIndex(Request $request)
    {
        try {
            $query = Content::public();

            // Filtros
            if ($request->filled('content_type')) {
                $query->where('content_type', $request->content_type);
            }

            if ($request->filled('search')) {
                $query->search($request->search);
            }

            // Ordenamiento
            $sortBy = $request->get('sort', 'created_at');
            $query->orderBy($sortBy, 'desc');

            $contents = $query->paginate(12);

            // Contenido popular
            $popularContents = Content::public()->popular(5)->get();

            // Contenido reciente
            $recentContents = Content::public()->recent(7)->take(5)->get();

            // Tipos de contenido disponibles
            $contentTypes = Content::public()
                ->select('content_type')
                ->groupBy('content_type')
                ->pluck('content_type');

            return view('contents.public', compact(
                'contents', 
                'popularContents', 
                'recentContents', 
                'contentTypes'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar contenido público: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('contents.create');
    }

    /**
     * Guardar nuevo contenido
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'content_type' => 'required|in:article,announcement,guide,faq,tutorial,documentation,template,resource',
                'file' => 'nullable|file|max:10240', // 10MB máximo
                'is_public' => 'boolean'
            ]);

            // Manejar archivo si se proporciona
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('contents', $fileName, 'public');
                $validated['file_path'] = $filePath;
            }

            $validated['is_public'] = $request->has('is_public');
            $validated['views_count'] = 0;

            $content = Content::create($validated);

            return redirect()->route('contents.show', $content)
                ->with('success', 'Contenido creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear contenido: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar contenido específico
     */
    public function show(Content $content)
    {
        try {
            // Incrementar contador de vistas
            $content->incrementViews();

            return view('contents.show', compact('content'));
        } catch (\Exception $e) {
            return redirect()->route('contents.index')
                ->with('error', 'Error al cargar contenido: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Content $content)
    {
        return view('contents.edit', compact('content'));
    }

    /**
     * Actualizar contenido
     */
    public function update(Request $request, Content $content)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'content_type' => 'required|in:article,announcement,guide,faq,tutorial,documentation,template,resource',
                'file' => 'nullable|file|max:10240',
                'is_public' => 'boolean'
            ]);

            // Manejar archivo si se proporciona uno nuevo
            if ($request->hasFile('file')) {
                // Eliminar archivo anterior si existe
                if ($content->file_path && Storage::disk('public')->exists($content->file_path)) {
                    Storage::disk('public')->delete($content->file_path);
                }

                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('contents', $fileName, 'public');
                $validated['file_path'] = $filePath;
            }

            $validated['is_public'] = $request->has('is_public');

            $content->update($validated);

            return redirect()->route('contents.show', $content)
                ->with('success', 'Contenido actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar contenido: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar contenido
     */
    public function destroy(Content $content)
    {
        try {
            // Eliminar archivo asociado si existe
            if ($content->file_path && Storage::disk('public')->exists($content->file_path)) {
                Storage::disk('public')->delete($content->file_path);
            }

            $content->delete();

            return redirect()->route('contents.index')
                ->with('success', 'Contenido eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar contenido: ' . $e->getMessage());
        }
    }

    /**
     * Descargar archivo de contenido
     */
    public function download(Content $content)
    {
        try {
            if (!$content->file_path || !Storage::disk('public')->exists($content->file_path)) {
                return redirect()->back()
                    ->with('error', 'El archivo no existe.');
            }

            return Storage::disk('public')->download($content->file_path, basename($content->file_path));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al descargar archivo: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar visibilidad del contenido (AJAX)
     */
    public function toggleVisibility(Content $content)
    {
        try {
            $content->update(['is_public' => !$content->is_public]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Visibilidad actualizada exitosamente',
                    'is_public' => $content->is_public
                ]);
            }

            return redirect()->back()
                ->with('success', 'Visibilidad del contenido actualizada.');
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

    /**
     * Buscar contenido (AJAX)
     */
    public function search(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2'
            ]);

            $contents = Content::public()
                ->search($request->query)
                ->take(10)
                ->get(['id', 'title', 'content_type', 'created_at']);

            return response()->json([
                'success' => true,
                'contents' => $contents
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener contenido por tipo (AJAX)
     */
    public function getByType(Request $request)
    {
        try {
            $request->validate([
                'content_type' => 'required|string'
            ]);

            $contents = Content::public()
                ->byType($request->content_type)
                ->latest()
                ->take(10)
                ->get(['id', 'title', 'description', 'created_at']);

            return response()->json([
                'success' => true,
                'contents' => $contents
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de contenido (AJAX)
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_contents' => Content::count(),
                'public_contents' => Content::public()->count(),
                'total_views' => Content::sum('views_count'),
                'content_types' => Content::select('content_type')
                    ->selectRaw('COUNT(*) as count')
                    ->groupBy('content_type')
                    ->get(),
                'popular_contents' => Content::public()
                    ->orderBy('views_count', 'desc')
                    ->take(5)
                    ->get(['id', 'title', 'views_count']),
                'recent_contents' => Content::public()
                    ->latest()
                    ->take(5)
                    ->get(['id', 'title', 'created_at'])
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
