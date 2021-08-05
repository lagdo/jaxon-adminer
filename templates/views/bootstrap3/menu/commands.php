<div class="btn-group" role="group" aria-label="...">
<?php foreach($this->sql_actions as $id => $title): ?>
    <button type="button" class="btn btn-default" id="adminer-menu-action-<?php echo $id ?>"><?php echo $title ?></button>
<?php endforeach ?>
</div>
