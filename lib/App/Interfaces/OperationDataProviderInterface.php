<?php
namespace \Demo\Interfaces;

interface OperationDataProviderInterface
{
    public function getId();
    public function getDate();
    public function getType();
    public function getUserId();
    public function getAmount();
    public function getCurrency();
}