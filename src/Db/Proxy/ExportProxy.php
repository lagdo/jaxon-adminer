<?php

namespace Lagdo\Adminer\Db\Proxy;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class ExportProxy extends AbstractProxy
{
    /**
     * The databases to dump
     *
     * @var array
     */
    protected $databases;

    /**
     * The tables to dump
     *
     * @var array
     */
    protected $tables;

    /**
     * The dump options
     *
     * @var array
     */
    protected $options;

    /**
     * The queries generated by the dump
     *
     * @var array
     */
    protected $queries = [];

    /**
     * Get data for export
     *
     * @param string $database      The database name
     * @param string $table
     *
     * @return array
     */
    public function getExportOptions(string $database, string $table = '')
    {
        // From dump.inc.php
        $db_style = ['', 'USE', 'DROP+CREATE', 'CREATE'];
        $table_style = ['', 'DROP+CREATE', 'CREATE'];
        $data_style = ['', 'TRUNCATE+INSERT', 'INSERT'];
        if ($this->server->jush == 'sql') { //! use insertUpdate() in all drivers
            $data_style[] = 'INSERT+UPDATE';
        }
        // \parse_str($_COOKIE['adminer_export'], $row);
        // if(!$row) {
        $row = [
                'output' => 'text',
                'format' => 'sql',
                'db_style' => ($database != '' ? '' : 'CREATE'),
                'table_style' => 'DROP+CREATE',
                'data_style' => 'INSERT',
            ];
        // }
        // if(!isset($row['events'])) { // backwards compatibility
        $row['routines'] = $row['events'] = ($table == '');
        $row['triggers'] = $row['table_style'];
        // }
        $options = [
            'output' => [
                'label' => $this->ui->lang('Output'),
                'options' => $this->ui->dumpOutput(),
                'value' => $row['output'],
            ],
            'format' => [
                'label' => $this->ui->lang('Format'),
                'options' => $this->ui->dumpFormat(),
                'value' => $row['format'],
            ],
            'table_style' => [
                'label' => $this->ui->lang('Tables'),
                'options' => $table_style,
                'value' => $row['table_style'],
            ],
            'auto_increment' => [
                'label' => $this->ui->lang('Auto Increment'),
                'value' => 1,
                'checked' => $row['auto_increment'] ?? false,
            ],
            'data_style' => [
                'label' => $this->ui->lang('Data'),
                'options' => $data_style,
                'value' => $row['data_style'],
            ],
        ];
        if ($this->server->jush !== 'sqlite') {
            $options['db_style'] = [
                'label' => $this->ui->lang('Database'),
                'options' => $db_style,
                'value' => $row['db_style'],
            ];
            if ($this->server->support('routine')) {
                $options['routines'] = [
                    'label' => $this->ui->lang('Routines'),
                    'value' => 1,
                    'checked' => $row['routines'],
                ];
            }
            if ($this->server->support('event')) {
                $options['events'] = [
                    'label' => $this->ui->lang('Events'),
                    'value' => 1,
                    'checked' => $row['events'],
                ];
            }
        }
        if ($this->server->support('trigger')) {
            $options['triggers'] = [
                'label' => $this->ui->lang('Triggers'),
                'value' => 1,
                'checked' => $row['triggers'],
            ];
        }

        $results = [
            'options' => $options,
            'prefixes' => [],
        ];
        if (($database)) {
            $tables = [
                'headers' => [$this->ui->lang('Tables'), $this->ui->lang('Data')],
                'details' => [],
            ];
            $tables_list = $this->server->tables_list();
            foreach ($tables_list as $name => $type) {
                $prefix = \preg_replace('~_.*~', '', $name);
                //! % may be part of table name
                // $checked = ($TABLE == "" || $TABLE == (\substr($TABLE, -1) == "%" ? "$prefix%" : $name));
                // $results['prefixes'][$prefix]++;

                $tables['details'][] = \compact('prefix', 'name', 'type'/*, 'checked'*/);
            }
            $results['tables'] = $tables;
        } else {
            $databases = [
                'headers' => [$this->ui->lang('Database'), $this->ui->lang('Data')],
                'details' => [],
            ];
            $databases_list = $this->db->databases(false) ?? [];
            foreach ($databases_list as $name) {
                if (!$this->server->information_schema($name)) {
                    $prefix = \preg_replace('~_.*~', '', $name);
                    // $results['prefixes'][$prefix]++;

                    $databases['details'][] = \compact('prefix', 'name');
                }
            }
            $results['databases'] = $databases;
        }

        $results['options'] = $options;
        $results['labels'] = [
            'export' => $this->ui->lang('Export'),
        ];
        return $results;
    }

    /**
     * Dump routines and events in the connected database
     *
     * @param string $database      The database name
     *
     * @return void
     */
    protected function dumpRoutinesAndEvents(string $database)
    {
        // From dump.inc.php
        $style = $this->options["db_style"];
        $queries = [];

        if ($this->options["routines"]) {
            $sql = "SHOW FUNCTION STATUS WHERE Db = " . $this->server->q($database);
            foreach ($this->db->get_rows($sql, null, "-- ") as $row) {
                $sql = "SHOW CREATE FUNCTION " . $this->server->idf_escape($row["Name"]);
                $create = $this->db->remove_definer($this->connection->result($sql, 2));
                $queries[] = $this->db->set_utf8mb4($create);
                if ($style != 'DROP+CREATE') {
                    $queries[] = "DROP FUNCTION IF EXISTS " . $this->server->idf_escape($row["Name"]) . ";;";
                }
                $queries[] = "$create;;\n";
            }
            $sql = "SHOW PROCEDURE STATUS WHERE Db = " . $this->server->q($database);
            foreach ($this->db->get_rows($sql, null, "-- ") as $row) {
                $sql = "SHOW CREATE PROCEDURE " . $this->server->idf_escape($row["Name"]);
                $create = $this->db->remove_definer($this->connection->result($sql, 2));
                $queries[] = $this->db->set_utf8mb4($create);
                if ($style != 'DROP+CREATE') {
                    $queries[] = "DROP PROCEDURE IF EXISTS " . $this->server->idf_escape($row["Name"]) . ";;";
                }
                $queries[] = "$create;;\n";
            }
        }

        if ($this->options["events"]) {
            foreach ($this->db->get_rows("SHOW EVENTS", null, "-- ") as $row) {
                $sql = "SHOW CREATE EVENT " . $this->server->idf_escape($row["Name"]);
                $create = $this->db->remove_definer($this->connection->result($sql, 3));
                $queries[] = $this->db->set_utf8mb4($create);
                if ($style != 'DROP+CREATE') {
                    $queries[] = "DROP EVENT IF EXISTS " . $this->server->idf_escape($row["Name"]) . ";;";
                }
                $queries[] = "$create;;\n";
            }
        }

        if (\count($queries) > 0) {
            $this->queries[] = "DELIMITER ;;\n";
            foreach ($queries as $query) {
                $this->queries[] = $query;
            }
            $this->queries[] = "DELIMITER ;;\n";
        }
    }

    /**
     * Print CSV row
     *
     * @param array  $row
     *
     * @return void
     */
    protected function dumpCsv(array $row)
    {
        // From functions.inc.php
        foreach ($row as $key => $val) {
            if (\preg_match('~["\n,;\t]|^0|\.\d*0$~', $val) || $val === "") {
                $row[$key] = '"' . \str_replace('"', '""', $val) . '"';
            }
        }
        $separator = $this->options["format"] == "csv" ? "," :
            ($this->options["format"] == "tsv" ? "\t" : ";");
        $this->queries[] = \implode($separator, $row);
    }

    /**
     * Convert a value to string
     *
     * @param mixed  $val
     * @param array  $field
     *
     * @return string
     */
    protected function convertToString($val, array $field)
    {
        // From functions.inc.php
        if ($val === null) {
            return "NULL";
        }
        return $this->server->unconvert_field($field, \preg_match(
            $this->db->number_type(),
            $field["type"]
        ) && !\preg_match('~\[~', $field["full_type"]) &&
            \is_numeric($val) ? $val : $this->server->q(($val === false ? 0 : $val)));
    }

    /**
     * Export table structure
     *
     * @param string $table
     * @param string $style
     * @param int    $is_view       0 table, 1 view, 2 temporary view table
     *
     * @return null prints data
     */
    protected function dumpTable(string $table, string $style, int $is_view = 0)
    {
        // From adminer.inc.php
        if ($this->options['format'] != "sql") {
            $this->queries[] = "\xef\xbb\xbf"; // UTF-8 byte order mark
            if ($style) {
                $this->dumpCsv(\array_keys($this->server->fields($table)));
            }
            return;
        }

        if ($is_view == 2) {
            $fields = [];
            foreach ($this->server->fields($table) as $name => $field) {
                $fields[] = $this->server->idf_escape($name) . ' ' . $field['full_type'];
            }
            $create = "CREATE TABLE " . $this->server->table($table) . " (" . \implode(", ", $fields) . ")";
        } else {
            $create = $this->server->create_sql($table, $this->options['auto_increment'], $style);
        }
        $this->db->set_utf8mb4($create);
        if ($style && $create) {
            if ($style == "DROP+CREATE" || $is_view == 1) {
                $this->queries[] = "DROP " . ($is_view == 2 ? "VIEW" : "TABLE") .
                    " IF EXISTS " . $this->server->table($table) . ';';
            }
            if ($is_view == 1) {
                $create = $this->db->remove_definer($create);
            }
            $this->queries[] = $create . ';';
        }
    }

    /** Export table data
     *
     * @param string
     * @param string
     * @param string
     *
     * @return null prints data
     */
    protected function dumpData($table, $style, $query)
    {
        $fields = [];
        $max_packet = ($this->server->jush == "sqlite" ? 0 : 1048576); // default, minimum is 1024
        if ($style) {
            if ($this->options["format"] == "sql") {
                if ($style == "TRUNCATE+INSERT") {
                    $this->queries[] = $this->server->truncate_sql($table) . ";\n";
                }
                $fields = $this->server->fields($table);
            }
            $result = $this->connection->query($query, 1); // 1 - MYSQLI_USE_RESULT //! enum and set as numbers
            if ($result) {
                $insert = "";
                $buffer = "";
                $keys = [];
                $suffix = "";
                $fetch_function = ($table != '' ? 'fetch_assoc' : 'fetch_row');
                while ($row = $result->$fetch_function()) {
                    if (!$keys) {
                        $values = [];
                        foreach ($row as $val) {
                            $field = $result->fetch_field();
                            $keys[] = $field->name;
                            $key = $this->server->idf_escape($field->name);
                            $values[] = "$key = VALUES($key)";
                        }
                        $suffix = ";\n";
                        if ($style == "INSERT+UPDATE") {
                            $suffix = "\nON DUPLICATE KEY UPDATE " . \implode(", ", $values) . ";\n";
                        }
                    }
                    if ($this->options["format"] != "sql") {
                        if ($style == "table") {
                            $this->dumpCsv($keys);
                            $style = "INSERT";
                        }
                        $this->dumpCsv($row);
                    } else {
                        if (!$insert) {
                            $insert = "INSERT INTO " . $this->server->table($table) . " (" .
                                \implode(", ", \array_map(function ($key) {
                                    return $this->server->idf_escape($key);
                                }, $keys)) . ") VALUES";
                        }
                        foreach ($row as $key => $val) {
                            $field = $fields[$key];
                            $row[$key] = $this->convertToString($val, $field);
                        }
                        $s = ($max_packet ? "\n" : " ") . "(" . \implode(",\t", $row) . ")";
                        if (!$buffer) {
                            $buffer = $insert . $s;
                        } elseif (\strlen($buffer) + 4 + \strlen($s) + \strlen($suffix) < $max_packet) { // 4 - length specification
                            $buffer .= ",$s";
                        } else {
                            $this->queries[] = $buffer . $suffix;
                            $buffer = $insert . $s;
                        }
                    }
                }
                if ($buffer) {
                    $this->queries[] = $buffer . $suffix;
                }
            } elseif ($this->options["format"] == "sql") {
                $this->queries[] = "-- " . \str_replace("\n", " ", $this->connection->error) . "\n";
            }
        }
    }

    /**
     * Dump tables and views in the connected database
     *
     * @param string $database      The database name
     *
     * @return array
     */
    protected function dumpTablesAndViews(string $database)
    {
        if (!$this->options["table_style"] && !$this->options["data_style"]) {
            return [];
        }

        $dbDumpTable = $this->tables['list'] === '*' &&
            \in_array($database, $this->databases["list"]);
        $dbDumpData = \in_array($database, $this->databases["data"]);
        $views = [];
        $dbTables = $this->server->table_status('', true);
        foreach ($dbTables as $table => $table_status) {
            $dumpTable = $dbDumpTable || \in_array($table, $this->tables['list']);
            $dumpData = $dbDumpData || \in_array($table, $this->tables["data"]);
            if ($dumpTable || $dumpData) {
                // if($ext == "tar")
                // {
                //     $tmp_file = new TmpFile;
                //     ob_start([$tmp_file, 'write'], 1e5);
                // }

                $this->dumpTable(
                    $table,
                    ($dumpTable ? $this->options["table_style"] : ""),
                    ($this->server->is_view($table_status) ? 2 : 0)
                );
                if ($this->server->is_view($table_status)) {
                    $views[] = $table;
                } elseif ($dumpData) {
                    $fields = $this->server->fields($table);
                    $query = "SELECT *" . $this->db->convert_fields($fields, $fields) .
                        " FROM " . $this->server->table($table);
                    $this->dumpData($table, $this->options["data_style"], $query);
                }
                if ($this->options['is_sql'] && $this->options["triggers"] && $dumpTable &&
                    ($triggers = $this->server->trigger_sql($table))) {
                    $this->queries[] = "DELIMITER ;";
                    $this->queries[] = $triggers;
                    $this->queries[] = "DELIMITER ;";
                }

                // if($ext == "tar")
                // {
                //     ob_end_flush();
                //     tar_file((DB != "" ? "" : "$db/") . "$table.csv", $tmp_file);
                // } else
                if ($this->options['is_sql']) {
                    $this->queries[] = '';
                }
            }
        }

        // add FKs after creating tables (except in MySQL which uses SET FOREIGN_KEY_CHECKS=0)
        if ($this->server->support('fkeys_sql')) {
            foreach ($dbTables as $table => $table_status) {
                $dumpTable = true; // (DB == "" || \in_array($table, $this->options["tables"]));
                if ($dumpTable && !$this->server->is_view($table_status)) {
                    $this->queries[] = $this->server->foreign_keys_sql($table);
                }
            }
        }

        foreach ($views as $view) {
            $this->dumpTable($view, $this->options["table_style"], 1);
        }

        // if($ext == "tar") {
        //     $this->queries[] = pack("x512");
        // }
    }

    /**
     * Export databases
     *
     * @param array  $databases     The databases to dump
     * @param array  $tables        The tables to dump
     * @param array  $options       The export options
     *
     * @return array
     */
    public function exportDatabases(array $databases, array $tables, array $options)
    {
        // From dump.inc.php
        // $tables = array_flip($options["tables"]) + array_flip($options["data"]);
        // $ext = dump_headers((count($tables) == 1 ? key($tables) : DB), (DB == "" || count($tables) > 1));
        $options['is_sql'] = \preg_match('~sql~', $options["format"]);
        $this->databases = $databases;
        $this->tables = $tables;
        $this->options = $options;

        $headers = null;
        if ($this->options['is_sql']) {
            $headers = [
                'version' => $this->db->version(),
                'driver' => $this->server->getName(),
                'server' => \str_replace("\n", " ", $this->connection->server_info),
                'sql' => false,
                'data_style' => false,
            ];
            if ($this->server->jush == "sql") {
                $headers['sql'] = true;
                if (isset($options["data_style"])) {
                    $headers['data_style'] = true;
                }
                // Set some options in database server
                $this->connection->query("SET time_zone = '+00:00'");
                $this->connection->query("SET sql_mode = ''");
            }
        }

        $style = $options["db_style"];

        foreach (\array_unique(\array_merge($databases['list'], $databases['data'])) as $db) {
            // $this->ui->dumpDatabase($db);
            if ($this->connection->select_db($db)) {
                $sql = "SHOW CREATE DATABASE " . $this->server->idf_escape($db);
                if ($this->options['is_sql'] && \preg_match('~CREATE~', $style) &&
                    ($create = $this->connection->result($sql, 1))) {
                    $this->db->set_utf8mb4($create);
                    if ($style == "DROP+CREATE") {
                        $this->queries[] = "DROP DATABASE IF EXISTS " . $this->server->idf_escape($db) . ";";
                    }
                    $this->queries[] = $create . ";\n";
                }
                if ($this->options['is_sql']) {
                    if ($style) {
                        if (($query = $this->server->use_sql($db))) {
                            $this->queries[] = $query . ";";
                        }
                        $this->queries[] = ''; // Empty line
                    }

                    $this->dumpRoutinesAndEvents($db);
                }

                $this->dumpTablesAndViews($db);
            }
        }

        if ($this->options['is_sql']) {
            $this->queries[] = "-- " . $this->connection->result("SELECT NOW()");
        }

        return [
            'headers' => $headers,
            'queries' => $this->queries,
        ];
    }
}
