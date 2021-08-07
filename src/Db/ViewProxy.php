<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class ViewProxy
{
    use ProxyTrait;

    /**
     * The current table status
     *
     * @var mixed
     */
    protected $viewStatus = null;

    /**
     * Get the current table status
     *
     * @param string $table
     *
     * @return mixed
     */
    protected function status(string $table)
    {
        if(!$this->viewStatus)
        {
            $this->viewStatus = $this->server->table_status1($table, true);
        }
        return $this->viewStatus;
    }

    /**
     * Print links after select heading
     * Copied from selectLinks() in adminer.inc.php
     *
     * @param string new item options, NULL for no new item
     *
     * @return array
     */
    protected function getViewLinks($set = null)
    {
        $links = [
            "select" => $this->adminer->lang('Select data'),
        ];
        if($this->server->support("table") || $this->server->support("indexes"))
        {
            $links["table"] = $this->adminer->lang('Show structure');
        }
        if($this->server->support("table"))
        {
            $links["alter"] = $this->adminer->lang('Alter view');
        }
        if($set !== null)
        {
            $links["edit"] = $this->adminer->lang('New item');
        }
        // $links['docs'] = \doc_link([$this->server->jush => $this->driver->tableHelp($name)], "?");

        return $links;
    }

    /**
     * Get details about a view
     *
     * @param string $view      The view name
     *
     * @return array
     */
    public function getViewInfo(string $table)
    {
        $main_actions = [
            'edit-view' => $this->adminer->lang('Edit view'),
            'drop-view' => $this->adminer->lang('Drop view'),
        ];

        // From table.inc.php
        $status = $this->status($table);
        $name = $this->adminer->tableName($status);
        $title = ($status['Engine'] == 'materialized view' ?
            $this->adminer->lang('Materialized view') : $this->adminer->lang('View')) .
            ": " . ($name != "" ? $name : $this->adminer->h($table));

        $comment = $status["Comment"] ?? '';

        $tabs = [
            'fields' => $this->adminer->lang('Columns'),
            // 'indexes' => $this->adminer->lang('Indexes'),
            // 'foreign-keys' => $this->adminer->lang('Foreign keys'),
            // 'triggers' => $this->adminer->lang('Triggers'),
        ];
        if($this->server->support("view_trigger"))
        {
            $tabs['triggers'] = $this->adminer->lang('Triggers');
        }

        return \compact('main_actions', 'title', 'comment', 'tabs');
    }

    /**
     * Get the fields of a table or a view
     *
     * @param string $view      The view name
     *
     * @return array
     */
    public function getViewFields(string $table)
    {
        // From table.inc.php
        $fields = $this->server->fields($table);
        if(!$fields)
        {
            throw new Exception($this->server->error());
        }

        $main_actions = $this->getViewLinks();

        $tabs = [
            'fields' => $this->adminer->lang('Columns'),
            // 'triggers' => $this->adminer->lang('Triggers'),
        ];
        if($this->server->support("view_trigger"))
        {
            $tabs['triggers'] = $this->adminer->lang('Triggers');
        }

        $headers = [
            $this->adminer->lang('Name'),
            $this->adminer->lang('Type'),
            $this->adminer->lang('Collation'),
        ];
        $hasComment = $this->server->support('comment');
        if($hasComment)
        {
            $headers[] = $this->adminer->lang('Comment');
        }

        $details = [];
        foreach($fields as $field)
        {
            $type = $this->adminer->h($field["full_type"]);
            if($field["null"])
            {
                $type .= " <i>nullable</i>"; // " <i>NULL</i>";
            }
            if($field["auto_increment"])
            {
                $type .= " <i>" . $this->adminer->lang('Auto Increment') . "</i>";
            }
            if(\array_key_exists("default", $field))
            {
                $type .= /*' ' . $this->adminer->lang('Default value') .*/ ' [<b>' . $this->adminer->h($field["default"]) . '</b>]';
            }
            $detail = [
                'name' => $this->adminer->h($field["field"] ?? ''),
                'type' => $type,
                'collation' => $this->adminer->h($field["collation"] ?? ''),
            ];
            if($hasComment)
            {
                $detail['comment'] = $this->adminer->h($field["comment"] ?? '');
            }

            $details[] = $detail;
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the triggers of a table
     *
     * @param string $view      The view name
     *
     * @return array|null
     */
    public function getViewTriggers(string $table)
    {
        $status = $this->status($table);
        if(!$this->server->support("view_trigger"))
        {
            return null;
        }

        // From table.inc.php
        $triggers = $this->server->triggers($table);
        $main_actions = [
            $this->adminer->lang('Add trigger'),
        ];

        $headers = [
            $this->adminer->lang('Name'),
            '&nbsp;',
            '&nbsp;',
            '&nbsp;',
        ];

        if(!$triggers)
        {
            $triggers = [];
        }
        $details = [];
        // From table.inc.php
        foreach($triggers as $key => $val)
        {
            $details[] = [
                $this->adminer->h($val[0]),
                $this->adminer->h($val[1]),
                $this->adminer->h($key),
                $this->adminer->lang('Alter'),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get a view
     *
     * @param string $view      The view name
     *
     * @return array
     */
    public function getView(string $view)
    {
        // From view.inc.php
        $orig_type = "VIEW";
        if($this->server->jush == "pgsql")
        {
            $status = $this->server->table_status($view);
            $orig_type = \strtoupper($status["Engine"]);
        }
        $values = $this->server->view($view);
        $values["name"] = $view;
        $values["materialized"] = ($orig_type != "VIEW");

        $error = $this->server->error();
        if(($error))
        {
            throw new Exception($error);
        }

        return ['view' => $values, 'orig_type' => $orig_type];
    }

    /**
     * Create a view
     *
     * @param array  $values    The view values
     *
     * @return array
     */
    public function createView(array $values)
    {
        // From view.inc.php
        $name = \trim($values["name"]);
        $location = null; // ME . "table=" . urlencode($name);
        $message = $this->adminer->lang('View has been created.');
        $type = $values["materialized"] ? "MATERIALIZED VIEW" : "VIEW";

        $sql = ($this->server->jush == "mssql" ? "ALTER" : "CREATE OR REPLACE") .
            " $type " . $this->server->table($name) . " AS\n" . $values['select'];
        $success = $this->adminer->query_redirect($sql, $location, $message);

        $error = $this->server->error();

        return \compact('success', 'message', 'error');
    }

    /**
     * Update a view
     *
     * @param string $view      The view name
     * @param array  $values    The view values
     *
     * @return array
     */
    public function updateView(string $view, array $values)
    {
        // From view.inc.php
        $orig_type = "VIEW";
        if($this->server->jush == "pgsql")
        {
            $status = $this->server->table_status($view);
            $orig_type = \strtoupper($status["Engine"]);
        }

        $name = \trim($values["name"]);
        $location = null; // $_POST["drop"] ? \substr(ME, 0, -1) : ME . "table=" . \urlencode($name);
        $message = $this->adminer->lang('View has been altered.');
        $type = $values["materialized"] ? "MATERIALIZED VIEW" : "VIEW";
        $temp_name = $name . "_adminer_" . \uniqid();

        $this->adminer->drop_create(
            "DROP $orig_type " . $this->server->table($view),
            "CREATE $type " . $this->server->table($name) . " AS\n" . $values['select'],
            "DROP $type " . $this->server->table($name),
            "CREATE $type " . $this->server->table($temp_name) . " AS\n" . $values['select'],
            "DROP $type " . $this->server->table($temp_name),
            $location,
            $this->adminer->lang('View has been dropped.'),
            $message,
            $this->adminer->lang('View has been created.'),
            $view,
            $name
        );

        $error = $this->server->error();
        $success = !$error;
        return \compact('success', 'message', 'error');
    }

    /**
     * Drop a view
     *
     * @param string $view      The view name
     *
     * @return array
     */
    public function dropView(string $view)
    {
        // From view.inc.php
        $orig_type = "VIEW";
        if($this->server->jush == "pgsql")
        {
            $status = $this->server->table_status($view);
            $orig_type = \strtoupper($status["Engine"]);
        }

        $sql = "DROP $orig_type " . $this->server->table($view);
        $location = null; // $_POST["drop"] ? \substr(ME, 0, -1) : ME . "table=" . \urlencode($name);
        $message = $this->adminer->lang('View has been dropped.');
        $success =$this->adminer->drop_only($sql, $location, $message);

        $error = $this->server->error();

        return \compact('success', 'message', 'error');
    }
}
