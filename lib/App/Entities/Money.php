<?php
namespace Demo\Entities;

use \Demo\Interfaces\MoneyInterface;
use Symfony\Component\Yaml\Yaml;
use \Demo\Exceptions\CurrencyNotSupportedException;

class Money implements MoneyInterface
{
    /** @var float */
    public $amount;

    /** @var string */
    public $currency;

    protected $supportedCurrencies;
    protected $currencyCfg;

    /**
     * @param float $amount
     * @param string $currency
     * @throws CurrencyNotSupportedException
     */
    public function __construct($amount, $currency)
    {
        $this->currencyCfg = self::getCurrencySettings();
        $this->supportedCurrencies = array_keys($this->currencyCfg['currencies']);

        // In a real life situation I probably wouldn't use in_array() due to speed concerns.
        // In this case array consists of 3 elements only, so I am using it for convenience

        if ( !in_array($currency, $this->supportedCurrencies) ) {
            throw new CurrencyNotSupportedException($currency);
        }

        $this->amount = (float)$amount;
        $this->currency = $currency;
    }

    /**
     * @param MoneyInterface $money
     * @return Money
     * @throws CurrencyNotSupportedException
     */
    public function subtract(MoneyInterface $money)
    {
        $amount1 = $this->amount;
        $amount2 = ( $this->currency == $money->currency ) ? $money->amount : $money->getIn($this->currency);

        return new Money($amount1 - $amount2, $this->currency);
    }

    /**
     *
     * @return float
     */
    public function getInDefaultCurrency()
    {
        $defaultCurrency = $this->currencyCfg['default_currency'];

        if ( $this->currency == $defaultCurrency ) {
            return $this->amount; // already in default currency
        }
      
        $rate = $this->currencyCfg['rates'][$defaultCurrency][$this->currency];
        return $this->amount / $rate;
    }

    /**
     *  This method would be useful if we wanted to i.e. easily change the default currency to something other than EUR,
     *  also if we need to convert money to different currencies on a regular basis for other purposes
     *
     * @param float $currency
     * @throws \InvalidArgumentException
     * @throws CurrencyNotSupportedException
     * @return float
     */
    public function getIn($currency)
    {
        if ( !in_array($currency, $this->supportedCurrencies) ) {
            throw new CurrencyNotSupportedException($currency);
        }

        if ( $currency == $this->currency ) {
           return $this->amount;
        }

        $defaultCurrency = $this->currencyCfg['default_currency'];

        // This wouldn't be necessary with i.e. db table containing all rates every currency to every other currency
        $rateTo = ($currency == $defaultCurrency) ? 1 : $this->currencyCfg['rates'][$defaultCurrency][$currency];
        $rateFrom = ( $this->currency == $defaultCurrency ) ? 1 : $this->currencyCfg['rates'][$defaultCurrency][$this->currency];
        $rate = $rateTo / $rateFrom;

        $amount = $this->amount * $rate;
        return $amount;
    }

    /**
     * Formats the amount for displaying where needed.
     * In this case we only need to round it up to the smallest subunit of a given currency
     *
     * @return float
     */
    public function format()
    {
        $subunitRate = $this->currencyCfg['currencies'][$this->currency]['subunit']['rate'];
        $amountInSubunits = $this->amount / $subunitRate;
        $ceiledAmountInSubunits = ceil($amountInSubunits);
        $roundedAmount = $ceiledAmountInSubunits * $subunitRate;

        $decimalDigitCount = strlen((string)$subunitRate) - strpos((string)$subunitRate, '.') - 1; // additional minus 1 to compensate strpos position starting with 0
        return sprintf('%0.' . $decimalDigitCount . 'f', $roundedAmount);
    }

    /**
     *
     * @return array
     */
    public static function getCurrencySettings()
    {
        // I'm just hardcoding path due to time constrains, in a real life situation it would be stored somewhere in a
        // global app settings file
        $filepath = 'cfg/currencies.yml';
        
        if ( !file_exists($filepath) || !is_readable($filepath) ) {
            throw new Exception('Cannot read currency config file');
        }

        return $currencyCfg = Yaml::parse(file_get_contents($filepath));
    }

    /**
     * For testing and debugging only
     * @return string
     */
    public function __toString()
    {
        return $this->format() . ' ' . $this->currency;
    }
}
