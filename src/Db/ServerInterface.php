<?php

namespace Lagdo\Adminer\Db;

interface ServerInterface
{
    /**
     * Get the driver name
     *
     * @return string
     */
    public function name();

    /**
     * Connect to the database server
     * Return a string for error
     *
     * @return ConnectionInterface|string
     */
    public function createConnection();

    /**
     * Select the database and schema
     *
     * @param string $database  The database name
     * @param string $schema    The database schema
     *
     * @return array
     */
    public function selectDatabase(string $database, string $schema);

    /**
     * Select the database and schema
     *
     * @return string
     */
    public function selectedDatabase();

    /**
     * Select the database and schema
     *
     * @return string
     */
    public function selectedSchema();

    /**
     * Get the name of the primary id field
     *
     * @return string
     */
    public function primaryIdName();

    /**
     * Escape database identifier
     *
     * @param string $idf
     *
     * @return string
     */
    public function escapeId($idf);

    /**
     * Unescape database identifier
     * @param string $idf
     * @return string
     */
    public function unescapeId($idf);

    /**
     * Shortcut for $this->connection->quote($string)
     * @param string
     * @return string
     */
    public function quote($string);

    /**
     * Get connection charset
     *
     * @return string
     */
    public function charset();

    /**
     * Get escaped table name
     *
     * @param string
     *
     * @return string
     */
    public function table($idf);

    /**
     * Get cached list of databases
     *
     * @param bool
     *
     * @return array
     */
    public function databases($flush);

    /**
     * Formulate SQL query with limit
     * @param string everything after SELECT
     * @param string including WHERE
     * @param int
     * @param int
     * @param string
     * @return string
     */
    public function limit($query, $where, $limit, $offset = 0, $separator = " ");

    /**
     * Formulate SQL modification query with limit 1
     * @param string
     * @param string everything after UPDATE or DELETE
     * @param string
     * @param string
     * @return string
     */
    public function limitToOne($table, $query, $where, $separator = "\n");

    /**
     * Get database collation
     * @param string
     * @param array result of collations()
     * @return string
     */
    public function databaseCollation($db, $collations);

    /**
     * Get supported engines
     * @return array
     */
    public function engines();

    /**
     * Get logged user
     * @return string
     */
    public function loggedUser();

    /**
     * Format foreign key to use in SQL query
     *
     * @param array ("db" => string, "ns" => string, "table" => string, "source" => array, "target" => array,
     * "on_delete" => one of $this->onActions, "on_update" => one of $this->onActions)
     *
     * @return string
     */
    public function formatForeignKey($foreignKey);

    /**
     * Get tables list
     * @return array array($name => $type)
     */
    public function tables();

    /**
     * Count tables in all databases
     * @param array
     * @return array array($db => $tables)
     */
    public function countTables($databases);

    /**
     * Get table status
     * @param string
     * @param bool return only "Name", "Engine" and "Comment" fields
     * @return array array($name => array("Name" => , "Engine" => , "Comment" => , "Oid" => , "Rows" => , "Collation" => , "Auto_increment" => , "Data_length" => , "Index_length" => , "Data_free" => )) or only inner array with $name
     */
    public function tableStatus($name = "", $fast = false);

    /**
     * Get status of a single table and fall back to name on error
     * @param string
     * @param bool
     * @return array
     */
    public function tableStatusOrName($table, $fast = false);

    /**
     * Find out whether the identifier is view
     * @param array
     * @return bool
     */
    public function isView($tableStatus);

    /**
     * Check if table supports foreign keys
     * @param array result of table_status
     * @return bool
     */
    public function supportForeignKeys($tableStatus);

    /**
     * Get information about fields
     * @param string
     * @return array array($name => array("field" => , "full_type" => , "type" => , "length" => , "unsigned" => , "default" => , "null" => , "auto_increment" => , "on_update" => , "collation" => , "privileges" => , "comment" => , "primary" => ))
     */
    public function fields($table);

    /**
     * Get table indexes
     * @param string
     * @param string ConnectionInterface to use
     * @return array array($key_name => array("type" => , "columns" => [], "lengths" => [], "descs" => []))
     */
    public function indexes($table, $connection = null);

    /**
     * Get foreign keys in table
     * @param string
     * @return array array($name => array("db" => , "ns" => , "table" => , "source" => [], "target" => [], "on_delete" => , "on_update" => ))
     */
    public function foreignKeys($table);

    /**
     * Get view SELECT
     * @param string
     * @return array array("select" => )
     */
    public function view($name);

    /**
     * Get sorted grouped list of collations
     * @return array
     */
    public function collations();

    /**
     * Find out if database is information_schema
     * @param string
     * @return bool
     */
    public function isInformationSchema($db);

    /**
     * Create database
     * @param string
     * @param string
     * @return string|boolean
     */
    public function createDatabase($db, $collation) ;

    /**
     * Drop databases
     * @param array
     * @return bool
     */
    public function dropDatabases($databases);

    /**
     * Rename database from DB
     * @param string new name
     * @param string
     * @return bool
     */
    public function renameDatabase($name, $collation);

    /**
     * Generate modifier for auto increment column
     * @return string
     */
    public function autoIncrement();

    /**
     * Get last auto increment ID
     * @return string
     */
    public function lastAutoIncrementId();

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
    public function alterTable($table, $name, $fields, $foreign, $comment, $engine, $collation, $auto_increment, $partitioning);

    /**
     * Run commands to alter indexes
     * @param string escaped table name
     * @param array of array("index type", "name", array("column definition", ...)) or array("index type", "name", "DROP")
     * @return bool
     */
    public function alterIndexes($table, $alter);

    /**
     * Drop views
     * @param array
     * @return bool
     */
    public function dropViews($views);

    /**
     * Run commands to truncate tables
     * @param array
     * @return bool
     */
    public function truncateTables($tables);

    /**
     * Drop tables
     * @param array
     * @return bool
     */
    public function dropTables($tables);

    /**
     * Move tables to other schema
     * @param array
     * @param array
     * @param string
     * @return bool
     */
    public function moveTables($tables, $views, $target);

    /**
     * Copy tables to other schema
     * @param array
     * @param array
     * @param string
     * @return bool
     */
    public function copyTables($tables, $views, $target);

    /**
     * Get information about trigger
     * @param string trigger name
     * @return array array("Trigger" => , "Timing" => , "Event" => , "Of" => , "Type" => , "Statement" => )
     */
    public function trigger($name);

    /**
     * Get defined triggers
     * @param string
     * @return array array($name => array($timing, $event))
     */
    public function triggers($table);

    /**
     * Get trigger options
     * @return array ("Timing" => [], "Event" => [], "Type" => [])
     */
    public function triggerOptions();

    /**
     * Get information about stored routine
     * @param string
     * @param string "FUNCTION" or "PROCEDURE"
     * @return array ("fields" => array("field" => , "type" => , "length" => , "unsigned" => , "inout" => , "collation" => ), "returns" => , "definition" => , "language" => )
     */
    public function routine($name, $type);

    /**
     * Get list of routines
     * @return array ("SPECIFIC_NAME" => , "ROUTINE_NAME" => , "ROUTINE_TYPE" => , "DTD_IDENTIFIER" => )
     */
    public function routines();

    /**
     * Get list of available routine languages
     * @return array
     */
    public function routineLanguages() ;

    /**
     * Get routine signature
     * @param string
     * @param array result of routine()
     * @return string
     */
    public function routineId($name, $row);

    /**
     * Explain select
     * @param ConnectionInterface
     * @param string
     * @return Statement|null
     */
    public function explain($connection, $query);

    /**
     * Get user defined types
     * @return array
     */
    public function userTypes();

    /**
     * Get existing schemas
     * @return array
     */
    public function schemas();

    /**
     * Get current schema
     * @return string
     */
    public function schema();

    /**
     * Set current schema
     * @param string
     * @param ConnectionInterface
     * @return bool
     */
    public function selectSchema($schema, $connection = null);

    /**
     * Get SQL command to create table
     * @param string
     * @param bool
     * @param string
     * @return string
     */
    public function createTableSql($table, $auto_increment, $style);

    /**
     * Get SQL command to create foreign keys
     *
     * createTableSql() produces CREATE TABLE without FK CONSTRAINTs
     * foreignKeysSql() produces all FK CONSTRAINTs as ALTER TABLE ... ADD CONSTRAINT
     * so that all FKs can be added after all tables have been created, avoiding any need
     * to reorder CREATE TABLE statements in order of their FK dependencies
     *
     * @param string
     *
     * @return string
     */
    public function foreignKeysSql($table);

    /**
     * Get SQL command to truncate table
     * @param string
     * @return string
     */
    public function truncateTableSql($table);

    /**
     * Get SQL command to change database
     * @param string
     * @return string
     */
    public function useDatabaseSql($database);

    /**
     * Get SQL commands to create triggers
     * @param string
     * @return string
     */
    public function createTriggerSql($table);

    /**
     * Get server variables
     * @return array ($name => $value)
     */
    public function variables();

    /**
     * Get status variables
     * @return array ($name => $value)
     */
    public function statusVariables();

    /**
     * Get process list
     * @return array ($row)
     */
    public function processes();

    /**
     * Convert field in select and edit
     * @param array $field one element from $this->fields()
     * @return string
     */
    public function convertField(array $field);

    /**
     * Convert value in edit after applying functions back
     * @param array $field one element from $this->fields()
     * @param string $return
     * @return string
     */
    public function unconvertField(array $field, $return);

    /**
     * Check whether a feature is supported
     * @param string "comment", "copy", "database", "descidx", "drop_col", "dump", "event", "indexes", "kill", "materializedview", "partitioning", "privileges", "procedure", "processlist", "routine", "scheme", "sequence", "status", "table", "trigger", "type", "variables", "view", "view_trigger"
     * @return bool
     */
    public function support($feature);

    /**
     * Check if connection has at least the given version
     * @param string $version required version
     * @param string $maria_db required MariaDB version
     * @param ConnectionInterface|null $connection
     * @return bool
     */
    public function minVersion($version, $maria_db = "", ConnectionInterface $connection = null);

    /**
     * Kill a process
     * @param int
     * @return bool
     */
    // public function killProcess($val);

    /**
     * Return query to get connection ID
     * @return string
     */
    // public function connectionId();

    /**
     * Get maximum number of connections
     * @return int
     */
    // public function maxConnections();

    /**
     * Get driver config
     * @return array
     */
    public function driverConfig();

    /**
     * Get the server jush
     * @return string
     */
    public function jush();

    /**
     * @return array
     */
    public function unsigned();

    /**
     * @return array
     */
    public function functions();

    /**
     * @return array
     */
    public function grouping();

    /**
     * @return array
     */
    public function operators();

    /**
     * @return array
     */
    public function editFunctions();

    /**
     * @return array
     */
    public function types();

    /**
     * @param string $type
     *
     * @return bool
     */
    public function typeExists(string $type);

    /**
     * @param string $type
     *
     * @return mixed
     */
    public function type(string $type);

    /**
     * @return array
     */
    public function structuredTypes();

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function setStructuredType(string $key, $value);

    /**
     * @return array
     */
    public function onActions();
}
