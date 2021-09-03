<?php

namespace Lagdo\DbAdmin\DbAdmin;

use Exception;

/**
 * Admin database functions
 */
class DatabaseAdmin extends AbstractAdmin
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
        $sqlActions = [
            'database-command' => $this->util->lang('SQL command'),
            'database-import' => $this->util->lang('Import'),
            'database-export' => $this->util->lang('Export'),
        ];

        $menuActions = [
            'table' => $this->util->lang('Tables'),
            // 'view' => $this->util->lang('Views'),
            // 'routine' => $this->util->lang('Routines'),
            // 'sequence' => $this->util->lang('Sequences'),
            // 'type' => $this->util->lang('User types'),
            // 'event' => $this->util->lang('Events'),
        ];
        if ($this->db->support('view')) {
            $menuActions['view'] = $this->util->lang('Views');
        }
        if ($this->db->support('routine')) {
            $menuActions['routine'] = $this->util->lang('Routines');
        }
        if ($this->db->support('sequence')) {
            $menuActions['sequence'] = $this->util->lang('Sequences');
        }
        if ($this->db->support('type')) {
            $menuActions['type'] = $this->util->lang('User types');
        }
        if ($this->db->support('event')) {
            $menuActions['event'] = $this->util->lang('Events');
        }

        // From db.inc.php
        $schemas = null;
        if ($this->db->support("scheme")) {
            $schemas = $this->schemas();
        }
        // $tables_list = $this->db->tables();

        // $tables = [];
        // foreach($tableStatus as $table)
        // {
        //     $tables[] = $this->util->html($table);
        // }

        return \compact('sqlActions', 'menuActions', 'schemas'/*, 'tables'*/);
    }

    /**
     * Get the tables from a database server
     *
     * @return void
     */
    public function getTables()
    {
        $mainActions = [
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
        // $tableStatus = $this->db->tableStatus('', true); // Tables details
        $tableStatus = $this->db->tableStatus(); // Tables details

        $details = [];
        foreach ($tableStatus as $table => $status) {
            if (!$this->db->isView($status)) {
                $details[] = [
                    'name' => $this->util->tableName($status),
                    'engine' => \array_key_exists('Engine', $status) ? $status['Engine'] : '',
                    'collation' => '',
                    'comment' => \array_key_exists('Comment', $status) ? $status['Comment'] : '',
                ];
            }
        }

        $select = $this->util->lang('Select');
        return \compact('mainActions', 'headers', 'details', 'select');
    }

    /**
     * Get the views from a database server
     * Almost the same as getTables()
     *
     * @return void
     */
    public function getViews()
    {
        $mainActions = [
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
        // $tableStatus = $this->db->tableStatus('', true); // Tables details
        $tableStatus = $this->db->tableStatus(); // Tables details

        $details = [];
        foreach ($tableStatus as $table => $status) {
            if ($this->db->isView($status)) {
                $details[] = [
                    'name' => $this->util->tableName($status),
                    'engine' => \array_key_exists('Engine', $status) ? $status['Engine'] : '',
                    'comment' => \array_key_exists('Comment', $status) ? $status['Comment'] : '',
                ];
            }
        }

        return \compact('mainActions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @return void
     */
    public function getRoutines()
    {
        $mainActions = [
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
                    $this->util->html($routine["ROUTINE_NAME"]) : '',
                'type' => \array_key_exists("ROUTINE_TYPE", $routine) ?
                    $this->util->html($routine["ROUTINE_TYPE"]) : '',
                'returnType' => \array_key_exists("DTD_IDENTIFIER", $routine) ?
                    $this->util->html($routine["DTD_IDENTIFIER"]) : '',
                // 'alter' => $this->util->lang('Alter'),
            ];
        }

        return \compact('mainActions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @return void
     */
    public function getSequences()
    {
        $mainActions = [
            'sequence' => $this->util->lang('Create sequence'),
        ];

        $headers = [
            $this->util->lang('Name'),
        ];

        $sequences = [];
        if ($this->db->support("sequence")) {
            // From db.inc.php
            $sequences = $this->db->values("SELECT sequence_name FROM information_schema.sequences ".
                "WHERE sequence_schema = selectedSchema() ORDER BY sequence_name");
        }
        $details = [];
        foreach ($sequences as $sequence) {
            $details[] = [
                'name' => $this->util->html($sequence),
            ];
        }

        return \compact('mainActions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @return array
     */
    public function getUserTypes()
    {
        $mainActions = [
            'type' => $this->util->lang('Create type'),
        ];

        $headers = [
            $this->util->lang('Name'),
        ];

        // From db.inc.php
        $userTypes = $this->db->support("type") ? $this->db->userTypes() : [];
        $details = [];
        foreach ($userTypes as $userType) {
            $details[] = [
                'name' => $this->util->html($userType),
            ];
        }

        return \compact('mainActions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @return array
     */
    public function getEvents()
    {
        $mainActions = [
            'event' => $this->util->lang('Create event'),
        ];

        $headers = [
            $this->util->lang('Name'),
            $this->util->lang('Schedule'),
            $this->util->lang('Start'),
            // $this->util->lang('End'),
        ];

        // From db.inc.php
        $events = $this->db->support("event") ? $this->db->rows("SHOW EVENTS") : [];
        $details = [];
        foreach ($events as $event) {
            $detail = [
                'name' => $this->util->html($event["Name"]),
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

        return \compact('mainActions', 'headers', 'details');
    }
}
