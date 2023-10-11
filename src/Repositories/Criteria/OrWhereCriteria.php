<?php

namespace Vdhoangson\LaravelRepository\Repositories\Criteria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class OrWhereCriteria.
 *
 */
class OrWhereCriteria extends BaseCriteria
{
    /**
     * @var array
     */
    protected $where;

    /**
     * OrWhereCriteria constructor.
     *
     * @param array $where
     */
    public function __construct(array $where)
    {
        $this->where = $where;
    }

    /**
     * Apply criteria on entity.
     *
     * @param Model|Builder $entity
     *
     * @return Model|Builder
     */
    public function apply($entity)
    {
        return $entity->orWhere($this->where);
    }
}