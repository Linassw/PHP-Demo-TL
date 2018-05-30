<?php
namespace Demo;

use \Symfony\Component\Yaml\Yaml;

class YamlRatesProvider implements Interfaces\RatesProvider
{
    /** @var string */
    protected $configFilePath;

    /** @var array */
    protected $rates = [];


    /**
     *
     * @param YamlFileLoader $ymlLoader
     * @param string $configFilePath
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function __construct($configFilePath, $userType, $operationType)
    {
        if ( !file_exists($configFilePath) ) {
            throw new \InvalidArgumentException('File ' . $configFilePath . ' does not exist');
        }

        if ( !class_exists('\Symfony\Component\Yaml\Yaml') ) {
            throw new \Exception('Symfony Yaml component not installed');
        }

        $this->configFilePath = $configFilePath;
        $operationsConfig = Yaml::parse(file_get_contents($this->configFilePath));
        $this->rates = $operationsConfig['rates'];

        $this->userType = $userType;
        $this->operationType = $operationType;

        if ( !isset($this->rates[$this->operationType]) ) {
            throw new \InvalidArgumentException('Operation type ' . $this->operationType . ' does not exist');
        }

        if ( !isset($this->rates[$this->operationType][$this->userType]) ) {
            throw new \InvalidArgumentException('User type ' . $this->userType . ' does not exist');
        }

        if ( empty($this->rates[$this->operationType][$this->userType]['default_rate']) || !is_numeric($this->rates[$this->operationType][$this->userType]['default_rate']) ) {
            throw new Exceptions\NotImplementedException('Commission rate for user type ' . $this->userType . ' and operation type ' . $this->operationType . ' is not implemented yet');
        }
    }

    /**
     *
     * @return float
     * @throws \InvalidArgumentException
     * @throws Exceptions\NotImplementedException
     */
    public function getCommissionBaseRate()
    {
        if ( empty($this->rates[$this->operationType][$this->userType]['default_rate']) || !is_numeric($this->rates[$this->operationType][$this->userType]['default_rate']) ) {
            throw new Exceptions\NotImplementedException('Commission rate for user type ' . $this->userType . ' and operation type ' . $this->operationType . ' is not implemented yet');
        }

        return $this->rates[$this->operationType][$this->userType]['default_rate'];
    }

    public function getCommissionMaxAmount()
    {
        $amount = $this->rates[$this->operationType][$this->userType]['max_commission']['amount'];
        $currency = $this->rates[$this->operationType][$this->userType]['max_commission']['currency'];
        return new Entities\Money($amount, $currency);
    }

    public function getCommissionMinAmount()
    {
        $amount = $this->rates[$this->operationType][$this->userType]['min_commission']['amount'];
        $currency = $this->rates[$this->operationType][$this->userType]['min_commission']['currency'];
        return new Entities\Money($amount, $currency);
    }

    public function getDiscountCoeff()
    {
        return $this->rates[$this->operationType][$this->userType]['discount']['coeff'];
    }

    public function isDiscountEnabled()
    {
        return $this->rates[$this->operationType][$this->userType]['discount']['enabled'];
    }

    /**
     *
     * @return \Demo\Entities\DiscountConditions
     * @throws \InvalidArgumentException
     */
    public function getDiscountConditions()
    {
        $discountConditions = new Entities\DiscountConditions();
        $discountConditions->maxAmountPerWeek = $this->rates[$this->operationType][$this->userType]['discount']['amount_per_week']['value'];
        $discountConditions->maxWithdrawnsPerWeek = $this->rates[$this->operationType][$this->userType]['discount']['wd_per_week'];
        $discountConditions->currency = $this->rates[$this->operationType][$this->userType]['discount']['amount_per_week']['currency'];

        return $discountConditions;
    }
}
