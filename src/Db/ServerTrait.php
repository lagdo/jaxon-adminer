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
     * Create a new connection to the database server
     * Return a string for error
     *
     * @return ConnectionInterface|string
     */
    public function createConnection()
    {
        // Returns the existing connection. A new connection is not created. Todo?
        return $this->server->getConnection();
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
    public function currentDatabase()
    {
        return $this->server->currentDatabase();
    }

    /**
     * Select the database and schema
     *
     * @return string
     */
    public function currentSchema()
    {
        return $this->server->currentSchema();
    }

    /**
     * Get the name of the primary id field
     *
     * @return string
     */
    public function primaryIdName()
    {
        return $this->server->primaryIdName();
    }

    /**
     * Escape database identifier
     *
     * @param string $idf
     *
     * @return string
     */
    public function escapeId($idf)
    {
        return $this->server->escapeId($idf);
    }

    /**
     * Unescape database identifier
     * @param string $idf
     * @return string
     */
    public function unescapeId($idf)
    {
        return $this->server->unescapeId($idf);
    }

    /**
     * Shortcut for $this->connection->quote($string)
     * @param string
     * @return string
     */
    public function quote($string)
    {
        return $this->server->quote($string);
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
    public function databases($flush)
    {
        return $this->server->databases($flush);
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
    public function limitToOne($table, $query, $where, $separator = "\n")
    {
        return $this->server->limitToOne($table, $query, $where, $separator);
    }

    /**
     * Get database collation
     * @param string
     * @param array result of collations()
     * @return string
     */
    public function databaseCollation($db, $collations)
    {
        return $this->server->databaseCollation($db, $collations);
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
    public function loggedUser()
    {
        return $this->server->loggedUser();
    }

    /**
     * Format foreign key to use in SQL query
     *
     * @param array ("db" => string, "ns" => string, "table" => string, "source" => array, "target" => array,
     * "on_delete" => one of $this->onActions, "on_update" => one of $this->onActions)
     *
     * @return string
     */
    public function formatForeignKey($foreignKey)
    {
        return $this->server->formatForeignKey($foreignKey);
    }

    /**
     * Get tables list
     * @return array array($name => $type)
     */
    public function tables()
    {
        return $this->server->tables();
    }

    /**
     * Count tables in all databases
     * @param array
     * @return array array($db => $tables)
     */
    public function countTables($databases)
    {
        return $this->server->countTables($databases);
    }

    /**
     * Get table status
     * @param string
     * @param bool return only "Name", "Engine" and "Comment" fields
     * @return array array($name => array("Name" => , "Engine" => , "Comment" => , "Oid" => , "Rows" => , "Collation" => , "Auto_increment" => , "Data_length" => , "Index_length" => , "Data_free" => )) or only inner array with $name
     */
    public function tableStatus($name = "", $fast = false)
    {
        return $this->server->tableStatus($name, $fast);
    }

    /**
     * Get status of a single table and fall back to name on error
     * @param string
     * @param bool
     * @return array
     */
    public function tableStatusOrName($table, $fast = false)
    {
        return $this->server->tableStatusOrName($table, $fast);
    }

    /**
     * Find out whether the identifier is view
     * @param array
     * @return bool
     */
    public function isView($tableStatus)
    {
        return $this->server->isView($tableStatus);
    }

    /**
     * Check if table supports foreign keys
     * @param array result of table_status
     * @return bool
     */
    public function supportForeignKeys($tableStatus)
    {
        return $this->server->supportForeignKeys($tableStatus);
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
    public function indexes($table, $connection = null)
    {
        return $this->server->indexes($table, $connection);
    }

    /**
     * Get foreign keys in table
     * @param string
     * @return array array($name => array("db" => , "ns" => , "table" => , "source" => [], "target" => [], "on_delete" => , "on_update" => ))
     */
    public function foreignKeys($table)
    {
        return $this->server->foreignKeys($table);
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
    public function isInformationSchema($db)
    {
        return $this->server->isInformationSchema($db);
    }

    /**
     * Create database
     * @param string
     * @param string
     * @return string|boolean
     */
    public function createDatabase($db, $collation)
    {
        return $this->server->createDatabase($db, $collation) ;
    }

    /**
     * Drop databases
     * @param array
     * @return bool
     */
    public function dropDatabases($databases)
    {
        return $this->server->dropDatabases($databases);
    }

    /**
     * Rename database from DB
     * @param string new name
     * @param string
     * @return bool
     */
    public function renameDatabase($name, $collation)
    {
        return $this->server->renameDatabase($name, $collation);
    }

    /**
     * Generate modifier for auto increment column
     * @return string
     */
    public function autoIncrement()
    {
        return $this->server->autoIncrement();
    }

    /**
     * Get last auto increment ID
     * @return string
     */
    public function lastAutoIncrementId()
    {
        return $this->server->lastAutoIncrementId();
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
    public function alterTable($table, $name, $fields, $foreign, $comment, $engine, $collation, $auto_increment, $partitioning)
    {
        return $this->server->alterTable($table, $name, $fields, $foreign, $comment, $engine, $collation, $auto_increment, $partitioning);
    }

    /**
     * Run commands to alter indexes
     * @param string escaped table name
     * @param array of array("index type", "name", array("column definition", ...)) or array("index type", "name", "DROP")
     * @return bool
     */
    public function alterIndexes($table, $alter)
    {
        return $this->server->alterIndexes($table, $alter);
    }

    /**
     * Drop views
     * @param array
     * @return bool
     */
    public function dropViews($views)
    {
        return $this->server->dropViews($views);
    }

    /**
     * Run commands to truncate tables
     * @param array
     * @return bool
     */
    public function truncateTables($tables)
    {
        return $this->server->truncateTables($tables);
    }

    /**
     * Drop tables
     * @param array
     * @return bool
     */
    public function dropTables($tables)
    {
        return $this->server->dropTables($tables);
    }

    /**
     * Move tables to other schema
     * @param array
     * @param array
     * @param string
     * @return bool
     */
    public function moveTables($tables, $views, $target)
    {
        return $this->server->moveTables($tables, $views, $target);
    }

    /**
     * Copy tables to other schema
     * @param array
     * @param array
     * @param string
     * @return bool
     */
    public function copyTables($tables, $views, $target)
    {
        return $this->server->copyTables($tables, $views, $target);
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
    public function triggerOptions()
    {
        return $this->server->triggerOptions();
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
    public function routineLanguages()
    {
        return $this->server->routineLanguages() ;
    }

    /**
     * Get routine signature
     * @param string
     * @param array result of routine()
     * @return string
     */
    public function routineId($name, $row)
    {
        return $this->server->routineId($name, $row);
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
     * Get user defined types
     * @return array
     */
    public function userTypes()
    {
        return $this->server->userTypes() ;
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
    public function schema()
    {
        return $this->server->schema();
    }

    /**
     * Set current schema
     * @param string
     * @param ConnectionInterface
     * @return bool
     */
    public function selectSchema($schema, $connection = null)
    {
        return $this->server->selectSchema($schema, $connection = null);
    }

    /**
     * Get SQL command to create table
     * @param string
     * @param bool
     * @param string
     * @return string
     */
    public function createTableSql($table, $auto_increment, $style)
    {
        return $this->server->createTableSql($table, $auto_increment, $style);
    }

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
    public function foreignKeysSql($table)
    {
        return $this->server->foreignKeysSql($table);
    }

    /**
     * Get SQL command to truncate table
     * @param string
     * @return string
     */
    public function truncateTableSql($table)
    {
        return $this->server->truncateTableSql($table);
    }

    /**
     * Get SQL command to change database
     * @param string
     * @return string
     */
    public function useDatabaseSql($database)
    {
        return $this->server->useDatabaseSql($database);
    }

    /**
     * Get SQL commands to create triggers
     * @param string
     * @return string
     */
    public function createTriggerSql($table)
    {
        return $this->server->createTriggerSql($table);
    }

    /**
     * Get server variables
     * @return array ($name => $value)
     */
    public function variables()
    {
        return $this->server->variables();
    }

    /**
     * Get status variables
     * @return array ($name => $value)
     */
    public function statusVariables()
    {
        return $this->server->statusVariables();
    }

    /**
     * Get process list
     * @return array ($row)
     */
    public function processes()
    {
        return $this->server->processes();
    }

    /**
     * Convert field in select and edit
     * @param array $field one element from $this->fields()
     * @return string
     */
    public function convertField(array $field)
    {
        return $this->server->convertField($field);
    }

    /**
     * Convert value in edit after applying functions back
     * @param array $field one element from $this->fields()
     * @param string $return
     * @return string
     */
    public function unconvertField(array $field, $return)
    {
        return $this->server->unconvertField($field, $return);
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
     * @param ConnectionInterface|null $connection
     * @return bool
     */
    public function minVersion($version, $maria_db = "", ConnectionInterface $connection = null)
    {
        return $this->server->minVersion($version, $maria_db, $connection);
    }

    /**
     * Kill a process
     * @param int
     * @return bool
     */
    // public function killProcess($val)
    // {
    //     return $this->server->killProcess($val);
    // }

    /**
     * Return query to get connection ID
     * @return string
     */
    // public function connectionId()
    // {
    //     return $this->server->connectionId();
    // }

    /**
     * Get maximum number of connections
     * @return int
     */
    // public function maxConnections()
    // {
    //     return $this->server->maxConnections();
    // }

    /**
     * Get driver config
     * @return array array('possibleDrivers' => , 'jush' => , 'types' => , 'structuredTypes' => , 'unsigned' => , 'operators' => , 'functions' => , 'grouping' => , 'editFunctions' => )
     */
    public function driverConfig()
    {
        return $this->server->driverConfig();
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
        return $this->server->editFunctions;
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
        return $this->server->structuredTypes;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function setStructuredType(string $key, $value)
    {
        $this->server->structuredTypes[$key] = $value;
    }

    /**
     * @return array
     */
    public function onActions()
    {
        return \explode('|', $this->server->onActions);
    }
}
