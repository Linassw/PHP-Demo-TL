<?php
namespace Demo\Interfaces;

interface MoneyInterface
{
    public function format();
    public function getIn($currency);
}