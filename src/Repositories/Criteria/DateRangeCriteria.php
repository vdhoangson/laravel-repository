<?php
/**
 * DateRangeCriteria
 * 
 * @package Vdhoangson\LaravelRepository\Repositories\Criteria
 * @author vdhoangson <vdhoangson@gmail.com>
 * @link https://github.com/vdhoangson/laravel-repository
 */
namespace Vdhoangson\LaravelRepository\Repositories\Criteria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Class DateRangeCriteria.
 *
 */
class DateRangeCriteria extends BaseCriteria
{
    /**
     * Date from.
     *
     * @var Carbon
     */
    protected ?Carbon $dateFrom;

    /**
     * Date to.
     *
     * @var Carbon
     */
    protected ?Carbon $dateTo;

    /**
     * Column name to search.
     *
     * @var string
     */
    protected string $column;

    /**
     * DateRangeCriteria constructor.
     *
     * @param null|string $dateFrom
     * @param null|string $dateTo
     * @param string      $column
     */
    public function __construct(
        string $column = Model::CREATED_AT,
        ?string $dateFrom,
        ?string $dateTo
    ) {
        $this->column = $column;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    /**
     * Apply criteria on entity.
     *
     * @param Model|Builder $entity
     *
     * @return Model|Builder
     */
    public function apply(Model|Builder $entity): Model|Builder
    {
        if ($this->dateFrom === null) {
            $entity->whereDate($this->column, '<=', $this->dateTo);
        } else {
            if ($this->dateTo === null) {
                $entity->whereDate($this->column, '>=', $this->dateFrom);
            } else {
                $entity->whereBetween($this->column, [$this->dateFrom, $this->dateTo]);
            }
        }

        return $entity;
    }
}
