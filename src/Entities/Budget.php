<?php

namespace Trixworks\Budget\Entities;

use DateTime;
use Trixworks\Budget\Exceptions\Date as DateException;

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
     * @param DateTime $date
     * @return float
     */
    public function savings(DateTime $date = null) : float
    {
        $date = $this->validDate($date ?: new DateTime());
        $cash = $this->cash - $this->dailyCash() * ($this->remainingDays()-1);
        $savings = $cash - $this->expenses->spentUntilDate($date);

        return $savings;
    }

    /**
     * @return Expense[]
     */
    public function allExpenses() : array
    {
        return $this->expenses->all();
    }

    /**
     * @param DateTime $date
     * @return Expense[]
     */
    public function expenses(DateTime $date = null) : array
    {
        $date = $this->validDate($date ?: new DateTime());
        return $this->expenses->fromDate($date);
    }

    /**
     * @param DateTime $date
     * @return float
     */
    public function spent(DateTime $date = null) : float
    {
        $date = $this->validDate($date ?: new DateTime());
        if (! $expenses = $this->expenses->fromDate($date)) {
            return 0;
        }

        $expenses = new ExpenseCollection($expenses);

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
    public function dailyCash() : float
    {
        return round($this->cash / $this->dateRange->days(), 2);
    }

    /**
     * @param DateTime|null $date
     * @return float
     */
    public function remainingCash(DateTime $date = null) : float
    {
        $date = $this->validDate($date ?: new DateTime());
        $cash = $this->startingCash() - $this->expenses->spentUntilDate($date);

        return $cash;
    }

    /**
     * @param DateTime $date
     * @return float
     */
    public function remainingBudget(DateTime $date = null) : float
    {
        $date = $this->validDate($date ?: new DateTime());
        $budget = $this->dailyCash() - $this->expenses->spentOnDate($date);

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

        foreach ($this->expenses() as $expense) {
            $expenses[] = $expense->process();
        }

        $this->expenses = new ExpenseCollection($expenses);

        return $this;
    }
}
