<?php

namespace Lagdo\Adminer\Db\Proxy;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class ViewProxy extends AbstractProxy
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
        if (!$this->viewStatus) {
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
            "select" => $this->ui->lang('Select data'),
        ];
        if ($this->server->support("table") || $this->server->support("indexes")) {
            $links["table"] = $this->ui->lang('Show structure');
        }
        if ($this->server->support("table")) {
            $links["alter"] = $this->ui->lang('Alter view');
        }
        if ($set !== null) {
            $links["edit"] = $this->ui->lang('New item');
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
            'edit-view' => $this->ui->lang('Edit view'),
            'drop-view' => $this->ui->lang('Drop view'),
        ];

        // From table.inc.php
        $status = $this->status($table);
        $name = $this->ui->tableName($status);
        $title = ($status['Engine'] == 'materialized view' ?
            $this->ui->lang('Materialized view') : $this->ui->lang('View')) .
            ": " . ($name != "" ? $name : $this->ui->h($table));

        $comment = $status["Comment"] ?? '';

        $tabs = [
            'fields' => $this->ui->lang('Columns'),
            // 'indexes' => $this->ui->lang('Indexes'),
            // 'foreign-keys' => $this->ui->lang('Foreign keys'),
            // 'triggers' => $this->ui->lang('Triggers'),
        ];
        if ($this->server->support("view_trigger")) {
            $tabs['triggers'] = $this->ui->lang('Triggers');
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
        if (!$fields) {
            throw new Exception($this->server->error());
        }

        $main_actions = $this->getViewLinks();

        $tabs = [
            'fields' => $this->ui->lang('Columns'),
            // 'triggers' => $this->ui->lang('Triggers'),
        ];
        if ($this->server->support("view_trigger")) {
            $tabs['triggers'] = $this->ui->lang('Triggers');
        }

        $headers = [
            $this->ui->lang('Name'),
            $this->ui->lang('Type'),
            $this->ui->lang('Collation'),
        ];
        $hasComment = $this->server->support('comment');
        if ($hasComment) {
            $headers[] = $this->ui->lang('Comment');
        }

        $details = [];
        foreach ($fields as $field) {
            $type = $this->ui->h($field["full_type"]);
            if ($field["null"]) {
                $type .= " <i>nullable</i>"; // " <i>NULL</i>";
            }
            if ($field["auto_increment"]) {
                $type .= " <i>" . $this->ui->lang('Auto Increment') . "</i>";
            }
            if (\array_key_exists("default", $field)) {
                $type .= /*' ' . $this->ui->lang('Default value') .*/ ' [<b>' . $this->ui->h($field["default"]) . '</b>]';
            }
            $detail = [
                'name' => $this->ui->h($field["field"] ?? ''),
                'type' => $type,
                'collation' => $this->ui->h($field["collation"] ?? ''),
            ];
            if ($hasComment) {
                $detail['comment'] = $this->ui->h($field["comment"] ?? '');
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
        if (!$this->server->support("view_trigger")) {
            return null;
        }

        // From table.inc.php
        $triggers = $this->server->triggers($table);
        $main_actions = [
            $this->ui->lang('Add trigger'),
        ];

        $headers = [
            $this->ui->lang('Name'),
            '&nbsp;',
            '&nbsp;',
            '&nbsp;',
        ];

        if (!$triggers) {
            $triggers = [];
        }
        $details = [];
        // From table.inc.php
        foreach ($triggers as $key => $val) {
            $details[] = [
                $this->ui->h($val[0]),
                $this->ui->h($val[1]),
                $this->ui->h($key),
                $this->ui->lang('Alter'),
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
        if ($this->server->jush == "pgsql") {
            $status = $this->server->table_status($view);
            $orig_type = \strtoupper($status["Engine"]);
        }
        $values = $this->server->view($view);
        $values["name"] = $view;
        $values["materialized"] = ($orig_type != "VIEW");

        $error = $this->server->error();
        if (($error)) {
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
        $message = $this->ui->lang('View has been created.');
        $type = $values["materialized"] ? "MATERIALIZED VIEW" : "VIEW";

        $sql = ($this->server->jush == "mssql" ? "ALTER" : "CREATE OR REPLACE") .
            " $type " . $this->server->table($name) . " AS\n" . $values['select'];
        $success = $this->ui->query_redirect($sql, $location, $message);

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
        if ($this->server->jush == "pgsql") {
            $status = $this->server->table_status($view);
            $orig_type = \strtoupper($status["Engine"]);
        }

        $name = \trim($values["name"]);
        $location = null; // $_POST["drop"] ? \substr(ME, 0, -1) : ME . "table=" . \urlencode($name);
        $message = $this->ui->lang('View has been altered.');
        $type = $values["materialized"] ? "MATERIALIZED VIEW" : "VIEW";
        $temp_name = $name . "_adminer_" . \uniqid();

        $this->ui->drop_create(
            "DROP $orig_type " . $this->server->table($view),
            "CREATE $type " . $this->server->table($name) . " AS\n" . $values['select'],
            "DROP $type " . $this->server->table($name),
            "CREATE $type " . $this->server->table($temp_name) . " AS\n" . $values['select'],
            "DROP $type " . $this->server->table($temp_name),
            $location,
            $this->ui->lang('View has been dropped.'),
            $message,
            $this->ui->lang('View has been created.'),
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
        if ($this->server->jush == "pgsql") {
            $status = $this->server->table_status($view);
            $orig_type = \strtoupper($status["Engine"]);
        }

        $sql = "DROP $orig_type " . $this->server->table($view);
        $location = null; // $_POST["drop"] ? \substr(ME, 0, -1) : ME . "table=" . \urlencode($name);
        $message = $this->ui->lang('View has been dropped.');
        $success =$this->ui->drop_only($sql, $location, $message);

        $error = $this->server->error();

        return \compact('success', 'message', 'error');
    }
}
