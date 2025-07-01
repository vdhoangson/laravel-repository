<?php

namespace Vdhoangson\LaravelRepository\Exceptions;

use Throwable;

/**
 * Class RepositoryEntityException.
 * */
class RepositoryEntityException extends \Exception
{
    /**
     * @var string
     */
    protected $message = 'Given class (:namespace) must be instance of Model class!';

    /**
     * @var int
     */
    protected $exceptionCode = 500;

    /**
     * RepositoryEntityException constructor.
     *
     * @param string|null    $namespace
     * @param Throwable|null $previous
     */
    public function __construct(
        ?string $namespace,
        ?Throwable $previous = null
    ) {
        $message = strtr(
            $this->message,
            [
                ':namespace' => $namespace,
            ]
        );
        parent::__construct(
            $message,
            $this->exceptionCode,
            $previous
        );
    }
}
