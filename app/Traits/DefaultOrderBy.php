<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait DefaultOrderBy
{
    protected static function bootDefaultOrderBy()
    {
        if (empty(static::$orderByColumn)) {
            return;
        }

        $column = static::$orderByColumn;
        $direction = static::$orderByColumnDirection ?? 'asc';

        static::addGlobalScope('default_order_by', function (Builder $builder) use ($column, $direction) {
            $builder->orderBy($column, $direction);
        });
    }
}