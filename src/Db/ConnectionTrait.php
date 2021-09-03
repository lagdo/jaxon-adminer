<?php

namespace Lagdo\DbAdmin\Db;

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
     * Sets the client character set
     * @param string
     * @return bool
     */
    public function setCharset($charset)
    {
        return $this->connection->setCharset($charset);
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
    public function nextResult()
    {
        return $this->connection->nextResult();
    }

    /**
     * Execute a query on the current database and store the result
     *
     * @param string $query
     *
     * @return mixed
     */
    public function multiQuery($query)
    {
        return $this->connection->multiQuery($query);
    }

    /**
     * Get the result saved by the multiQuery() method
     *
     * @param mixed $result
     *
     * @return mixed
     */
    public function storedResult($result = null)
    {
        return $this->connection->storedResult($result);
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
