<?php

namespace Vdhoangson\LaravelRepository\Repositories\Criteria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class DateCriteria.
 *
 */
class DateCriteria extends BaseCriteria
{
    /**
     * Date from.
     *
     * @var string
     */
    private $dateFrom;

    /**
     * Date to.
     *
     * @var string
     */
    private $dateTo;

    /**
     * Column name to search.
     *
     * @var string
     */
    private $column;

    /**
     * DateCriteria constructor.
     *
     * @param null|string $dateFrom
     * @param null|string $dateTo
     * @param string      $column
     */
    public function __construct(
        ?string $dateFrom,
        ?string $dateTo,
        string $column = Model::CREATED_AT
    ) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->column = $column;
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
        if ($this->dateFrom !== null) {
            $entity = $entity->where($this->column, '>=', $this->dateFrom);
        }

        if ($this->dateTo !== null) {
            $entity = $entity->where($this->column, '<=', $this->dateTo);
        }

        return $entity;
    }
}