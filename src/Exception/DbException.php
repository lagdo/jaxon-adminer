<?php

namespace Lagdo\DbAdmin\Exception;

use Exception;

class DbException extends Exception
{
    /**
     * The constructor
     *
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
