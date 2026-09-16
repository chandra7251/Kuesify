<?php

namespace App\Models\Concerns;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('organization', function (Builder $query): void {
            $organizationId = app(TenantContext::class)->id();

            $organizationId === null
                ? $query->whereRaw('1 = 0')
                : $query->where('organization_id', $organizationId);
        });
    }
}
