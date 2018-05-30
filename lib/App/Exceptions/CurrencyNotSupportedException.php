<?php
namespace Demo\Exceptions;

class CurrencyNotSupportedException extends \Exception
{
    protected $invalidCurrency;

    public function __construct($invalidCurrency, $code=0, $previous=null)
    {
        $this->invalidCurrency = $invalidCurrency;
        parent::__construct($invalidCurrency, $code, $previous);
    }

    public function getCurrency()
    {
        return $this->invalidCurrency;
    }
}
