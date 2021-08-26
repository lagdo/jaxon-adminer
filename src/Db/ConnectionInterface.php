<?php

namespace Lagdo\Adminer\Db;

/**
 * Access functions for connection data
 */
interface ConnectionInterface
{
    /**
     * Get the last error message
     *
     * @return string
     */
    public function errorMessage();

    /**
     * Get the last error number
     *
     * @return string
     */
    public function errorNumber();

    /**
     * Check if the last query returned an error number
     *
     * @return string
     */
    public function hasErrorNumber();

    /**
     * Get the number of rows affected by the last query
     *
     * @return integer
     */
    public function affectedRows();

    /**
     * Get information about the last query
     *
     * @return string
     */
    public function info();

    /**
     * Get the server description
     *
     * @return string
     */
    public function serverInfo();

    /**
     * Get the driver extension
     *
     * @return string
     */
    public function extension();
    /**
     * Set the current database
     *
     * @param string $database
     *
     * @return boolean
     */
    public function select_db($database);

    /**
     * Sets the client character set
     * @param string
     * @return bool
     */
    public function set_charset($charset);

    /**
     * Execute a query on the current database
     *
     * @param string $query
     * @param boolean $unbuffered
     *
     * @return mixed
     */
    public function query($query, $unbuffered = false);

    /**
     * Execute a query on the current database and fetch the specified field
     *
     * @param string $query
     * @param mixed $field
     *
     * @return mixed
     */
    public function result($query, $field = 1);

    /**
     * Get the next row set of the last query
     *
     * @return mixed
     */
    public function next_result();

    /**
     * Execute a query on the current database and ??
     *
     * @param string $query
     *
     * @return mixed
     */
    public function multi_query($query);

    /**
     * Get the result saved by the multi_query() method
     *
     * @param mixed $result
     *
     * @return mixed
     */
    public function store_result($result = null);

    /**
     * Convert value returned by database to actual value
     * @param string
     * @param array
     * @return string
     */
    public function value($val, $field);
}
