<?php

namespace Lagdo\Adminer\Facade;

use Exception;

/**
 * Facade to calls to the Adminer functions
 */
class ServerFacade extends AbstractFacade
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
        if (\array_key_exists('access', $options) &&
            \is_array($options['access']) &&
            \array_key_exists('databases', $options['access']) &&
            \is_array($options['access']['databases'])) {
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
        if ($this->finalDatabases === null) {
            $this->finalDatabases = $this->db->databases(false);
            if (\is_array($this->userDatabases)) {
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
        $server = $this->util->lang(
            '%s version: %s. PHP extension %s.',
            $this->db->getName(),
            "<b>" . $this->util->h($this->db->serverInfo()) . "</b>",
            "<b>{$this->db->extension()}</b>"
        );
        $user = $this->util->lang('Logged as: %s.', "<b>" . $this->util->h($this->db->logged_user()) . "</b>");

        $sql_actions = [
            'server-command' => $this->util->lang('SQL command'),
            'server-import' => $this->util->lang('Import'),
            'server-export' => $this->util->lang('Export'),
        ];

        // Content from the connect_error() function in connect.inc.php
        $menu_actions = [
            'databases' => $this->util->lang('Databases'),
        ];
        // if($this->db->support('database'))
        // {
        //     $menu_actions['databases'] = $this->util->lang('Databases');
        // }
        if ($this->db->support('privileges')) {
            $menu_actions['privileges'] = $this->util->lang('Privileges');
        }
        if ($this->db->support('processlist')) {
            $menu_actions['processes'] = $this->util->lang('Process list');
        }
        if ($this->db->support('variables')) {
            $menu_actions['variables'] = $this->util->lang('Variables');
        }
        if ($this->db->support('status')) {
            $menu_actions['status'] = $this->util->lang('Status');
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
        return $this->db->create_database($database, $collation);
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
        return $this->db->drop_databases([$database]);
    }

    /**
     * Get the collation list
     *
     * @return array
     */
    public function getCollations()
    {
        return $this->db->collations();
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
        $tables = $this->db->count_tables($databases);

        $main_actions = [
            'add-database' => $this->util->lang('Create database'),
        ];

        $headers = [
            $this->util->lang('Database'),
            $this->util->lang('Collation'),
            $this->util->lang('Tables'),
            $this->util->lang('Size'),
            '',
        ];

        $collations = $this->db->collations();
        $details = [];
        foreach ($databases as $database) {
            $details[] = [
                'name' => $this->util->h($database),
                'collation' => $this->util->h($this->db->db_collation($database, $collations)),
                'tables' => \array_key_exists($database, $tables) ? $tables[$database] : 0,
                'size' => $this->util->format_number($this->db->db_size($database)),
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
        $processes = $this->db->process_list();

        $jush = $this->db->jush();
        // From processlist.inc.php
        // TODO: Add a kill column in the headers
        $headers = [];
        $details = [];
        foreach ($processes as $process) {
            // Set the keys of the first etry as headers
            if (\count($headers) === 0) {
                $headers = \array_keys($process);
            }
            $detail = [];
            foreach ($process as $key => $val) {
                $match = \array_key_exists('Command', $process) &&
                    \preg_match("~Query|Killed~", $process["Command"]);
                $detail[] =
                    ($jush == "sql" && $key == "Info" && $match && $val != "") ||
                    ($jush == "pgsql" && $key == "current_query" && $val != "<IDLE>") ||
                    ($jush == "oracle" && $key == "sql_text" && $val != "") ?
                    "<code class='jush-{$jush}'>" . $this->util->shorten_utf8($val, 50) .
                    "</code>" . $this->util->lang('Clone') : $this->util->h($val);
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
        $variables = $this->db->show_variables();

        $headers = false;

        $details = [];
        // From variables.inc.php
        foreach ($variables as $key => $val) {
            $details[] = [$this->util->h($key), $this->util->shorten_utf8($val, 50)];
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
        $status = $this->db->show_status();
        if (!\is_array($status)) {
            $status = [];
        }

        $headers = false;

        $details = [];
        // From variables.inc.php
        foreach ($status as $key => $val) {
            $details[] = [$this->util->h($key), $this->util->h($val)];
        }

        return \compact('headers', 'details');
    }
}
