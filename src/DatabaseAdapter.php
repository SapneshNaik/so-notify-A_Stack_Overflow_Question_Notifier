<?php namespace KernelDev;

use PDO;

class DatabaseAdapter
{

    /**
     * The database connection.
     *
     * @var PDO
     */
    protected $connection;

    /**
     * Create a new DatabaseAdapter instance.
     *
     * @param PDO $connection
     */
    function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Fetch all rows from a table.
     *
     * @param $tableName
     * @return array
     */
    public function fetchAll($tableName)
    {
        return $this->connection->query('select * from ' . $tableName)->fetchAll();
    }

    /**
     * Perform a generic database query.
     *
     * @param $sql
     * @param $parameters
     * @return bool
     */
    public function query($sql, $parameters)
    {
        return $this->connection->prepare($sql)->execute($parameters);
    }

    // protected function persist($item_value, $item_type, $table)
    // {
    //     $IDq = $this->connection->query("SELECT * FROM ".$table." WHERE ".$item_type."= '".$item_value."'");
    //     $IDf = $IDq->fetch();
    //     if ($IDf[$item_type]) {
    //         return true;
    //     } else {
    //         return false;
    //     }
    // }
}
