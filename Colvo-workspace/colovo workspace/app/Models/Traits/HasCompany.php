<?php

namespace App\Models\Traits;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;

trait HasCompany
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);
        
        static::creating(function ($model) {
            if (auth()->hasUser() && !$model->company_id) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }

    /**
     * Get the company that owns the model.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
