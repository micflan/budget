<?php

namespace Trixworks\Budget\Entities;

use DateTime;
use Trixworks\Budget\Exceptions\Date as DateException;

class Budget
{
    private $dateRange;

    private $cash;

    private $expenses;

    /**
     * Budget constructor.
     * @param DateRange $dateRange
     * @param float $cash
     */
    public function __construct(DateRange $dateRange, float $cash)
    {
        $this->dateRange = $dateRange;
        $this->cash = $cash;
        $this->expenses = new ExpenseCollection;
    }

    /**
     * @param float $cash
     * @param DateTime $date
     * @return Budget
     * @throws DateException
     */
    public function spend(float $cash, DateTime $date = null) : self
    {
        $this->expenses->add(new Expense($cash, $this->validDate($date ?: new DateTime())));

        return $this;
    }

    /**
     * @param DateTime $date
     * @param float $cash
     * @return Budget
     * @throws DateException
     */
    public function spendOnDate(DateTime $date, float $cash) : self
    {
        return $this->spend($cash, $date);
    }

    /**
     * @param DateTime $date
     * @return float
     */
    public function savings(DateTime $date = null) : float
    {
        return $this->cash - $this->expenses->spentUntilDate($this->validDate($date ?: new DateTime()));
    }

    /**
     * @param DateTime $date
     * @return float
     */
    public function savingsOnDate(DateTime $date) : float
    {
        return $this->cash - $this->expenses->spentUntilDate($date);
    }

    /**
     * @return float
     */
    public function startingCash() : float
    {
        return $this->cash;
    }

    /**
     * @return float
     */
    public function dailyCash() : float
    {
        return $this->cash / $this->dateRange->days();
    }

    /**
     * @param DateTime|null $date
     * @return float
     */
    public function remainingCash(DateTime $date = null) : float
    {
        return $this->startingCash() - $this->expenses->spentUntilDate($this->validDate($date ?: new DateTime()));
    }

    /**
     * @param DateTime $date
     * @return float
     */
    public function remainingBudget(DateTime $date = null) : float
    {
        return $this->dailyCash() - $this->expenses->spentOnDate($this->validDate($date ?: new DateTime()));
    }

    /**
     * @return int
     */
    public function totalDays() : int
    {
        return $this->dateRange->days();
    }

    /**
     * @return int
     */
    public function remainingDays() : int
    {
        $today = new DateTime();
        return $today->diff($this->dateRange->end())->days;

    }

    /**
     * @param DateTime $date
     * @return DateTime
     * @throws DateException
     */
    private function validDate(DateTime $date) : DateTime
    {
        if (! $this->dateRange->containsDate($date)) {
            throw new DateException('Date is out of range');
        }

        return $date;
    }

    /**
     * @return DateTime
     */
    public function startDate() : DateTime
    {
        return $this->dateRange->start();
    }

    /**
     * @return DateTime
     */
    public function endDate() : DateTime
    {
        return $this->dateRange->end();
    }
}
