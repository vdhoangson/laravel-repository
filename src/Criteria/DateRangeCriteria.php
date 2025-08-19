<?php
/**
 * DateRangeCriteria
 *
 * @package Vdhoangson\LaravelRepository\Criteria
 * @author vdhoangson <vdhoangson@gmail.com>
 * @link https://github.com/vdhoangson/laravel-repository
 */
namespace Vdhoangson\LaravelRepository\Criteria;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class DateRangeCriteria.
 *
 */
class DateRangeCriteria extends BaseCriteria
{
    /**
     * Date from.
     *
     * @var string
     */
    protected ?string $dateFrom;

    /**
     * Date to.
     *
     * @var string
     */
    protected ?string $dateTo;

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
     * @param $entity
     *
     * @return Model|Builder
     */
    public function apply($entity): Model|Builder
    {
        if ($this->dateFrom === null) {
            $entity = $entity->whereDate($this->column, '<=', $this->dateTo);
        } else {
            if ($this->dateTo === null) {
                $entity = $entity->whereDate($this->column, '>=', $this->dateFrom);
            } else {
                $entity = $entity->whereBetween($this->column, [$this->dateFrom, $this->dateTo]);
            }
        }

        return $entity;
    }
}
