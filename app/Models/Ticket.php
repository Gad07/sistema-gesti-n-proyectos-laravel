<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'user_id',
        'priority',
        'status'
    ];

    /**
     * Relación con proyecto
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relación con usuario (si se implementa autenticación)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación polimórfica con media
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }

    /**
     * Obtener el color de prioridad
     */
    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'critical' => '#FB0009',
            'high' => '#FF6B35',
            'medium' => '#FFA500',
            'low' => '#28A745',
            default => '#BBBBBB'
        };
    }

    /**
     * Obtener el texto de prioridad
     */
    public function getPriorityTextAttribute()
    {
        return match($this->priority) {
            'critical' => 'Crítica',
            'high' => 'Alta',
            'medium' => 'Media',
            'low' => 'Baja',
            default => 'Sin definir'
        };
    }

    /**
     * Obtener el color del estado
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'open' => '#FB0009',
            'in_progress' => '#FFA500',
            'resolved' => '#28A745',
            'closed' => '#6C757D',
            default => '#BBBBBB'
        };
    }

    /**
     * Obtener el texto del estado
     */
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'open' => 'Abierto',
            'in_progress' => 'En Progreso',
            'resolved' => 'Resuelto',
            'closed' => 'Cerrado',
            default => 'Sin definir'
        };
    }

    /**
     * Scope para tickets abiertos
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope para tickets en progreso
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope para tickets por prioridad
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope para tickets críticos
     */
    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }
}
