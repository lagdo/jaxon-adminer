<?php

namespace Lagdo\Adminer\Db;

trait ConnectionTrait
{
    /**
     * Get information about the last query
     *
     * @return string
     */
    public function info()
    {
        return $this->connection->info;
    }

    /**
     * Get the server description
     *
     * @return string
     */
    public function serverInfo()
    {
        return $this->connection->getServerInfo();
    }

    /**
     * Get the driver extension
     *
     * @return string
     */
    public function extension()
    {
        return $this->connection->extension;
    }

    /**
     * Set the current database
     *
     * @param string $database
     *
     * @return boolean
     */
    public function select_db($database)
    {
        return $this->connection->select_db($database);
    }

    /**
     * Sets the client character set
     * @param string
     * @return bool
     */
    public function set_charset($charset)
    {
        return $this->connection->set_charset($charset);
    }

    /**
     * Execute a query on the current database
     *
     * @param string $query
     * @param boolean $unbuffered
     *
     * @return mixed
     */
    public function query($query, $unbuffered = false)
    {
        return $this->connection->query($query, $unbuffered);
    }

    /**
     * Execute a query on the current database and fetch the specified field
     *
     * @param string $query
     * @param integer $field
     *
     * @return mixed
     */
    public function result($query, $field = null)
    {
        if ($field === null) {
            $field = $this->connection->defaultField();
        }
        return $this->connection->result($query, $field);
    }

    /**
     * Get the next row set of the last query
     *
     * @return mixed
     */
    public function next_result()
    {
        return $this->connection->next_result();
    }

    /**
     * Execute a query on the current database and store the result
     *
     * @param string $query
     *
     * @return mixed
     */
    public function multi_query($query)
    {
        return $this->connection->multi_query($query);
    }

    /**
     * Get the result saved by the multi_query() method
     *
     * @param mixed $result
     *
     * @return mixed
     */
    public function store_result($result = null)
    {
        return $this->connection->store_result($result);
    }

    /**
     * Convert value returned by database to actual value
     * @param string
     * @param array
     * @return string
     */
    public function value($val, $field)
    {
        return $this->connection->value($val, $field);
    }
}
