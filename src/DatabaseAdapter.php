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

    /**
     * Check whther a field exists in a table.
     *
     * @param $table
     * @param $column
     * @param $value
     * @return mixed
     */
    public function checkField($table, $column, $value)
    {
        return $this->connection->query("SELECT * FROM ".$table." WHERE $column= '$value'")->fetchAll();
    }

    /**
     * Check whther a id exists in a table.
     *
     * @param $table
     * @param $id
     * @return mixed
     */
    public function checkId($table, $id)
    {
        return $this->connection->query("SELECT * FROM ".$table." WHERE id= '$id'")->fetchAll();
    }

    /**
     * get number of rows in a table.
     *
     * @param $table
     * @return mixed
     */
    public function getRowCount($table)
    {
        return $this->connection->query("SELECT COUNT(*) FROM ".$table)->fetchColumn();
    }
}
