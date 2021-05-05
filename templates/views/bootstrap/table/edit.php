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
<?php foreach($this->fields as $i => $field): ?>
        <div class="form-group">
            <div class="col-md-3 adminer-edit-column-left">
                <input name="fields[1][field]" class="form-control" value="<?php
                    echo $field['field'] ?>" data-maxlength="64" autocapitalize="off">
                <input type="hidden" name="fields[1][orig]" value="<?php echo $field['field'] ?>">
            </div>
            <div class="col-md-2 adminer-edit-column-middle">
                <select name="fields[1][type]" class="form-control">
<?php foreach($field['_types_'] as $group => $types): ?>
                    <optgroup label="<?php echo $group ?>">
<?php foreach($types as $type): ?>
                        <option <?php if($field['type'] === $type): ?>selected<?php endif ?>><?php echo $type ?></option>
<?php endforeach ?>
                    </optgroup>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-1 adminer-edit-column-middle">
                <input name="fields[1][length]" class="form-control<?php if($field['_length_required_']):
                    ?> required<?php endif ?>" value="<?php echo $field['length'] ?>" size="3">
            </div>
            <div class="col-md-2 adminer-edit-column-middle">
                <select name="fields[1][collation]" class="form-control<?php
                    if($field['_collation_hidden_']): ?> hidden<?php endif ?>">
                    <option value="">(<?php echo \adminer\lang('collation') ?>)</option>
<?php foreach($this->collations as $group => $collations): ?>
                    <optgroup label="<?php echo $group ?>">
<?php foreach($collations as $collation): ?>
                        <option <?php if($field['collation'] === $collation): ?>selected<?php
                            endif ?>><?php echo $collation ?></option>
<?php endforeach ?>
                    </optgroup>
<?php endforeach ?>
                </select>
<?php if($this->unsigned): ?>
                <select name="fields[1][unsigned]" class="form-control<?php
                    if($field['_unsigned_hidden_']): ?> hidden<?php endif ?>">
                    <option value=""></option>
<?php foreach($this->unsigned as $option): ?>
                    <option <?php if($field['unsigned'] === $option): ?>selected<?php
                        endif ?>><?php echo $option ?></option>
<?php endforeach ?>
                </select>
<?php endif ?>
<?php if(isset($field['on_update'])): ?>
                <select name="fields[1][on_update]" class="form-control<?php
                    if($field['_on_update_hidden_']): ?> hidden<?php endif ?>">
                    <option value="">(<?php echo \adminer\lang('ON UPDATE') ?>)</option>
<?php foreach($this->options['on_update'] as $group => $option): ?>
                    <option <?php if($field['on_update'] === $option): ?>selected<?php
                        endif ?>><?php echo $option ?></option>
<?php endforeach ?>
                </select>
<?php endif ?>
<?php if($this->foreign_keys): ?>
                <select name="fields[1][on_delete]" class="form-control<?php
                    if($field['_on_delete_hidden_']): ?> hidden<?php endif ?>">
                    <option value="">(<?php echo \adminer\lang('ON DELETE') ?>)</option>
<?php foreach($this->options['on_delete'] as $option): ?>
                    <option <?php if($field['on_delete'] === $option): ?>selected<?php
                        endif ?>><?php echo $option ?></option>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-1 adminer-edit-column-null" style="padding-left:10px;padding-right:1px;">
                <input type="checkbox" name="fields[6][null]" value="1" <?php
                    if($field['null']): ?>checked <?php endif ?>/>
            </div>
            <div class="col-md-1 adminer-edit-column-middle">
                <input type="radio" name="auto_increment_col" value="6" <?php
                    if($field['auto_increment']): ?>checked <?php endif ?>/>
            </div>
            <div class="col-md-2 adminer-edit-column-right">
                <button type="button" class="btn btn-primary btn-xs" id="adminer-table-add-column">
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-primary btn-xs" id="adminer-table-column-up">
                    <span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-primary btn-xs" id="adminer-table-column-down">
                    <span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-primary btn-xs" id="adminer-table-del-column">
                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                </button>
            </div>
<?php endif ?>
        </div>
<?php endforeach ?>
    </form>
