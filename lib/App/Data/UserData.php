<?php
namespace Demo\Data;

use Demo\Interfaces\UserDataProviderInterface;

class UserData implements UserDataProviderInterface
{
    /** @var integer */
    protected $id;

    /** @var string */
    protected $type;

    /** @var array */
    protected $operations;

    /** @var \Demo\Interfaces\StorageInterface */
    protected $storage;

    /**
     * UserData constructor.
     * @param $id
     * @param \Demo\Interfaces\StorageInterface $storage
     */
    public function __construct($id, \Demo\Interfaces\StorageInterface $storage)
    {
        $this->id = $id;
        $this->storage = $storage;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param $operations
     * @return $this
     */
    public function setOperations($operations)
    {
        $this->operations = $operations;
        return $this;
    }

    /**
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param null $filters
     * @return array
     */
    public function getOperationsByPeriod(\DateTimeInterface $from, \DateTimeInterface $to, $filters=null)
    {
        $this->storage->openDb();
        $operations = $this->storage->getUserOperations($this->id, $from, $to, $filters);
        $this->storage->close();

        return $operations;
    }
}
