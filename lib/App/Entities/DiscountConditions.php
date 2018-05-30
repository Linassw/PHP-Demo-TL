<?php
namespace Demo\Entities;

class DiscountConditions
{
    public $maxAmountPerWeek; // ...that discount is applied to
    public $maxWithdrawsPerWeek; // ...that discount is applied to
    public $currency; //... for maxAmountPerWeek

    /**
     * @return Money
     * @throws \Demo\Exceptions\CurrencyNotSupportedException
     */
    public function getMax()
    {
        return new Money($this->maxAmountPerWeek, $this->currency);
    }
}