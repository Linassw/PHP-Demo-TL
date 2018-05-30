<?php
namespace Demo\Entities;

use \Demo\Interfaces\RatesProvider;
use \Demo\Interfaces\MoneyInterface;

class Operation
{
    /** @var \DateTimeInterface  */
    public $date;

    /** @var string */
    public $type;

    /** @var MoneyInterface  */
    public $money;

    /** @var User  */
    public $user;

    protected $rates;

    /**
     * Operation constructor.
     * @param string $type
     * @param MoneyInterface $money
     * @param User $user
     * @param \DateTimeInterface $date
     */
    public function __construct($type, MoneyInterface $money, User $user, \DateTimeInterface $date)
    {
        $this->date = $date;
        $this->type = $type;
        $this->money = $money;

        $this->user = $user;
        $this->currencyCfg = Money::getCurrencySettings(); // TODO decouple
    }

    /**
     * @return int
     */
    public function userWithdrawsThisWeek()
    {
        return $this->user->withdrawCountWithinWeek($this->date);
    }

    /**
     *
     * @return float
     */
    public function getCommissionAmount($commissionBaseRate)
    {
        if ( !is_float($commissionBaseRate) ) {
            throw new \InvalidArgumentException('commission base rate must be a float');
        }

        return ($this->money->amount / 100) * $commissionBaseRate;
    }

    /**
     * @param RatesProvider $rates
     * @return float|int
     * @throws \Demo\Exceptions\CurrencyNotSupportedException
     */
    public function getCommissionAmountDiscounted(RatesProvider $rates)
    {
        $commissionRate = $rates->getCommissionBaseRate();
        $alreadyWithdrawnAmount = $this->user->amountWithdrawnWithinWeek($this->date); // in default currency
        
        $alreadyWithdrawnMoney = new Money($alreadyWithdrawnAmount, $this->currencyCfg['default_currency']);
        $discountConditions = $rates->getDiscountConditions();

        $discountableAmountLeft = ($discountConditions->getMax()->getIn($this->money->currency) > $alreadyWithdrawnMoney->getIn($this->money->currency))
            ?  $discountConditions->getMax()->getIn($this->money->currency) - $alreadyWithdrawnMoney->getIn($this->money->currency)
            : 0;
        $discountableMoneyLeft = new Money($discountableAmountLeft, $this->money->currency);

        $discountableAmount = min($discountableMoneyLeft->amount, $this->money->amount);
        $normalAmount = $this->money->amount - $discountableAmount;

        return ($discountableAmount / 100) * ($commissionRate * $rates->getDiscountCoeff()) + ($normalAmount / 100) * $commissionRate;
    }
}
