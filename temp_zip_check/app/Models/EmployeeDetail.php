<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDetail extends Model
{
    protected $fillable = [
        'user_id',
        'father_name',
        'mother_name',
        'father_occupation',
        'bank_name',
        'bank_account_no',
        'bank_ifsc',
        'marksheet_10th_path',
        'marksheet_12th_path',
        'passport_photo_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
