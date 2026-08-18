<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCompany;

class PerformanceReview extends Model
{
    use HasCompany;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reviewer_id',
        'review_period',
        'score',
        'evaluation',
        'classification',
        'action_plan',
        'company_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
