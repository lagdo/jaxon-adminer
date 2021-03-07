<?php
// Vars
global $adminer, $VERSION, $jush, $drivers, $connection;

$databases = $adminer->databases();
if (DB && $databases && !in_array(DB, $databases)) {
    array_unshift($databases, DB);
}
// TODO: Move this to a class
if ($_GET["ns"] != "") {
    set_schema($_GET["ns"]);
}

$support_sql = support("sql");
$server_info = $connection->server_info;
$_version = is_object($connection) ? preg_replace('~^(\d\.?\d).*~s', '\1', $server_info) : "";
$_bodyLoadParams = ["'$_version'"];
if(preg_match('~MariaDB~', $server_info))
{
    $_bodyLoadParams[] = "true";
}

$tables = [];
$links = [];
if($_GET["ns"] !== "" && !$missing && DB != "")
{
    $connection->select_db(DB);
    $tables = table_status('', true);
}

$_jushLinks = false;
if($support_sql)
{
    if($tables)
    {
        foreach($tables as $table => $type)
        {
            $links[] = preg_quote($table, '/');
        }
        $_jushLinks = js_escape(ME) . (support("table") ? "table=" : "select=") .
            "\$&', /\\b(" . implode("|", $links) . ")\\b/g";
    }
}

$db_events = script("mixin(qsl('select'), {onmousedown: dbMouseDown, onchange: dbChange});");

// TODO: Move these includes to the page header
echo script_src("../externals/jush/modules/jush.js");
echo script_src("../externals/jush/modules/jush-textarea.js");
echo script_src("../externals/jush/modules/jush-txt.js");
echo script_src("../externals/jush/modules/jush-js.js");

if($support_sql)
{
    echo script_src("../externals/jush/modules/jush-$jush.js");
}
?>

<?php if($support_sql): ?>
    <script <?php echo nonce(); ?>>
    <?php if($_jushLinks): ?>
        var jushLinks = { <?php echo $jush; ?>: [ <?php echo $_jushLinks; ?> ] };
    <?php foreach(["bac", "bra", "sqlite_quo", "mssql_bra"] as $val): ?>
        jushLinks.<?php echo $val; ?> = jushLinks.<?php echo $jush; ?>;
    <?php endforeach ?>
    <?php endif ?>
    bodyLoad(implode(", ", $_bodyLoadParams));
    </script>
<?php endif ?>

<form action="">
    <?php include('../form/hidden_fields.php'); ?>
    <p id="dbs">
        <span title="<?php echo lang('database'); ?>"><?php echo lang('DB'); ?></span>:
<?php if($databases): ?>
        <select name='db'>
            <?php echo optionlist(["" => ""] + $databases, DB); ?>
        </select>
        <?php echo $db_events; ?>
        <input type="submit" value="<?php echo lang('Use'); ?>" class="hidden">
<?php else: ?>
        <input name="db" value="<?php echo h(DB); ?>" autocapitalize="off">
        <input type="submit" value="<?php echo lang('Use'); ?>">
<?php endif ?>
<?php if($missing != "db" && DB != "" && $connection->select_db(DB) && support("scheme")): ?>
        <br/><?php echo lang('Schema'); ?>
        <select name='ns'>
            <?php echo optionlist(["" => ""] + $adminer->schemas(), $_GET["ns"]); ?>
        </select>
        <?php echo $db_events; ?>
<?php endif ?>
<?php foreach(["import", "sql", "schema", "dump", "privileges"] as $val): ?>
    <?php if(isset($_GET[$val])): ?>
        <input type="hidden" name="<?php echo $val; ?>" value="">
        <?php break; ?>
    <?php endif ?>
<?php endforeach ?>
    </p>
</form>

<p class="links">
<?php if(DB == "" || !$missing): ?>
    <?php if(support("sql")): ?>
    <a href="<?php echo h(ME); ?>"><?php echo lang('SQL command'); ?></a>
    <a href="<?php echo h(ME); ?>"><?php echo lang('Import'); ?></a>
    <?php endif ?>
    <?php if(support("dump")): ?>
    <a href="<?php echo h(ME); ?>"><?php echo lang('Export'); ?></a>
    <?php endif ?>
    <?php if($_GET["ns"] !== "" && !$missing && DB != ""): ?>
    <a href="<?php echo h(ME); ?>"><?php echo lang('Create table'); ?></a>
    <?php endif ?>
<?php endif ?>
</p>

<?php if($_GET["ns"] !== "" && !$missing && DB != ""): ?>
    <?php if(!$tables): ?>
<p class="message"><?php echo lang('No tables.'); ?></p>
    <?php else: ?>
<ul id="tables">
    <?php echo script("mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});"); ?>
<?php foreach($tables as $table => $status): ?>
<?php $name = $adminer->tableName($status); ?>
<?php if($name != ""): ?>
    <li><a href="<?php echo h(ME); ?>"><?php echo lang('select'); ?></a></li>
    <?php if(support("table") || support("indexes")): ?>
        <a href="<?php echo h(ME); ?>" title="<?php echo lang('Show structure'); ?>"><?php echo $name; ?></a>
    <?php else: ?>
        <span><?php echo $name; ?></span>
    <?php endif ?>
<?php endif ?>
<?php endforeach ?>
</ul>
    <?php endif ?>
<?php endif ?>
