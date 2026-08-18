<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'address', 'phone', 'logo', 'settings', 'smtp_config'
    , 'company_id'];

    protected $casts = [
        'settings' => 'array',
        'smtp_config' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceSetting()
    {
        return $this->hasOne(CompanyAttendanceSetting::class);
    }
}
