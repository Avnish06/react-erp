<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeQuery extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'user_id', 'assigned_to', 'subject', 'description', 'status'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
