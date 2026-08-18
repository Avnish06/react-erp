<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Traits\HasCompany;

class DailyReport extends Model
{
    use HasFactory, HasCompany;

    protected $fillable = [
        'user_id',
        'company_id',
        'report_date',
        'tasks_completed',
        'challenges',
        'plan_tomorrow'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
