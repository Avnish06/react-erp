<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'office_latitude',
        'office_longitude',
        'allowed_radius',
        'attendance_photo_required',
        'location_required',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
