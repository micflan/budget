<?php

namespace Trixworks\Budget\Entities;

use DateTime;

class DateRange
{
    private $start;

    private $end;

    /**
     * DateRange constructor.
     * @param DateTime $start
     * @param DateTime $end
     */
    public function __construct(DateTime $start, DateTime $end)
    {
        $this->start = $start->setTime( 0, 0, 0 );
        $this->end = $end->setTime( 23, 59, 59 );
    }

    /**
     * @return DateTime
     */
    public function start() : DateTime
    {
        return $this->start;
    }

    /**
     * @return DateTime
     */
    public function end() : DateTime
    {
        return $this->end;
    }

    /**
     * @param DateTime $date
     * @return bool
     */
    public function containsDate(DateTime $date)
    {
        return $date >= $this->start and $date <= $this->end;
    }

    public function days()
    {
        return $this->start()->diff($this->end())->days + 1;
    }
}
