<?php

namespace Trixworks\Budget\Entities;

use DateTime;

class Expense
{
    private $value;

    private $date;

    private $isNew;

    /**
     * Expense constructor.
     * @param float $value
     * @param DateTime|null $date
     */
    public function __construct(float $value, DateTime $date = null)
    {
        $this->value = round($value, 2);
        $this->date = $date ?: new DateTime();
        $this->isNew = true;
    }

    /**
     * @return DateTime
     */
    public function date() : DateTime
    {
        return $this->date;
    }

    /**
     * @return float
     */
    public function value() : float
    {
        return $this->value;
    }

    public function isNew()
    {
        return $this->isNew;
    }

    public function process()
    {
        $this->isNew = false;

        return $this;
    }
}
