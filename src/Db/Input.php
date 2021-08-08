<?php

namespace Lagdo\Adminer\Db;

use Lagdo\Adminer\Drivers\InputInterface;

class Input implements InputInterface
{
    /**
     * @inheritDoc
     */
    public function create()
    {
        // $_GET["create"]
    }

    /**
     * @inheritDoc
     */
    public function trigger()
    {
        // $_GET["trigger"]
    }

    /**
     * @inheritDoc
     */
    public function select()
    {
        // $_GET["select"]
    }

    /**
     * @inheritDoc
     */
    public function where()
    {
        // $_GET["where"]
    }

    /**
     * @inheritDoc
     */
    public function limit()
    {
        // $_GET["limit"]
    }

    /**
     * @inheritDoc
     */
    public function fields()
    {
        // $_POST["fields"]
    }

    /**
     * @inheritDoc
     */
    public function autoIncrementStep()
    {
        // $_POST["Auto_increment"], formatted with $this->adminer->number()
    }

    /**
     * @inheritDoc
     */
    public function autoIncrementField()
    {
        // $_POST["auto_increment_col"]
    }

    /**
     * @inheritDoc
     */
    public function checks()
    {
        // $_POST["check"]
    }

    /**
     * @inheritDoc
     */
    public function overwrite()
    {
        // $_POST["overwrite"]
    }
}
