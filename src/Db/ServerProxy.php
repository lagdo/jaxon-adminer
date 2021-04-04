<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class ServerProxy
{
    /**
     * The final database list
     *
     * @var array
     */
    protected $finalDatabases = null;

    /**
     * The databases the user has access to
     *
     * @var array
     */
    protected $userDatabases = null;

    /**
     * The constructor
     *
     * @param array $options    The server config options
     */
    public function __construct(array $options)
    {
        // Set the user databases, if defined.
        if(\array_key_exists('access', $options) &&
            \is_array($options['access']) &&
            \array_key_exists('databases', $options['access']) &&
            \is_array($options['access']['databases']))
        {
            $this->userDatabases = $options['access']['databases'];
        }
    }

    /**
     * Fetch and return the database from the connected server
     *
     * @return array
     */
    protected function databases()
    {
        global $adminer;
        // Get the database lists
        // Passing false as parameter to this call prevent from using the slow_query() function,
        // which outputs data to the browser that are prepended to the Jaxon response.
        if($this->finalDatabases === null)
        {
            $this->finalDatabases = $adminer->databases(false);
            if(\is_array($this->userDatabases))
            {
                $this->finalDatabases = \array_intersect($this->finalDatabases, $this->userDatabases);
            }
        }
        return $this->finalDatabases;
    }

    /**
     * Connect to a database server
     *
     * @return void
     */
    public function getServerInfo()
    {
        global $drivers, $connection;

        $server = \adminer\lang('%s version: %s. PHP extension %s.', $drivers[DRIVER],
            "<b>" . \adminer\h($connection->server_info) . "</b>", "<b>$connection->extension</b>");
        $user = \adminer\lang('Logged as: %s.', "<b>" . \adminer\h(\adminer\logged_user()) . "</b>");

        $actions = [
            'server-command' => \adminer\lang('SQL command'),
            'server-import' => \adminer\lang('Import'),
            'server-export' => \adminer\lang('Export'),
        ];

        // Content from the connect_error() function in connect.inc.php
        $menu_actions = [
            'databases' => \adminer\lang('Databases'),
        ];
        // if(\adminer\support('database'))
        // {
        //     $menu_actions['databases'] = \adminer\lang('Databases');
        // }
        if(\adminer\support('privileges'))
        {
            $menu_actions['privileges'] = \adminer\lang('Privileges');
        }
        if(\adminer\support('processlist'))
        {
            $menu_actions['processes'] = \adminer\lang('Process list');
        }
        if(\adminer\support('variables'))
        {
            $menu_actions['variables'] = \adminer\lang('Variables');
        }
        if(\adminer\support('status'))
        {
            $menu_actions['status'] = \adminer\lang('Status');
        }

        // Get the database list
        $databases = $this->databases();

        return \compact('server', 'user', 'databases', 'actions', 'menu_actions');
    }

    /**
     * Create a database
     *
     * @param string $database  The database name
     * @param string $collation The database collation
     *
     * @return bool
     */
    public function createDatabase(string $database, string $collation = '')
    {
        return \adminer\create_database($database, $collation);
    }

    /**
     * Drop a database
     *
     * @param string $database  The database name
     *
     * @return bool
     */
    public function dropDatabase(string $database)
    {
        return \adminer\drop_databases([$database]);
    }

    /**
     * Get the collation list
     *
     * @return array
     */
    public function getCollations()
    {
        return \adminer\collations();
    }

    /**
     * Get the database list
     *
     * @return array
     */
    public function getDatabases()
    {
        // Get the database list
        $databases = $this->databases();
        $tables = \adminer\count_tables($databases);

        $actions = [
            'add-database' => \adminer\lang('Create database'),
        ];

        $headers = [
            \adminer\lang('Database'),
            \adminer\lang('Collation'),
            \adminer\lang('Tables'),
            \adminer\lang('Size'),
            '',
        ];

        $collations = \adminer\collations();
        $details = [];
        foreach($databases as $database)
        {
            $details[] = [
                'name' => \adminer\h($database),
                'collation' => \adminer\h(\adminer\db_collation($database, $collations)),
                'tables' => \array_key_exists($database, $tables) ? $tables[$database] : 0,
                'size' => \adminer\db_size($database),
            ];
        }

        return \compact('headers', 'details', 'actions');
    }

    /**
     * Get the processes
     *
     * @return array
     */
    public function getProcesses()
    {
        global $jush;
        // From processlist.inc.php
        $processes = \adminer\process_list();

        // From processlist.inc.php
        // TODO: Add a kill column in the headers
        $headers = [];
        $details = [];
        foreach($processes as $process)
        {
            // Set the keys of the first etry as headers
            if(\count($headers) === 0)
            {
                $headers = \array_keys($process);
            }
            $detail = [];
            foreach($process as $key => $val)
            {
                $match = \array_key_exists('Command', $process) &&
                    \preg_match("~Query|Killed~", $process["Command"]);
                $detail[] =
                    ($jush == "sql" && $key == "Info" && $match && $val != "") ||
                    ($jush == "pgsql" && $key == "current_query" && $val != "<IDLE>") ||
                    ($jush == "oracle" && $key == "sql_text" && $val != "") ?
                    "<code class='jush-$jush'>" . \adminer\shorten_utf8($val, 50) .
                    "</code>" . \adminer\lang('Clone') : \adminer\h($val);
            }
            $details[] = $detail;
        }

        return \compact('headers', 'details');
    }

    /**
     * Get the variables
     *
     * @return array
     */
    public function getVariables()
    {
        // From variables.inc.php
        $variables = \adminer\show_variables();

        $headers = false;

        $details = [];
        // From variables.inc.php
        foreach($variables as $key => $val)
        {
            $details[] = [\adminer\h($key), \adminer\shorten_utf8($val, 50)];
        }

        return \compact('headers', 'details');
    }

    /**
     * Get the server status
     *
     * @return array|null
     */
    public function getStatus()
    {
        // From variables.inc.php
        $status = \adminer\show_status();
        if(!\is_array($status))
        {
            $status = [];
        }

        $headers = false;

        $details = [];
        // From variables.inc.php
        foreach($status as $key => $val)
        {
            $details[] = [\adminer\h($key), \adminer\h($val)];
        }

        return \compact('headers', 'details');
    }
}
