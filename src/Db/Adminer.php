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
     * The constructor
     *
     * @param string $vendor
     * @param array $credentials
     */
    public function __construct(array $credentials, $vendor)
    {
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
    public function escape_string($val)
    {
        return substr($this->server->q($val), 1, -1);
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
        switch (strtolower(substr($val, -1))) {
            case 'g': $val *= 1024; // no break
            case 'm': $val *= 1024; // no break
            case 'k': $val *= 1024;
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
}
