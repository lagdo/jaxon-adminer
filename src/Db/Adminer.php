<?php

namespace Lagdo\Adminer\Db;

global $LANG, $translations;

include __DIR__ . '/../../adminer/include/lang.inc.php';
include __DIR__ . "/../../adminer/lang/en.inc.php";

use Lagdo\Adminer\Drivers\AdminerTrait;
use Lagdo\Adminer\Drivers\AdminerInterface;
use Lagdo\Adminer\Drivers\ServerInterface;
use Lagdo\Adminer\Drivers\DriverInterface;
use Lagdo\Adminer\Drivers\ConnectionInterface;

use function adminer\lang;
use function adminer\format_number;

class Adminer implements AdminerInterface
{
    use AdminerTrait;

    /**
     * @var array
     */
    public $credentials;

    /**
     * @var Input
     */
    public $input;

    /**
     * The constructor
     *
     * @param string $vendor
     * @param array $credentials
     */
    public function __construct(array $credentials, $vendor)
    {
        $this->input = new Input();
        $this->credentials = $credentials;
        $this->connect($this, $vendor);
    }

    /**
     * get the Adminer version
     *
     * @return string
     */
    public function version()
    {
        return "4.8.1-dev";
    }

    /**
     * @inheritDoc
     */
    public function input()
    {
        return $this->input;
    }

    /**
     * @inheritDoc
     */
    public function lang($idf)
    {
        return \call_user_func_array("\\adminer\\lang", \func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function credentials()
    {
        return $this->credentials;
    }

    /**
     * @inheritDoc
     */
    public function connectSsl()
    {}

    /**
     * @inheritDoc
     */
    public function buildSelectQuery(array $select, array $where, array $group, array $order = [], $limit = 1, $page = 0)
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function default_value($field)
    {
        $default = $field["default"];
        return ($default === null ? "" : " DEFAULT " .
            (preg_match('~char|binary|text|enum|set~', $field["type"]) ||
            preg_match('~^(?![a-z])~i', $default) ? $this->server->q($default) : $default));
    }

    /**
     * @inheritDoc
     */
    public function queries($query)
    {
        static $queries = [];
        static $start;
        if (!$start) {
            $start = microtime(true);
        }
        if ($query === null) {
            // return executed queries
            return array(implode("\n", $queries), $this->format_time($start));
        }
        $queries[] = (preg_match('~;$~', $query) ? "DELIMITER ;;\n$query;\nDELIMITER " : $query) . ";";
        return $this->connection->query($query);
    }

    /**
     * @inheritDoc
     */
    public function apply_queries($query, $tables, $escape = null)
    {
        if(!$escape)
        {
            $escape = function($table) { return $this->server->table($table); };
        }
        foreach ($tables as $table) {
            if (!$this->queries("$query " . $escape($table))) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function get_vals($query, $column = 0)
    {
        $return = [];
        $result = $this->connection->query($query);
        if (is_object($result)) {
            while ($row = $result->fetch_row()) {
                $return[] = $row[$column];
            }
        }
        return $return;
    }

    /**
     * @inheritDoc
     */
    public function get_key_vals($query, $connection2 = null, $set_keys = true)
    {
        if (!is_object($connection2)) {
            $connection2 = $this->connection;
        }
        $return = [];
        $result = $connection2->query($query);
        if (is_object($result)) {
            while ($row = $result->fetch_row()) {
                if ($set_keys) {
                    $return[$row[0]] = $row[1];
                } else {
                    $return[] = $row[0];
                }
            }
        }
        return $return;
    }

    /**
     * @inheritDoc
     */
    public function get_rows($query, $connection2 = null)
    {
        $conn = (is_object($connection2) ? $connection2 : $this->connection);
        $return = [];
        $result = $conn->query($query);
        if (is_object($result)) { // can return true
            while ($row = $result->fetch_assoc()) {
                $return[] = $row;
            }
        }
        return $return;
    }

    /**
     * Escape for HTML
     * @param string
     * @return string
     */
    public function h($string)
    {
        return str_replace("\0", "&#0;", htmlspecialchars($string, ENT_QUOTES, 'utf-8'));
    }

    /**
     * Create repeat pattern for preg
     * @param string
     * @param int
     * @return string
     */
    public function repeat_pattern($pattern, $length)
    {
        // fix for Compilation failed: number too big in {} quantifier
        // can create {0,0} which is OK
        return str_repeat("$pattern{0,65535}", $length / 65535) . "$pattern{0," . ($length % 65535) . "}";
    }

    /**
     * @inheritDoc
     */
    public function is_utf8($val)
    {
        // don't print control chars except \t\r\n
        return (preg_match('~~u', $val) && !preg_match('~[\0-\x8\xB\xC\xE-\x1F]~', $val));
    }

    /**
     * @inheritDoc
     */
    public function number($val)
    {
        return preg_replace('~[^0-9]+~', '', $val);
    }

    /**
     * @inheritDoc
     */
    public function number_type()
    {
        return '((?<!o)int(?!er)|numeric|real|float|double|decimal|money)'; // not point, not interval
    }

    /**
     * @inheritDoc
     */
    public function is_mail($email)
    {
        $atom = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]'; // characters of local-name
        $domain = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])'; // one domain component
        $pattern = "$atom+(\\.$atom+)*@($domain?\\.)+$domain";
        return is_string($email) && preg_match("(^$pattern(,\\s*$pattern)*\$)i", $email);
    }

    /**
     * @inheritDoc
     */
    public function is_url($string)
    {
        $domain = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])'; // one domain component //! IDN
        //! restrict path, query and fragment characters
        return preg_match("~^(https?)://($domain?\\.)+$domain(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i", $string);
    }

    /**
     * @inheritDoc
     */
    public function is_shortable($field)
    {
        return preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea~', $field["type"] ?? '');
    }

    /**
     * @inheritDoc
     */
    public function ini_bool($ini)
    {
        $val = ini_get($ini);
        return (preg_match('~^(on|true|yes)$~i', $val) || (int) $val); // boolean values set by php_value are strings
    }

    /**
     * @inheritDoc
     */
    public function ini_bytes($ini)
    {
        $val = ini_get($ini);
        $unit = strtolower(substr($val, -1)); // Get the last char
        $ival = intval(substr($val, 0, -1)); // Remove the last char
        switch ($unit) {
            case 'g': $val = $ival * 1024 * 1024 * 1024; break;
            case 'm': $val = $ival * 1024 * 1024; break;
            case 'k': $val = $ival * 1024; break;
        }
        return $val;
    }

    /**
     * @inheritDoc
     */
    public function unique_array($row, $indexes)
    {
        foreach ($indexes as $index) {
            if (preg_match("~PRIMARY|UNIQUE~", $index["type"])) {
                $return = [];
                foreach ($index["columns"] as $key) {
                    if (!isset($row[$key])) { // NULL is ambiguous
                        continue 2;
                    }
                    $return[$key] = $row[$key];
                }
                return $return;
            }
        }
    }

    /**
     * Shorten UTF-8 string
     * @param string
     * @param int
     * @param string
     * @return string escaped string with appended ...
     */
    public function shorten_utf8($string, $length = 80, $suffix = "")
    {
        if (!preg_match("(^(" . $this->repeat_pattern("[\t\r\n -\x{10FFFF}]", $length) . ")($)?)u", $string, $match))
        {
            // ~s causes trash in $match[2] under some PHP versions, (.|\n) is slow
            preg_match("(^(" . $this->repeat_pattern("[\t\r\n -~]", $length) . ")($)?)", $string, $match);
        }
        return $this->h($match[1]) . $suffix . (isset($match[2]) ? "" : "<i>…</i>");
    }

    /**
     * Escape or unescape string to use inside form []
     * @param string
     * @param bool
     * @return string
     */
    public function bracket_escape($idf, $back = false) {
        // escape brackets inside name="x[]"
        static $trans = array(':' => ':1', ']' => ':2', '[' => ':3', '"' => ':4');
        return strtr($idf, ($back ? array_flip($trans) : $trans));
    }

    /**
     * Escape column key used in where()
     * @param string
     * @return string
     */
    public function escape_key($key)
    {
        if (preg_match('(^([\w(]+)(' .
            str_replace("_", ".*", preg_quote($this->server->idf_escape("_"))) . ')([ \w)]+)$)', $key, $match))
        {
            //! columns looking like functions
            return $match[1] . $this->server->idf_escape($this->server->idf_unescape($match[2])) . $match[3]; //! SQL injection
        }
        return $this->server->idf_escape($key);
    }

    /**
     * @inheritDoc
     */
    public function format_time($start)
    {
        return $this->lang('%.3f s', max(0, microtime(true) - $start));
    }

    /**
     * @inheritDoc
     */
    public function format_number($val)
    {
        return format_number($val);
    }

    /**
     * @inheritDoc
     */
    public function nl_br($string)
    {
        return str_replace("\n", "<br>", $string); // nl2br() uses XHTML before PHP 5.3
    }

    /**
     * Compute size of database
     * @param string
     * @return string formatted
     */
    public function db_size($database)
    {
        if (!$this->connection->select_db($database)) {
            return "?";
        }
        $return = 0;
        foreach ($this->server->table_status() as $table_status) {
            $return += $table_status["Data_length"] + $table_status["Index_length"];
        }
        return $this->format_number($return);
    }

    /**
     * Apply SQL function
     * @param string
     * @param string escaped column identifier
     * @return string
     */
    public function apply_sql_function($function, $column)
    {
        return ($function ? ($function == "unixepoch" ? "DATETIME($column, '$function')" :
            ($function == "count distinct" ? "COUNT(DISTINCT " : strtoupper("$function(")) . "$column)") : $column);
    }

    /**
     * @inheritDoc
     */
    public function set_utf8mb4($create)
    {
        static $set = false;
        // possible false positive
        if (!$set && preg_match('~\butf8mb4~i', $create))
        {
            $set = true;
            return "SET NAMES " . $this->server->charset() . ";\n\n";
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public function remove_definer($query)
    {
        return preg_replace('~^([A-Z =]+) DEFINER=`' . preg_replace('~@(.*)~', '`@`(%|\1)',
            $this->server->logged_user()) . '`~', '\1', $query); //! proper escaping of user
    }

    /**
     * @inheritDoc
     */
    public function column_foreign_keys($table)
    {
        $return = [];
        foreach ($this->server->foreign_keys($table) as $foreign_key) {
            foreach ($foreign_key["source"] as $val) {
                $return[$val][] = $foreign_key;
            }
        }
        return $return;
    }

    /**
     * @inheritDoc
     */
    public function convert_fields($columns, $fields, $select = [])
    {
        $return = "";
        foreach ($columns as $key => $val) {
            if ($select && !in_array($this->server->idf_escape($key), $select)) {
                continue;
            }
            $as = $this->server->convert_field($fields[$key]);
            if ($as) {
                $return .= ", $as AS " . $this->server->idf_escape($key);
            }
        }
        return $return;
    }

    /**
     * @inheritDoc
     */
    public function count_rows($table, $where, $is_group, $group)
    {
        $query = " FROM " . $this->server->table($table) . ($where ? " WHERE " . implode(" AND ", $where) : "");
        return ($is_group && ($this->server->jush == "sql" || count($group) == 1)
            ? "SELECT COUNT(DISTINCT " . implode(", ", $group) . ")$query"
            : "SELECT COUNT(*)" . ($is_group ? " FROM (SELECT 1$query GROUP BY " . implode(", ", $group) . ") x" : $query)
        );
    }

    /**
     * @inheritDoc
     */
    public function process_length($length)
    {
        if (!$length) {
            return "";
        }
        return (preg_match("~^\\s*\\(?\\s*$enum_length(?:\\s*,\\s*$enum_length)*+\\s*\\)?\\s*\$~", $length) &&
            preg_match_all("~$enum_length~", $length, $matches) ? "(" . implode(",", $matches[0]) . ")" :
            preg_replace('~^[0-9].*~', '(\0)', preg_replace('~[^-0-9,+()[\]]~', '', $length))
        );
    }

    /**
     * @inheritDoc
     */
    public function process_type($field, $collate = "COLLATE")
    {
        $values = [
            'unsigned' => $field["unsigned"] ?? null,
            'collation' => $field["collation"] ?? null,
        ];
        return " $field[type]" . $this->process_length($field["length"]) .
            (preg_match($this->number_type(), $field["type"]) && in_array($values["unsigned"], $this->server->unsigned) ?
            " $values[unsigned]" : "") . (preg_match('~char|text|enum|set~', $field["type"]) &&
            $values["collation"] ? " $collate " . $this->server->q($values["collation"]) : "")
        ;
    }

    /**
     * @inheritDoc
     */
    public function process_field($field, $type_field)
    {
        return array(
            $this->server->idf_escape(trim($field["field"])),
            $this->process_type($type_field),
            ($field["null"] ? " NULL" : " NOT NULL"), // NULL for timestamp
            $this->default_value($field),
            (preg_match('~timestamp|datetime~', $field["type"]) && $field["on_update"] ? " ON UPDATE $field[on_update]" : ""),
            ($this->server->support("comment") && $field["comment"] != "" ? " COMMENT " . $this->server->q($field["comment"]) : ""),
            ($field["auto_increment"] ? $this->server->auto_increment() : null),
        );
    }

    /**
     * @inheritDoc
     */
    public function process_input($field, $inputs)
    {
        $idf = $this->bracket_escape($field["field"]);
        $function = $inputs["function"][$idf] ?? '';
        $value = $inputs["fields"][$idf];
        if ($field["type"] == "enum") {
            if ($value == -1) {
                return false;
            }
            if ($value == "") {
                return "NULL";
            }
            return +$value;
        }
        if ($field["auto_increment"] && $value == "") {
            return null;
        }
        if ($function == "orig") {
            return (preg_match('~^CURRENT_TIMESTAMP~i', $field["on_update"]) ? $this->server->idf_escape($field["field"]) : false);
        }
        if ($function == "NULL") {
            return "NULL";
        }
        if ($field["type"] == "set") {
            return array_sum((array) $value);
        }
        if ($function == "json") {
            $function = "";
            $value = json_decode($value, true);
            if (!is_array($value)) {
                return false; //! report errors
            }
            return $value;
        }
        if (preg_match('~blob|bytea|raw|file~', $field["type"]) && $this->ini_bool("file_uploads")) {
            $file = $this->get_file("fields-$idf");
            if (!is_string($file)) {
                return false; //! report errors
            }
            return $this->driver->quoteBinary($file);
        }
        return $this->processInput($field, $value, $function);
    }

    /**
     * Process sent input
     * @param array single field from fields()
     * @param string
     * @param string
     * @return string expression to use in a query
     */
    private function processInput($field, $value, $function = "")
    {
        if ($function == "SQL") {
            return $value; // SQL injection
        }
        $name = $field["field"];
        $return = $this->server->q($value);
        if (preg_match('~^(now|getdate|uuid)$~', $function)) {
            $return = "$function()";
        } elseif (preg_match('~^current_(date|timestamp)$~', $function)) {
            $return = $function;
        } elseif (preg_match('~^([+-]|\|\|)$~', $function)) {
            $return = $this->server->idf_escape($name) . " $function $return";
        } elseif (preg_match('~^[+-] interval$~', $function)) {
            $return = $this->server->idf_escape($name) . " $function " .
                (preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i", $value) ? $value : $return);
        } elseif (preg_match('~^(addtime|subtime|concat)$~', $function)) {
            $return = "$function(" . $this->server->idf_escape($name) . ", $return)";
        } elseif (preg_match('~^(md5|sha1|password|encrypt)$~', $function)) {
            $return = "$function($return)";
        }
        return $this->server->unconvert_field($field, $return);
    }

    /**
     * Get file contents from $_FILES
     * @param string
     * @param bool
     * @return mixed int for error, string otherwise
     */
    private function get_file($key, $decompress = false)
    {
        $file = $_FILES[$key];
        if (!$file) {
            return null;
        }
        foreach ($file as $key => $val) {
            $file[$key] = (array) $val;
        }
        $return = '';
        foreach ($file["error"] as $key => $error) {
            if ($error) {
                return $error;
            }
            $name = $file["name"][$key];
            $tmp_name = $file["tmp_name"][$key];
            $content = file_get_contents($decompress && preg_match('~\.gz$~', $name)
                ? "compress.zlib://$tmp_name"
                : $tmp_name
            ); //! may not be reachable because of open_basedir
            if ($decompress) {
                $start = substr($content, 0, 3);
                if (function_exists("iconv") && preg_match("~^\xFE\xFF|^\xFF\xFE~", $start, $regs))
                {
                    // not ternary operator to save memory
                    $content = iconv("utf-16", "utf-8", $content);
                } elseif ($start == "\xEF\xBB\xBF") { // UTF-8 BOM
                    $content = substr($content, 3);
                }
                $return .= $content . "\n\n";
            } else {
                $return .= $content;
            }
        }
        //! support SQL files not ending with semicolon
        return $return;
    }

    /**
     * @inheritDoc
     */
    public function referencable_primary($self)
    {
        $return = []; // table_name => field
        foreach ($this->server->table_status('', true) as $table_name => $table) {
            if ($table_name != $self && $this->server->fk_support($table)) {
                foreach ($this->server->fields($table_name) as $field) {
                    if (isset($field["primary"])) {
                        if (isset($return[$table_name])) { // multi column primary key
                            unset($return[$table_name]);
                            break;
                        }
                        $return[$table_name] = $field;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * @inheritDoc
     */
    public function where($where, $fields = [])
    {
        $return = [];
        $wheres = $where["where"] ?? [];
        foreach ((array) $wheres as $key => $val) {
            $key = $this->bracket_escape($key, 1); // 1 - back
            $column = $this->escape_key($key);
            $return[] = $column
                // LIKE because of floats but slow with ints
                . ($this->server->jush == "sql" && is_numeric($val) && preg_match('~\.~', $val) ? " LIKE " . $this->server->q($val)
                    : ($this->server->jush == "mssql" ? " LIKE " . $this->server->q(preg_replace('~[_%[]~', '[\0]', $val)) // LIKE because of text
                    : " = " . $this->server->unconvert_field($fields[$key], $this->server->q($val))
                ))
            ; //! enum and set
            if ($this->server->jush == "sql" && preg_match('~char|text~', $fields[$key]["type"]) && preg_match("~[^ -@]~", $val))
            {
                // not just [a-z] to catch non-ASCII characters
                $return[] = "$column = " . $this->server->q($val) . " COLLATE " . $this->server->charset() . "_bin";
            }
        }
        $nulls = $where["null"] ?? [];
        foreach ((array) $nulls as $key) {
            $return[] = $this->escape_key($key) . " IS NULL";
        }
        return implode(" AND ", $return);
    }

    /**
     * Identifier of selected database
     * @return string
     */
    public function database()
    {
        // should be used everywhere instead of DB
        return $this->server->getCurrentDatabase();
    }

    /**
     * Get cached list of databases
     * @param bool
     * @return array
     */
    public function databases($flush = true)
    {
        return $this->server->get_databases($flush);
    }

    /**
     * Get list of schemas
     * @return array
     */
    public function schemas()
    {
        return $this->server->schemas();
    }

    /**
     * Table caption used in navigation and headings
     * @param array result of SHOW TABLE STATUS
     * @return string HTML code, "" to ignore table
     */
    public function tableName($tableStatus)
    {
        return $this->h($tableStatus["Name"]);
    }

    /**
     * Field caption used in select and edit
     * @param array single field returned from fields()
     * @param int order of column in select
     * @return string HTML code, "" to ignore field
     */
    public function fieldName($field, $order = 0)
    {
        return '<span title="' . $this->h($field["full_type"]) . '">' . $this->h($field["field"]) . '</span>';
    }








    /** Format value to use in select
    * @param string
    * @param string
    * @param array
    * @param int
    * @return string HTML
    */
    public function select_value($val, $link, $field, $text_length) {
        if (is_array($val)) {
            $return = "";
            foreach ($val as $k => $v) {
                $return .= "<tr>"
                    . ($val != array_values($val) ? "<th>" . h($k) : "")
                    . "<td>" . $this->select_value($v, $link, $field, $text_length)
                ;
            }
            return "<table cellspacing='0'>$return</table>";
        }
        if (!$link) {
            $link = $this->selectLink($val, $field);
        }
        if ($link === null) {
            if ($this->is_mail($val)) {
                $link = "mailto:$val";
            }
            if ($this->is_url($val)) {
                $link = $val; // IE 11 and all modern browsers hide referrer
            }
        }
        $return = $this->editVal($val, $field);
        if ($return !== null) {
            if (!$this->is_utf8($return)) {
                $return = "\0"; // htmlspecialchars of binary data returns an empty string
            } elseif ($text_length != "" && $this->is_shortable($field)) {
                // usage of LEFT() would reduce traffic but complicate query - expected average speedup: .001 s VS .01 s on local network
                $return = $this->shorten_utf8($return, max(0, +$text_length));
            } else {
                $return = $this->h($return);
            }
        }
        return $this->selectVal($return, $link, $field, $val);
    }

    /** Query printed in SQL command before execution
    * @param string query to be executed
    * @return string escaped query to be printed
    */
    public function sqlCommandQuery($query)
    {
        return $this->shorten_utf8(trim($query), 1000);
    }

    /** Export database structure
    * @param string
    * @return null prints data
    */
    public function dumpDatabase($db) {
    }

    /** Returns export format options
    * @return array empty to disable export
    */
    public function dumpFormat() {
        return array('sql' => 'SQL', 'csv' => 'CSV,', 'csv;' => 'CSV;', 'tsv' => 'TSV');
    }

    /** Returns export output options
    * @return array
    */
    public function dumpOutput() {
        $return = array('text' => lang('open'), 'file' => lang('save'));
        if (function_exists('gzencode')) {
            $return['gz'] = 'gzip';
        }
        return $return;
    }

    /** Set the path of the file for webserver load
    * @return string path of the sql dump file
    */
    public function importServerPath() {
        return "adminer.sql";
    }

    /** Print before edit form
    * @param string
    * @param array
    * @param mixed
    * @param bool
    * @return null
    */
    public function editRowPrint($table, $fields, $row, $update) {
    }

    /** Functions displayed in edit form
    * @param array single field from fields()
    * @return array
    */
    public function editFunctions($field) {
        $return = ($field["null"] ? "NULL/" : "");
        $update = isset($_GET["select"]) || $this->where($_GET);
        foreach ($this->server->edit_functions as $key => $functions) {
            if (!$key || (!isset($_GET["call"]) && $update)) { // relative functions
                foreach ($functions as $pattern => $val) {
                    if (!$pattern || preg_match("~$pattern~", $field["type"])) {
                        $return .= "/$val";
                    }
                }
            }
            if ($key && !preg_match('~set|blob|bytea|raw|file|bool~', $field["type"])) {
                $return .= "/SQL";
            }
        }
        if ($field["auto_increment"] && !$update) {
            $return = lang('Auto Increment');
        }
        return explode("/", $return);
    }

    /** Get options to display edit field
    * @param string table name
    * @param array single field from fields()
    * @param string attributes to use inside the tag
    * @param string
    * @return string custom input field or empty string for default
    */
    public function editInput($table, $field, $attrs, $value) {
        if ($field["type"] == "enum") {
            return (isset($_GET["select"]) ? "<label><input type='radio'$attrs value='-1' checked><i>" .
                lang('original') . "</i></label> " : "")
                . ($field["null"] ? "<label><input type='radio'$attrs value=''" .
                ($value !== null || isset($_GET["select"]) ? "" : " checked") . "><i>NULL</i></label> " : "")
                . $this->enum_input("radio", $attrs, $field, $value, 0) // 0 - empty
            ;
        }
        return "";
    }

    /** Get hint for edit field
    * @param string table name
    * @param array single field from fields()
    * @param string
    * @return string
    */
    public function editHint($table, $field, $value) {
        return "";
    }

    /** Value printed in select table
    * @param string HTML-escaped value to print
    * @param string link to foreign key
    * @param array single field returned from fields()
    * @param array original value before applying editVal() and escaping
    * @return string
    */
    public function selectVal($val, $link, $field, $original) {
        $type = $field["type"] ?? '';
        $return = ($val === null ? "<i>NULL</i>" : (preg_match("~char|binary|boolean~", $type) &&
            !preg_match("~var~", $type) ? "<code>$val</code>" : $val));
        if (preg_match('~blob|bytea|raw|file~', $type) && !$this->is_utf8($val)) {
            $return = "<i>" . lang('%d byte(s)', strlen($original)) . "</i>";
        }
        if (preg_match('~json~', $type)) {
            $return = "<code class='jush-js'>$return</code>";
        }
        return ($link ? "<a href='" . $this->h($link) . "'" .
            ($this->is_url($link) ? $this->target_blank() : "") . ">$return</a>" : $return);
    }

    /** Value conversion used in select and edit
    * @param string
    * @param array single field returned from fields()
    * @return string
    */
    public function editVal($val, $field) {
        return $val;
    }

    /** Get a link to use in select table
    * @param string raw value of the field
    * @param array single field returned from fields()
    * @return string or null to create the default link
    */
    public function selectLink($val, $field) {
    }

    /** Print table structure in tabular format
    * @param array data about individual fields
    * @return null
    */
    public function tableStructurePrint($fields) {
        echo "<div class='scrollable'>\n";
        echo "<table cellspacing='0' class='nowrap'>\n";
        echo "<thead><tr><th>" . lang('Column') . "<td>" . lang('Type') . (support("comment") ? "<td>" . lang('Comment') : "") . "</thead>\n";
        foreach ($fields as $field) {
            echo "<tr" . odd() . "><th>" . h($field["field"]);
            echo "<td><span title='" . h($field["collation"]) . "'>" . h($field["full_type"]) . "</span>";
            echo ($field["null"] ? " <i>NULL</i>" : "");
            echo ($field["auto_increment"] ? " <i>" . lang('Auto Increment') . "</i>" : "");
            echo (isset($field["default"]) ? " <span title='" . lang('Default value') . "'>[<b>" . h($field["default"]) . "</b>]</span>" : "");
            echo (support("comment") ? "<td>" . h($field["comment"]) : "");
            echo "\n";
        }
        echo "</table>\n";
        echo "</div>\n";
    }

    /** Print list of indexes on table in tabular format
    * @param array data about all indexes on a table
    * @return null
    */
    public function tableIndexesPrint($indexes) {
        echo "<table cellspacing='0'>\n";
        foreach ($indexes as $name => $index) {
            ksort($index["columns"]); // enforce correct columns order
            $print = array();
            foreach ($index["columns"] as $key => $val) {
                $print[] = "<i>" . h($val) . "</i>"
                    . ($index["lengths"][$key] ? "(" . $index["lengths"][$key] . ")" : "")
                    . ($index["descs"][$key] ? " DESC" : "")
                ;
            }
            echo "<tr title='" . h($name) . "'><th>$index[type]<td>" . implode(", ", $print) . "\n";
        }
        echo "</table>\n";
    }

    /** Print columns box in select
    * @param array result of selectColumnsProcess()[0]
    * @param array selectable columns
    * @return null
    */
    public function selectColumnsPrint($select, $columns) {
        global $functions, $grouping;
        print_fieldset("select", lang('Select'), $select);
        $i = 0;
        $select[""] = array();
        foreach ($select as $key => $val) {
            $val = $_GET["columns"][$key];
            $column = select_input(
                " name='columns[$i][col]'",
                $columns,
                $val["col"],
                ($key !== "" ? "selectFieldChange" : "selectAddRow")
            );
            echo "<div>" . ($functions || $grouping ? "<select name='columns[$i][fun]'>"
                . optionlist(array(-1 => "") + array_filter(array(lang('Functions') => $functions, lang('Aggregation') => $grouping)), $val["fun"]) . "</select>"
                . on_help("getTarget(event).value && getTarget(event).value.replace(/ |\$/, '(') + ')'", 1)
                . script("qsl('select').onchange = function () { helpClose();" . ($key !== "" ? "" : " qsl('select, input', this.parentNode).onchange();") . " };", "")
                . "($column)" : $column) . "</div>\n";
            $i++;
        }
        echo "</div></fieldset>\n";
    }

    /** Print search box in select
    * @param array result of selectSearchProcess()
    * @param array selectable columns
    * @param array
    * @return null
    */
    public function selectSearchPrint($where, $columns, $indexes) {
        print_fieldset("search", lang('Search'), $where);
        foreach ($indexes as $i => $index) {
            if ($index["type"] == "FULLTEXT") {
                echo "<div>(<i>" . implode("</i>, <i>", array_map(function($column) {
                    return $this->h($column);
                }, $index["columns"])) . "</i>) AGAINST";
                echo " <input type='search' name='fulltext[$i]' value='" . h($_GET["fulltext"][$i]) . "'>";
                echo script("qsl('input').oninput = selectFieldChange;", "");
                echo checkbox("boolean[$i]", 1, isset($_GET["boolean"][$i]), "BOOL");
                echo "</div>\n";
            }
        }
        $change_next = "this.parentNode.firstChild.onchange();";
        foreach (array_merge((array) $_GET["where"], array(array())) as $i => $val) {
            if (!$val || ("$val[col]$val[val]" != "" && in_array($val["op"], $this->operators))) {
                echo "<div>" . select_input(
                    " name='where[$i][col]'",
                    $columns,
                    $val["col"],
                    ($val ? "selectFieldChange" : "selectAddRow"),
                    "(" . lang('anywhere') . ")"
                );
                echo html_select("where[$i][op]", $this->operators, $val["op"], $change_next);
                echo "<input type='search' name='where[$i][val]' value='" . h($val["val"]) . "'>";
                echo script("mixin(qsl('input'), {oninput: function () { $change_next }, onkeydown: selectSearchKeydown, onsearch: selectSearchSearch});", "");
                echo "</div>\n";
            }
        }
        echo "</div></fieldset>\n";
    }

    /** Print order box in select
    * @param array result of selectOrderProcess()
    * @param array selectable columns
    * @param array
    * @return null
    */
    public function selectOrderPrint($order, $columns, $indexes) {
        print_fieldset("sort", lang('Sort'), $order);
        $i = 0;
        foreach ((array) $_GET["order"] as $key => $val) {
            if ($val != "") {
                echo "<div>" . select_input(" name='order[$i]'", $columns, $val, "selectFieldChange");
                echo checkbox("desc[$i]", 1, isset($_GET["desc"][$key]), lang('descending')) . "</div>\n";
                $i++;
            }
        }
        echo "<div>" . select_input(" name='order[$i]'", $columns, "", "selectAddRow");
        echo checkbox("desc[$i]", 1, false, lang('descending')) . "</div>\n";
        echo "</div></fieldset>\n";
    }

    /** Print limit box in select
    * @param string result of selectLimitProcess()
    * @return null
    */
    public function selectLimitPrint($limit) {
        echo "<fieldset><legend>" . lang('Limit') . "</legend><div>"; // <div> for easy styling
        echo "<input type='number' name='limit' class='size' value='" . h($limit) . "'>";
        echo script("qsl('input').oninput = selectFieldChange;", "");
        echo "</div></fieldset>\n";
    }

    /** Print text length box in select
    * @param string result of selectLengthProcess()
    * @return null
    */
    public function selectLengthPrint($text_length) {
        if ($text_length !== null) {
            echo "<fieldset><legend>" . lang('Text length') . "</legend><div>";
            echo "<input type='number' name='text_length' class='size' value='" . h($text_length) . "'>";
            echo "</div></fieldset>\n";
        }
    }

    /** Print action box in select
    * @param array
    * @return null
    */
    public function selectActionPrint($indexes) {
        echo "<fieldset><legend>" . lang('Action') . "</legend><div>";
        echo "<input type='submit' value='" . lang('Select') . "'>";
        echo " <span id='noindex' title='" . lang('Full table scan') . "'></span>";
        echo "<script" . nonce() . ">\n";
        echo "var indexColumns = ";
        $columns = array();
        foreach ($indexes as $index) {
            $current_key = reset($index["columns"]);
            if ($index["type"] != "FULLTEXT" && $current_key) {
                $columns[$current_key] = 1;
            }
        }
        $columns[""] = 1;
        foreach ($columns as $key => $val) {
            json_row($key);
        }
        echo ";\n";
        echo "selectFieldChange.call(qs('#form')['select']);\n";
        echo "</script>\n";
        echo "</div></fieldset>\n";
    }

    /** Print command box in select
    * @return bool whether to print default commands
    */
    public function selectCommandPrint() {
        return !information_schema(DB);
    }

    /** Print import box in select
    * @return bool whether to print default import
    */
    public function selectImportPrint() {
        return !information_schema(DB);
    }

    /** Print extra text in the end of a select form
    * @param array fields holding e-mails
    * @param array selectable columns
    * @return null
    */
    public function selectEmailPrint($emailFields, $columns) {
    }

    /** Process columns box in select
    * @param array selectable columns
    * @param array
    * @return array (array(select_expressions), array(group_expressions))
    */
    public function selectColumnsProcess($columns, $indexes) {
        global $functions, $grouping;
        $select = array(); // select expressions, empty for *
        $group = array(); // expressions without aggregation - will be used for GROUP BY if an aggregation function is used
        foreach ((array) $_GET["columns"] as $key => $val) {
            if ($val["fun"] == "count" || ($val["col"] != "" && (!$val["fun"] || in_array($val["fun"], $functions) || in_array($val["fun"], $grouping)))) {
                $select[$key] = apply_sql_function($val["fun"], ($val["col"] != "" ? idf_escape($val["col"]) : "*"));
                if (!in_array($val["fun"], $grouping)) {
                    $group[] = $select[$key];
                }
            }
        }
        return array($select, $group);
    }

    /** Process search box in select
    * @param array
    * @param array
    * @return array expressions to join by AND
    */
    public function selectSearchProcess($fields, $indexes) {
        global $connection, $driver;
        $return = array();
        foreach ($indexes as $i => $index) {
            if ($index["type"] == "FULLTEXT" && $_GET["fulltext"][$i] != "") {
                $return[] = "MATCH (" . implode(", ", array_map(function($column) {
                        return $this->server->idf_escape($column);
                    }, $index["columns"])) . ") AGAINST (" . $this->server->q($_GET["fulltext"][$i]) .
                    (isset($_GET["boolean"][$i]) ? " IN BOOLEAN MODE" : "") . ")";
            }
        }
        foreach ((array) $_GET["where"] as $key => $val) {
            if ("$val[col]$val[val]" != "" && in_array($val["op"], $this->operators)) {
                $prefix = "";
                $cond = " $val[op]";
                if (preg_match('~IN$~', $val["op"])) {
                    $in = process_length($val["val"]);
                    $cond .= " " . ($in != "" ? $in : "(NULL)");
                } elseif ($val["op"] == "SQL") {
                    $cond = " $val[val]"; // SQL injection
                } elseif ($val["op"] == "LIKE %%") {
                    $cond = " LIKE " . $this->processInput($fields[$val["col"]], "%$val[val]%");
                } elseif ($val["op"] == "ILIKE %%") {
                    $cond = " ILIKE " . $this->processInput($fields[$val["col"]], "%$val[val]%");
                } elseif ($val["op"] == "FIND_IN_SET") {
                    $prefix = "$val[op](" . q($val["val"]) . ", ";
                    $cond = ")";
                } elseif (!preg_match('~NULL$~', $val["op"])) {
                    $cond .= " " . $this->processInput($fields[$val["col"]], $val["val"]);
                }
                if ($val["col"] != "") {
                    $return[] = $prefix . $driver->convertSearch(idf_escape($val["col"]), $val, $fields[$val["col"]]) . $cond;
                } else {
                    // find anywhere
                    $cols = array();
                    foreach ($fields as $name => $field) {
                        if ((preg_match('~^[-\d.' . (preg_match('~IN$~', $val["op"]) ? ',' : '') . ']+$~', $val["val"]) || !preg_match('~' . number_type() . '|bit~', $field["type"]))
                            && (!preg_match("~[\x80-\xFF]~", $val["val"]) || preg_match('~char|text|enum|set~', $field["type"]))
                            && (!preg_match('~date|timestamp~', $field["type"]) || preg_match('~^\d+-\d+-\d+~', $val["val"]))
                        ) {
                            $cols[] = $prefix . $driver->convertSearch(idf_escape($name), $val, $field) . $cond;
                        }
                    }
                    $return[] = ($cols ? "(" . implode(" OR ", $cols) . ")" : "1 = 0");
                }
            }
        }
        return $return;
    }

    /** Process order box in select
    * @param array
    * @param array
    * @return array expressions to join by comma
    */
    public function selectOrderProcess($fields, $indexes) {
        $return = array();
        foreach ((array) $_GET["order"] as $key => $val) {
            if ($val != "") {
                $return[] = (preg_match('~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~', $val) ? $val : idf_escape($val)) //! MS SQL uses []
                    . (isset($_GET["desc"][$key]) ? " DESC" : "")
                ;
            }
        }
        return $return;
    }

    /** Process limit box in select
    * @return string expression to use in LIMIT, will be escaped
    */
    public function selectLimitProcess() {
        return (isset($_GET["limit"]) ? $_GET["limit"] : "50");
    }

    /** Process length box in select
    * @return string number of characters to shorten texts, will be escaped
    */
    public function selectLengthProcess() {
        return (isset($_GET["text_length"]) ? $_GET["text_length"] : "100");
    }

    /** Process extras in select form
    * @param array AND conditions
    * @param array
    * @return bool true if processed, false to process other parts of form
    */
    public function selectEmailProcess($where, $foreignKeys) {
        return false;
    }

    /** Print links after select heading
    * @param array result of SHOW TABLE STATUS
    * @param string new item options, NULL for no new item
    * @return null
    */
    public function selectLinks($tableStatus, $set = "") {
        global $jush, $driver;
        echo '<p class="links">';
        $links = array("select" => lang('Select data'));
        if (support("table") || support("indexes")) {
            $links["table"] = lang('Show structure');
        }
        if (support("table")) {
            if (is_view($tableStatus)) {
                $links["view"] = lang('Alter view');
            } else {
                $links["create"] = lang('Alter table');
            }
        }
        if ($set !== null) {
            $links["edit"] = lang('New item');
        }
        $name = $tableStatus["Name"];
        foreach ($links as $key => $val) {
            echo " <a href='" . h(ME) . "$key=" . urlencode($name) . ($key == "edit" ? $set : "") . "'" . bold(isset($_GET[$key])) . ">$val</a>";
        }
        echo doc_link(array($jush => $driver->tableHelp($name)), "?");
        echo "\n";
    }

    /**
     * Query printed after execution in the message
     * @param string executed query
     * @param string elapsed time
     * @param bool
     * @return string
     */
    public function messageQuery($query, $time, $failed = false)
    {
        global $jush, $driver;
        // restart_session();
        // $history = &get_session("queries");
        // if (!$history[$_GET["db"]]) {
        //     $history[$_GET["db"]] = array();
        // }
        if (strlen($query) > 1e6) {
            $query = preg_replace('~[\x80-\xFF]+$~', '', substr($query, 0, 1e6)) . "\n…"; // [\x80-\xFF] - valid UTF-8, \n - can end by one-line comment
        }
        return $query;
        // $history[$_GET["db"]][] = array($query, time(), $time); // not DB - $_GET["db"] is changed in database.inc.php //! respect $_GET["ns"]
        // $sql_id = "sql-" . count($history[$_GET["db"]]);
        // $return = "<a href='#$sql_id' class='toggle'>" . lang('SQL command') . "</a>\n";
        // if (!$failed && ($warnings = $driver->warnings())) {
        //     $id = "warnings-" . count($history[$_GET["db"]]);
        //     $return = "<a href='#$id' class='toggle'>" . lang('Warnings') . "</a>, $return<div id='$id' class='hidden'>\n$warnings</div>\n";
        // }
        // return " <span class='time'>" . @date("H:i:s") . "</span>" // @ - time zone may be not set
        //     . " $return<div id='$sql_id' class='hidden'><pre><code class='jush-$jush'>" . shorten_utf8($query, 1000) . "</code></pre>"
        //     . ($time ? " <span class='time'>($time)</span>" : '')
        //     . (support("sql") ? '<p><a href="' . h(str_replace("db=" . urlencode(DB), "db=" . urlencode($_GET["db"]), ME) . 'sql=&history=' . (count($history[$_GET["db"]]) - 1)) . '">' . lang('Edit') . '</a>' : '')
        //     . '</div>'
        // ;
    }

    /**
     * Execute query and redirect if successful
     * @param string
     * @param string
     * @param string
     * @param bool
     * @param bool
     * @param bool
     * @param string
     * @return bool
     */
    public function query_redirect($query, $location = null, $message = null,
        $redirect = false, $execute = true, $failed = false, $time = "")
    {
        global $connection, $error, $adminer;
        if ($execute) {
            $start = microtime(true);
            $failed = !$connection->query($query);
            $time = format_time($start);
        }
        $sql = "";
        if ($query) {
            $sql = $adminer->messageQuery($query, $time, $failed);
        }
        if ($failed) {
            $error = error() . $sql . script("messagesPrint();");
            return false;
        }
        // if ($redirect) {
        //     redirect($location, $message . $sql);
        // }
        return true;
    }

    /** Drop old object and create a new one
    * @param string drop old object query
    * @param string create new object query
    * @param string drop new object query
    * @param string create test object query
    * @param string drop test object query
    * @param string
    * @param string
    * @param string
    * @param string
    * @param string
    * @param string
    * @return null redirect in success
    */
    public function drop_create($drop, $create, $drop_created, $test, $drop_test, $location, $message_drop, $message_alter, $message_create, $old_name, $new_name) {
        if ($old_name == "") {
            query_redirect($drop, $location, $message_drop);
        } elseif ($old_name == "") {
            query_redirect($create, $location, $message_create);
        } elseif ($old_name != $new_name) {
            $created = queries($create);
            queries_redirect($location, $message_alter, $created && queries($drop));
            if ($created) {
                queries($drop_created);
            }
        } else {
            queries_redirect(
                $location,
                $message_alter,
                queries($test) && queries($drop_test) && queries($drop) && queries($create)
            );
        }
    }

    /** Drop old object and redirect
    * @param string drop old object query
    * @param string
    * @param string
    * @return null redirect in success
    */
    public function drop_only($drop, $location, $message_drop) {
        return query_redirect($drop, $location, $message_drop);
    }

    /** Find backward keys for table
    * @param string
    * @param string
    * @return array $return[$target_table]["keys"][$key_name][$target_column] = $source_column; $return[$target_table]["name"] = $this->tableName($target_table);
    */
    public function backwardKeys($table, $tableName) {
        return array();
    }

    /** Get descriptions of selected data
    * @param array all data to print
    * @param array
    * @return array
    */
    public function rowDescriptions($rows, $foreignKeys) {
        return $rows;
    }

    /** Print enum input field
    * @param string "radio"|"checkbox"
    * @param string
    * @param array
    * @param mixed int|string|array
    * @param string
    * @return null
    */
    function enum_input($type, $attrs, $field, $value, $empty = null) {
        global $adminer;
        preg_match_all("~'((?:[^']|'')*)'~", $field["length"], $matches);
        $return = ($empty !== null ? "<label><input type='$type'$attrs value='$empty'" . ((is_array($value) ? in_array($empty, $value) : $value === 0) ? " checked" : "") . "><i>" . lang('empty') . "</i></label>" : "");
        foreach ($matches[1] as $i => $val) {
            $val = stripcslashes(str_replace("''", "'", $val));
            $checked = (is_int($value) ? $value == $i+1 : (is_array($value) ? in_array($i+1, $value) : $value === $val));
            $return .= " <label><input type='$type'$attrs value='" . ($i+1) . "'" . ($checked ? ' checked' : '') . '>' . h($adminer->editVal($val, $field)) . '</label>';
        }
        return $return;
    }
}
