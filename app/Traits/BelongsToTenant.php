<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function booted()
    {
        // Auto asignar tenant
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });

        // Scope global
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check()) {
                $model = $builder->getModel();

                $builder->where(
                    $model->getTable() . '.tenant_id',
                    auth()->user()->tenant_id
                );
            }
        });
    }
}