<?php

namespace Lagdo\Adminer\Db;

use Lagdo\Adminer\Drivers\InputInterface;

class Input implements InputInterface
{
    /**
     * @var string
     */
    public $table = '';

    /**
     * @var array
     */
    public $values = [];

    /**
     * @inheritDoc
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @inheritDoc
     */
    public function hasTable()
    {
        return $this->table !== '';
    }

    /**
     * @inheritDoc
     */
    public function getSelect()
    {
        if (!isset($this->values['select'])) {
            return [];
        }
        return $this->values['select'];
    }

    /**
     * @inheritDoc
     */
    public function getWhere()
    {
        if (!isset($this->values['where'])) {
            return [];
        }
        return $this->values['where'];
    }

    /**
     * @inheritDoc
     */
    public function getLimit()
    {
        if (!isset($this->values['limit'])) {
            return 0;
        }
        return $this->values['limit'];
    }

    /**
     * @inheritDoc
     */
    public function getFields()
    {
        if (!isset($this->values['fields'])) {
            return [];
        }
        return $this->values['fields'];
    }

    /**
     * @inheritDoc
     */
    public function getAutoIncrementStep()
    {
        if (!isset($this->values['auto_increment']) || $this->values['auto_increment'] == '') {
            return '';
        }
        return $this->values['auto_increment'];
    }

    /**
     * @inheritDoc
     */
    public function getAutoIncrementField()
    {
        if (!isset($this->values['auto_increment_col'])) {
            return '';
        }
        return $this->values['auto_increment_col'];
    }

    /**
     * @inheritDoc
     */
    public function getChecks()
    {
        if (!isset($this->values['checks'])) {
            return [];
        }
        return $this->values['checks'];
    }

    /**
     * @inheritDoc
     */
    public function getOverwrite()
    {
        if (!isset($this->values['overwrite'])) {
            return false;
        }
        return $this->values['overwrite'];
    }
}
