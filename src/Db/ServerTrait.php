<?php

namespace Lagdo\Adminer\Db;

trait ServerTrait
{
    /**
     * Get the driver name
     *
     * @return string
     */
    public function getName()
    {
        return $this->server->getName();
    }

    /**
     * Connect to the database server
     * Return a string for error
     *
     * @return ConnectionInterface|string
     */
    public function createConnection()
    {
        return $this->server->connect();
    }

    /**
     * Select the database and schema
     *
     * @param string $database  The database name
     * @param string $schema    The database schema
     *
     * @return array
     */
    public function selectDatabase(string $database, string $schema)
    {
        return $this->server->selectDatabase($database, $schema);
    }

    /**
     * Select the database and schema
     *
     * @return string
     */
    public function current_db()
    {
        return $this->server->current_db();
    }

    /**
     * Select the database and schema
     *
     * @return string
     */
    public function current_schema()
    {
        return $this->server->current_schema();
    }

    /**
     * Get the name of the primary id field
     *
     * @return string
     */
    public function primary()
    {
        return $this->server->primary();
    }

    /**
     * Escape database identifier
     *
     * @param string $idf
     *
     * @return string
     */
    public function idf_escape($idf)
    {
        return $this->server->idf_escape($idf);
    }

    /**
     * Unescape database identifier
     * @param string $idf
     * @return string
     */
    public function idf_unescape($idf)
    {
        return $this->server->idf_unescape($idf);
    }

    /**
     * Shortcut for $this->connection->quote($string)
     * @param string
     * @return string
     */
    public function quote($string)
    {
        return $this->server->q($string);
    }

    /**
     * Get connection charset
     *
     * @return string
     */
    public function charset()
    {
        return $this->server->charset();
    }

    /**
     * Get escaped table name
     *
     * @param string
     *
     * @return string
     */
    public function table($idf)
    {
        return $this->server->table($idf);
    }

    /**
     * Get cached list of databases
     *
     * @param bool
     *
     * @return array
     */
    public function get_databases($flush)
    {
        return $this->server->get_databases($flush);
    }

    /**
     * Formulate SQL query with limit
     * @param string everything after SELECT
     * @param string including WHERE
     * @param int
     * @param int
     * @param string
     * @return string
     */
    public function limit($query, $where, $limit, $offset = 0, $separator = " ")
    {
        return $this->server->limit($query, $where, $limit, $offset, $separator);
    }

    /**
     * Formulate SQL modification query with limit 1
     * @param string
     * @param string everything after UPDATE or DELETE
     * @param string
     * @param string
     * @return string
     */
    public function limit1($table, $query, $where, $separator = "\n")
    {
        return $this->server->limit1($table, $query, $where, $separator);
    }

    /**
     * Get database collation
     * @param string
     * @param array result of collations()
     * @return string
     */
    public function db_collation($db, $collations)
    {
        return $this->server->db_collation($db, $collations);
    }

    /**
     * Get supported engines
     * @return array
     */
    public function engines()
    {
        return $this->server->engines();
    }

    /**
     * Get logged user
     * @return string
     */
    public function logged_user()
    {
        return $this->server->logged_user();
    }

    /**
     * Format foreign key to use in SQL query
     *
     * @param array ("db" => string, "ns" => string, "table" => string, "source" => array, "target" => array,
     * "on_delete" => one of $this->on_actions, "on_update" => one of $this->on_actions)
     *
     * @return string
     */
    public function format_foreign_key($foreign_key)
    {
        return $this->server->format_foreign_key($foreign_key);
    }

    /**
     * Get tables list
     * @return array array($name => $type)
     */
    public function tables_list()
    {
        return $this->server->tables_list();
    }

    /**
     * Count tables in all databases
     * @param array
     * @return array array($db => $tables)
     */
    public function count_tables($databases)
    {
        return $this->server->count_tables($databases);
    }

    /**
     * Get table status
     * @param string
     * @param bool return only "Name", "Engine" and "Comment" fields
     * @return array array($name => array("Name" => , "Engine" => , "Comment" => , "Oid" => , "Rows" => , "Collation" => , "Auto_increment" => , "Data_length" => , "Index_length" => , "Data_free" => )) or only inner array with $name
     */
    public function table_status($name = "", $fast = false)
    {
        return $this->server->table_status($name, $fast);
    }

    /**
     * Get status of a single table and fall back to name on error
     * @param string
     * @param bool
     * @return array
     */
    public function table_status1($table, $fast = false)
    {
        return $this->server->table_status1($table, $fast);
    }

    /**
     * Find out whether the identifier is view
     * @param array
     * @return bool
     */
    public function is_view($table_status)
    {
        return $this->server->is_view($table_status);
    }

    /**
     * Check if table supports foreign keys
     * @param array result of table_status
     * @return bool
     */
    public function fk_support($table_status)
    {
        return $this->server->fk_support($table_status);
    }

    /**
     * Get information about fields
     * @param string
     * @return array array($name => array("field" => , "full_type" => , "type" => , "length" => , "unsigned" => , "default" => , "null" => , "auto_increment" => , "on_update" => , "collation" => , "privileges" => , "comment" => , "primary" => ))
     */
    public function fields($table)
    {
        return $this->server->fields($table);
    }

    /**
     * Get table indexes
     * @param string
     * @param string ConnectionInterface to use
     * @return array array($key_name => array("type" => , "columns" => [], "lengths" => [], "descs" => []))
     */
    public function indexes($table, $connection2 = null)
    {
        return $this->server->indexes($table, $connection2);
    }

    /**
     * Get foreign keys in table
     * @param string
     * @return array array($name => array("db" => , "ns" => , "table" => , "source" => [], "target" => [], "on_delete" => , "on_update" => ))
     */
    public function foreign_keys($table)
    {
        return $this->server->foreign_keys($table);
    }

    /**
     * Get view SELECT
     * @param string
     * @return array array("select" => )
     */
    public function view($name)
    {
        return $this->server->view($name);
    }

    /**
     * Get sorted grouped list of collations
     * @return array
     */
    public function collations()
    {
        return $this->server->collations();
    }

    /**
     * Find out if database is information_schema
     * @param string
     * @return bool
     */
    public function information_schema($db)
    {
        return $this->server->information_schema($db);
    }

    /**
     * Get escaped error message
     * @return string
     */
    public function error()
    {
        return $this->server->error();
    }

    /**
     * Create database
     * @param string
     * @param string
     * @return string|boolean
     */
    public function create_database($db, $collation)
    {
        return $this->server->create_database($db, $collation) ;
    }

    /**
     * Drop databases
     * @param array
     * @return bool
     */
    public function drop_databases($databases)
    {
        return $this->server->drop_databases($databases);
    }

    /**
     * Rename database from DB
     * @param string new name
     * @param string
     * @return bool
     */
    public function rename_database($name, $collation)
    {
        return $this->server->rename_database($name, $collation);
    }

    /**
     * Generate modifier for auto increment column
     * @return string
     */
    public function auto_increment()
    {
        return $this->server->auto_increment();
    }

    /**
     * Get last auto increment ID
     * @return string
     */
    public function last_id()
    {
        return $this->server->last_id();
    }

    /**
     * Run commands to create or alter table
     * @param string "" to create
     * @param string new name
     * @param array of array($orig, $process_field, $after)
     * @param array of strings
     * @param string
     * @param string
     * @param string
     * @param string number
     * @param string
     * @return bool
     */
    public function alter_table($table, $name, $fields, $foreign, $comment, $engine, $collation, $auto_increment, $partitioning)
    {
        return $this->server->alter_table($table, $name, $fields, $foreign, $comment, $engine, $collation, $auto_increment, $partitioning);
    }

    /**
     * Run commands to alter indexes
     * @param string escaped table name
     * @param array of array("index type", "name", array("column definition", ...)) or array("index type", "name", "DROP")
     * @return bool
     */
    public function alter_indexes($table, $alter)
    {
        return $this->server->alter_indexes($table, $alter);
    }

    /**
     * Drop views
     * @param array
     * @return bool
     */
    public function drop_views($views)
    {
        return $this->server->drop_views($views);
    }

    /**
     * Run commands to truncate tables
     * @param array
     * @return bool
     */
    public function truncate_tables($tables)
    {
        return $this->server->truncate_tables($tables);
    }

    /**
     * Drop tables
     * @param array
     * @return bool
     */
    public function drop_tables($tables)
    {
        return $this->server->drop_tables($tables);
    }

    /**
     * Move tables to other schema
     * @param array
     * @param array
     * @param string
     * @return bool
     */
    public function move_tables($tables, $views, $target)
    {
        return $this->server->move_tables($tables, $views, $target);
    }

    /**
     * Copy tables to other schema
     * @param array
     * @param array
     * @param string
     * @return bool
     */
    public function copy_tables($tables, $views, $target)
    {
        return $this->server->copy_tables($tables, $views, $target);
    }

    /**
     * Get information about trigger
     * @param string trigger name
     * @return array array("Trigger" => , "Timing" => , "Event" => , "Of" => , "Type" => , "Statement" => )
     */
    public function trigger($name)
    {
        return $this->server->trigger($name);
    }

    /**
     * Get defined triggers
     * @param string
     * @return array array($name => array($timing, $event))
     */
    public function triggers($table)
    {
        return $this->server->triggers($table);
    }

    /**
     * Get trigger options
     * @return array ("Timing" => [], "Event" => [], "Type" => [])
     */
    public function trigger_options()
    {
        return $this->server->trigger_options();
    }

    /**
     * Get information about stored routine
     * @param string
     * @param string "FUNCTION" or "PROCEDURE"
     * @return array ("fields" => array("field" => , "type" => , "length" => , "unsigned" => , "inout" => , "collation" => ), "returns" => , "definition" => , "language" => )
     */
    public function routine($name, $type)
    {
        return $this->server->routine($name, $type);
    }

    /**
     * Get list of routines
     * @return array ("SPECIFIC_NAME" => , "ROUTINE_NAME" => , "ROUTINE_TYPE" => , "DTD_IDENTIFIER" => )
     */
    public function routines()
    {
        return $this->server->routines();
    }

    /**
     * Get list of available routine languages
     * @return array
     */
    public function routine_languages()
    {
        return $this->server->routine_languages() ;
    }

    /**
     * Get routine signature
     * @param string
     * @param array result of routine()
     * @return string
     */
    public function routine_id($name, $row)
    {
        return $this->server->routine_id($name, $row);
    }

    /**
     * Explain select
     * @param ConnectionInterface
     * @param string
     * @return Statement|null
     */
    public function explain($connection, $query)
    {
        return $this->server->explain($connection, $query);
    }

    /**
     * Get approximate number of rows
     * @param array
     * @param array
     * @return int or null if approximate number can't be retrieved
     */
    public function found_rows($table_status, $where)
    {
        return $this->server->found_rows($table_status, $where);
    }

    /**
     * Get user defined types
     * @return array
     */
    public function user_types()
    {
        return $this->server->user_types() ;
    }

    /**
     * Get existing schemas
     * @return array
     */
    public function schemas()
    {
        return $this->server->schemas();
    }

    /**
     * Get current schema
     * @return string
     */
    public function get_schema()
    {
        return $this->server->get_schema();
    }

    /**
     * Set current schema
     * @param string
     * @param ConnectionInterface
     * @return bool
     */
    public function set_schema($schema, $connection2 = null)
    {
        return $this->server->set_schema($schema, $connection2 = null);
    }

    /**
     * Get SQL command to create table
     * @param string
     * @param bool
     * @param string
     * @return string
     */
    public function create_sql($table, $auto_increment, $style)
    {
        return $this->server->create_sql($table, $auto_increment, $style);
    }

    /**
     * Get SQL command to create foreign keys
     *
     * create_sql() produces CREATE TABLE without FK CONSTRAINTs
     * foreign_keys_sql() produces all FK CONSTRAINTs as ALTER TABLE ... ADD CONSTRAINT
     * so that all FKs can be added after all tables have been created, avoiding any need
     * to reorder CREATE TABLE statements in order of their FK dependencies
     *
     * @param string
     *
     * @return string
     */
    public function foreign_keys_sql($table)
    {
        return $this->server->foreign_keys_sql($table);
    }

    /**
     * Get SQL command to truncate table
     * @param string
     * @return string
     */
    public function truncate_sql($table)
    {
        return $this->server->truncate_sql($table);
    }

    /**
     * Get SQL command to change database
     * @param string
     * @return string
     */
    public function use_sql($database)
    {
        return $this->server->use_sql($database);
    }

    /**
     * Get SQL commands to create triggers
     * @param string
     * @return string
     */
    public function trigger_sql($table)
    {
        return $this->server->trigger_sql($table);
    }

    /**
     * Get server variables
     * @return array ($name => $value)
     */
    public function show_variables()
    {
        return $this->server->show_variables();
    }

    /**
     * Get status variables
     * @return array ($name => $value)
     */
    public function show_status()
    {
        return $this->server->show_status();
    }

    /**
     * Get process list
     * @return array ($row)
     */
    public function process_list()
    {
        return $this->server->process_list();
    }

    /**
     * Convert field in select and edit
     * @param array $field one element from $this->fields()
     * @return string
     */
    public function convert_field(array $field)
    {
        return $this->server->convert_field($field);
    }

    /**
     * Convert value in edit after applying functions back
     * @param array $field one element from $this->fields()
     * @param string $return
     * @return string
     */
    public function unconvert_field(array $field, $return)
    {
        return $this->server->unconvert_field($field, $return);
    }

    /**
     * Check whether a feature is supported
     * @param string "comment", "copy", "database", "descidx", "drop_col", "dump", "event", "indexes", "kill", "materializedview", "partitioning", "privileges", "procedure", "processlist", "routine", "scheme", "sequence", "status", "table", "trigger", "type", "variables", "view", "view_trigger"
     * @return bool
     */
    public function support($feature)
    {
        return $this->server->support($feature);
    }

    /**
     * Check if connection has at least the given version
     * @param string $version required version
     * @param string $maria_db required MariaDB version
     * @param ConnectionInterface|null $connection2
     * @return bool
     */
    public function min_version($version, $maria_db = "", ConnectionInterface $connection2 = null)
    {
        return $this->server->min_version($version, $maria_db, $connection2);
    }

    /**
     * Kill a process
     * @param int
     * @return bool
     */
    // public function kill_process($val)
    // {
    //     return $this->server->kill_process($val);
    // }

    /**
     * Return query to get connection ID
     * @return string
     */
    // public function connection_id()
    // {
    //     return $this->server->connection_id();
    // }

    /**
     * Get maximum number of connections
     * @return int
     */
    // public function max_connections()
    // {
    //     return $this->server->max_connections();
    // }

    /**
     * Get driver config
     * @return array array('possible_drivers' => , 'jush' => , 'types' => , 'structured_types' => , 'unsigned' => , 'operators' => , 'functions' => , 'grouping' => , 'edit_functions' => )
     */
    public function driver_config()
    {
        return $this->server->driver_config();
    }





    /**
     * Get the server jush
     * @return string
     */
    public function jush()
    {
        return $this->server->jush;
    }

    /**
     * @return array
     */
    public function unsigned()
    {
        return $this->server->unsigned;
    }

    /**
     * @return array
     */
    public function functions()
    {
        return $this->server->functions;
    }

    /**
     * @return array
     */
    public function grouping()
    {
        return $this->server->grouping;
    }

    /**
     * @return array
     */
    public function operators()
    {
        return $this->server->operators;
    }

    /**
     * @return array
     */
    public function editFunctions()
    {
        return $this->server->edit_functions;
    }

    /**
     * @return array
     */
    public function types()
    {
        return $this->server->types;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function typeExists(string $type)
    {
        return isset($this->server->types[$type]);
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    public function type(string $type)
    {
        return $this->server->types[$type];
    }

    /**
     * @return array
     */
    public function structuredTypes()
    {
        return $this->server->structured_types;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function setStructuredType(string $key, $value)
    {
        $this->server->structured_types[$key] = $value;
    }

    /**
     * @return array
     */
    public function onActions()
    {
        return \explode('|', $this->server->on_actions);
    }
}
