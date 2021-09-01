<?php

namespace Lagdo\Adminer\Db;

/**
 * Access functions for connection data
 */
interface ConnectionInterface
{
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
     * Sets the client character set
     * @param string
     * @return bool
     */
    public function setCharset($charset);

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
    public function nextResult();

    /**
     * Execute a query on the current database and ??
     *
     * @param string $query
     *
     * @return mixed
     */
    public function multiQuery($query);

    /**
     * Get the result saved by the multiQuery() method
     *
     * @param mixed $result
     *
     * @return mixed
     */
    public function storedResult($result = null);

    /**
     * Convert value returned by database to actual value
     * @param string
     * @param array
     * @return string
     */
    public function value($val, $field);
}
