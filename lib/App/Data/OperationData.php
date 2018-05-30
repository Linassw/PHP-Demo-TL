<?php
namespace \Demo\Data;

class OperationData implements \Demo\Interfaces\OperationDataProviderInterface
{
    /** @var integer */
    protected $id;
    protected $type;
    protected $date;
    protected $amount;
    protected $currency;

    /**
     * OperationData constructor.
     * @param $type
     * @param $date
     * @param $amount
     * @param $currency
     */
    public function __construct($type, $date, $amount, $currency)
    {
        $this->type = $type;
        $this->date = $date;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }
}
