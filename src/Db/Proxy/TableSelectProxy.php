<?php

namespace Lagdo\Adminer\Db\Proxy;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class TableSelectProxy extends AbstractProxy
{
    /**
     * Print columns box in select
     * @param array result of selectColumnsProcess()[0]
     * @param array selectable columns
     * @return null
     */
    private function getColumnsOptions($select, $columns)
    {
        return [
            'select' => $select,
            'values' => (array)$_GET["columns"],
            'columns' => $columns,
            'functions' => $this->server->functions,
            'grouping' => $this->server->grouping,
        ];
    }

    /**
     * Print search box in select
     * @param array result of selectSearchProcess()
     * @param array selectable columns
     * @param array
     * @return null
     */
    private function getFiltersOptions($where, $columns, $indexes)
    {
        $fulltexts = [];
        foreach($indexes as $i => $index)
        {
            $fulltexts[$i] = $index["type"] == "FULLTEXT" ? h($_GET["fulltext"][$i]) : '';
        }
        return [
            // 'where' => $where,
            'values' => (array)$_GET["where"],
            'columns' => $columns,
            'indexes' => $indexes,
            'operators' => $this->server->operators,
            'fulltexts' => $fulltexts,
        ];
    }

    /**
     * Print order box in select
     * @param array result of selectOrderProcess()
     * @param array selectable columns
     * @param array
     * @return null
     */
    private function getSortingOptions($order, $columns, $indexes)
    {
        $values = [];
        $descs = (array)$_GET["desc"];
        foreach((array)$_GET["order"] as $key => $value)
        {
            $values[] = [
                'col' => $value,
                'desc' => $descs[$key] ?? 0,
            ];
        }
        return [
            // 'order' => $order,
            'values' => $values,
            'columns' => $columns,
        ];
    }

    /**
     * Print limit box in select
     * @param string result of selectLimitProcess()
     * @return null
     */
    private function getLimitOptions($limit)
    {
        return ['value' => $this->adminer->h($limit)];
    }

    /**
     * Print text length box in select
     * @param string result of selectLengthProcess()
     * @return null
     */
    private function getLengthOptions($text_length)
    {
        if($text_length === null)
        {
            return null;
        }
        return ['value' => $this->adminer->h($text_length)];
    }

    /**
     * Print action box in select
     * @param array
     * @return null
     */
    private function getActionOptions($indexes)
    {
        $columns = [];
        foreach($indexes as $index)
        {
            $current_key = \reset($index["columns"]);
            if($index["type"] != "FULLTEXT" && $current_key)
            {
                $columns[$current_key] = 1;
            }
        }
        $columns[""] = 1;
        return ['columns' => $columns];
    }

    /**
     * Print command box in select
     * @return bool whether to print default commands
     */
    private function getCommandOptions()
    {
        return !$this->server->information_schema(DB);
    }

    /**
     * Print import box in select
     * @return bool whether to print default import
     */
    private function getImportOptions()
    {
        return !$this->server->information_schema(DB);
    }

    /**
     * Print extra text in the end of a select form
     * @param array fields holding e-mails
     * @param array selectable columns
     * @return null
     */
    private function getEmailOptions($emailFields, $columns)
    {}

    /**
     * Select data from table
     *
	 * @param string $table
	 * @param array $select
	 * @param array $where
	 * @param array $group
	 * @param array $order
	 * @param int $limit
	 * @param int $page index of page starting at zero
	 *
	 * @return string
	 */
    private function buildSelectQuery($table, $select, $where, $group, $order = [], $limit = 1, $page = 0)
    {
        // From driver.inc.php
		$is_group = (\count($group) < \count($select));
		$query = $this->adminer->buildSelectQuery($select, $where, $group, $order, $limit, $page);
        if(!$query)
        {
			$query = "SELECT" . $this->server->limit(
                ($page != "last" && $limit != "" && $group && $is_group && $this->server->jush == "sql" ?
                    "SQL_CALC_FOUND_ROWS " : "") . \implode(", ", $select) . "\nFROM " . $this->server->table($table),
                ($where ? "\nWHERE " . \implode(" AND ", $where) : "") . ($group && $is_group ?
                    "\nGROUP BY " . \implode(", ", $group) : "") . ($order ? "\nORDER BY " . \implode(", ", $order) : ""),
				($limit != "" ? +$limit : null),
				($page ? $limit * $page : 0),
				"\n"
			);
		}

        // From adminer.inc.php
        return \str_replace("\n", " ", $query);
	}

    /**
     * Get required data for create/update on tables
     *
     * @param string $table         The table name
     * @param array  $queryOptions  The query options
     *
     * @return array
     */
    private function prepareSelect(string $table, array &$queryOptions = [])
    {
        if(!isset($queryOptions['columns']))
        {
            $queryOptions['columns'] = [];
        }
        if(!isset($queryOptions['where']))
        {
            $queryOptions['where'] = [];
        }
        if(!isset($queryOptions['order']))
        {
            $queryOptions['order'] = [];
        }
        if(!isset($queryOptions['desc']))
        {
            $queryOptions['desc'] = [];
        }

        // Set request parameters for Adminer functions
        $_GET['columns'] = $queryOptions['columns'] ?? [];
        $_GET['where'] = $queryOptions['where'] ?? [];
        $_GET['order'] = $queryOptions['order'] ?? [];
        $_GET['desc'] = $queryOptions['desc'] ?? [];
        $_GET['fulltext'] = $queryOptions['fulltext'] ?? [];
        $_GET['limit'] = $queryOptions['limit'] ?? '50';
        $_GET['text_length'] = $queryOptions['text_length'] ?? '100';
        $page = \intval($queryOptions['page'] ?? 1);
        if ($page > 0) {
            $page -= 1; // Page numbers start at 0 here, instead of 1.
        }
        $_GET['page'] = $page;

        // From select.inc.php
        $table_status = $this->server->table_status1($table);
        $indexes = $this->server->indexes($table);
        $fields = $this->server->fields($table);
        $foreign_keys = $this->adminer->column_foreign_keys($table);
        $oid = $table_status["Oid"] ?? null;

        $rights = []; // privilege => 0
        $columns = []; // selectable columns
        $text_length = null;
        foreach($fields as $key => $field)
        {
            $name = $this->adminer->fieldName($field);
            if(isset($field["privileges"]["select"]) && $name != "")
            {
                $columns[$key] = \html_entity_decode(\strip_tags($name), ENT_QUOTES);
                if($this->adminer->is_shortable($field))
                {
                    $text_length = $this->adminer->selectLengthProcess();
                }
            }
            $rights += $field["privileges"];
        }

        list($select, $group) = $this->adminer->selectColumnsProcess($columns, $indexes);
        $is_group = \count($group) < \count($select);
        $where = $this->adminer->selectSearchProcess($fields, $indexes);
        $order = $this->adminer->selectOrderProcess($fields, $indexes);
        $limit = $this->adminer->selectLimitProcess();

        // if($_GET["val"] && is_ajax()) {
        //     header("Content-Type: text/plain; charset=utf-8");
        //     foreach($_GET["val"] as $unique_idf => $row) {
        //         $as = convert_field($fields[key($row)]);
        //         $select = array($as ? $as : idf_escape(key($row)));
        //         $where[] = where_check($unique_idf, $fields);
        //         $return = $this->driver->select($table, $select, $where, $select);
        //         if($return) {
        //             echo reset($return->fetch_row());
        //         }
        //     }
        //     exit;
        // }

        $primary = $unselected = null;
        foreach($indexes as $index)
        {
            if($index["type"] == "PRIMARY")
            {
                $primary = \array_flip($index["columns"]);
                $unselected = ($select ? $primary : []);
                foreach($unselected as $key => $val)
                {
                    if(\in_array($this->server->idf_escape($key), $select))
                    {
                        unset($unselected[$key]);
                    }
                }
                break;
            }
        }
        if($oid && !$primary)
        {
            $primary = $unselected = [$oid => 0];
            $indexes[] = ["type" => "PRIMARY", "columns" => [$oid]];
        }

        $table_name = $this->adminer->tableName($table_status);

        // $set = null;
        // if(isset($rights["insert"]) || !support("table")) {
        //     $set = "";
        //     foreach((array) $_GET["where"] as $val) {
        //         if($foreign_keys[$val["col"]] && count($foreign_keys[$val["col"]]) == 1 && ($val["op"] == "="
        //             || (!$val["op"] && !preg_match('~[_%]~', $val["val"])) // LIKE in Editor
        //         )) {
        //             $set .= "&set" . urlencode("[" . $this->adminer->bracket_escape($val["col"]) . "]") . "=" . urlencode($val["val"]);
        //         }
        //     }
        // }
        // $this->adminer->selectLinks($table_status, $set);

        if(!$columns && $this->server->support("table"))
        {
            throw new Exception($this->adminer->lang('Unable to select the table') .
                ($fields ? "." : ": " . $this->server->error()));
        }

        // if($page == "last")
        // {
        //     $found_rows = $this->connection->result($this->adminer->count_rows($table, $where, $is_group, $group));
        //     $page = \floor(\max(0, $found_rows - 1) / $limit);
        // }

        $options = [
            'columns' => $this->getColumnsOptions($select, $columns),
            'filters' => $this->getFiltersOptions($where, $columns, $indexes),
            'sorting' => $this->getSortingOptions($order, $columns, $indexes),
            'limit' => $this->getLimitOptions($limit),
            'length' => $this->getLengthOptions($text_length),
            // 'action' => $this->getActionOptions($indexes),
        ];

        $select2 = $select;
        $group2 = $group;
        if(!$select2)
        {
            $select2[] = "*";
            $convert_fields = $this->adminer->convert_fields($columns, $fields, $select);
            if($convert_fields)
            {
                $select2[] = \substr($convert_fields, 2);
            }
        }
        foreach($select as $key => $val)
        {
            $field = $fields[$this->server->idf_unescape($val)] ?? null;
            if($field && ($as = $this->server->convert_field($field)))
            {
                $select2[$key] = "$as AS $val";
            }
        }
        if(!$is_group && $unselected)
        {
            foreach($unselected as $key => $val)
            {
                $select2[] = $this->server->idf_escape($key);
                if($group2)
                {
                    $group2[] = $this->server->idf_escape($key);
                }
            }
        }

        // $print = true; // Output the SQL select query
        // ob_start();
        // $result = $this->driver->select($table, $select2, $where, $group2, $order, $limit, $page, $print);
        // $query = ob_get_clean();
		$query = $this->buildSelectQuery($table, $select2, $where, $group2, $order, $limit, $page);

        return [$table_name, $select, $group, $fields, $foreign_keys, $columns, $indexes,
            $where, $order, $limit, $page, $text_length, $options, $query, $is_group];
    }

    /**
     * Get required data for create/update on tables
     *
     * @param string $table         The table name
     * @param array  $queryOptions  The query options
     *
     * @return array
     */
    public function getSelectData(string $table, array $queryOptions = [])
    {
        list($table_name, $select, $group, $fields, $foreign_keys, $columns, $indexes, $where, $order,
            $limit, $page, $text_length, $options, $query) = $this->prepareSelect($table, $queryOptions);
        $query = $this->adminer->h($query);

        $main_actions = [
            'select-exec' => $this->adminer->lang('Execute'),
            'select-cancel' => $this->adminer->lang('Cancel'),
        ];

        return \compact('main_actions', 'options', 'query');
	}

    /**
     * Get required data for create/update on tables
     *
     * @param string $table         The table name
     * @param array  $queryOptions  The query options
     *
     * @return array
     */
    public function execSelect(string $table, array $queryOptions)
    {
        list($table_name, $select, $group, $fields, $foreign_keys, $columns, $indexes, $where, $order, $limit, $page,
            $text_length, $options, $query, $is_group) = $this->prepareSelect($table, $queryOptions);

        $error = null;
        // From driver.inc.php
        $start = microtime(true);
        $result = $this->connection->query($query);
        // From adminer.inc.php
        $duration = $this->adminer->format_time($start); // Compute and format the duration

        if(!$result)
        {
            return ['error' => $this->server->error()];
        }
        // From select.inc.php
        $rows = [];
        while(($row = $result->fetch_assoc()))
        {
            if($page && $this->server->jush == "oracle")
            {
                unset($row["RNUM"]);
            }
            $rows[] = $row;
        }
        if(!$rows)
        {
            return ['error' => $this->adminer->lang('No rows.')];
        }
        // $backward_keys = $this->adminer->backwardKeys($table, $table_name);

        // Results headers
        $headers = [
            '', // !$group && $select ? '' : lang('Modify');
        ];
        $names = [];
        $functions = [];
        reset($select);
        $rank = 1;
        foreach($rows[0] as $key => $val)
        {
            $header = [];
            if(!isset($unselected[$key]))
            {
                $val = $queryOptions["columns"][key($select)] ?? [];
                $fun = $val["fun"] ?? '';
                $field = $fields[$select ? ($val ? $val["col"] : current($select)) : $key];
                $name = ($field ? $this->adminer->fieldName($field, $rank) : ($fun ? "*" : $key));
                $header = \compact('val', 'field', 'name');
                if($name != "") {
                    $rank++;
                    $names[$key] = $name;
                    $column = $this->server->idf_escape($key);
                    // $href = remove_from_uri('(order|desc)[^=]*|page') . '&order%5B0%5D=' . urlencode($key);
                    // $desc = "&desc%5B0%5D=1";
                    $header['column'] = $column;
                    $header['key'] = $this->adminer->h($this->adminer->bracket_escape($key));
                    $header['sql'] = $this->adminer->apply_sql_function($fun, $name); //! columns looking like functions
                }
                $functions[$key] = $fun;
                next($select);
            }
            $headers[] = $header;
        }

        // $lengths = [];
        // if($_GET["modify"])
        // {
        //     foreach($rows as $row)
        //     {
        //         foreach($row as $key => $val)
        //         {
        //             $lengths[$key] = \max($lengths[$key], \min(40, strlen(\utf8_decode($val))));
        //         }
        //     }
        // }

        $results = [];
        foreach($this->adminer->rowDescriptions($rows, $foreign_keys) as $n => $row)
        {
            $unique_array = $this->adminer->unique_array($rows[$n], $indexes);
            if(!$unique_array)
            {
                $unique_array = [];
                foreach($rows[$n] as $key => $val)
                {
                    if(!\preg_match('~^(COUNT\((\*|(DISTINCT )?`(?:[^`]|``)+`)\)|(AVG|GROUP_CONCAT|MAX|MIN|SUM)\(`(?:[^`]|``)+`\))$~', $key))
                    {
                        //! columns looking like functions
                        $unique_array[$key] = $val;
                    }
                }
            }

            // Unique identifier to edit returned data.
            // $unique_idf = "";
            $rowIds = [
                'where' => [],
                'null' => [],
            ];
            foreach($unique_array as $key => $val)
            {
                $key = \trim($key);
                $type = $fields[$key]["type"] ?? '';
                $collation = $fields[$key]["collation"] ?? '';
                if(($this->server->jush == "sql" || $this->server->jush == "pgsql") &&
                    \preg_match('~char|text|enum|set~', $type) && strlen($val) > 64)
                {
                    $key = (\strpos($key, '(') ? $key : $this->server->idf_escape($key)); //! columns looking like functions
                    $key = "MD5(" . ($this->server->jush != 'sql' || \preg_match("~^utf8~", $collation) ?
                        $key : "CONVERT($key USING " . $this->server->charset() . ")") . ")";
                    $val = \md5($val);
                }
                if($val !== null)
                {
                    $rowIds['where'][$this->adminer->bracket_escape($key)] = $val;
                }
                else
                {
                    $rowIds['null'][] = $this->adminer->bracket_escape($key);
                }
                // $unique_idf .= "&" . ($val !== null ? \urlencode("where[" . $this->adminer->bracket_escape($key) . "]") .
                //     "=" . \urlencode($val) : \urlencode("null[]") . "=" . \urlencode($key));
            }

            $cols = [];
            foreach($row as $key => $val)
            {
                if(isset($names[$key]))
                {
                    $field = $fields[$key] ?? [];
                    $val = $this->connection->value($val, $field);
                    if($val != "" && (!isset($email_fields[$key]) || $email_fields[$key] != ""))
                    {
                        //! filled e-mails can be contained on other pages
                        $email_fields[$key] = ($this->adminer->is_mail($val) ? $names[$key] : "");
                    }

                    $link = "";

                    $val = $this->adminer->select_value($val, $link, $field, $text_length);
                    $text = \preg_match('~text|lob~', $field["type"] ?? '');

                    $cols[] = \compact(/*'id', */'text', 'val'/*, 'editable'*/);
                }
            }
            $results[] = ['ids' => $rowIds, 'cols' => $cols];
        }

        $total = $this->connection->result($this->adminer->count_rows($table, $where, $is_group, $group));

        $rows = $results;
        return \compact('duration', 'headers', 'query', 'rows', 'limit', 'total', 'error');
    }
}
