<?php
namespace Bdd88\RestApi\Model;

use mysqli;
use mysqli_stmt;
use Exception;

/** This class performs queries the MySQL database. Supports CRUD operations as well as prepared statements. */
class MySql
{

    private mysqli $connection;
    private array $databaseSettings;

    public function __construct(ConfigDatabase $databaseConfig)
    {
        $this->databaseSettings = $databaseConfig->getAllSettings();
        $this->connect();
    }

    public function __destruct()
    {
        $this->connection->close();
    }

    /** Create a new database connection using information from the config file. */
    private function connect(): void
    {
        $this->connection = new mysqli($this->databaseSettings['host'], $this->databaseSettings['username'], $this->databaseSettings['password'], $this->databaseSettings['database']);
        if ($this->connection->connect_error) {
            throw new Exception("Connection failed: " . $this->connection->connect_error);
        }
    }

    /** Run a query on the database. */
    private function query(string $queryString): mysqli
    {
        $queryResponse = $this->connection->real_query($queryString);
        if ($queryResponse === FALSE) {
            throw new Exception('Query: ' . $queryString . '\nError: ' . $this->connection->error);
        }
        return $this->connection;
    }

    /** Encapsulates a string in backticks. */
    private function backticks(string $string): string
    {
        return '`' . $string . '`';
    }

    /** Encapsulates a string in single quotes. */
    private function singleQuotes(string $string): string
    {
        return '\'' . $string . '\'';
    }

    /**
     * Use a multidimentional array array to create the WHERE portion of a query string.
     * Each array entry should be an array of 3 items: (string) $column, (string) $operator, and (string|array|null) $value.
     * 
     * Valid operators are: isEqual, notEqual, isBetween, and notBetween.
     * isEqual/notEqual operators can accept $value as an array to create multiple OR conditions for the same column.
     * isBetween/notBetween operators can only accept a two item array for $value: start and stop respectively.
     * 
     * E.G.
     * whereString([
     *     ['id', 'isEqual', 43],
     *     ['type', 'notEqual', ['apple', 'orange', 'pear']],
     *     ['expiration', 'isBetween', ['monday', 'friday']],
     *     ['price', 'notEqual', NULL]
     * ]);
     * returns the following:
     * (`id` = '43') AND (`type` != 'apple' OR `type` != 'orange' OR `type` != 'pear') AND (`expiration` BETWEEN 'monday' AND 'friday') AND (`price` IS NOT NULL)
     */
    private function whereString(array $array): string
    {
        $output = 'WHERE ';
        foreach ($array as $key => $value) {
            $column = $value[0];
            $condition = $value[1];
            $value = $value[2];
            $string = '(';
            if ($condition === 'isEqual' || $condition === 'notEqual') {
                $operator = ($condition === 'isEqual') ? ' = ' : ' != ';
                if (is_array($value)) {
                    foreach ($value as $subKey => $subValue) {
                        $string .= $this->backticks($column) . $operator . $this->singleQuotes($subValue);
                        if ($subKey !== array_key_last($value)) {
                            $string .= ' OR ';
                        }
                    }
                } elseif (is_null($value)) {
                    $string .= $this->backticks($column);
                    $string .= ($condition === 'isEqual') ? ' IS NULL' : ' IS NOT NULL';
                } else {
                    $string .= $this->backticks($column) . $operator . $this->singleQuotes($value);
                }
            } elseif ($condition === 'isBetween' || $condition === 'notBetween') {
                $string .= $this->backticks($column);
                $condition = ($condition === 'isBetween') ? ' BETWEEN ' : ' NOT BETWEEN ';
                $string .= $condition . $this->singleQuotes($value[0]) . ' AND ' . $this->singleQuotes($value[1]);
            }
    
            $string .= ')';
            if ($key !== array_key_last($array)) {
                $string .= ' AND ';
            }
            $output .= $string;
        }
        return $output;
    }

    /** Use an associative array to create the INSERT section of a query. */
    private function insertString(array $array): string
    {
        $columnString = '(';
        $valueString = '(';
        foreach ($array as $key => $value) {
            $columnString .= $this->backticks($key);
            $valueString .= $this->singleQuotes($value);
            if ($key !== array_key_last($array)) {
                $columnString .= ', ';
                $valueString .= ', ';
            } else {
                $columnString .= ')';
                $valueString .= ')';
            }
        }
        return $columnString . ' VALUES ' . $valueString;
    }

    /** Use an associative array to create the SET section of a query. */
    private function setString(array $array): string
    {
        $setString = 'SET ';
        foreach ($array as $key => $value) {
            $setString .= $this->backticks($key) . ' = ' . $this->singleQuotes($value);
            if ($key !== array_key_last($array)) {
                $setString .= ', ';
            }
        }
        return $setString;
    }

    /**
     * Create the SELECT section of a query.
     * Select all columns by supplying NULL.
     * Select specific columns by supplying an indexed array of column names.
     * Run a select function by supplying an associative array with keys: 'function', 'column', and (optionally) 'as'.
     * 
     * E.G.
     * selectString(['function' => 'sum', 'column' => 'quanitity', 'as' => 'totalQuantity']);
     * returns the following string: SELECT SUM(`quantity`) AS 'totalQuantity'
     */
    private function selectString(?array $array): string
    {
        $setString = 'SELECT ';
        if ($array === NULL) {
            $setString .= '* ';
        } else {
            if (isset($array['function'])) {
                $functions = ['AVG', 'COUNT', 'MAX', 'MIN', 'SUM'];
                $function = strtoupper($array['function']);
                if (in_array($function, $functions) === TRUE) {
                    $setString .= $function . '(' . $this->backticks($array['column']) . ') ';
                    if (isset($array['as'])) {
                        $setString .= 'AS ' . $this->singleQuotes($array['as']) . ' ';
                    }
                } else {
                    throw new Exception('Specified MySql function not supported.');
                }
            } else {
                foreach ($array as $key => $value) {
                    $setString .= $this->backticks($value);
                    if ($key !== array_key_last($array)) {
                        $setString .= ', ';
                    }
                }
            }
        }
        return $setString;
    }

    /** Create the ORDER BY section of a query from an array.
     * Each array entry should be an array containing the column and direction to sort.
     * E.G.
     * orderbyString([
     *     ['date', 'DESC'],
     *     ['type', 'ASC']
     * ]);
     * returns the following string: ORDER BY `date` DESC, `type` ASC
    */
    private function orderbyString(array $array): string
    {
        $string = 'ORDER BY ';
        foreach ($array as $key => $value) {
            $direction = strtoupper($value[1]);
            if ($direction !== 'DESC' && $direction !== 'ASC') {
                throw new Exception('Invalid order direction.');
            }
            $string .= $this->backticks($value[0]) . $direction;
            if ($key !== array_key_last($array)) {
                $string .= ', ';
            }
        }
        return $string;
    }

    /** Execute a prepared statement and returns the mysqli_stmt object. */
    public function preparedStatement(string $queryString, array $bindParams): mysqli_stmt
    {
        $preparedStatement = $this->connection->prepare($queryString);
        $preparedStatement->bind_param(...$bindParams);
        $preparedStatement->execute();
        $preparedStatement->store_result();
        if ($preparedStatement === FALSE) {
            throw new Exception('Query: ' . $queryString . '\nError: ' . $this->connection->error);
        }
        return $preparedStatement;
    }

    /** Create a row in the database using Key/Value pairs from $insertArray as the Columns/Values. */
    public function create(string $tableName, array $insertArray): int
    {
        $queryString = 'INSERT INTO ' . $this->backticks($tableName) . ' ' . $this->insertString($insertArray) . ' ON DUPLICATE KEY UPDATE `id`=`id`';
        $insertId = $this->query($queryString)->insert_id;
        return $insertId;
    }

    /** Read from the database and return the results an array of objects representing the rows. */
    public function read(string $tableName, ?array $selectColumns = NULL, ?array $whereArray = NULL, ?array $orderArray = NULL): array
    {
        $queryString = $this->selectString($selectColumns);
        $queryString .= 'FROM ' . $this->backticks($tableName);
        $queryString .= ($whereArray !== NULL) ? ' ' . $this->whereString($whereArray) : '';
        $queryString .= ($orderArray !== NULL) ? ' ' . $this->orderbyString($orderArray) : '';
        $results = $this->query($queryString)->store_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($results as &$result) {
            $result = (object) $result;
        }
        return $results;
    }

    /** Update rows in the database using Key/Value pairs from $whereArray and $setArray. */
    public function update(string $tableName, array $whereArray, array $setArray): int
    {
        $queryString = 'UPDATE ' . $this->backticks($tableName) . ' ' . $this->setString($setArray) . ' ' . $this->whereString($whereArray);
        return $this->query($queryString)->affected_rows;
    }

    /** Delete selected rows from the database using Key/Value pairs from $whereArray. */
    public function delete(string $tableName, array $whereArray): int
    {
        $queryString = 'DELETE FROM ' . $this->backticks($tableName) . ' ' . $this->whereString($whereArray);
        $affectedRows = $this->query($queryString)->affected_rows;
        return $affectedRows;
    }

    /** Delete an entire selected table from the database. */
    public function truncate(string $tableName): int
    {
        $queryString = 'TRUNCATE ' . $this->backticks($this->databaseSettings['database']) . '.' . $this->backticks($tableName);
        $affectedRows = $this->query($queryString)->affected_rows;
        return $affectedRows;

    }

}

?>
