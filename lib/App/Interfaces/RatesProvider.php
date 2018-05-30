<?php
namespace Demo\Interfaces;

interface RatesProvider
{
    /**
     * @return float;
     */
    public function getCommissionBaseRate();

    /**
     *
     * @return boolean
     */
    public function isDiscountEnabled();

    /**
     * @return \Demo\Entities\DiscountConditions
     */
    public function getDiscountConditions();

    /**
     *
     * @return float
     */
    public function getDiscountCoeff();

    /**
     * @return float;
     */
    public function getCommissionMaxAmount();

    /**
     * @return float;
     */
    public function getCommissionMinAmount();
}