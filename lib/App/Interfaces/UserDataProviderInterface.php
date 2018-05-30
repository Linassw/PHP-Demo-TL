<?php
namespace Demo\Interfaces;

/**
 *
 * @author Linas
 */
interface UserDataProviderInterface
{
    /**
     * @return integer
     */
    public function getId();

    /**
     * @return string
     */
    public function getType();

    /**
     * @return array
     */
    public function getOperations();

    /**
     *
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @return array
     */
    public function getOperationsByPeriod(\DateTimeInterface $from, \DateTimeInterface $to);
}