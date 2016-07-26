<?php

namespace Trixworks\Budget\Entities;

use DateTime;

class Expense
{
    private $value;

    private $date;

    /**
     * Expense constructor.
     * @param float $value
     * @param DateTime|null $date
     */
    public function __construct(float $value, DateTime $date = null)
    {
        $this->value = $value;
        $this->date = $date ?: new DateTime();
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
}
