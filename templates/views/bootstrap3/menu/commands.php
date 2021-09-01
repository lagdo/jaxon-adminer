<div class="btn-group" role="group" aria-label="...">
<?php foreach($this->sqlActions as $id => $title): ?>
    <button type="button" class="btn btn-default adminer-menu-item" id="adminer-menu-action-<?php echo $id ?>"><?php echo $title ?></button>
<?php endforeach ?>
</div>
