<?php

use Trixworks\Budget\Entities\Budget;
use Trixworks\Budget\Entities\DateRange;
use Trixworks\Budget\Exceptions\Date as DateException;

class BudgetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param $cash
     * @param string $start
     * @param string $end
     * @return Budget
     */
    private function createBudget($cash, $start = '-14 days', $end = '+15 days') {
        $dateRange = new DateRange(new DateTime($start), new DateTime($end));

        /** @var Budget $budget */
        $budget = new Budget($dateRange, $cash);

        return $budget;
    }

    public function testCanCreateABudget()
    {
        $budget = $this->createBudget(650);

        $this->assertEquals(650, $budget->remainingCash());
    }

    public function testCanSpendCashAndSeeSavings()
    {
        $budget = $this->createBudget(650);

        $budget->spend(75);

        $this->assertEquals(650 - 75, $budget->remainingCash());

        $budget->spend(123);

        $this->assertEquals(650 - 75 - 123, $budget->remainingCash());
    }

    public function testCanSeeSavingsForSpecificDate()
    {
        $budget = $this->createBudget(650);

        $budget->spend(75, new DateTime('-3 days'));

        $budget->spend(123, new DateTime('yesterday'));

        $this->assertEquals(650 - 75, $budget->remainingCash(new DateTime('-3 days')));

        $this->assertEquals(650 - 75 - 123, $budget->remainingCash());
    }

    public function testCanSpecifyExpenseDate()
    {
        $budget = $this->createBudget(650);

        $budget->spend(75, new DateTime('-3 days'));

        $this->assertEquals(650 - 75, $budget->remainingCash());

        $budget->spend(123, new DateTime('yesterday'));

        $this->assertEquals(650 - 75 - 123, $budget->remainingCash());
    }

    public function testCanNotSpendOutsideDateRange()
    {
        $budget = $this->createBudget(650);

        $this->setExpectedException(DateException::class);

        $budget->spend(75, new DateTime('-50 days'));
    }

    public function testCanSeeTotalDaysInBudget()
    {
        $budget = $this->createBudget(100);
        $this->assertEquals(30, $budget->totalDays());
    }

    public function testCanSeeRemainingDaysInBudget()
    {
        $budget = $this->createBudget(100);
        $this->assertEquals(16, $budget->remainingDays());
    }

    public function testCanSeeElapsedDaysInBudget()
    {
        $budget = $this->createBudget(100);
        $this->assertEquals(14, $budget->elapsedDays());
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
        $this->assertEquals(10, $budget->dailyBudget());
    }

    public function testCanSeeTodaysRemainingBudget()
    {
        $budget = $this->createBudget(10*30);
        $budget->spend(5);
        $this->assertEquals(5, $budget->remainingBudget());
    }

    public function testCanSeeBudgetStartDate()
    {
        $startDate = new DateTime('-14 days');
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

    public function testReturnsUniqueKey()
    {
        $key1 = $this->createBudget(100)->getKey();
        $key2 = $this->createBudget(100)->getKey();

        $this->assertGreaterThan(10, strlen($key1));
        $this->assertGreaterThan(10, strlen($key2));

        $this->assertNotEquals($key1, $key2);
    }

    public function testCanBulkProcessExpenses()
    {
        $budget = $this->createBudget(650);

        $budget->spend(50, new DateTime('-3 days'));
        $budget->spend(50, new DateTime('yesterday'));
        $budget->spend(50, new DateTime('tomorrow'));

        $this->assertEquals(500, $budget->remainingCash());

        foreach ($budget->expenses() as $expense)
        {
            $this->assertTrue($expense->isNew());
        }

        $budget->process();

        $this->assertEquals(500, $budget->remainingCash());

        foreach ($budget->expenses() as $expense)
        {
            $this->assertFalse($expense->isNew());
        }
    }

    public function testCanRecalculateBudget()
    {
        $budget = $this->createBudget(300, 'today', '+30 days');

        $this->assertEquals(10, round($budget->dailyBudget()));
        $budget->spend(150);
        $this->assertEquals(10, round($budget->dailyBudget()));
        $budget->recalculate();
        $this->assertEquals(5, round($budget->dailyBudget()));
        $this->assertEquals(5, round($budget->savings()));


        $budget = $this->createBudget(300);
        $this->assertEquals(10, $budget->dailyBudget());

        for ($i=-14; $i <= 0; $i++) {
            $budget->spend(15, new DateTime("$i days"));
        }
        $this->assertEquals(10, $budget->dailyBudget());
        $this->assertEquals(225, $budget->expenses()->totalSpent());
        $budget->recalculate();
        $this->assertEquals($budget->totalDays(), $budget->remainingDays());
        $this->assertEquals(5, round($budget->dailyBudget()));
        $this->assertEquals(5, round($budget->savings()));
        $this->assertCount(0, $budget->expenses()->all());
    }
}
