<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type',
        'model_id',
        'file_path',
        'file_type',
        'file_name',
        'file_size'
    ];

    /**
     * Relación polimórfica
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Obtener la URL completa del archivo
     */
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Obtener el tamaño del archivo formateado
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /**
     * Verificar si es una imagen
     */
    public function getIsImageAttribute()
    {
        return in_array($this->file_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Verificar si es un video
     */
    public function getIsVideoAttribute()
    {
        return strpos($this->file_type, 'video/') === 0;
    }

    /**
     * Verificar si es un documento
     */
    public function getIsDocumentAttribute()
    {
        return in_array($this->file_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }

    /**
     * Obtener el icono según el tipo de archivo
     */
    public function getIconAttribute()
    {
        if ($this->is_image) {
            return '🖼️';
        } elseif ($this->is_video) {
            return '🎥';
        } elseif ($this->is_document) {
            return '📄';
        } else {
            return '📎';
        }
    }

    /**
     * Scope para imágenes
     */
    public function scopeImages($query)
    {
        return $query->where('file_type', 'like', 'image/%');
    }

    /**
     * Scope para videos
     */
    public function scopeVideos($query)
    {
        return $query->where('file_type', 'like', 'video/%');
    }

    /**
     * Scope para documentos
     */
    public function scopeDocuments($query)
    {
        return $query->whereIn('file_type', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }
}
