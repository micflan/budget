<?php

use Trixworks\Budget\Entities\Budget;
use Trixworks\Budget\Entities\DateRange;
use Trixworks\Budget\Exceptions\Date as DateException;

class BudgetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param $cash
     * @return Budget
     */
    private function createBudget($cash) {
        $dateRange = new DateRange(new DateTime('-15 days'), new DateTime('+15 days'));

        /** @var Budget $budget */
        $budget = new Budget($dateRange, $cash);

        return $budget;
    }

    public function testCanCreateABudget()
    {
        $budget = $this->createBudget(650);

        $this->assertEquals(650, $budget->savings());
    }

    public function testCanSpendCashAndSeeSavings()
    {
        $budget = $this->createBudget(650);

        $budget->spend(75);

        $this->assertEquals(650 - 75, $budget->savings());

        $budget->spend(123);

        $this->assertEquals(650 - 75 - 123, $budget->savings());
    }

    public function testCanSeeSavingsForSpecificDate()
    {
        $budget = $this->createBudget(650);

        $budget->spendOnDate(new DateTime('-3 days'), 75);

        $budget->spendOnDate(new DateTime('yesterday'), 123);

        $this->assertEquals(650 - 75, $budget->savingsOnDate(new DateTime('-3 days')));

        $this->assertEquals(650 - 75 - 123, $budget->savings());
    }

    public function testCanSpecifyExpenseDate()
    {
        /** @var Budget $budget */
        $budget = $this->createBudget(650);

        $budget->spendOnDate(new DateTime('-3 days'), 75);

        $this->assertEquals(650 - 75, $budget->savings());

        $budget->spendOnDate(new DateTime('yesterday'), 123);

        $this->assertEquals(650 - 75 - 123, $budget->savings());
    }

    public function testCanNotSpendOutsideDateRange()
    {
        $budget = $this->createBudget(650);

        $this->setExpectedException(DateException::class);

        $budget->spendOnDate(new DateTime('-50 days'), 75);
    }

    public function testCanSeeTotalDaysInBudget()
    {
        $budget = $this->createBudget(100);
        $this->assertEquals(30, $budget->totalDays());
    }

    public function testCanSeeRemainingDaysInBudget()
    {
        $budget = $this->createBudget(100);
        $this->assertEquals(15, $budget->remainingDays());
    }

    public function testCanSeeTotalCashInBudget()
    {
        $budget = $this->createBudget(100);
        $budget->spend(50);
        $this->assertEquals(100, $budget->startingCash());
    }

    public function testCanSeeRemainingCashInBudget()
    {
        $budget = $this->createBudget(100);
        $budget->spend(50);
        $this->assertEquals(50, $budget->remainingCash());
    }

    public function testCanSeeDailyBudgetAmount()
    {
        $budget = $this->createBudget(10*30);
        $this->assertEquals(10, $budget->dailyCash());
    }

    public function testCanSeeTodaysRemainingBudget()
    {
        $budget = $this->createBudget(10*30);
        $budget->spend(5);
        $this->assertEquals(5, $budget->remainingBudget());
    }

    public function testCanSeeBudgetStartDate()
    {
        $startDate = new DateTime('-15 days');
        $startDate->setTime(0,0,0);

        $budget = $this->createBudget(100);
        $this->assertEquals($startDate, $budget->startDate());
    }

    public function testCanSeeBudgetEndDate()
    {
        $endDate = new DateTime('+15 days');
        $endDate->setTime(23,59,59);

        $budget = $this->createBudget(100);
        $this->assertEquals($endDate, $budget->endDate());
    }
}
