<?php

namespace Lagdo\Adminer\Db\Proxy;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class DatabaseProxy extends AbstractProxy
{
    /**
     * The final schema list
     *
     * @var array
     */
    protected $finalSchemas = null;

    /**
     * The schemas the user has access to
     *
     * @var array
     */
    protected $userSchemas = null;

    /**
     * The constructor
     *
     * @param array $options    The server config options
     */
    public function __construct(array $options)
    {
        // Set the user schemas, if defined.
        if (\array_key_exists('access', $options) &&
            \is_array($options['access']) &&
            \array_key_exists('schemas', $options['access']) &&
            \is_array($options['access']['schemas'])) {
            $this->userSchemas = $options['access']['schemas'];
        }
    }

    /**
     * Get the schemas from the connected database
     *
     * @return array
     */
    protected function schemas()
    {
        // Get the schema lists
        if ($this->finalSchemas === null) {
            $this->finalSchemas = $this->server->schemas();
            if (\is_array($this->userSchemas)) {
                // Only keep schemas that appear in the config.
                $this->finalSchemas = \array_intersect($this->finalSchemas, $this->userSchemas);
                $this->finalSchemas = \array_values($this->finalSchemas);
            }
        }
        return $this->finalSchemas;
    }

    /**
     * Connect to a database server
     *
     * @return void
     */
    public function getDatabaseInfo()
    {
        $sql_actions = [
            'database-command' => $this->ui->lang('SQL command'),
            'database-import' => $this->ui->lang('Import'),
            'database-export' => $this->ui->lang('Export'),
        ];

        $menu_actions = [
            'table' => $this->ui->lang('Tables'),
            // 'view' => $this->ui->lang('Views'),
            // 'routine' => $this->ui->lang('Routines'),
            // 'sequence' => $this->ui->lang('Sequences'),
            // 'type' => $this->ui->lang('User types'),
            // 'event' => $this->ui->lang('Events'),
        ];
        if ($this->server->support('view')) {
            $menu_actions['view'] = $this->ui->lang('Views');
        }
        if ($this->server->support('routine')) {
            $menu_actions['routine'] = $this->ui->lang('Routines');
        }
        if ($this->server->support('sequence')) {
            $menu_actions['sequence'] = $this->ui->lang('Sequences');
        }
        if ($this->server->support('type')) {
            $menu_actions['type'] = $this->ui->lang('User types');
        }
        if ($this->server->support('event')) {
            $menu_actions['event'] = $this->ui->lang('Events');
        }

        // From db.inc.php
        $schemas = null;
        if ($this->server->support("scheme")) {
            $schemas = $this->schemas();
        }
        // $tables_list = $this->server->tables_list();

        // $tables = [];
        // foreach($table_status as $table)
        // {
        //     $tables[] = $this->ui->h($table);
        // }

        return \compact('sql_actions', 'menu_actions', 'schemas'/*, 'tables'*/);
    }

    /**
     * Get the tables from a database server
     *
     * @return void
     */
    public function getTables()
    {
        $main_actions = [
            'add-table' => $this->ui->lang('Create table'),
        ];

        $headers = [
            $this->ui->lang('Table'),
            $this->ui->lang('Engine'),
            $this->ui->lang('Collation'),
            // $this->ui->lang('Data Length'),
            // $this->ui->lang('Index Length'),
            // $this->ui->lang('Data Free'),
            // $this->ui->lang('Auto Increment'),
            // $this->ui->lang('Rows'),
            $this->ui->lang('Comment'),
        ];

        // From db.inc.php
        // $table_status = $this->server->table_status('', true); // Tables details
        $table_status = $this->server->table_status(); // Tables details

        $details = [];
        foreach ($table_status as $table => $status) {
            if (!$this->server->is_view($status)) {
                $details[] = [
                    'name' => $this->ui->tableName($status),
                    'engine' => \array_key_exists('Engine', $status) ? $status['Engine'] : '',
                    'collation' => '',
                    'comment' => \array_key_exists('Comment', $status) ? $status['Comment'] : '',
                ];
            }
        }

        $select = \adminer\lang('Select');
        return \compact('main_actions', 'headers', 'details', 'select');
    }

    /**
     * Get the views from a database server
     * Almost the same as getTables()
     *
     * @return void
     */
    public function getViews()
    {
        $main_actions = [
            'add-view' => $this->ui->lang('Create view'),
        ];

        $headers = [
            $this->ui->lang('View'),
            $this->ui->lang('Engine'),
            // $this->ui->lang('Data Length'),
            // $this->ui->lang('Index Length'),
            // $this->ui->lang('Data Free'),
            // $this->ui->lang('Auto Increment'),
            // $this->ui->lang('Rows'),
            $this->ui->lang('Comment'),
        ];

        // From db.inc.php
        // $table_status = $this->server->table_status('', true); // Tables details
        $table_status = $this->server->table_status(); // Tables details

        $details = [];
        foreach ($table_status as $table => $status) {
            if ($this->server->is_view($status)) {
                $details[] = [
                    'name' => $this->ui->tableName($status),
                    'engine' => \array_key_exists('Engine', $status) ? $status['Engine'] : '',
                    'comment' => \array_key_exists('Comment', $status) ? $status['Comment'] : '',
                ];
            }
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @return void
     */
    public function getRoutines()
    {
        $main_actions = [
            'procedure' => $this->ui->lang('Create procedure'),
            'function' => $this->ui->lang('Create function'),
        ];

        $headers = [
            $this->ui->lang('Name'),
            $this->ui->lang('Type'),
            $this->ui->lang('Return type'),
        ];

        // From db.inc.php
        $routines = $this->server->support("routine") ? $this->server->routines() : [];
        $details = [];
        foreach ($routines as $routine) {
            // not computed on the pages to be able to print the header first
            // $name = ($routine["SPECIFIC_NAME"] == $routine["ROUTINE_NAME"] ?
            //     "" : "&name=" . urlencode($routine["ROUTINE_NAME"]));

            $details[] = [
                'name' => \array_key_exists("ROUTINE_NAME", $routine) ?
                    $this->ui->h($routine["ROUTINE_NAME"]) : '',
                'type' => \array_key_exists("ROUTINE_TYPE", $routine) ?
                    $this->ui->h($routine["ROUTINE_TYPE"]) : '',
                'returnType' => \array_key_exists("DTD_IDENTIFIER", $routine) ?
                    $this->ui->h($routine["DTD_IDENTIFIER"]) : '',
                // 'alter' => $this->ui->lang('Alter'),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @return void
     */
    public function getSequences()
    {
        $main_actions = [
            'sequence' => $this->ui->lang('Create sequence'),
        ];

        $headers = [
            $this->ui->lang('Name'),
        ];

        $sequences = [];
        if ($this->server->support("sequence")) {
            // From db.inc.php
            $sequences = $this->db->get_vals("SELECT sequence_name FROM information_schema.sequences ".
                "WHERE sequence_schema = current_schema() ORDER BY sequence_name");
        }
        $details = [];
        foreach ($sequences as $sequence) {
            $details[] = [
                'name' => $this->ui->h($sequence),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @return array
     */
    public function getUserTypes()
    {
        $main_actions = [
            'type' => $this->ui->lang('Create type'),
        ];

        $headers = [
            $this->ui->lang('Name'),
        ];

        // From db.inc.php
        $userTypes = $this->server->support("type") ? $this->server->types() : [];
        $details = [];
        foreach ($userTypes as $userType) {
            $details[] = [
                'name' => $this->ui->h($userType),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @return array
     */
    public function getEvents()
    {
        $main_actions = [
            'event' => $this->ui->lang('Create event'),
        ];

        $headers = [
            $this->ui->lang('Name'),
            $this->ui->lang('Schedule'),
            $this->ui->lang('Start'),
            // $this->ui->lang('End'),
        ];

        // From db.inc.php
        $events = $this->server->support("event") ? $this->db->get_rows("SHOW EVENTS") : [];
        $details = [];
        foreach ($events as $event) {
            $detail = [
                'name' => $this->ui->h($event["Name"]),
            ];
            if (($event["Execute at"])) {
                $detail['schedule'] = $this->ui->lang('At given time');
                $detail['start'] = $event["Execute at"];
            // $detail['end'] = '';
            } else {
                $detail['schedule'] = $this->ui->lang('Every') . " " .
                    $event["Interval value"] . " " . $event["Interval field"];
                $detail['start'] = $event["Starts"];
                // $detail['end'] = '';
            }
            $details[] = $detail;
        }

        return \compact('main_actions', 'headers', 'details');
    }
}
