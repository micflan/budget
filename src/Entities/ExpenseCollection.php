<?php

namespace Trixworks\Budget\Entities;

use DateTime;

class ExpenseCollection
{
    private $expenses;

    /**
     * ExpenseCollection constructor.
     * @param array $expenses
     */
    public function __construct(array $expenses = null)
    {
        $this->expenses = $expenses ?: [];
    }

    /**
     * @return array
     */
    public function all() : array
    {
        return $this->expenses;
    }

    /**
     * @param Expense $expense
     * @return ExpenseCollection
     */
    public function add(Expense $expense) : self
    {
        $this->expenses[] = $expense;

        return $this;
    }

    /**
     * @param DateTime $date
     * @return ExpenseCollection
     */
    public function onDate(DateTime $date) : self
    {
        $date->setTime( 0, 0, 0 );

        $expenses = array_filter($this->expenses, function(Expense $expense) use ($date) {
            $diff = $date->diff( $expense->date()->setTime( 0, 0, 0 ) );
            return $diff->days === 0;
        });

        return new static($expenses);
    }

    /**
     * @return float
     */
    public function totalSpent() : float
    {
        return array_reduce($this->expenses, function ($result, $item) {
            return $result += $item->value();
        }, 0);
    }

    /**
     * @param DateTime $date
     * @return float
     */
    public function spentUntilDate(DateTime $date) : float
    {
        $date->setTime( 23, 59, 59 );

        $expenses = array_filter($this->expenses, function(Expense $expense) use ($date) {
            return $expense->date() <= $date;
        });

        return array_reduce($expenses, function ($result, $item) {
            return $result += $item->value();
        }, 0);
    }

    /**
     * @param DateTime $date
     * @return float
     */
    public function spentOnDate(DateTime $date) : float
    {
        $date->setTime( 0, 0, 0 );

        $expenses = array_filter($this->expenses, function(Expense $expense) use ($date) {
            $diff = $date->diff( $expense->date()->setTime( 0, 0, 0 ) );
            return $diff->days === 0;
        });

        return array_reduce($expenses, function ($result, $item) {
            return $result += $item->value();
        }, 0);
    }
}
