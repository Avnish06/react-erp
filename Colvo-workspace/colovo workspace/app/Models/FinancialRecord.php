<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCompany;

class FinancialRecord extends Model
{
    use HasCompany;
    use HasFactory;

    protected $fillable = [
        'year',
        'earnings',
        'expenditures',
        'summary',
        'company_id'
    ];
}
