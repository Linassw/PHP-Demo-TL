<?php
use PHPUnit\Framework\TestCase;
use Demo\Entities\Money;

class MoneyTest extends TestCase
{
    public function testConvert()
    {
        $moneyEUR = new Money(100, 'EUR');

        $actual = $moneyEUR->getIn('USD');
        $expected = 114.97;

        $this->assertEquals($expected, $actual);
    }

    public function testSubtract()
    {
        $money = new Money(100, 'EUR');
        $otherMoney = new Money(25, 'EUR');

        $resultMoney = $money->subtract($otherMoney);

        $this->assertSame($resultMoney->amount, (float)75);
        $this->assertSame($resultMoney->currency, 'EUR');
    }
}
