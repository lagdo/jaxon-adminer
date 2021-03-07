<?php
// Vars
$link = substr(preg_replace('~\b(username|db|ns)=[^&]*&~', '', ME), 0, -1);
$db_link = $link . "&db=" . urlencode(DB) . (support("scheme") ? "&ns=" : "");
$server = $adminer->serverName(SERVER);
$server = ($server != "" ? $server : lang('Server'));
// $breadcrumb // must be defined
// Pre process breadcrumbs
$_breadcrumbs = [];
if (is_array($breadcrumb)) {
    foreach ($breadcrumb as $key => $val) {
        $desc = is_array($val) ? $val[1] : h($val);
        $key_value = is_array($val) ? $val[0] : $val;
        if ($desc != "") {
            $_breadcrumbs[] = [
                'url' => h(ME . "$key=") . urlencode($key_value),
                'desc' => $desc,
            ];
        }
    }
}
?>

<?php if($breadcrumb !== null): ?>
        <p id="breadcrumb">
            <a href="<?php echo h($link ? $link : "."); ?>"><?php echo $drivers[DRIVER]; ?></a> &raquo;
    <?php if($breadcrumb === false): ?>
            <?php echo $server; ?>
    <?php else: ?>
            <a href="<?php echo h($link); ?>" accesskey="1" title="Alt+Shift+1"><?php echo $server; ?></a> &raquo;
    <?php endif ?>
    <?php if($_GET["ns"] != "" || (DB != "" && is_array($breadcrumb))): ?>
            <a href="<?php echo h($db_link); ?>"><?php echo h(DB); ?></a> &raquo;
    <?php endif ?>
    <?php if(is_array($breadcrumb)): ?>
        <?php if($_GET["ns"] != ""): ?>
            <a href="<?php echo h(substr(ME, 0, -1)); ?>"><?php echo h($_GET["ns"]); ?></a> &raquo;
        <?php endif ?>
        <?php foreach($_breadcrumbs as $_breadcrumb): ?>
            <a href="<?php echo $_breadcrumb['url']; ?>"><?php echo $_breadcrumb['desc']; ?></a> &raquo;
        <?php endforeach ?>
    <?php endif ?>
        </p>
<?php endif ?>
