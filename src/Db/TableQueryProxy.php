<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class TableQueryProxy
{
    use ProxyTrait;

    /**
     * Get data for an input field
     */
    protected function getFieldInput($table, $field, $value, $function, $save)
    {
        // From functions.inc.php (function input($field, $value, $function))
        $name = $this->adminer->h($this->adminer->bracket_escape($field["field"]));
        $entry = [
            'type' => $this->adminer->h($field["full_type"]),
            'name' => $name,
            'field' => [
                'type' => $field['type'],
            ],
        ];

        if(\is_array($value) && !$function)
        {
            $args = [$value];
            if(\version_compare(PHP_VERSION, 5.4) >= 0)
            {
                $args[] = JSON_PRETTY_PRINT;
            }
            $value = \call_user_func_array('json_encode', $args); //! requires PHP 5.2
            $function = "json";
        }
        $reset = ($this->server->jush == "mssql" && $field["auto_increment"]);
        if($reset && !$save)
        {
            $function = null;
        }
        $functions = ($reset ? ["orig" => $this->adminer->lang('original')] : []) + $this->adminer->editFunctions($field);

        // Input for functions
        $has_function = (\in_array($function, $functions) || isset($functions[$function]));
        if($field["type"] == "enum")
        {
            $entry['functions'] = [
                'type' => 'name',
                'name' => $this->adminer->h($functions[""] ?? ''),
            ];
        }
        elseif(\count($functions) > 1)
        {
            $entry['functions'] = [
                'type' => 'select',
                'name' => "function[$name]",
                'options' => $functions,
                'selected' => $function === null || $has_function ? $function : "",
            ];
        }
        else
        {
            $entry['functions'] = [
                'type' => 'name',
                'name' => $this->adminer->h(\reset($functions)),
            ];
        }

        // Input for value
        // The HTML code generated by Adminer is kept here.
        $attrs = " name='fields[$name]'";
        $entry['input'] = ['type' => ''];
        if($field["type"] == "enum")
        {
            $entry['input']['type'] = 'radio';
            $entry['input']['value'] = [];
            // From adminer.inc.php (function editInput(())
            if(($field["null"]))
            {
                $entry['input']['value'][] = "<label><input type='radio'$attrs value=''" .
                    ($value !== null ? "" : " checked") . "><i>NULL</i></label>";
            }
            // From functions.inc.php (function enum_input())
            $empty = 0;
            \preg_match_all("~'((?:[^']|'')*)'~", $field["length"], $matches);
            $return = ($empty !== null ? "<label><input type='radio'$attrs value='$empty'" .
                ((\is_array($value) ? \in_array($empty, $value) : $value === 0) ? " checked" : "") .
                "><i>" . $this->adminer->lang('empty') . "</i></label>" : "");
            foreach($matches[1] as $i => $val)
            {
                $val = \stripcslashes(\str_replace("''", "'", $val));
                $checked = (\is_int($value) ? $value == $i+1 : (\is_array($value) ? \in_array($i+1, $value) : $value === $val));
                $entry['input']['value'][] = "<label><input type='radio'$attrs value='" . ($i+1) . "'" .
                    ($checked ? ' checked' : '') . '>' . $this->adminer->h($this->adminer->editVal($val, $field)) . '</label>';
            }
        }
        elseif(\preg_match('~bool~', $field["type"]))
        {
            $entry['input']['value'] = "<input type='hidden'$attrs value='0'>" . "<input type='checkbox'" .
                (\preg_match('~^(1|t|true|y|yes|on)$~i', $value) ? " checked='checked'" : "") . "$attrs value='1'>";
        }
        elseif($field["type"] == "set")
        {
            $entry['input']['type'] = 'checkbox';
            $entry['input']['value'] = [];
            //! 64 bits
            $entry['input']['value'] = '';
            \preg_match_all("~'((?:[^']|'')*)'~", $field["length"], $matches);
            foreach($matches[1] as $i => $val)
            {
                $val = \stripcslashes(\str_replace("''", "'", $val));
                $checked = (\is_int($value) ? ($value >> $i) & 1 : \in_array($val, \explode(",", $value), true));
                $entry['input']['value'][] = "<label><input type='checkbox' name='fields[$name][$i]' value='" . (1 << $i) . "'" .
                    ($checked ? ' checked' : '') . ">" . $this->adminer->h($this->adminer->editVal($val, $field)) . '</label>';
            }
        }
        elseif(\preg_match('~blob|bytea|raw|file~', $field["type"]) && $this->adminer->ini_bool("file_uploads"))
        {
            $entry['input']['value'] = "<input type='file' name='fields-$name'>";
        }
        elseif(($text = \preg_match('~text|lob|memo~i', $field["type"])) || \preg_match("~\n~", $value))
        {
            if($text && $this->server->jush != "sqlite")
            {
                $attrs .= " cols='50' rows='12'";
            }
            else
            {
                $rows = \min(12, \substr_count($value, "\n") + 1);
                $attrs .= " cols='30' rows='$rows'" . ($rows == 1 ? " style='height: 1.2em;'" : ""); // 1.2em - line-height
            }
            $entry['input']['value'] = "<textarea$attrs>" . $this->adminer->h($value) . '</textarea>';
        }
        elseif($function == "json" || \preg_match('~^jsonb?$~', $field["type"]))
        {
            $entry['input']['value'] = "<textarea$attrs cols='50' rows='12' class='jush-js'>" . $this->adminer->h($value) . '</textarea>';
        }
        else
        {
            $unsigned = $field["unsigned"] ?? false;
            // int(3) is only a display hint
            $maxlength = (!\preg_match('~int~', $field["type"]) &&
                \preg_match('~^(\d+)(,(\d+))?$~', $field["length"], $match) ?
                ((\preg_match("~binary~", $field["type"]) ? 2 : 1) * $match[1] + (($match[3] ?? null) ? 1 : 0) +
                (($match[2] ?? false) && !$unsigned ? 1 : 0)) :
                ($this->server->types[$field["type"]] ? $this->server->types[$field["type"]] + ($unsigned ? 0 : 1) : 0));
            if($this->server->jush == 'sql' && $this->server->min_version(5.6) && \preg_match('~time~', $field["type"]))
            {
                $maxlength += 7; // microtime
            }
            // type='date' and type='time' display localized value which may be confusing,
            // type='datetime' uses 'T' as date and time separator
            $entry['input']['value'] = "<input" . ((!$has_function || $function === "") &&
                \preg_match('~(?<!o)int(?!er)~', $field["type"]) &&
                !\preg_match('~\[\]~', $field["full_type"]) ? " type='number'" : "") . " value='" .
                $this->adminer->h($value) . "'" . ($maxlength ? " data-maxlength='$maxlength'" : "") .
                (\preg_match('~char|binary~', $field["type"]) && $maxlength > 20 ? " size='40'" : "") . "$attrs>";
        }

        return $entry;
    }

    /**
     * Get the table fields
     *
     * @param string $table         The table name
     * @param array  $queryOptions  The query options
     *
     * @return array
     */
    private function getFields(string $table, array $queryOptions)
    {
        // From edit.inc.php
        $fields = $this->server->fields($table);

        //!!!! $_GET["select"] is never set here !!!!//

        $where = $this->adminer->where($queryOptions, $fields);
        $update = $where;
        foreach($fields as $name => $field)
        {
            $generated = $field["generated"] ?? false;
            if(!isset($field["privileges"][$update ? "update" : "insert"]) ||
                $this->adminer->fieldName($field) == "" || $generated)
            {
                unset($fields[$name]);
            }
        }

        return [$fields, $where, $update];
    }

    /**
     * Get data for insert/update on a table
     *
     * @param string $table         The table name
     * @param array  $queryOptions  The query options
     *
     * @return array
     */
    public function getQueryData(string $table, array $queryOptions = [])
    {
        // Default options
        $queryOptions['clone'] = false;
        $queryOptions['save'] = false;

        list($fields, $where, $update) = $this->getFields($table, $queryOptions);

        // From edit.inc.php
        $row = null;
        if($where)
        {
            $select = [];
            foreach($fields as $name => $field)
            {
                if(isset($field["privileges"]["select"]))
                {
                    $as = $this->server->convert_field($field);
                    if($queryOptions["clone"] && $field["auto_increment"])
                    {
                        $as = "''";
                    }
                    if($this->server->jush == "sql" && \preg_match("~enum|set~", $field["type"]))
                    {
                        $as = "1*" . $this->server->idf_escape($name);
                    }
                    $select[] = ($as ? "$as AS " : "") . $this->server->idf_escape($name);
                }
            }
            $row = [];
            if(!$this->server->support("table"))
            {
                $select = ["*"];
            }
            if($select)
            {
                $result = $this->driver->select($table, $select, [$where], $select, [], (isset($_GET["select"]) ? 2 : 1));
                if(!$result)
                {
                    // $error = $this->server->error();
                }
                else
                {
                    $row = $result->fetch_assoc();
                    if(!$row)
                    {
                        // MySQLi returns null
                        $row = false;
                    }
                }
                // if(isset($_GET["select"]) && (!$row || $result->fetch_assoc()))
                // {
                //     // $result->num_rows != 1 isn't available in all drivers
                //     $row = null;
                // }
            }
        }

        if(!$this->server->support("table") && !$fields)
        {
            if(!$where)
            {
                // insert
                $result = $this->driver->select($table, ["*"], $where, ["*"]);
                $row = ($result ? $result->fetch_assoc() : false);
                if(!$row)
                {
                    $row = [$this->driver->primary => ""];
                }
            }
            if($row)
            {
                foreach($row as $key => $val)
                {
                    if(!$where)
                    {
                        $row[$key] = null;
                    }
                    $fields[$key] = [
                        "field" => $key,
                        "null" => ($key != $this->driver->primary),
                        "auto_increment" => ($key == $this->driver->primary)
                    ];
                }
            }
        }

        // From functions.inc.php (function edit_form($table, $fields, $row, $update))
        $entries = [];
        $table_name = $this->adminer->tableName($this->server->table_status1($table, true));
        $error = null;
        if($row === false)
        {
            $error = $this->adminer->lang('No rows.');
        }
        elseif(!$fields)
        {
            $error = $this->adminer->lang('You have no privileges to update this table.');
        }
        else
        {
            foreach($fields as $name => $field)
            {
                // $default = $_GET["set"][$this->adminer->bracket_escape($name)] ?? null;
                // if($default === null)
                // {
                    $default = $field["default"];
                    if($field["type"] == "bit" && \preg_match("~^b'([01]*)'\$~", $default, $regs))
                    {
                        $default = $regs[1];
                    }
                // }
                $value = ($row !== null
                    ? ($row[$name] != "" && $this->server->jush == "sql" && \preg_match("~enum|set~", $field["type"])
                        ? (\is_array($row[$name]) ? \array_sum($row[$name]) : +$row[$name])
                        : (\is_bool($row[$name]) ? +$row[$name] : $row[$name])
                    )
                    : (!$update && $field["auto_increment"]
                        ? ""
                        : (isset($_GET["select"]) ? false : $default)
                    )
                );
                if(!$queryOptions["save"] && \is_string($value)) {
                    $value = $this->adminer->editVal($value, $field);
                }
                $function = ($queryOptions["save"]
                    ? (string) $_POST["function"][$name]
                    : ($update && \preg_match('~^CURRENT_TIMESTAMP~i', $field["on_update"])
                        ? "now"
                        : ($value === false ? null : ($value !== null ? '' : 'NULL'))
                    )
                );
                if(/*!$_POST && */!$update && $value == $field["default"] && \preg_match('~^[\w.]+\(~', $value))
                {
                    $function = "SQL";
                }
                if(\preg_match("~time~", $field["type"]) && \preg_match('~^CURRENT_TIMESTAMP~i', $value))
                {
                    $value = "";
                    $function = "now";
                }

                $entries[$name] = $this->getFieldInput($table, $field, $value, $function, $queryOptions["save"]);
            }
        }

        $main_actions = [
            'query-save' => $this->adminer->lang('Save'),
            'query-cancel' => $this->adminer->lang('Cancel'),
        ];

        $fields = $entries;
        return \compact('main_actions', 'table_name', 'error', 'fields');
    }

    /**
     * Insert a new item in a table
     *
     * @param string $table         The table name
     * @param array  $queryOptions  The query options
     *
     * @return array
     */
    public function insertItem(string $table, array $queryOptions)
    {
        list($fields, $where, $update) = $this->getFields($table, $queryOptions);

        // From edit.inc.php
        $set = [];
        foreach($fields as $name => $field)
        {
            $val = $this->adminer->process_input($field, $queryOptions);
            if($val !== false && $val !== null)
            {
                $set[$this->server->idf_escape($name)] = $val;
            }
        }

        $result = $this->driver->insert($table, $set);
        $lastId = ($result ? $this->server->last_id() : 0);
        $message = $this->adminer->lang('Item%s has been inserted.', ($lastId ? " $lastId" : ""));

        $error = $this->server->error();

        return \compact('result', 'message', 'error');
    }

    /**
     * Update one or more items in a table
     *
     * @param string $table         The table name
     * @param array  $queryOptions  The query options
     *
     * @return array
     */
    public function updateItem(string $table, array $queryOptions)
    {
        list($fields, $where, $update) = $this->getFields($table, $queryOptions);

        // From edit.inc.php
        $indexes = $this->server->indexes($table);
        $unique_array = $this->adminer->unique_array($queryOptions["where"], $indexes);
        $query_where = "\nWHERE $where";

        $set = [];
        foreach($fields as $name => $field)
        {
            $val = $this->adminer->process_input($field, $queryOptions);
            if($val !== false && $val !== null)
            {
                $set[$this->server->idf_escape($name)] = $val;
            }
        }

        $result = $this->driver->update($table, $set, $query_where, !$unique_array);
        $message = $this->adminer->lang('Item has been updated.');

        $error = $this->server->error();

        return \compact('result', 'message', 'error');
    }

    /**
     * Delete one or more items in a table
     *
     * @param string $table         The table name
     * @param array  $queryOptions  The query options
     *
     * @return array
     */
    public function deleteItem(string $table, array $queryOptions)
    {
        list($fields, $where, $update) = $this->getFields($table, $queryOptions);

        // From edit.inc.php
        $indexes = $this->server->indexes($table);
        $unique_array = $this->adminer->unique_array($queryOptions["where"], $indexes);
        $query_where = "\nWHERE $where";

        $result = $this->driver->delete($table, $query_where, !$unique_array);
        $message = $this->adminer->lang('Item has been deleted.');

        $error = $this->server->error();

        return \compact('result', 'message', 'error');
    }
}
