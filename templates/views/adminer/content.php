<?php
// Vars
$databases = &get_session("dbs");
if (DB != "" && $databases && !in_array(DB, $databases, true)) {
    $databases = null;
}
?>

<div id="help" class="jush-<?php echo $jush; ?> jsonly hidden"></div>
<?php echo script("mixin(qs('#help'), {onmouseover: function () { helpOpen = 1; }, onmouseout: helpMouseout});"); ?>

<div id="content">

<?php include('./breadcrumbs.php'); ?>

    <h2><?php echo $title_all; ?></h2>
    <div id="ajaxstatus" class="jsonly hidden"></div>

<?php  /* restart_session(); */ ?>
<?php include('./messages.php'); ?>
<?php /* stop_session(); */ define("PAGE_HEADER", 1); ?>
</div>

<div id="menu">
<?php include('./nav/menu.php'); ?>
</div>
<?php echo script("setupSubmitHighlight(document);"); ?>
