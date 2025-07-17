<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'scheduled_time',
        'calendly_url',
        'status'
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
    ];

    /**
     * Relación con proyecto
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Obtener el estado de la reunión
     */
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'scheduled' => 'Programada',
            'in_progress' => 'En Progreso',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
            default => 'Sin definir'
        };
    }

    /**
     * Obtener el color del estado
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'scheduled' => '#FFA500',
            'in_progress' => '#FB0009',
            'completed' => '#28A745',
            'cancelled' => '#6C757D',
            default => '#BBBBBB'
        };
    }

    /**
     * Verificar si la reunión es próxima (en las próximas 24 horas)
     */
    public function getIsUpcomingAttribute()
    {
        return $this->scheduled_time && 
               $this->scheduled_time->isFuture() && 
               $this->scheduled_time->diffInHours(now()) <= 24;
    }

    /**
     * Verificar si la reunión ya pasó
     */
    public function getIsPastAttribute()
    {
        return $this->scheduled_time && $this->scheduled_time->isPast();
    }

    /**
     * Obtener tiempo restante hasta la reunión
     */
    public function getTimeUntilMeetingAttribute()
    {
        if (!$this->scheduled_time || $this->scheduled_time->isPast()) {
            return null;
        }

        return $this->scheduled_time->diffForHumans();
    }

    /**
     * Obtener la URL de Calendly completa
     */
    public function getFullCalendlyUrlAttribute()
    {
        if ($this->calendly_url) {
            return $this->calendly_url;
        }

        return config('app.calendly_embed_url', env('CALENDLY_EMBED_URL'));
    }

    /**
     * Scope para reuniones programadas
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope para reuniones próximas
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_time', '>', now())
                    ->where('scheduled_time', '<=', now()->addDay())
                    ->where('status', 'scheduled');
    }

    /**
     * Scope para reuniones de hoy
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_time', today());
    }

    /**
     * Scope para reuniones de esta semana
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('scheduled_time', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }
}
