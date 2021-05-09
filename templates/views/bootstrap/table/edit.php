    <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
        <div class="form-group adminer-edit-table-header" id="<?php echo $this->tableId ?>">
            <label class="col-md-2">Table</label>
        </div>
        <div class="form-group">
            <div class="col-md-3 adminer-edit-table-name">
                <input type="text" name="name" class="form-control" value="<?php
                    echo $this->table['Name'] ?>" placeholder="Name" />
            </div>
<?php if($this->engines): ?>
            <div class="col-md-2 adminer-edit-table-engine">
                <select name="engine" class="form-control">
                    <option value="">(engine)</option>
<?php foreach($this->engines as $group => $engine): ?>
                    <option <?php if(!strcasecmp($this->table['Engine'], $engine)): ?>selected<?php
                        endif ?>><?php echo $engine ?></option>
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
                        <option <?php if($this->table['Collation'] === $collation): ?>selected<?php
                            endif ?>><?php echo $collation ?></option>
<?php endforeach ?>
                    </optgroup>
<?php endforeach ?>
                </select>
            </div>
<?php endif ?>
        </div>
        <div class="form-group adminer-edit-column-header">
            <label class="col-md-3 adminer-edit-column-left"><?php echo \adminer\lang('Column') ?></label>
            <label class="col-md-2 adminer-edit-column-middle"><?php echo \adminer\lang('Type') ?></label>
            <label class="col-md-1 adminer-edit-column-middle"><?php echo \adminer\lang('Length') ?></label>
            <label class="col-md-2 adminer-edit-column-middle"><?php echo \adminer\lang('Options') ?></label>
            <label class="col-md-1 adminer-edit-column-null">NULL</label>
            <label class="col-md-1 adminer-edit-column-middle">
                <input type="radio" name="auto_increment_col" value=""> AI
            </label>
            <div class="col-md-2 adminer-edit-column-right">
<?php if($this->support['columns']): ?>
                <button type="button" class="btn btn-primary btn-xs" id="adminer-table-add-column">
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                </button>
<?php endif ?>
            </div>
        </div>
<?php foreach($this->fields as $index => $field): ?>
<?php echo $this->render('adminer::views::table/field', [
    'class' => $this->formId . '-column',
    'index' => $index,
    'field' => $field,
    'collations' => $this->collations,
    'unsigned' => $this->unsigned,
    'foreign_keys' => $this->foreign_keys,
    'options' => $this->options,
]) ?>
<?php endforeach ?>
    </form>
