<div class="btn-group d-flex" role="group">
<?php foreach($this->sqlActions as $id => $title): ?>
    <button type="button" class="btn btn-outline-secondary w-100 adminer-menu-item" id="adminer-menu-action-<?php echo $id ?>"><?php echo $title ?></button>
<?php endforeach ?>
</div>
