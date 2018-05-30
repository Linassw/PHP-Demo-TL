<?php
namespace Demo\Entities;

use Demo\Interfaces\UserDataProviderInterface;

class User implements \Demo\Interfaces\UserInterface
{
    /** @var UserDataProviderInterface */
    protected $userData;


    /**
     *
     * @param UserDataProviderInterface $userData
     */
    public function __construct(UserDataProviderInterface $userData)
    {
        $this->userData = $userData;
    }

    /**
     * @param \DateTimeInterface $date
     * @param $operationType
     * @return Money
     * @throws \Demo\Exceptions\CurrencyNotSupportedException
     */
    public function getDiscountableAmountLeft(\DateTimeInterface $date, $operationType)
    {
        $cfg = Money::getCurrencySettings();
        $rates = $this->getCommissionRates($operationType);

        // If discount is disabled, discountable amount is zero
        if ( !$rates->discountEnabled ) {
            return new Money(0, $cfg['default_currency']);
        }

        // otherwise, if user exceeded max actions per week, discountable amount is still zero
        if ( $this->withdrawCountWithinWeek($date) >= $rates->discount->maxWithdraws ) {
            return new Money(0, $cfg['default_currency']);
        }
        
        // otherwise, discountable amount is maxDiscountableAmount minus amount already discounted this week, or zero
        
        $alreadyWithdrawnAmount = $this->amountWithdrawnWithinWeek($date);
        $alreadyWithdrawnMoney = new Money($alreadyWithdrawnAmount, $cfg['default_currency']);

        $maxDiscountableAmount = $rates->discount->maxAmountPerWeek;
        $maxDiscountableMoney = new Money($maxDiscountableAmount, $rates->currency);

        $amount = max(0, $maxDiscountableMoney->getIn($cfg['default_currency']) - $alreadyWithdrawnMoney->amount);
        return new Money($amount, $cfg['default_currency']);
    }

    /**
     * @param \DateTimeInterface $date
     * @return float|int
     * @throws \Demo\Exceptions\CurrencyNotSupportedException
     */
    public function amountWithdrawnWithinWeek(\DateTimeInterface $date)
    {
        $lastMondayString = $date->format('l') == 'monday' ? $date->format('Y-m-d') : date('Y-m-d', strtotime('previous monday', strtotime($date->format('Y-m-d'))));
        $lastMonday = new \DateTime($lastMondayString);
        
        $operations = $this->userData->getOperationsByPeriod($lastMonday, $date, ['type' => 'cash_out']);
        $amount = 0;

        foreach ( $operations as $operationData ) {
            $money = new Money($operationData['amount'], $operationData['currency']);
            $amount += $money->getInDefaultCurrency();
        }        

        return $amount;
    }
    

    /**
     *
     * @param \DateTime $date
     * @return int
     */
    public function withdrawCountWithinWeek(\DateTime $date)
    {
        $lastMondayString = $date->format('l') == 'monday' ? $date->format('Y-m-d') : date('Y-m-d', strtotime('previous monday', strtotime($date->format('Y-m-d'))));
        $lastMonday = new \DateTime($lastMondayString);
        
        $operations = $this->userData->getOperationsByPeriod($lastMonday, $date, ['type' => 'cash_out']);
        return count($operations);
    }

    /**
     * @param $operationType
     * @return mixed
     */
    public function getCommissionRates($operationType)
    {
        $naturalUserRates = new \stdClass();
        $juridicalUserRates = new \stdClass();

        $cashOutNatural = new \stdClass();
        $cashInNatural = new \stdClass();

        $cashInNatural->defaultRate = 0.03;
        $cashInNatural->maxAmount = 5;
        $cashInNatural->minAmount = 0;
        $cashInNatural->currency = 'EUR';
        $cashInNatural->discountEnabled = false;

        $naturalUserRates->cashIn = $cashInNatural;
        $juridicalUserRates->cashIn = $cashInNatural; // Juridical and Natural users have the same cash in commission rates

        $cashOutNatural->defaultRate = 0.3;
        $cashOutNatural->maxAmount = 5;
        $cashOutNatural->minAmount = 0;
        $cashOutNatural->currency = 'EUR';
        $cashOutNatural->discountEnabled = true;

        $discount = new \stdClass();
        $discount->maxWithdraws = 3;
        $discount->maxAmountPerWeek = 1000;
        $discount->currency = 'EUR';
        $discount->coeff = 0; // default rate is multiplied by discount coeff when calculating commission amount.

        $cashOutNatural->discount = $discount;

        $naturalUserRates->cashOut = $cashOutNatural;

        $cashOutJuridical = new \stdClass();
        $cashOutJuridical->defaultRate = 0.3;
        $cashOutJuridical->minCommission = 0.5;
        $cashOutJuridical->currency = 'EUR';
        $cashOutJuridical->discountEnabled = false;
        $juridicalUserRates->cashOut = $cashOutJuridical;

        $userRates = $this->userData->getType() == 'juridical' ? $juridicalUserRates : $naturalUserRates;
        $rates = $operationType == 'cash_in' ? $userRates->cashIn : $userRates->cashOut;
        return $rates;
    }
}
