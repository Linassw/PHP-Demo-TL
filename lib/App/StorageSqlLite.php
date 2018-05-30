<?php
namespace Demo;

class StorageSqlLite extends \SQLite3 implements Interfaces\StorageInterface
{
    protected $filepath;

    /**
     * StorageSqlLite constructor.
     * @param $filepath
     */
    function __construct($filepath)
    {
        if ( !file_exists($filepath) || is_dir($filepath) ) {
            throw new \InvalidArgumentException('File ' . $filepath . ' does not exist');
        }

        $this->filepath = $filepath;
    }

    public function openDb()
    {
        $this->open($this->filepath);
    }

    /**
     * @param $userId
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     * @param array|null $filters
     * @return array
     */
    public function getUserOperations($userId, \DateTimeInterface $from, \DateTimeInterface $to, array $filters=null)
    {
        $queryString = 'SELECT * FROM demo_operations WHERE user_id = ' . $userId
            . ' AND datetime(date) >= datetime("' . $from->format('Y-m-d') . '") AND datetime(date) <= datetime("' . $to->format('Y-m-d') . '")';

        if (!empty($filters)) {

            foreach ($filters as $column => $value) {
                $queryString .= ' AND ' . $column . ' = "' . $value . '"';
            }
        }
        $result = $this->query($queryString);

        $operations = [];
        while ($operation = $result->fetchArray(SQLITE3_ASSOC)) {
            $operations[$operation['id']] = $operation;
        }

        return $operations;
    }

    public function getUserData($userId)
    {
        
    }

    public function __destruct()
    {
        $this->close();
    }
}