<?php
namespace Demo\Interfaces;

interface StorageInterface
{
    public function getUserData($userId);
    public function getUserOperations($userId, \DateTimeInterface $from, \DateTimeInterface $to);
}
