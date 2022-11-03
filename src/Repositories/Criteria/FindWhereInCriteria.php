<?php

namespace Vdhoangson\LaravelRepository\Repositories\Criteria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class FindWhereInCriteria.
 *
 */
class FindWhereInCriteria extends BaseCriteria
{
    /**
     * @var string
     */
    protected $column;

    /**
     * @var array
     */
    protected $in;

    /**
     * FindWhereInCriteria constructor.
     *
     * @param string $column
     * @param array  $in
     */
    public function __construct(string $column, array $in)
    {
        $this->column = $column;
        $this->in = $in;
    }

    /**
     * Apply criteria on entity.
     *
     * @param Model|Builder $entity
     *
     * @return Model|Builder
     *
     *
     */
    public function apply($entity)
    {
        return $entity->whereIn($this->column, $this->in);
    }
}
