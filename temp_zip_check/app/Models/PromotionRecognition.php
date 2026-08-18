<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCompany;

class PromotionRecognition extends Model
{
    use HasCompany;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'detail',
        'amount',
        'date_awarded',
        'company_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
