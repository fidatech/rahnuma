<?php
namespace App\Models\Concerns;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
trait BelongsToTenant {
 protected static function bootBelongsToTenant():void {
   static::addGlobalScope('tenant',function(Builder $builder){ if(TenantContext::id()) $builder->where($builder->getModel()->getTable().'.agency_id',TenantContext::id()); });
   static::creating(function($model){ if(!$model->agency_id && TenantContext::id()) $model->agency_id=TenantContext::id(); });
 }
}
