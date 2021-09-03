<?php

namespace Lagdo\DbAdmin\Db;

use Lagdo\DbAdmin\Driver\DbTrait;
use Lagdo\DbAdmin\Driver\DbInterface;

class Db implements DbInterface, ConnectionInterface, DriverInterface, ServerInterface
{
    use DbTrait;
    use ConnectionTrait;
    use DriverTrait;
    use ServerTrait;

    /**
     * @var array
     */
    public $options;

    /**
     * The current database name
     *
     * @var string
     */
    public $database = '';

    /**
     * The current schema name
     *
     * @var string
     */
    public $schema = '';

    /**
     * The last error code
     *
     * @var int
     */
    protected $errno = 0;

    /**
     * The last error message
     *
     * @var string
     */
    protected $error = '';

    /**
     * The number of rows affected by the last query
     *
     * @var int
     */
    protected $affectedRows;

    /**
     * The constructor
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Get the Adminer version
     *
     * @return string
     */
    public function version()
    {
        return "4.8.1-dev";
    }

    /**
     * @inheritDoc
     */
    public function setError(string $error = '')
    {
        $this->error = $error;
    }

    /**
     * @inheritDoc
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * @inheritDoc
     */
    public function hasError()
    {
        return $this->error !== '';
    }

    /**
     * @inheritDoc
     */
    public function setErrno($errno)
    {
        $this->errno = $errno;
    }

    /**
     * @inheritDoc
     */
    public function errno()
    {
        return $this->errno;
    }

    /**
     * @inheritDoc
     */
    public function hasErrno()
    {
        return $this->errno !== 0;
    }

    /**
     * @inheritDoc
     */
    public function setAffectedRows($affectedRows)
    {
        $this->affectedRows = $affectedRows;
    }

    /**
     * @inheritDoc
     */
    public function affectedRows()
    {
        return $this->affectedRows;
    }

    /**
     * @inheritDoc
     */
    public function options()
    {
        $server = $this->options['host'];
        $port = $this->options['port'] ?? ''; // Optional
        // Append the port to the host if it is defined.
        if (($port)) {
            $server .= ":$port";
        }

        return [$server, $this->options];
    }

    /**
     * @inheritDoc
     */
    public function sslOptions()
    {
    }

    /**
     * @inheritDoc
     */
    public function buildSelectQuery(array $select, array $where, array $group, array $order = [], $limit = 1, $page = 0)
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function defaultValue($field)
    {
        $default = $field["default"];
        return ($default === null ? "" : " DEFAULT " .
            (preg_match('~char|binary|text|enum|set~', $field["type"]) ||
            preg_match('~^(?![a-z])~i', $default) ? $this->server->quote($default) : $default));
    }

    /**
     * @inheritDoc
     */
    public function queries($query)
    {
        static $queries = [];
        static $start;
        if (!$start) {
            $start = microtime(true);
        }
        if ($query === null) {
            // return executed queries
            return array(implode("\n", $queries), $this->formatTime($start));
        }
        $queries[] = (preg_match('~;$~', $query) ? "DELIMITER ;;\n$query;\nDELIMITER " : $query) . ";";
        return $this->connection->query($query);
    }

    /**
     * @inheritDoc
     */
    public function applyQueries($query, $tables, $escape = null)
    {
        if (!$escape) {
            $escape = function ($table) {
                return $this->server->table($table);
            };
        }
        foreach ($tables as $table) {
            if (!$this->queries("$query " . $escape($table))) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function values($query, $column = 0)
    {
        $return = [];
        $result = $this->connection->query($query);
        if (is_object($result)) {
            while ($row = $result->fetch_row()) {
                $return[] = $row[$column];
            }
        }
        return $return;
    }

    /**
     * @inheritDoc
     */
    public function keyValues($query, $connection = null, $set_keys = true)
    {
        if (!is_object($connection)) {
            $connection = $this->connection;
        }
        $return = [];
        $result = $connection->query($query);
        if (is_object($result)) {
            while ($row = $result->fetch_row()) {
                if ($set_keys) {
                    $return[$row[0]] = $row[1];
                } else {
                    $return[] = $row[0];
                }
            }
        }
        return $return;
    }

    /**
     * @inheritDoc
     */
    public function rows($query, $connection = null)
    {
        if (!is_object($connection)) {
            $connection = $this->connection;
        }
        $return = [];
        $result = $connection->query($query);
        if (is_object($result)) { // can return true
            while ($row = $result->fetch_assoc()) {
                $return[] = $row;
            }
        }
        return $return;
    }

    /**
     * @inheritDoc
     */
    public function numberRegex()
    {
        return '((?<!o)int(?!er)|numeric|real|float|double|decimal|money)'; // not point, not interval
    }

    /**
     * Compute size of database
     * @param string
     * @return string formatted
     */
    public function databaseSize($database)
    {
        if (!$this->connection->selectDatabase($database)) {
            return "?";
        }
        $return = 0;
        foreach ($this->server->tableStatus() as $tableStatus) {
            $return += $tableStatus["Data_length"] + $tableStatus["Index_length"];
        }
        return $return;
    }

    /**
     * Apply SQL function
     * @param string
     * @param string escaped column identifier
     * @return string
     */
    public function applySqlFunction($function, $column)
    {
        return ($function ? ($function == "unixepoch" ? "DATETIME($column, '$function')" :
            ($function == "count distinct" ? "COUNT(DISTINCT " : strtoupper("$function(")) . "$column)") : $column);
    }

    /**
     * Get SET NAMES if utf8mb4 might be needed
     *
     * @param string
     *
     * @return string
     */
    public function setUtf8mb4($create)
    {
        static $set = false;
        // possible false positive
        if (!$set && preg_match('~\butf8mb4~i', $create)) {
            $set = true;
            return "SET NAMES " . $this->server->charset() . ";\n\n";
        }
        return '';
    }

    /**
     * Remove current user definer from SQL command
     * @param string
     * @return string
     */
    public function removeDefiner($query)
    {
        return preg_replace('~^([A-Z =]+) DEFINER=`' . preg_replace(
            '~@(.*)~',
            '`@`(%|\1)',
            $this->server->loggedUser()
        ) . '`~', '\1', $query); //! proper escaping of user
    }

    /**
     * Find out foreign keys for each column
     * @param string
     * @return array array($col => [])
     */
    public function columnForeignKeys($table)
    {
        $return = [];
        foreach ($this->server->foreignKeys($table) as $foreignKey) {
            foreach ($foreignKey["source"] as $val) {
                $return[$val][] = $foreignKey;
            }
        }
        return $return;
    }

    /**
     * Get select clause for convertible fields
     * @param array
     * @param array
     * @param array
     * @return string
     */
    public function convertFields($columns, $fields, $select = [])
    {
        $return = "";
        foreach ($columns as $key => $val) {
            if ($select && !in_array($this->server->escapeId($key), $select)) {
                continue;
            }
            $as = $this->server->convertField($fields[$key]);
            if ($as) {
                $return .= ", $as AS " . $this->server->escapeId($key);
            }
        }
        return $return;
    }

    /**
     * Get query to compute number of found rows
     * @param string
     * @param array
     * @param bool
     * @param array
     * @return string
     */
    public function countRows($table, $where, $is_group, $group)
    {
        $query = " FROM " . $this->server->table($table) . ($where ? " WHERE " . implode(" AND ", $where) : "");
        return ($is_group && ($this->server->jush == "sql" || count($group) == 1)
            ? "SELECT COUNT(DISTINCT " . implode(", ", $group) . ")$query"
            : "SELECT COUNT(*)" . ($is_group ? " FROM (SELECT 1$query GROUP BY " . implode(", ", $group) . ") x" : $query)
        );
    }

    /**
     * Find backward keys for table
     * @param string
     * @param string
     * @return array $return[$target_table]["keys"][$key_name][$target_column] = $source_column; $return[$target_table]["name"] = $this->tableName($target_table);
     */
    public function backwardKeys($table, $tableName)
    {
        return [];
    }

    /**
     * Get descriptions of selected data
     * @param array all data to print
     * @param array
     * @return array
     */
    public function rowDescriptions($rows, $foreignKeys)
    {
        return $rows;
    }
}
