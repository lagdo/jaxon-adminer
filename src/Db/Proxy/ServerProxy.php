<?php

namespace Lagdo\Adminer\Db\Proxy;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class ServerProxy extends AbstractProxy
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
     * Get the databases from the connected server
     *
     * @return array
     */
    protected function databases()
    {
        // Get the database lists
        // Passing false as parameter to this call prevent from using the slow_query() function,
        // which outputs data to the browser are prepended to the Jaxon response.
        if($this->finalDatabases === null)
        {
            $this->finalDatabases = $this->adminer->databases(false);
            if(\is_array($this->userDatabases))
            {
                // Only keep databases that appear in the config.
                $this->finalDatabases = \array_intersect($this->finalDatabases, $this->userDatabases);
                $this->finalDatabases = \array_values($this->finalDatabases);
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
        $server = $this->adminer->lang('%s version: %s. PHP extension %s.', $this->server->getName(),
            "<b>" . $this->adminer->h($this->connection->server_info) . "</b>", "<b>{$this->connection->extension}</b>");
        $user = $this->adminer->lang('Logged as: %s.', "<b>" . $this->adminer->h($this->server->logged_user()) . "</b>");

        $sql_actions = [
            'server-command' => $this->adminer->lang('SQL command'),
            'server-import' => $this->adminer->lang('Import'),
            'server-export' => $this->adminer->lang('Export'),
        ];

        // Content from the connect_error() function in connect.inc.php
        $menu_actions = [
            'databases' => $this->adminer->lang('Databases'),
        ];
        // if($this->server->support('database'))
        // {
        //     $menu_actions['databases'] = $this->adminer->lang('Databases');
        // }
        if($this->server->support('privileges'))
        {
            $menu_actions['privileges'] = $this->adminer->lang('Privileges');
        }
        if($this->server->support('processlist'))
        {
            $menu_actions['processes'] = $this->adminer->lang('Process list');
        }
        if($this->server->support('variables'))
        {
            $menu_actions['variables'] = $this->adminer->lang('Variables');
        }
        if($this->server->support('status'))
        {
            $menu_actions['status'] = $this->adminer->lang('Status');
        }

        // Get the database list
        $databases = $this->databases();

        return \compact('server', 'user', 'databases', 'sql_actions', 'menu_actions');
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
        return $this->server->create_database($database, $collation);
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
        return $this->server->drop_databases([$database]);
    }

    /**
     * Get the collation list
     *
     * @return array
     */
    public function getCollations()
    {
        return $this->server->collations();
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
        $tables = $this->server->count_tables($databases);

        $main_actions = [
            'add-database' => $this->adminer->lang('Create database'),
        ];

        $headers = [
            $this->adminer->lang('Database'),
            $this->adminer->lang('Collation'),
            $this->adminer->lang('Tables'),
            $this->adminer->lang('Size'),
            '',
        ];

        $collations = $this->server->collations();
        $details = [];
        foreach($databases as $database)
        {
            $details[] = [
                'name' => $this->adminer->h($database),
                'collation' => $this->adminer->h($this->server->db_collation($database, $collations)),
                'tables' => \array_key_exists($database, $tables) ? $tables[$database] : 0,
                'size' => $this->adminer->db_size($database),
            ];
        }

        return \compact('headers', 'details', 'main_actions');
    }

    /**
     * Get the processes
     *
     * @return array
     */
    public function getProcesses()
    {
        // From processlist.inc.php
        $processes = $this->server->process_list();

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
                    ($this->server->jush == "sql" && $key == "Info" && $match && $val != "") ||
                    ($this->server->jush == "pgsql" && $key == "current_query" && $val != "<IDLE>") ||
                    ($this->server->jush == "oracle" && $key == "sql_text" && $val != "") ?
                    "<code class='jush-{$this->server->jush}'>" . $this->adminer->shorten_utf8($val, 50) .
                    "</code>" . $this->adminer->lang('Clone') : $this->adminer->h($val);
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
        $variables = $this->server->show_variables();

        $headers = false;

        $details = [];
        // From variables.inc.php
        foreach($variables as $key => $val)
        {
            $details[] = [$this->adminer->h($key), $this->adminer->shorten_utf8($val, 50)];
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
        $status = $this->server->show_status();
        if(!\is_array($status))
        {
            $status = [];
        }

        $headers = false;

        $details = [];
        // From variables.inc.php
        foreach($status as $key => $val)
        {
            $details[] = [$this->adminer->h($key), $this->adminer->h($val)];
        }

        return \compact('headers', 'details');
    }
}
