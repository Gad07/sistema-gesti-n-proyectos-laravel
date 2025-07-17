<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'content_type',
        'file_path',
        'user_id',
        'is_public',
        'views_count'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'views_count' => 'integer',
    ];

    /**
     * Relación con usuario (si se implementa autenticación)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener la URL del archivo si existe
     */
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    /**
     * Obtener el tipo de contenido formateado
     */
    public function getContentTypeTextAttribute()
    {
        return match($this->content_type) {
            'article' => 'Artículo',
            'announcement' => 'Anuncio',
            'guide' => 'Guía',
            'faq' => 'FAQ',
            'tutorial' => 'Tutorial',
            'documentation' => 'Documentación',
            'template' => 'Plantilla',
            'resource' => 'Recurso',
            default => 'Contenido'
        };
    }

    /**
     * Obtener el color del tipo de contenido
     */
    public function getContentTypeColorAttribute()
    {
        return match($this->content_type) {
            'article' => '#FB0009',
            'announcement' => '#FFA500',
            'guide' => '#28A745',
            'faq' => '#17A2B8',
            'tutorial' => '#6F42C1',
            'documentation' => '#6C757D',
            'template' => '#E83E8C',
            'resource' => '#20C997',
            default => '#BBBBBB'
        };
    }

    /**
     * Incrementar contador de vistas
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Verificar si tiene archivo adjunto
     */
    public function getHasFileAttribute()
    {
        return !empty($this->file_path);
    }

    /**
     * Obtener extracto de la descripción
     */
    public function getExcerptAttribute($length = 150)
    {
        if (strlen($this->description) <= $length) {
            return $this->description;
        }

        return substr($this->description, 0, $length) . '...';
    }

    /**
     * Scope para contenido público
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope para contenido por tipo
     */
    public function scopeByType($query, $type)
    {
        return $query->where('content_type', $type);
    }

    /**
     * Scope para contenido popular (más visto)
     */
    public function scopePopular($query, $limit = 10)
    {
        return $query->orderBy('views_count', 'desc')->limit($limit);
    }

    /**
     * Scope para contenido reciente
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope para búsqueda
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
