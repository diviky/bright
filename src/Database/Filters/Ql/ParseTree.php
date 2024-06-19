<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Filters\Ql;

class ParseTree
{
    public const COMBINED_BY_AND = 'AND';

    public const COMBINED_BY_OR = 'OR';

    public const COMBINED_BY_IN = 'IN';

    /**
     * @var array
     */
    public $predicates = [];

    /**
     * @var null|ParseTree
     */
    protected $nestParent;

    final public function __construct() {}

    /**
     * @return array
     */
    public function getPredicates()
    {
        return $this->predicates;
    }

    /**
     * @return self
     */
    public function setPredicates(array $predicates)
    {
        $this->predicates[] = $predicates;

        return $this;
    }

    public function addPredicate(PredicateInterface $predicate, string $combinedBy = self::COMBINED_BY_AND): void
    {
        if (!in_array($combinedBy, [self::COMBINED_BY_AND, self::COMBINED_BY_OR, self::COMBINED_BY_IN])) {
            throw new \InvalidArgumentException('Must be combined by AND or OR or IN');
        }

        $this->predicates[] = new Result($predicate, $combinedBy);
    }

    /**
     * @return ParseTree
     */
    public function nest()
    {
        $parseTree = new static();
        $parseTree->nestParent = $this;

        return $parseTree;
    }

    /**
     * @return null|ParseTree
     */
    public function unnest()
    {
        $parent = $this->nestParent;

        if (isset($parent)) {
            $parent->setPredicates($this->predicates);
        }

        $this->nestParent = null;

        return $parent;
    }
}
