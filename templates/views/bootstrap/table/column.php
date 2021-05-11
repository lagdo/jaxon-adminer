<?php if(isset($this->class)): ?>
        <div class="form-group <?php echo $this->class ?>" id="<?php
            echo sprintf('%s-%02d', $this->class, $this->index) ?>">
<?php endif ?>
            <div class="col-md-3 adminer-edit-column-left">
                <input name="fields[<?php echo $this->index ?>][field]" class="form-control" value="<?php
                    echo $this->field['field'] ?? '' ?>" data-maxlength="64" autocapitalize="off">
                <input type="hidden" name="fields[<?php
                    echo $this->index ?>][orig]" value="<?php echo $this->field['field'] ?? '' ?>">
            </div>
            <div class="col-md-2 adminer-edit-column-middle">
                <select name="fields[<?php echo $this->index ?>][type]" class="form-control">
<?php foreach($this->field['_types_'] as $group => $types): ?>
                    <optgroup label="<?php echo $group ?>">
<?php foreach($types as $type): ?>
                        <option <?php if($this->field['type'] === $type): ?>selected<?php
                            endif ?>><?php echo $type ?></option>
<?php endforeach ?>
                    </optgroup>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-1 adminer-edit-column-middle">
                <input name="fields[<?php echo $this->index ?>][length]" class="form-control<?php
                    if($this->field['_length_required_']): ?> required<?php endif ?>" value="<?php
                    echo $this->field['length'] ?>" size="3">
            </div>
            <div class="col-md-2 adminer-edit-column-middle">
                <select name="fields[<?php echo $this->index ?>][collation]" class="form-control<?php
                    if($this->field['_collation_hidden_']): ?> hidden<?php endif ?>">
                    <option value="">(<?php echo \adminer\lang('collation') ?>)</option>
<?php foreach($this->collations as $group => $collations): ?>
                    <optgroup label="<?php echo $group ?>">
<?php foreach($collations as $collation): ?>
                        <option <?php if($this->field['collation'] === $collation): ?>selected<?php
                            endif ?>><?php echo $collation ?></option>
<?php endforeach ?>
                    </optgroup>
<?php endforeach ?>
                </select>
<?php if($this->unsigned): ?>
                <select name="fields[<?php echo $this->index ?>][unsigned]" class="form-control<?php
                    if($this->field['_unsigned_hidden_']): ?> hidden<?php endif ?>">
                    <option value=""></option>
<?php foreach($this->unsigned as $option): ?>
                    <option <?php if($this->field['unsigned'] === $option): ?>selected<?php
                        endif ?>><?php echo $option ?></option>
<?php endforeach ?>
                </select>
<?php endif ?>
<?php if(isset($this->field['on_update'])): ?>
                <select name="fields[<?php echo $this->index ?>][on_update]" class="form-control<?php
                    if($this->field['_on_update_hidden_']): ?> hidden<?php endif ?>">
                    <option value="">(<?php echo \adminer\lang('ON UPDATE') ?>)</option>
<?php foreach($this->options['on_update'] as $group => $option): ?>
                    <option <?php if($this->field['on_update'] === $option): ?>selected<?php
                        endif ?>><?php echo $option ?></option>
<?php endforeach ?>
                </select>
<?php endif ?>
<?php if($this->foreign_keys): ?>
                <select name="fields[<?php echo $this->index ?>][on_delete]" class="form-control<?php
                    if($this->field['_on_delete_hidden_']): ?> hidden<?php endif ?>">
                    <option value="">(<?php echo \adminer\lang('ON DELETE') ?>)</option>
<?php foreach($this->options['on_delete'] as $option): ?>
                    <option <?php if($this->field['on_delete'] === $option): ?>selected<?php
                        endif ?>><?php echo $option ?></option>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-1 adminer-edit-column-null" style="padding-left:10px;padding-right:1px;">
                <input type="checkbox" name="fields[<?php echo $this->index ?>][null]" value="1" <?php
                    if($this->field['null']): ?>checked <?php endif ?>/>
            </div>
            <div class="col-md-1 adminer-edit-column-middle">
                <input type="radio" name="auto_increment_col" value="<?php echo $this->index ?>" <?php
                    if($this->field['auto_increment']): ?>checked <?php endif ?>/>
            </div>
            <div class="col-md-2 adminer-edit-column-right" data-name="<?php
                echo $this->field['field'] ?>" data-index="<?php echo $this->index ?>">
                <button type="button" class="btn btn-primary btn-xs adminer-table-column-add">
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-primary btn-xs adminer-table-column-up">
                    <span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-primary btn-xs adminer-table-column-down">
                    <span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-primary btn-xs adminer-table-column-del">
                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                </button>
            </div>
<?php endif ?>
<?php if(isset($this->class)): ?>
        </div>
<?php endif ?>
