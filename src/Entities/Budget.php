<?php

namespace Trixworks\Budget\Entities;

use DateTime;
use Trixworks\Budget\Exceptions\Date as DateException;
use Trixworks\Budget\Exceptions\Date;

class Budget
{
    private $dateRange;

    private $cash;

    private $expenses;

    private $key;

    /**
     * Budget constructor.
     * @param DateRange $dateRange
     * @param float $cash
     */
    public function __construct(DateRange $dateRange, float $cash)
    {
        $this->dateRange = $dateRange;
        $this->cash = round($cash, 2);
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
        $date = $this->validDate($date ?: new DateTime());
        $this->expenses->add(new Expense($cash, $date));

        return $this;
    }

    /**
     * @return float
     */
    public function savings() : float
    {
        $cash = $this->cash - $this->dailyBudget() * ($this->remainingDays() - 1);

        return $cash - $this->expenses->spentUntilDate(new DateTime());
    }

    /**
     * @param string|DateTime $date
     * @return ExpenseCollection
     */
    public function expenses($date = null) : ExpenseCollection
    {
        if ($date) {
            return $this->expenses->onDate($this->validDate($date ?: new DateTime()));
        } else {
            return $this->expenses;
        }
    }

    /**
     * @param DateTime $date
     * @return float
     */
    public function spent(DateTime $date = null) : float
    {
        $date = $this->validDate($date ?: new DateTime());
        if (! $expenses = $this->expenses->onDate($date)) {
            return 0;
        }

        return $expenses->totalSpent();
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
    public function dailyBudget() : float
    {
        return round($this->cash / $this->dateRange->days(), 2);
    }

    /**
     * @param DateTime|null $date
     * @return float
     */
    public function remainingCash(DateTime $date = null) : float
    {
        if ($date) {
            $spent = $this->expenses->spentUntilDate($date);
        } else {
            $spent = $this->expenses->totalSpent();
        }

        return $this->startingCash() - $spent;
    }

    /**
     * @param DateTime $date
     * @return float
     */
    public function remainingBudget(DateTime $date = null) : float
    {
        $date = $this->validDate($date ?: new DateTime());
        $budget = $this->dailyBudget() - $this->expenses->spentOnDate($date);

        return $budget;
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
        return $today->diff($this->dateRange->end())->days + 1;

    }

    /**
     * @return int
     */
    public function elapsedDays() : int
    {
        return $this->dateRange->start()->diff(new DateTime())->days;
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

    /**
     * @param string $key
     * @return Budget
     */
    public function setKey(string $key) : self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey() : string
    {
        return $this->key = $this->key ?: uniqid();
    }

    /**
     * @return Budget
     */
    public function process() : self
    {
        $expenses = [];

        foreach ($this->expenses->all() as $expense) {
            $expenses[] = $expense->process();
        }

        $this->expenses = new ExpenseCollection($expenses);

        return $this;
    }

    /**
     * @return Budget
     */
    public function recalculate() : self
    {
        $savings = $this->savings();

        // Remove daily budget for past days from total cash
        $this->cash = $this->cash - ($this->dailyBudget() * $this->elapsedDays());

        // Add any savings accumulated
        $this->cash += $savings;

        // Reset date range
        $this->dateRange = new DateRange(new DateTime(), $this->dateRange->end());

        // Reset expenses collection
        $this->expenses = new ExpenseCollection();

        return $this;
    }

    /**
     * @param string|DateTime $date
     * @return DateTime
     * @throws DateException
     */
    private function validDate($date) : DateTime
    {
        if (! $date instanceof DateTime and ! $date = new DateTime($date)) {
            throw new DateException('Invalid date string or DateTime object');
        }

        if (! $this->dateRange->containsDate($date)) {
            throw new DateException('Date is out of range');
        }

        return $date;
    }
}
