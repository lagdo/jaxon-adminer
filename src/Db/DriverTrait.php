<?php

namespace Lagdo\Adminer\Db;

trait DriverTrait
{
    /**
     * Return a quoted string
     *
     * @param string $string
     *
     * @return string
     */
    public function quoteBinary($string)
    {
        return $this->driver->quoteBinary($string);
    }

    /**
     * Select data from table
     *
     * @param string $table
     * @param array $select result of $this->ui->selectColumnsProcess()[0]
     * @param array $where result of $this->ui->selectSearchProcess()
     * @param array $group result of $this->ui->selectColumnsProcess()[1]
     * @param array $order result of $this->ui->selectOrderProcess()
     * @param int $limit result of $this->ui->selectLimitProcess()
     * @param int $page index of page starting at zero
     *
     * @return Statement
     */
    public function select($table, $select, $where, $group, $order = [], $limit = 1, $page = 0)
    {
        return $this->driver->select($table, $select, $where, $group, $order, $limit, $page);
    }

    /**
     * Insert data into table
     *
     * @param string $table
     * @param array $set escaped columns in keys, quoted data in values
     *
     * @return bool
     */
    public function insert($table, $set)
    {
        return $this->driver->insert($table, $set);
    }

    /**
     * Update data in table
     *
     * @param string $table
     * @param array $set escaped columns in keys, quoted data in values
     * @param string $queryWhere " WHERE ..."
     * @param int $limit 0 or 1
     * @param string $separator
     *
     * @return bool
     */
    public function update($table, $set, $queryWhere, $limit = 0, $separator = "\n")
    {
        return $this->driver->update($table, $set, $queryWhere, $limit, $separator);
    }

    /**
     * Insert or update data in table
     *
     * @param string $table
     * @param array $rows
     * @param array $primary of arrays with escaped columns in keys and quoted data in values
     *
     * @return bool
     */
    public function insertUpdate($table, $rows, $primary)
    {
        return $this->driver->insertUpdate($table, $rows, $primary);
    }

    /**
     * Delete data from table
     *
     * @param string $table
     * @param string $queryWhere " WHERE ..."
     * @param int $limit 0 or 1
     *
     * @return bool
     */
    public function delete($table, $queryWhere, $limit = 0)
    {
        return $this->driver->delete($table, $queryWhere, $limit);
    }

    /**
     * Get warnings about the last command
     * @return string
     */
    public function warnings()
    {
        return $this->driver->warnings();
    }

    /**
     * Convert column to be searchable
     *
     * @param string $idf escaped column name
     * @param array $val array("op" => , "val" => )
     * @param array $field
     *
     * @return string
     */
    public function convertSearch($idf, $val, $field)
    {
        return $this->driver->convertSearch($idf, $val, $field);
    }

    /**
     * Get help link for table
     *
     * @param string $name
     *
     * @return string relative URL or null
     */
    public function tableHelp($name)
    {
        return $this->driver->tableHelp($name);
    }
}
