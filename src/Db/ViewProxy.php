<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class ViewProxy
{
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
            $this->viewStatus = \adminer\table_status1($table, true);
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
        global $jush, $driver;

        $links = [
            "select" => \adminer\lang('Select data'),
        ];
        if(\adminer\support("table") || \adminer\support("indexes"))
        {
            $links["table"] = \adminer\lang('Show structure');
        }
        if(\adminer\support("table"))
        {
            $links["alter"] = \adminer\lang('Alter view');
        }
        if($set !== null)
        {
            $links["edit"] = \adminer\lang('New item');
        }
        // $links['docs'] = \doc_link([$jush => $driver->tableHelp($name)], "?");

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
        global $adminer;

        $main_actions = [
            'edit-view' => \adminer\lang('Edit view'),
            'drop-view' => \adminer\lang('Drop view'),
        ];

        // From table.inc.php
        $status = $this->status($table);
        $name = $adminer->tableName($status);
        $title = ($status['Engine'] == 'materialized view' ?
            \adminer\lang('Materialized view') : \adminer\lang('View')) .
            ": " . ($name != "" ? $name : \adminer\h($table));

        $comment = $status["Comment"] ?? '';

        $tabs = [
            'fields' => \adminer\lang('Columns'),
            // 'indexes' => \adminer\lang('Indexes'),
            // 'foreign-keys' => \adminer\lang('Foreign keys'),
            // 'triggers' => \adminer\lang('Triggers'),
        ];
        if(\adminer\support("view_trigger"))
        {
            $tabs['triggers'] = \adminer\lang('Triggers');
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
        $fields = \adminer\fields($table);
        if(!$fields)
        {
            throw new Exception(\adminer\error());
        }

        $main_actions = $this->getViewLinks();

        $tabs = [
            'fields' => \adminer\lang('Columns'),
            // 'triggers' => \adminer\lang('Triggers'),
        ];
        if(\adminer\support("view_trigger"))
        {
            $tabs['triggers'] = \adminer\lang('Triggers');
        }

        $headers = [
            \adminer\lang('Name'),
            \adminer\lang('Type'),
            \adminer\lang('Collation'),
        ];
        $hasComment = \adminer\support('comment');
        if($hasComment)
        {
            $headers[] = \adminer\lang('Comment');
        }

        $details = [];
        foreach($fields as $field)
        {
            $type = \adminer\h($field["full_type"]);
            if($field["null"])
            {
                $type .= " <i>nullable</i>"; // " <i>NULL</i>";
            }
            if($field["auto_increment"])
            {
                $type .= " <i>" . \adminer\lang('Auto Increment') . "</i>";
            }
            if(\array_key_exists("default", $field))
            {
                $type .= /*' ' . \adminer\lang('Default value') .*/ ' [<b>' . \adminer\h($field["default"]) . '</b>]';
            }
            $detail = [
                'name' => \adminer\h($field["field"] ?? ''),
                'type' => $type,
                'collation' => \adminer\h($field["collation"] ?? ''),
            ];
            if($hasComment)
            {
                $detail['comment'] = \adminer\h($field["comment"] ?? '');
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
        if(!\adminer\support("view_trigger"))
        {
            return null;
        }

        // From table.inc.php
        $triggers = \adminer\triggers($table);
        $main_actions = [
            \adminer\lang('Add trigger'),
        ];

        $headers = [
            \adminer\lang('Name'),
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
                \adminer\h($val[0]),
                \adminer\h($val[1]),
                \adminer\h($key),
                \adminer\lang('Alter'),
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
        global $jush, $error;

        // From view.inc.php
        $orig_type = "VIEW";
        if($jush == "pgsql")
        {
            $status = \adminer\table_status($view);
            $orig_type = \strtoupper($status["Engine"]);
        }
        $values = \adminer\view($view);
        $values["name"] = $view;
        $values["materialized"] = ($orig_type != "VIEW");
        if(!$error)
        {
            $error = \adminer\error();
        }
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
        global $jush, $error;

        // From view.inc.php
        $name = \trim($values["name"]);
        $location = null; // ME . "table=" . urlencode($name);
        $message = \adminer\lang('View has been created.');
        $type = $values["materialized"] ? "MATERIALIZED VIEW" : "VIEW";

        $sql = ($jush == "mssql" ? "ALTER" : "CREATE OR REPLACE") .
            " $type " . \adminer\table($name) . " AS\n" . $values['select'];
        $success = \adminer\query_redirect($sql, $location, $message);

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
        global $jush, $error;

        // From view.inc.php
        $orig_type = "VIEW";
        if($jush == "pgsql")
        {
            $status = \adminer\table_status($view);
            $orig_type = \strtoupper($status["Engine"]);
        }

        $name = \trim($values["name"]);
        $location = null; // $_POST["drop"] ? \substr(ME, 0, -1) : ME . "table=" . \urlencode($name);
        $message = \adminer\lang('View has been altered.');
        $type = $values["materialized"] ? "MATERIALIZED VIEW" : "VIEW";
        $temp_name = $name . "_adminer_" . \uniqid();

        \adminer\drop_create(
            "DROP $orig_type " . \adminer\table($view),
            "CREATE $type " . \adminer\table($name) . " AS\n" . $values['select'],
            "DROP $type " . \adminer\table($name),
            "CREATE $type " . \adminer\table($temp_name) . " AS\n" . $values['select'],
            "DROP $type " . \adminer\table($temp_name),
            $location,
            \adminer\lang('View has been dropped.'),
            $message,
            \adminer\lang('View has been created.'),
            $view,
            $name
        );

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
        global $jush, $error;

        // From view.inc.php
        $orig_type = "VIEW";
        if($jush == "pgsql")
        {
            $status = \adminer\table_status($view);
            $orig_type = \strtoupper($status["Engine"]);
        }

        $sql = "DROP $orig_type " . \adminer\table($view);
        $location = null; // $_POST["drop"] ? \substr(ME, 0, -1) : ME . "table=" . \urlencode($name);
        $message = \adminer\lang('View has been dropped.');
        $success = \adminer\drop_only($sql, $location, $message);

        return \compact('success', 'message', 'error');
    }
}
