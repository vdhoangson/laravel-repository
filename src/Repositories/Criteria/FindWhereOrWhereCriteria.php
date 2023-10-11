<?php

namespace Vdhoangson\LaravelRepository\Repositories\Criteria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class FindWhereOrWhereCriteria.
 *
 */
class FindWhereOrWhereCriteria extends BaseCriteria
{
    /**
     * @var array
     */
    protected $where;

    /**
     * @var array
     */
    protected $orWhere;

    /**
     * FindWhereCriteria constructor.
     *
     * @param array $where
     * @param array $orWhere
     */
    public function __construct(array $where, array $orWhere = [])
    {
        $this->where = $where;
        $this->orWhere = $orWhere;
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
        return $entity->where(function ($query) {
            $query->where($this->where);

            foreach ($this->orWhere as $where) {
                $query->orWhere($where);
            }
        });
    }
}