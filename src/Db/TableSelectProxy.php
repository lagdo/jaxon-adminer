<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class TableSelectProxy
{
    /**
     * Print columns box in select
     * @param array result of selectColumnsProcess()[0]
     * @param array selectable columns
     * @return null
     */
    private function getColumnsOptions($select, $columns)
    {
        global $functions, $grouping;
        return [
            'select' => $select,
            'q_columns' => (array)$_GET["columns"],
            'columns' => $columns,
            'functions' => $functions,
            'grouping' => $grouping,
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
        global $adminer;

        $fulltexts = [];
        foreach($indexes as $i => $index)
        {
            $fulltexts[$i] = $index["type"] == "FULLTEXT" ? h($_GET["fulltext"][$i]) : '';
        }
        return [
            'where' => $where,
            'q_where' => \array_merge((array)$_GET["where"], [[]]),
            'columns' => $columns,
            'indexes' => $indexes,
            'operators' => $adminer->operators,
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
        return [
            'order' => $order,
            'q_order' => (array)$_GET["order"],
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
        return ['value' => \adminer\h($limit)];
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
        return ['value' => \adminer\h($text_length)];
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
        return !\adminer\information_schema(DB);
    }

    /**
     * Print import box in select
     * @return bool whether to print default import
     */
    private function getImportOptions()
    {
        return !\adminer\information_schema(DB);
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
    private function selectQueryBuild($table, $select, $where, $group, $order = [], $limit = 1, $page = 0)
    {
        // From driver.inc.php
        global $adminer, $jush;
		$is_group = (\count($group) < \count($select));
		$query = $adminer->selectQueryBuild($select, $where, $group, $order, $limit, $page);
        if(!$query)
        {
			$query = "SELECT" . \adminer\limit(
                ($page != "last" && $limit != "" && $group && $is_group && $jush == "sql" ?
                    "SQL_CALC_FOUND_ROWS " : "") . \implode(", ", $select) . "\nFROM " . \adminer\table($table),
                ($where ? "\nWHERE " . \implode(" AND ", $where) : "") . ($group && $is_group ?
                    "\nGROUP BY " . \implode(", ", $group) : "") . ($order ? "\nORDER BY " . \implode(", ", $order) : ""),
				($limit != "" ? +$limit : null),
				($page ? $limit * $page : 0),
				"\n"
			);
		}

        // From adminer.inc.php
        return '<p><code class="jush-' . $jush . '">' . \adminer\h(\str_replace("\n", " ", $query)) . '</code></p>';
	}

    /**
     * Get required data for create/update on tables
     *
     * @param string $table The table name
     *
     * @return array
     */
    public function getSelectData(string $table)
    {
        global $adminer, $driver, $connection;

        // Set request parameters
        $_GET['columns'] = [];
        $_GET["where"] = [];
        $_GET["order"] = [];
        $_GET["fulltext"] = [];
        $page = 0; // $_GET["page"];
        $_GET["page"] = $page;

        // From select.inc.php
        $table_status = \adminer\table_status1($table);
        $indexes = \adminer\indexes($table);
        $fields = \adminer\fields($table);
        $foreign_keys = \adminer\column_foreign_keys($table);
        $oid = $table_status["Oid"] ?? null;

        $rights = []; // privilege => 0
        $columns = []; // selectable columns
        $text_length = null;
        foreach($fields as $key => $field)
        {
            $name = $adminer->fieldName($field);
            if(isset($field["privileges"]["select"]) && $name != "")
            {
                $columns[$key] = \html_entity_decode(\strip_tags($name), ENT_QUOTES);
                if(\adminer\is_shortable($field))
                {
                    $text_length = $adminer->selectLengthProcess();
                }
            }
            $rights += $field["privileges"];
        }

        list($select, $group) = $adminer->selectColumnsProcess($columns, $indexes);
        $is_group = \count($group) < \count($select);
        $where = $adminer->selectSearchProcess($fields, $indexes);
        $order = $adminer->selectOrderProcess($fields, $indexes);
        $limit = $adminer->selectLimitProcess();

        // if($_GET["val"] && is_ajax()) {
        //     header("Content-Type: text/plain; charset=utf-8");
        //     foreach($_GET["val"] as $unique_idf => $row) {
        //         $as = convert_field($fields[key($row)]);
        //         $select = array($as ? $as : idf_escape(key($row)));
        //         $where[] = where_check($unique_idf, $fields);
        //         $return = $driver->select($table, $select, $where, $select);
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
                    if(\in_array(\adminer\idf_escape($key), $select))
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

        $table_name = $adminer->tableName($table_status);

        // $set = null;
        // if(isset($rights["insert"]) || !support("table")) {
        //     $set = "";
        //     foreach((array) $_GET["where"] as $val) {
        //         if($foreign_keys[$val["col"]] && count($foreign_keys[$val["col"]]) == 1 && ($val["op"] == "="
        //             || (!$val["op"] && !preg_match('~[_%]~', $val["val"])) // LIKE in Editor
        //         )) {
        //             $set .= "&set" . urlencode("[" . bracket_escape($val["col"]) . "]") . "=" . urlencode($val["val"]);
        //         }
        //     }
        // }
        // $adminer->selectLinks($table_status, $set);

        if(!$columns && \adminer\support("table"))
        {
            throw new Exception(\adminer\lang('Unable to select the table') .
                ($fields ? "." : ": " . \adminer\error()));
        }

        $options = [
            'columns' => $this->getColumnsOptions($select, $columns),
            'filters' => $this->getFiltersOptions($where, $columns, $indexes),
            'sorting' => $this->getSortingOptions($order, $columns, $indexes),
            'limit' => $this->getLimitOptions($limit),
            'length' => $this->getLengthOptions($text_length),
            // 'action' => $this->getActionOptions($indexes),
        ];

        if($page == "last")
        {
            $found_rows = $connection->result(\adminer\count_rows($table, $where, $is_group, $group));
            $page = \floor(\max(0, $found_rows - 1) / $limit);
        }

        $select2 = $select;
        $group2 = $group;
        if(!$select2)
        {
            $select2[] = "*";
            $convert_fields = \adminer\convert_fields($columns, $fields, $select);
            if($convert_fields)
            {
                $select2[] = \substr($convert_fields, 2);
            }
        }
        foreach($select as $key => $val)
        {
            $field = $fields[\adminer\idf_unescape($val)];
            if($field && ($as = \adminer\convert_field($field)))
            {
                $select2[$key] = "$as AS $val";
            }
        }
        if(!$is_group && $unselected)
        {
            foreach($unselected as $key => $val)
            {
                $select2[] = \adminer\idf_escape($key);
                if($group2)
                {
                    $group2[] = \adminer\idf_escape($key);
                }
            }
        }
        // $print = true; // Output the SQL select query
        // ob_start();
        // $result = $driver->select($table, $select2, $where, $group2, $order, $limit, $page, $print);
        // $query = ob_get_clean();

		$query = $this->selectQueryBuild($table, $select2, $where, $group2, $order, $limit, $page);

        $main_actions = [
            'select-back' => \adminer\lang('Back'),
        ];

        return \compact('main_actions', 'options', 'query');
    }

    /**
     * Get required data for create/update on tables
     *
     * @param string $table The table name
     *
     * @return array
     */
    public function execSelect(string $table, $page)
    {
        global $adminer, $connection, $driver;

        // From select.inc.php
        if($page == "last")
        {
            $found_rows = $connection->result(count_rows($table, $where, $is_group, $group));
            $page = floor(max(0, $found_rows - 1) / $limit);
        }

        $select2 = $select;
        $group2 = $group;
        if(!$select2) {
            $select2[] = "*";
            $convert_fields = convert_fields($columns, $fields, $select);
            if($convert_fields) {
                $select2[] = substr($convert_fields, 2);
            }
        }
        foreach($select as $key => $val) {
            $field = $fields[idf_unescape($val)];
            if($field && ($as = convert_field($field))) {
                $select2[$key] = "$as AS $val";
            }
        }
        if(!$is_group && $unselected) {
            foreach($unselected as $key => $val) {
                $select2[] = idf_escape($key);
                if($group2) {
                    $group2[] = idf_escape($key);
                }
            }
        }
        $result = $driver->select($table, $select2, $where, $group2, $order, $limit, $page, true);
    }
}
