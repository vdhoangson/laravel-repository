<?php

namespace Vdhoangson\LaravelRepository\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Interface BaseCriteriaInterface.
 * */
interface BaseCriteriaInterface
{
    /**
     * Apply criteria on entity.
     *
     * @param Model|Builder $entity
     *
     * @return Model|Builder
     */
    public function apply($entity);
}