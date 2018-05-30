<?php
namespace Demo;

use Demo\Entities\User;
use Demo\Entities\Operation;
use Demo\Entities\Money;

use Demo\Data\UserData;

class Book
{
    /**
     * @param $dateString
     * @param $userId
     * @param $userType
     * @param $operationType
     * @param $operationAmount
     * @param $operationCurrency
     * @param $storage
     * @return float
     * @throws Exceptions\CurrencyNotSupportedException
     */
    public static function getCommission($dateString, $userId, $userType, $operationType, $operationAmount, $operationCurrency, $storage)
    {
        $userData = new UserData($userId, $storage);
        $userData->setType($userType);
        
        $user = new User($userData);

        $date = new \DateTime($dateString);
        $money = new Money($operationAmount, $operationCurrency);
        $operation = new Operation($operationType, $money, $user, $date);

        $discountableAmount = min($money->getInDefaultCurrency(), $user->getDiscountableAmountLeft($date, $operationType)->getInDefaultCurrency());
        $discountableMoney = new Money($discountableAmount, 'EUR');

        $regularMoney = $money->subtract($discountableMoney);

        $rates = $user->getCommissionRates($operationType);
        $commissionAmount = ($regularMoney->amount / 100)  * $rates->defaultRate;

        if ( $rates->discountEnabled ) {
            $commissionAmount += ($discountableMoney->getIn($operationCurrency) / 100) * ($rates->defaultRate * $rates->discount->coeff);
        }

        $commission = new Money($commissionAmount, $operation->money->currency);

        if ( !empty($rates->maxAmount) ) {
            $commissionMax = new Money($rates->maxAmount, $rates->currency);

            if ( $commission->getIn($commissionMax->currency) > $commissionMax->amount ) {
                $commission->amount = $commissionMax->getIn($commission->currency);
            }
        }

        if ( !empty($rates->minAmount) ) {
            $commissionMin = new Money($rates->minAmount, $rates->currency);

            if ( $commission->getIn($commissionMin->currency) < $commissionMin->amount ) {
                $commission->amount = $commissionMin->getIn($commission->currency);
            }
        }

        return $commission->format();
    }
}
