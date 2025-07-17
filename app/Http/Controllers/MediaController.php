<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Subir archivo multimedia
     */
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240', // 10MB máximo
                'model_type' => 'required|string',
                'model_id' => 'required|integer'
            ]);

            $file = $request->file('file');
            
            // Validar tipos de archivo permitidos
            $allowedMimes = [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain',
                'video/mp4', 'video/avi', 'video/mov'
            ];

            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de archivo no permitido.'
                ], 422);
            }

            // Generar nombre único para el archivo
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            
            // Determinar carpeta según el tipo de modelo
            $folder = match($request->model_type) {
                'App\Models\Project' => 'projects',
                'App\Models\Task' => 'tasks',
                'App\Models\Ticket' => 'tickets',
                'App\Models\Content' => 'contents',
                default => 'general'
            };

            // Subir archivo
            $filePath = $file->storeAs("media/{$folder}", $fileName, 'public');

            // Crear registro en base de datos
            $media = Media::create([
                'model_type' => $request->model_type,
                'model_id' => $request->model_id,
                'file_path' => $filePath,
                'file_type' => $file->getMimeType(),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Archivo subido exitosamente',
                    'media' => $media
                ]);
            }

            return redirect()->back()
                ->with('success', 'Archivo subido exitosamente.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al subir archivo: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al subir archivo: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar archivo multimedia
     */
    public function destroy(Media $media)
    {
        try {
            // Eliminar archivo físico
            if (Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }

            // Eliminar registro de base de datos
            $media->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Archivo eliminado exitosamente'
                ]);
            }

            return redirect()->back()
                ->with('success', 'Archivo eliminado exitosamente.');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar archivo: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error al eliminar archivo: ' . $e->getMessage());
        }
    }

    /**
     * Descargar archivo
     */
    public function download(Media $media)
    {
        try {
            if (!Storage::disk('public')->exists($media->file_path)) {
                return redirect()->back()
                    ->with('error', 'El archivo no existe.');
            }

            return Storage::disk('public')->download($media->file_path, $media->file_name);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al descargar archivo: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar archivo (para imágenes)
     */
    public function show(Media $media)
    {
        try {
            if (!Storage::disk('public')->exists($media->file_path)) {
                abort(404, 'Archivo no encontrado');
            }

            $file = Storage::disk('public')->get($media->file_path);
            
            return response($file, 200)
                ->header('Content-Type', $media->file_type)
                ->header('Content-Disposition', 'inline; filename="' . $media->file_name . '"');

        } catch (\Exception $e) {
            abort(500, 'Error al mostrar archivo');
        }
    }

    /**
     * Obtener información de archivos por modelo (AJAX)
     */
    public function getByModel(Request $request)
    {
        try {
            $request->validate([
                'model_type' => 'required|string',
                'model_id' => 'required|integer'
            ]);

            $media = Media::where('model_type', $request->model_type)
                         ->where('model_id', $request->model_id)
                         ->orderBy('created_at', 'desc')
                         ->get();

            return response()->json([
                'success' => true,
                'media' => $media
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener archivos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subida múltiple de archivos (AJAX)
     */
    public function uploadMultiple(Request $request)
    {
        try {
            $request->validate([
                'files' => 'required|array',
                'files.*' => 'file|max:10240',
                'model_type' => 'required|string',
                'model_id' => 'required|integer'
            ]);

            $uploadedFiles = [];
            $errors = [];

            foreach ($request->file('files') as $file) {
                try {
                    // Validar tipo de archivo
                    $allowedMimes = [
                        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/plain',
                        'video/mp4', 'video/avi', 'video/mov'
                    ];

                    if (!in_array($file->getMimeType(), $allowedMimes)) {
                        $errors[] = "Archivo {$file->getClientOriginalName()}: tipo no permitido";
                        continue;
                    }

                    // Generar nombre único
                    $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    
                    // Determinar carpeta
                    $folder = match($request->model_type) {
                        'App\Models\Project' => 'projects',
                        'App\Models\Task' => 'tasks',
                        'App\Models\Ticket' => 'tickets',
                        'App\Models\Content' => 'contents',
                        default => 'general'
                    };

                    // Subir archivo
                    $filePath = $file->storeAs("media/{$folder}", $fileName, 'public');

                    // Crear registro
                    $media = Media::create([
                        'model_type' => $request->model_type,
                        'model_id' => $request->model_id,
                        'file_path' => $filePath,
                        'file_type' => $file->getMimeType(),
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize()
                    ]);

                    $uploadedFiles[] = $media;

                } catch (\Exception $e) {
                    $errors[] = "Archivo {$file->getClientOriginalName()}: {$e->getMessage()}";
                }
            }

            return response()->json([
                'success' => count($uploadedFiles) > 0,
                'message' => count($uploadedFiles) . ' archivo(s) subido(s) exitosamente',
                'uploaded_files' => $uploadedFiles,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir archivos: ' . $e->getMessage()
            ], 500);
        }
    }
}
