<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCompany;

class Attendance extends Model
{
    use HasCompany;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'company_id',
        'check_in_photo',
        'check_out_photo',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'check_in_distance',
        'check_out_distance',
        'working_hours',
        'correction_status',
        'correction_reason',
        'requested_check_in',
        'requested_check_out'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
