<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCompany;

class Leave extends Model
{
    use HasCompany;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'company_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
