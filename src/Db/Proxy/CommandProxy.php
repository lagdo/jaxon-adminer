<?php

namespace Lagdo\Adminer\Db\Proxy;

use Lagdo\Adminer\Drivers\ConnectionInterface;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class CommandProxy extends AbstractProxy
{
    /**
     * Connection for exploring indexes and EXPLAIN (to not replace FOUND_ROWS())
     * //! PDO - silent error
     *
     * @var ConnectionInterface
     */
    protected $connection2 = null;

    /**
     * Open a second connection to the server
     *
     * @param string $database      The database name
     * @param string $schema        The database schema
     *
     * @return void
     */
    public function connect(string $database = '', string $schema = '')
    {
        if($database != '')
        {
            // Connection for exploring indexes and EXPLAIN (to not replace FOUND_ROWS())
            //! PDO - silent error
            $connection = $this->server->connect();
            if(\is_object($connection))
            {
                $connection->select_db($database);
                if($schema !== '')
                {
                    $this->server->set_schema($schema, $connection);
                }
                $this->connection2 = $connection;
            }
        }
    }

    /**
     * Print select result
     * From editing.inc.php
     *
     * @param mixed
     * @param array
     * @param int
     *
     * @return array
    */
    protected function select($result, $orgtables = [], $limit = 0)
    {
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
                    if($orgtables && $this->server->jush == "sql")
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
                                $this->ui->bracket_escape($col) . "]") . "=" . \urlencode($row[$j]);
                        }
                    }
                }
                elseif($this->ui->is_url($val))
                {
                    $link = $val;
                }
                if($val === null)
                {
                    $val = "<i>NULL</i>";
                }
                elseif(isset($blobs[$key]) && $blobs[$key] && !$this->ui->is_utf8($val))
                {
                    //! link to download
                    $val = "<i>" . $this->ui->lang('%d byte(s)', \strlen($val)) . "</i>";
                }
                else
                {
                    $val = $this->ui->h($val);
                    if(isset($types[$key]) && $types[$key] == 254)
                    { // 254 - char
                        $val = "<code>$val</code>";
                    }
                }
                $detail[$key] = $val;
            }
            $details[] = $detail;
        }
        $message = $this->ui->lang('No rows.');
        if($rowCount > 0)
        {
            $num_rows = $result->num_rows;
            $message = ($num_rows ? ($limit && $num_rows > $limit ?
                $this->ui->lang('%d / ', $limit) :
                "") . $this->ui->lang('%d row(s)', $num_rows) : "");
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
            if($orgtables && $this->server->jush == "sql")
            { // MySQL EXPLAIN
                $links[$j] = ($name == "table" ? "table=" : ($name == "possible_keys" ? "indexes=" : null));
            }
            elseif($orgtable != "")
            {
                if(!isset($indexes[$orgtable]))
                {
                    // find primary key in each table
                    $indexes[$orgtable] = [];
                    foreach($this->server->indexes($orgtable, $this->connection2) as $index)
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
            $headers[] = $this->ui->h($name);
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
        if(\function_exists('memory_get_usage'))
        {
            // @ - may be disabled, 2 - substr and trim, 8e6 - other variables
            @\ini_set("memory_limit", \max($this->ui->ini_bytes("memory_limit"),
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
            ($this->server->jush == "sql" ? '`#' :
            ($this->server->jush == "sqlite" ? '`[' :
            ($this->server->jush == "mssql" ? '[' : ''))) . ']|/\*|-- |$' .
            ($this->server->jush == "pgsql" ? '|\$[^$]*\$' : '');
		// $total_start = \microtime(true);
		// \parse_str($_COOKIE["adminer_export"], $adminer_export);
		// $dump_format = $this->ui->dumpFormat();
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
            // $print = "<pre id='sql-$commands'><code class='jush-$this->server->jush'>" .
            //     $this->ui->sqlCommandQuery($q) . "</code></pre>\n";
            if($this->server->jush == "sqlite" && \preg_match("~^$space*+ATTACH\\b~i", $q, $match))
            {
                // PHP doesn't support setting SQLITE_LIMIT_ATTACHED
                // $errors[] = " <a href='#sql-$commands'>$commands</a>";
                $errors[] = $this->ui->lang('ATTACH queries are not supported.');
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
                if($this->connection->multi_query($q) && \is_object($this->connection2) && \preg_match("~^$space*+USE\\b~i", $q))
                {
                    $this->connection2->query($q);
                }

                do
                {
                    $result = $this->connection->store_result();

                    if($this->connection->error)
                    {
                        // echo ($onlyErrors ? $print : "");
                        // echo "<p class='error'>" . $this->ui->lang('Error in query') . ($this->connection->errno ?
                        //     " ($this->connection->errno)" : "") . ": " . $this->server->error() . "\n";
                        // $errors[] = " <a href='#sql-$commands'>$commands</a>";
                        $error = $this->server->error();
                        if(isset($this->connection->errno))
                        {
                            $error = "($this->connection->errno): $error";
                        }
                        $errors[] = $error;
                    }
                    else
                    {
                        $affected = $this->connection->affected_rows; // getting warnigns overwrites this
                        if(\is_object($result))
                        {
                            if(!$onlyErrors)
                            {
                                $select = $this->select($result, [], $limit);
                                $messages[] = $select['message'];
                            }
                        }
                        else
                        {
                            if(!$onlyErrors)
                            {
                                // $title = $this->ui->h($this->connection->info);
                                $messages[] = $this->ui->lang('Query executed OK, %d row(s) affected.', $affected); //  . "$time";
                            }
                        }
                    }

                    $results[] = [
                        'query' => $q,
                        'errors' => $errors,
                        'messages' => $messages,
                        'select' => $select,
                    ];

                    if($this->connection->error && $errorStops)
                    {
                        break 2;
                    }

                    $start = \microtime(true);
                }
                while($this->connection->next_result());
            }

            $queries = \substr($queries, $offset);
            $offset = 0;
        }

        if($empty)
        {
            $messages[] = $this->ui->lang('No commands to execute.');
        }
        elseif($onlyErrors)
        {
            $messages[] =  $this->ui->lang('%d query(s) executed OK.', $commands - \count($errors));
            // $timestamps[] = $this->ui->format_time($total_start);
        }
        // elseif($errors && $commands > 1)
        // {
        //     $errors[] = $this->ui->lang('Error in query') . ": " . \implode("", $errors);
		// }
		//! MS SQL - SET SHOWPLAN_ALL OFF

        return \compact('results', 'messages', 'errors', 'timestamps');
    }
}
