<?php
// Hidden fields for forms.
// Previously returned by the hidden_fields_get() function.
?>
<?php if(sid()): ?>
    <input type="hidden" name="<?php echo session_name(); ?>" value="<?php echo h(session_id()); ?>">
<?php endif ?>
<?php if(SERVER !== null): ?>
    <input type="hidden" name="<?php echo DRIVER; ?>" value="<?php echo h(SERVER); ?>">
<?php endif ?>
    <input type="hidden" name="username" value="<?php echo h($_GET["username"]); ?>">
