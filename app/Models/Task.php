<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'start_date',
        'due_date',
        'priority',
        'kanban_column',
        'position'
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Relación con proyecto
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
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
            'high' => '#FB0009',
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
            'high' => 'Alta',
            'medium' => 'Media',
            'low' => 'Baja',
            default => 'Sin definir'
        };
    }

    /**
     * Verificar si la tarea está vencida
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date->isPast() && $this->kanban_column !== 'Done';
    }

    /**
     * Obtener días restantes
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->due_date) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Scope para tareas por columna
     */
    public function scopeInColumn($query, $column)
    {
        return $query->where('kanban_column', $column);
    }

    /**
     * Scope para tareas por prioridad
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope para tareas vencidas
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('kanban_column', '!=', 'Done');
    }
}
