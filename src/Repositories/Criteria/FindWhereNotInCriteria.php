<?php

namespace Vdhoangson\LaravelRepository\Repositories\Criteria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class FindWhereNotInCriteria.
 * */
class FindWhereNotInCriteria extends BaseCriteria
{
    /**
     * @var string
     */
    protected $column;

    /**
     * @var array
     */
    protected $notIn;

    /**
     * FindWhereNotInCriteria constructor.
     *
     * @param string $column
     * @param array  $notIn
     */
    public function __construct(string $column, array $notIn)
    {
        $this->column = $column;
        $this->notIn = $notIn;
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
        return $entity->whereNotIn($this->column, $this->notIn);
    }
}
