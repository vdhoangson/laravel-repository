<?php

namespace Vdhoangson\LaravelRepository\Repositories\Criteria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class FindWhereCriteria.
 *
 */
class FindWhereCriteria extends BaseCriteria
{
    /**
     * @var array
     */
    protected $where;

    /**
     * FindWhereCriteria constructor.
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
     *
     */
    public function apply($entity)
    {
        return $entity->where($this->where);
    }
}
