<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relación con tareas
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Relación con tickets
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Relación con reuniones
     */
    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }

    /**
     * Relación polimórfica con media
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'model');
    }

    /**
     * Obtener tareas por columna de Kanban
     */
    public function getTasksByColumn($column)
    {
        return $this->tasks()
            ->where('kanban_column', $column)
            ->orderBy('position')
            ->get();
    }

    /**
     * Obtener progreso del proyecto
     */
    public function getProgressAttribute()
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            return 0;
        }
        
        $completedTasks = $this->tasks()->where('kanban_column', 'Done')->count();
        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    /**
     * Scope para proyectos activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
