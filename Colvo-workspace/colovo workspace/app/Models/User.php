<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_id',
        'department',
        'position',
        'salary',
        'profile_photo',
        'face_descriptor',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function performanceReviews()
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function promotionsRecognitions()
    {
        return $this->hasMany(PromotionRecognition::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin()
    {
        return $this->role === 'admin' || $this->role === 'superadmin';
    }

    public function isEmployee()
    {
        return $this->role === 'employee';
    }

    public function detail()
    {
        return $this->hasOne(EmployeeDetail::class);
    }
}
