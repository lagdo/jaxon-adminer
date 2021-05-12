    <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
        <div class="form-group adminer-edit-table-header" id="<?php echo $this->tableId ?>">
            <label class="col-md-2">Table</label>
        </div>
        <div class="form-group adminer-edit-table-header" id="<?php echo $this->tableId ?>">
            <div class="col-md-3 adminer-edit-table-name">
                <input type="text" name="name" class="form-control" placeholder="Name" />
            </div>
<?php if($this->engines): ?>
            <div class="col-md-3 adminer-edit-table-engine">
                <select name="engine" class="form-control">
                    <option value="">(engine)</option>
<?php foreach($this->engines as $group => $engine): ?>
                    <option><?php echo $engine ?></option>
<?php endforeach ?>
                </select>
            </div>
<?php endif ?>
<?php if($this->collations): ?>
            <div class="col-md-3 adminer-edit-table-collation">
                <select name="collation" class="form-control">
                    <option value="" selected>(collation)</option>
<?php foreach($this->collations as $group => $collations): ?>
                    <optgroup label="<?php echo $group ?>">
<?php foreach($collations as $collation): ?>
                        <option><?php echo $collation ?></option>
<?php endforeach ?>
                    </optgroup>
<?php endforeach ?>
                </select>
            </div>
<?php endif ?>
        </div>
        <div class="form-group adminer-table-column-header">
            <label class="col-md-2 adminer-table-column-left"><?php echo \adminer\lang('Column') ?></label>
            <label class="col-md-3 adminer-table-column-middle"><?php echo \adminer\lang('Length') ?></label>
            <label class="col-md-1 adminer-table-column-middle adminer-table-column-null" for="auto_increment_col">
                <input type="radio" name="auto_increment_col" value=""> AI
            </label>
            <label class="col-md-4 adminer-table-column-middle"><?php echo \adminer\lang('Options') ?></label>
            <div class="col-md-2 adminer-table-column-buttons">
<?php if($this->support['columns']): ?>
                <button type="button" class="btn btn-primary btn-xs" id="adminer-table-column-add">
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                </button>
<?php endif ?>
            </div>
        </div>
    </form>
