<?php

namespace Lagdo\Adminer\Facade;

use Exception;

/**
 * Facade to calls to the Adminer functions
 */
class DatabaseFacade extends AbstractFacade
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
            $this->finalSchemas = $this->db->schemas();
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
            'database-command' => $this->util->lang('SQL command'),
            'database-import' => $this->util->lang('Import'),
            'database-export' => $this->util->lang('Export'),
        ];

        $menu_actions = [
            'table' => $this->util->lang('Tables'),
            // 'view' => $this->util->lang('Views'),
            // 'routine' => $this->util->lang('Routines'),
            // 'sequence' => $this->util->lang('Sequences'),
            // 'type' => $this->util->lang('User types'),
            // 'event' => $this->util->lang('Events'),
        ];
        if ($this->db->support('view')) {
            $menu_actions['view'] = $this->util->lang('Views');
        }
        if ($this->db->support('routine')) {
            $menu_actions['routine'] = $this->util->lang('Routines');
        }
        if ($this->db->support('sequence')) {
            $menu_actions['sequence'] = $this->util->lang('Sequences');
        }
        if ($this->db->support('type')) {
            $menu_actions['type'] = $this->util->lang('User types');
        }
        if ($this->db->support('event')) {
            $menu_actions['event'] = $this->util->lang('Events');
        }

        // From db.inc.php
        $schemas = null;
        if ($this->db->support("scheme")) {
            $schemas = $this->schemas();
        }
        // $tables_list = $this->db->tables_list();

        // $tables = [];
        // foreach($table_status as $table)
        // {
        //     $tables[] = $this->util->h($table);
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
            'add-table' => $this->util->lang('Create table'),
        ];

        $headers = [
            $this->util->lang('Table'),
            $this->util->lang('Engine'),
            $this->util->lang('Collation'),
            // $this->util->lang('Data Length'),
            // $this->util->lang('Index Length'),
            // $this->util->lang('Data Free'),
            // $this->util->lang('Auto Increment'),
            // $this->util->lang('Rows'),
            $this->util->lang('Comment'),
        ];

        // From db.inc.php
        // $table_status = $this->db->table_status('', true); // Tables details
        $table_status = $this->db->table_status(); // Tables details

        $details = [];
        foreach ($table_status as $table => $status) {
            if (!$this->db->is_view($status)) {
                $details[] = [
                    'name' => $this->util->tableName($status),
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
            'add-view' => $this->util->lang('Create view'),
        ];

        $headers = [
            $this->util->lang('View'),
            $this->util->lang('Engine'),
            // $this->util->lang('Data Length'),
            // $this->util->lang('Index Length'),
            // $this->util->lang('Data Free'),
            // $this->util->lang('Auto Increment'),
            // $this->util->lang('Rows'),
            $this->util->lang('Comment'),
        ];

        // From db.inc.php
        // $table_status = $this->db->table_status('', true); // Tables details
        $table_status = $this->db->table_status(); // Tables details

        $details = [];
        foreach ($table_status as $table => $status) {
            if ($this->db->is_view($status)) {
                $details[] = [
                    'name' => $this->util->tableName($status),
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
            'procedure' => $this->util->lang('Create procedure'),
            'function' => $this->util->lang('Create function'),
        ];

        $headers = [
            $this->util->lang('Name'),
            $this->util->lang('Type'),
            $this->util->lang('Return type'),
        ];

        // From db.inc.php
        $routines = $this->db->support("routine") ? $this->db->routines() : [];
        $details = [];
        foreach ($routines as $routine) {
            // not computed on the pages to be able to print the header first
            // $name = ($routine["SPECIFIC_NAME"] == $routine["ROUTINE_NAME"] ?
            //     "" : "&name=" . urlencode($routine["ROUTINE_NAME"]));

            $details[] = [
                'name' => \array_key_exists("ROUTINE_NAME", $routine) ?
                    $this->util->h($routine["ROUTINE_NAME"]) : '',
                'type' => \array_key_exists("ROUTINE_TYPE", $routine) ?
                    $this->util->h($routine["ROUTINE_TYPE"]) : '',
                'returnType' => \array_key_exists("DTD_IDENTIFIER", $routine) ?
                    $this->util->h($routine["DTD_IDENTIFIER"]) : '',
                // 'alter' => $this->util->lang('Alter'),
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
            'sequence' => $this->util->lang('Create sequence'),
        ];

        $headers = [
            $this->util->lang('Name'),
        ];

        $sequences = [];
        if ($this->db->support("sequence")) {
            // From db.inc.php
            $sequences = $this->db->get_vals("SELECT sequence_name FROM information_schema.sequences ".
                "WHERE sequence_schema = current_schema() ORDER BY sequence_name");
        }
        $details = [];
        foreach ($sequences as $sequence) {
            $details[] = [
                'name' => $this->util->h($sequence),
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
            'type' => $this->util->lang('Create type'),
        ];

        $headers = [
            $this->util->lang('Name'),
        ];

        // From db.inc.php
        $userTypes = $this->db->support("type") ? $this->db->user_types() : [];
        $details = [];
        foreach ($userTypes as $userType) {
            $details[] = [
                'name' => $this->util->h($userType),
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
            'event' => $this->util->lang('Create event'),
        ];

        $headers = [
            $this->util->lang('Name'),
            $this->util->lang('Schedule'),
            $this->util->lang('Start'),
            // $this->util->lang('End'),
        ];

        // From db.inc.php
        $events = $this->db->support("event") ? $this->db->get_rows("SHOW EVENTS") : [];
        $details = [];
        foreach ($events as $event) {
            $detail = [
                'name' => $this->util->h($event["Name"]),
            ];
            if (($event["Execute at"])) {
                $detail['schedule'] = $this->util->lang('At given time');
                $detail['start'] = $event["Execute at"];
            // $detail['end'] = '';
            } else {
                $detail['schedule'] = $this->util->lang('Every') . " " .
                    $event["Interval value"] . " " . $event["Interval field"];
                $detail['start'] = $event["Starts"];
                // $detail['end'] = '';
            }
            $details[] = $detail;
        }

        return \compact('main_actions', 'headers', 'details');
    }
}
