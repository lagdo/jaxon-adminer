<?php

namespace Lagdo\Adminer\Db;

global $LANG, $translations;

include __DIR__ . '/../../adminer/lang.inc.php';
include __DIR__ . "/../../adminer/lang/en.inc.php";

use Lagdo\Adminer\Drivers\AdminerDbTrait;
use Lagdo\Adminer\Drivers\AdminerDbInterface;

use function adminer\lang;

class AdminerDb implements AdminerDbInterface, ConnectionInterface, DriverInterface, ServerInterface
{
    use AdminerDbTrait;
    use ConnectionTrait;
    use DriverTrait;
    use ServerTrait;

    /**
     * @var array
     */
    public $options;

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
    public function getOptions()
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
    public function connectSsl()
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
    public function default_value($field)
    {
        $default = $field["default"];
        return ($default === null ? "" : " DEFAULT " .
            (preg_match('~char|binary|text|enum|set~', $field["type"]) ||
            preg_match('~^(?![a-z])~i', $default) ? $this->server->q($default) : $default));
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
            return array(implode("\n", $queries), $this->format_time($start));
        }
        $queries[] = (preg_match('~;$~', $query) ? "DELIMITER ;;\n$query;\nDELIMITER " : $query) . ";";
        return $this->connection->query($query);
    }

    /**
     * @inheritDoc
     */
    public function apply_queries($query, $tables, $escape = null)
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
    public function get_vals($query, $column = 0)
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
    public function get_key_vals($query, $connection2 = null, $set_keys = true)
    {
        if (!is_object($connection2)) {
            $connection2 = $this->connection;
        }
        $return = [];
        $result = $connection2->query($query);
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
    public function get_rows($query, $connection2 = null)
    {
        $conn = (is_object($connection2) ? $connection2 : $this->connection);
        $return = [];
        $result = $conn->query($query);
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
    public function number_type()
    {
        return '((?<!o)int(?!er)|numeric|real|float|double|decimal|money)'; // not point, not interval
    }

    /**
     * Compute size of database
     * @param string
     * @return string formatted
     */
    public function db_size($database)
    {
        if (!$this->connection->select_db($database)) {
            return "?";
        }
        $return = 0;
        foreach ($this->server->table_status() as $table_status) {
            $return += $table_status["Data_length"] + $table_status["Index_length"];
        }
        return $return;
    }

    /**
     * Apply SQL function
     * @param string
     * @param string escaped column identifier
     * @return string
     */
    public function apply_sql_function($function, $column)
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
    public function set_utf8mb4($create)
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
    public function remove_definer($query)
    {
        return preg_replace('~^([A-Z =]+) DEFINER=`' . preg_replace(
            '~@(.*)~',
            '`@`(%|\1)',
            $this->server->logged_user()
        ) . '`~', '\1', $query); //! proper escaping of user
    }

    /**
     * Find out foreign keys for each column
     * @param string
     * @return array array($col => [])
     */
    public function column_foreign_keys($table)
    {
        $return = [];
        foreach ($this->server->foreign_keys($table) as $foreign_key) {
            foreach ($foreign_key["source"] as $val) {
                $return[$val][] = $foreign_key;
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
    public function convert_fields($columns, $fields, $select = [])
    {
        $return = "";
        foreach ($columns as $key => $val) {
            if ($select && !in_array($this->server->idf_escape($key), $select)) {
                continue;
            }
            $as = $this->server->convert_field($fields[$key]);
            if ($as) {
                $return .= ", $as AS " . $this->server->idf_escape($key);
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
    public function count_rows($table, $where, $is_group, $group)
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
