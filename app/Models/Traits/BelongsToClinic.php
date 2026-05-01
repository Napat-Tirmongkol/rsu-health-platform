<?php

namespace App\Models\Traits;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToClinic
{
    protected static function bootBelongsToClinic(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            if (is_null($model->clinic_id)) {
                $model->clinic_id = currentClinicId();
            }
        });
    }
}
