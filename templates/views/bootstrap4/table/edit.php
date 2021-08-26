    <form id="<?php echo $this->formId ?>">
        <div class="form-group row adminer-edit-table-header">
            <label class="col-md-2">Table</label>
        </div>
        <div class="form-group row adminer-edit-table-header">
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
<?php if($this->support['comment']): ?>
            <div class="col-md-4 adminer-table-column-middle">
                <input name="comment" class="form-control" value="<?php
                    echo $this->table['Comment'] ?? '' ?>" placeholder="<?php
                    echo $this->trans->lang('Comment') ?>" />
            </div>
<?php endif ?>
        </div>
        <div class="form-group row adminer-table-column-header">
            <label class="col-md-5 adminer-table-column-left"><?php echo $this->trans->lang('Column') ?></label>
            <label class="col-md-1 adminer-table-column-null-header" for="auto_increment_col">
                <input type="radio" name="auto_increment_col" value="" <?php
                    if(!$this->options['has_auto_increment']): ?>checked <?php endif ?>/> AI
            </label>
            <label class="col-md-4 adminer-table-column-middle"><?php echo $this->trans->lang('Options') ?></label>
            <div class="col-md-2 adminer-table-column-buttons-header">
<?php if($this->support['columns']): ?>
                <button type="button" class="btn btn-primary btn-sm" id="adminer-table-column-add">
                    <i class="bi bi-plus"></i>
                </button>
<?php endif ?>
            </div>
        </div>
<?php foreach($this->fields as $index => $field): ?>
<?php echo $this->render('adminer::views::table/column', [
    'trans' => $this->trans,
    'class' => $this->formId . '-column',
    'index' => $index,
    'field' => $field,
    'prefixFields' => sprintf("fields[%d]", $index + 1),
    'collations' => $this->collations,
    'unsigned' => $this->unsigned,
    'foreign_keys' => $this->foreign_keys,
    'options' => $this->options,
    'support' => $this->support,
]) ?>
<?php endforeach ?>
    </form>
