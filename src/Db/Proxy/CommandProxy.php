<?php

namespace Lagdo\Adminer\Db\Proxy;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class CommandProxy
{
    /**
     * Connection for exploring indexes and EXPLAIN (to not replace FOUND_ROWS())
     * //! PDO - silent error
     *
     * @var \adminer\Min_DB
     */
    protected $connection = null;

    /**
     * The constructor
     *
     * @param string $database      The database name
     * @param string $schema        The database schema
     */
    public function __construct(string $database = '', string $schema = '')
    {
        if($database != '')
        {
            // Connection for exploring indexes and EXPLAIN (to not replace FOUND_ROWS())
            //! PDO - silent error
            $connection = \adminer\connect();
            if(\is_object($connection))
            {
                $connection->select_db($database);
                if($schema !== '')
                {
                    \adminer\set_schema($schema, $connection);
                }
                $this->connection = $connection;
            }
        }
    }

    /**
     * Print select result
     * From editing.inc.php
     *
     * @param \adminer\Min_Result
     * @param array
     * @param int
     *
     * @return array
    */
    protected function select($result, $orgtables = [], $limit = 0)
    {
        global $jush;

        $links = []; // colno => orgtable - create links from these columns
        $indexes = []; // orgtable => array(column => colno) - primary keys
        $columns = []; // orgtable => array(column => ) - not selected columns in primary key
        $blobs = []; // colno => bool - display bytes for blobs
        $types = []; // colno => type - display char in <code>
        $tables = []; // table => orgtable - mapping to use in EXPLAIN

        $colCount = 0;
        $rowCount = 0;
        $details = [];
        while(($limit === 0 || $rowCount < $limit) && ($row = $result->fetch_row()))
        {
            $colCount = \count($row);
            $rowCount++;
            $detail = [];
            foreach($row as $key => $val)
            {
                $link = "";
                if(isset($links[$key]) && !$columns[$links[$key]])
                {
                    if($orgtables && $jush == "sql")
                    { // MySQL EXPLAIN
                        $table = $row[\array_search("table=", $links)];
                        $link = ME . $links[$key] .
                            \urlencode($orgtables[$table] != "" ? $orgtables[$table] : $table);
                    }
                    else
                    {
                        $link = ME . "edit=" . \urlencode($links[$key]);
                        foreach($indexes[$links[$key]] as $col => $j)
                        {
                            $link .= "&where" . \urlencode("[" .
                                \adminer\bracket_escape($col) . "]") . "=" . \urlencode($row[$j]);
                        }
                    }
                }
                elseif(\adminer\is_url($val))
                {
                    $link = $val;
                }
                if($val === null)
                {
                    $val = "<i>NULL</i>";
                }
                elseif(isset($blobs[$key]) && $blobs[$key] && !\adminer\is_utf8($val))
                {
                    //! link to download
                    $val = "<i>" . \adminer\lang('%d byte(s)', \strlen($val)) . "</i>";
                }
                else
                {
                    $val = \adminer\h($val);
                    if(isset($types[$key]) && $types[$key] == 254)
                    { // 254 - char
                        $val = "<code>$val</code>";
                    }
                }
                // if($link)
                // {
                //     $val = "<a href='" . \adminer\h($link) . "'" .
                //         (\adminer\is_url($link) ? \adminer\target_blank() : '') . ">$val</a>";
                // }
                $detail[$key] = $val;
            }
            $details[] = $detail;
        }
        $message = \adminer\lang('No rows.');
        if($rowCount > 0)
        {
            $num_rows = $result->num_rows;
            $message = ($num_rows ? ($limit && $num_rows > $limit ?
                \adminer\lang('%d / ', $limit) :
                "") . \adminer\lang('%d row(s)', $num_rows) : "");
        }

        // Table header
        $headers = [];
        for($j = 0; $j < $colCount; $j++)
        {
            $field = $result->fetch_field();
            $name = $field->name;
            $orgtable = $field->orgtable;
            $orgname = $field->orgname;
            // PostgreSQL fix: the table field can be missing.
            $tables[$field->table ?? $orgtable] = $orgtable;
            if($orgtables && $jush == "sql")
            { // MySQL EXPLAIN
                $links[$j] = ($name == "table" ? "table=" : ($name == "possible_keys" ? "indexes=" : null));
            }
            elseif($orgtable != "")
            {
                if(!isset($indexes[$orgtable]))
                {
                    // find primary key in each table
                    $indexes[$orgtable] = [];
                    foreach(\adminer\indexes($orgtable, $this->connection) as $index)
                    {
                        if($index["type"] == "PRIMARY")
                        {
                            $indexes[$orgtable] = \array_flip($index["columns"]);
                            break;
                        }
                    }
                    $columns[$orgtable] = $indexes[$orgtable];
                }
                if(isset($columns[$orgtable][$orgname]))
                {
                    unset($columns[$orgtable][$orgname]);
                    $indexes[$orgtable][$orgname] = $j;
                    $links[$j] = $orgtable;
                }
            }
            if($field->charsetnr == 63)
            { // 63 - binary
                $blobs[$j] = true;
            }
            $types[$j] = $field->type ?? ''; // Some drivers don't set the type field.
            $headers[] = \adminer\h($name);
            // $header = [
            //     'text' => \adminer\h($name),
            //     'title' => '',
            //     'doc' => '',
            // ];
            // if($orgtable != "" || $field->name != $orgname)
            // {
            //     $header['title'] = \adminer\h(($orgtable != "" ? "$orgtable." : "") . $orgname);
            // }
            // if($orgtables)
            // {
            //     $header['doc'] = \adminer\doc_link([
            //         'sql' => "explain-output.html#explain_" . \strtolower($name),
            //         'mariadb' => "explain/#the-columns-in-explain-select",
            //     ]);
            // }
            // $headers[] = $header;
        }

        return \compact('tables', 'headers', 'details', 'message');
    }

    /**
     * Execute a set of queries
     *
     * @param string $queries       The queries to execute
     * @param int    $limit         The max number of rows to return
     * @param bool   $errorStops    Stop executing the requests in case of error
     * @param bool   $onlyErrors    Return only errors
     *
     * @return array
     */
    public function executeCommands(string $queries, int $limit, bool $errorStops, bool $onlyErrors)
    {
        global $jush, $connection;

        if(\function_exists('memory_get_usage'))
        {
            // @ - may be disabled, 2 - substr and trim, 8e6 - other variables
            @\ini_set("memory_limit", \max(\adminer\ini_bytes("memory_limit"),
                2 * \strlen($queries) + \memory_get_usage() + 8e6));
		}

		// if($queries != "" && \strlen($queries) < 1e6) { // don't add big queries
		// 	$q = $queries . (\preg_match("~;[ \t\r\n]*\$~", $queries) ? "" : ";"); //! doesn't work with DELIMITER |
		// 	if(!$history || \reset(\end($history)) != $q) { // no repeated queries
		// 		\restart_session();
		// 		$history[] = [$q, \time()]; //! add elapsed time
		// 		\set_session("queries", $history_all); // required because reference is unlinked by stop_session()
		// 		\stop_session();
		// 	}
		// }

		$space = "(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)";
		$delimiter = ";";
		$offset = 0;
        $empty = true;

		$commands = 0;
        $timestamps = [];
        $parse = '[\'"' .
            ($jush == "sql" ? '`#' :
            ($jush == "sqlite" ? '`[' :
            ($jush == "mssql" ? '[' : ''))) . ']|/\*|-- |$' .
            ($jush == "pgsql" ? '|\$[^$]*\$' : '');
		// $total_start = \microtime(true);
		// \parse_str($_COOKIE["adminer_export"], $adminer_export);
		// $dump_format = $adminer->dumpFormat();
		// unset($dump_format["sql"]);

        $results = [];
        while($queries != "")
        {
            if($offset == 0 && \preg_match("~^$space*+DELIMITER\\s+(\\S+)~i", $queries, $match))
            {
				$delimiter = $match[1];
                $queries = \substr($queries, \strlen($match[0]));
                continue;
			}

            // should always match
            \preg_match('(' . \preg_quote($delimiter) . "\\s*|$parse)",
                $queries, $match, PREG_OFFSET_CAPTURE, $offset);
            list($found, $pos) = $match[0];

            if(!$found && \rtrim($queries) == "")
            {
                break;
            }
            $offset = $pos + \strlen($found);

            if($found && \rtrim($found) != $delimiter)
            {
                // find matching quote or comment end
                while(\preg_match('(' . ($found == '/*' ? '\*/' : ($found == '[' ? ']' :
                    (\preg_match('~^-- |^#~', $found) ? "\n" : \preg_quote($found) .
                    "|\\\\."))) . '|$)s', $queries, $match, PREG_OFFSET_CAPTURE, $offset))
                {
                    //! respect sql_mode NO_BACKSLASH_ESCAPES
                    $s = $match[0][0];
                    $offset = $match[0][1] + \strlen($s);
                    if($s[0] != "\\")
                    {
                        break;
                    }
                }
                continue;
            }

            // end of a query
            $errors = [];
            $messages = [];
            $select = null;

            $empty = false;
            $q = \substr($queries, 0, $pos);
            $commands++;
            // $print = "<pre id='sql-$commands'><code class='jush-$jush'>" .
            //     $adminer->sqlCommandQuery($q) . "</code></pre>\n";
            if($jush == "sqlite" && \preg_match("~^$space*+ATTACH\\b~i", $q, $match))
            {
                // PHP doesn't support setting SQLITE_LIMIT_ATTACHED
                // $errors[] = " <a href='#sql-$commands'>$commands</a>";
                $errors[] = \adminer\lang('ATTACH queries are not supported.');
                $results[] = [
                    'query' => $q,
                    'errors' => $errors,
                    'messages' => $messages,
                    'select' => $select,
                ];
                if($errorStops)
                {
                    break;
                }
            }
            else
            {
                // if(!$onlyErrors)
                // {
                //     echo $print;
                //     \ob_flush();
                //     \flush(); // can take a long time - show the running query
                // }
                $start = \microtime(true);
                //! don't allow changing of character_set_results, convert encoding of displayed query
                if($connection->multi_query($q) && \is_object($this->connection) && \preg_match("~^$space*+USE\\b~i", $q))
                {
                    $this->connection->query($q);
                }

                do
                {
                    $result = $connection->store_result();

                    if($connection->error)
                    {
                        // echo ($onlyErrors ? $print : "");
                        // echo "<p class='error'>" . \adminer\lang('Error in query') . ($connection->errno ?
                        //     " ($connection->errno)" : "") . ": " . \adminer\error() . "\n";
                        // $errors[] = " <a href='#sql-$commands'>$commands</a>";
                        $error = \adminer\error();
                        if(isset($connection->errno))
                        {
                            $error = "($connection->errno): $error";
                        }
                        $errors[] = $error;
                    }
                    else
                    {
                        // $time = " <span class='time'>(" . \adminer\format_time($start) . ")</span>"
                        //     . (\strlen($q) < 1000 ? " <a href='" . \adminer\h(ME) .
                        //     "sql=" . \urlencode(\trim($q)) . "'>" . \adminer\lang('Edit') . "</a>" : "")
                        //     // 1000 - maximum length of encoded URL in IE is 2083 characters
                        // ;
                        $affected = $connection->affected_rows; // getting warnigns overwrites this
                        // $warnings = ($onlyErrors ? "" : $driver->warnings());
                        // $warnings_id = "warnings-$commands";
                        // if($warnings)
                        // {
                        //     $time .= ", <a href='#$warnings_id'>" . \adminer\lang('Warnings') . "</a>" .
                        //         \adminer\script("qsl('a').onclick = partial(toggle, '$warnings_id');", "");
                        // }
                        // $explain = null;
                        // $explain_id = "explain-$commands";
                        if(\is_object($result))
                        {
                            if(!$onlyErrors)
                            {
                                $select = $this->select($result, [], $limit);
                                $messages[] = $select['message'];
                            }
                            // $orgtables = $this->select($result, [], $limit);
                            // if(!$onlyErrors)
                            // {
                            //     echo "<form action='' method='post'>\n";
                            //     $num_rows = $result->num_rows;
                            //     echo "<p>" . ($num_rows ? ($limit && $num_rows > $limit ?
                            //         \adminer\lang('%d / ', $limit) : "") . \adminer\lang('%d row(s)', $num_rows) : "");
                            //     echo $time;
                            //     if($this->connection && \preg_match("~^($space|\\()*+SELECT\\b~i", $q) &&
                            //         ($explain = \adminer\explain($this->connection, $q))) {
                            //         echo ", <a href='#$explain_id'>Explain</a>" .
                            //             \adminer\script("qsl('a').onclick = partial(toggle, '$explain_id');", "");
                            //     }
                            //     $id = "export-$commands";
                            //     echo ", <a href='#$id'>" . \adminer\lang('Export') . "</a>" .
                            //         \adminer\script("qsl('a').onclick = partial(toggle, '$id');", "") .
                            //         "<span id='$id' class='hidden'>: "
                            //         . \adminer\html_select("output", $adminer->dumpOutput(), $adminer_export["output"]) . " "
                            //         . \adminer\html_select("format", $dump_format, $adminer_export["format"])
                            //         . "<input type='hidden' name='query' value='" . \adminer\h($q) . "'>"
                            //         . " <input type='submit' name='export' value='" . \adminer\lang('Export') .
                            //         "'><input type='hidden' name='token' value='$token'></span>\n"
                            //         . "</form>\n"
                            //     ;
                            // }
                        }
                        else
                        {
                            // if(\preg_match("~^$space*+(CREATE|DROP|ALTER)$space++(DATABASE|SCHEMA)\\b~i", $q))
                            // {
                            //     \restart_session();
                            //     \set_session("dbs", null); // clear cache
                            //     \stop_session();
                            // }
                            if(!$onlyErrors)
                            {
                                // $title = \adminer\h($connection->info);
                                $messages[] = \adminer\lang('Query executed OK, %d row(s) affected.', $affected); //  . "$time";
                            }
                        }
                        // echo ($warnings ? "<div id='$warnings_id' class='hidden'>\n$warnings</div>\n" : "");
                        // if($explain)
                        // {
                        //     echo "<div id='$explain_id' class='hidden'>\n";
                        //     $this->select($explain, $orgtables);
                        //     echo "</div>\n";
                        // }
                    }

                    $results[] = [
                        'query' => $q,
                        'errors' => $errors,
                        'messages' => $messages,
                        'select' => $select,
                    ];

                    if($connection->error && $errorStops)
                    {
                        break 2;
                    }

                    $start = \microtime(true);
                }
                while($connection->next_result());
            }

            $queries = \substr($queries, $offset);
            $offset = 0;
        }

        if($empty)
        {
            $messages[] = \adminer\lang('No commands to execute.');
        }
        elseif($onlyErrors)
        {
            $messages[] =  \adminer\lang('%d query(s) executed OK.', $commands - \count($errors));
            // $timestamps[] = \adminer\format_time($total_start);
        }
        // elseif($errors && $commands > 1)
        // {
        //     $errors[] = \adminer\lang('Error in query') . ": " . \implode("", $errors);
		// }
		//! MS SQL - SET SHOWPLAN_ALL OFF

        return \compact('results', 'messages', 'errors', 'timestamps');
    }
}
