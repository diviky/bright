<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Filters\Ql;

class Result
{
    /**
     * @var PredicateInterface
     */
    public $predicate;
    /**
     * @var string
     */
    public $combinedBy;

    public function __construct(PredicateInterface $predicate, string $combinedBy)
    {
        $this->predicate = $predicate;
        $this->combinedBy = $combinedBy;
    }

    /**
     * Get the value of combinedBy.
     *
     * @return string
     */
    public function getCombinedBy()
    {
        return $this->combinedBy;
    }

    /**
     * Set the value of combinedBy.
     *
     * @param mixed $combinedBy
     *
     * @return self
     */
    public function setCombinedBy($combinedBy)
    {
        $this->combinedBy = $combinedBy;

        return $this;
    }

    /**
     * Get the value of predicate.
     *
     * @return PredicateInterface
     */
    public function getPredicate()
    {
        return $this->predicate;
    }

    /**
     * Set the value of predicate.
     *
     * @param mixed $predicate
     *
     * @return self
     */
    public function setPredicate($predicate)
    {
        $this->predicate = $predicate;

        return $this;
    }
}
