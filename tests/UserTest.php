<?php
use PHPUnit\Framework\TestCase;
use Demo\StorageSqlLite;
use Demo\Entities\Money;

class UserTest extends TestCase
{
    public function testIsEligibleForDiscount()
    {
        $storage = new StorageSqlLite(__DIR__ . '/../storage/demo.db');
        $userData = new \Demo\Data\UserData(1, $storage);
        $userData->setType('natural');
        $user = new Demo\Entities\User($userData);

        $maxWithdrawsPerWeek = 3;
        $date = new DateTime('2016-01-07');
        $actual = $user->getDiscountableAmountLeft($date, $maxWithdrawsPerWeek);
        $expected = true;

        $this->assertEquals($expected, $actual);
    }

    public function testWithdrawCountWithinWeek()
    {
        $storage = new StorageSqlLite(__DIR__ . '/../storage/demo.db');
        $userData = new \Demo\Data\UserData(1, $storage);
        $userData->setType('natural');
        $user = new Demo\Entities\User($userData);

        $actual = $user->withdrawCountWithinWeek(new \DateTime('2016-01-06')); // wednesday
        $expected = 1; // the only cash_out operation counting from the nearest monday to 2016-01-06 was at 2016-01-06 wednesday

        $this->assertEquals($expected, $actual);
    }

    public function testDiscountNaturalCashIn()
    {
        $storage = new StorageSqlLite(__DIR__ . '/../storage/demo.db');
        $userData = new \Demo\Data\UserData(1, $storage);
        $userData->setType('natural');
        $user = new Demo\Entities\User($userData);

        $rates = $user->getCommissionRates('cash_in');

        $this->assertEquals($rates->discountEnabled, false); // Natural users have no discounts for cash in
    }

    public function testWithdrawnAmount()
    {
        $storage = new StorageSqlLite(__DIR__ . '/../storage/demo.db');
        $userData = new \Demo\Data\UserData(1, $storage);
        $userData->setType('natural');
        $user = new Demo\Entities\User($userData);
        
        $actual = $user->amountWithdrawnWithinWeek(new \DateTime('2016-01-07')); // in default currency, i.e. EUR

        $first = new Money(1000, 'EUR');
        $second = new Money(100, 'USD');
        $third = new Money(30000, 'JPY');

        $expected = $first->getIn('EUR') + $second->getIn('EUR') + $third->getIn('EUR');

        $this->assertEquals($expected, $actual);
        $this->assertGreaterThan(1000, $actual); //expected value 1318.585... > 1000
    }

    public function testDiscountableAmount()
    {
        $storage = new StorageSqlLite(__DIR__ . '/../storage/demo.db'); // @TODO use mock?
        $userData = new \Demo\Data\UserData(1, $storage);
        $userData->setType('natural');
        $user = new Demo\Entities\User($userData);

        $actual = $user->getDiscountableAmountLeft(new \DateTime('2016-01-06'), 'cash_out')->amount;
        $moneyWithdrawn = new Demo\Entities\Money(30000, 'JPY');
        $expected = 1000 - $moneyWithdrawn->getInDefaultCurrency();

        $this->assertEquals($expected, $actual);
    }

    public function testDiscountNaturalCashOut()
    {
        $storage = new StorageSqlLite(__DIR__ . '/../storage/demo.db');
        $userData = new \Demo\Data\UserData(1, $storage);
        $userData->setType('natural');
        $user = new Demo\Entities\User($userData);

        $rates = $user->getCommissionRates('cash_out');

        $this->assertEquals($rates->discountEnabled, true); // Natural users do have discounts for cash out
    }
}