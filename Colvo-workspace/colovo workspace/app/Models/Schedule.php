<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\CompanyScope;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'time_string',
        'title',
        'subtitle',
        'color',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
    }
}
