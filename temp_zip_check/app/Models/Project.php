<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCompany;

class Project extends Model
{
    use HasCompany;
    use HasFactory;

    protected $fillable = ['title', 'description', 'status', 'budget', 'company_id', 'deadline', 'last_activity_at'];

    protected $appends = ['progress', 'calculated_status'];

    public function getProgressAttribute()
    {
        $total = $this->tasks()->count();
        if ($total == 0) return 0;
        $completed = $this->tasks()->where('status', 'completed')->count();
        return (int) round(($completed / $total) * 100);
    }

    public function getCalculatedStatusAttribute()
    {
        $progress = $this->progress;
        if ($progress == 0) return 'Not Started';
        if ($progress >= 1 && $progress <= 25) return 'Started';
        if ($progress >= 26 && $progress <= 50) return 'In Progress';
        if ($progress >= 51 && $progress <= 75) return 'Almost Completed';
        if ($progress >= 76 && $progress <= 99) return 'Near Completion';
        return 'Completed';
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
