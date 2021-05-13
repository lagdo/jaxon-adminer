<?php if(isset($this->class)): ?>
        <div class="form-group <?php echo $this->class ?>" id="<?php
            echo sprintf('%s-%02d', $this->class, $this->index) ?>">
<?php endif ?>
            <!-- Start first line -->
            <div class="col-md-3 adminer-table-column-left">
                <input name="<?php echo $this->prefixFields ?>[field]" class="form-control" value="<?php
                    echo $this->field['field'] ?? '' ?>" data-maxlength="64" autocapitalize="off">
                <input type="hidden" name="<?php echo $this->prefixFields ?>[orig]" value="<?php
                    echo $this->field['field'] ?? '' ?>">
            </div>
            <div class="col-md-2 adminer-table-column-middle">
                <select name="<?php echo $this->prefixFields ?>[collation]" class="form-control<?php
                    if($this->field['_collation_hidden_']): ?> readonly<?php endif ?>">
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
            </div>
            <label class="col-md-1 adminer-table-column-middle adminer-table-column-null" for="auto_increment_col">
                <input type="radio" name="auto_increment_col" value="<?php echo ($this->index + 1) ?>" <?php
                    if($this->field['auto_increment']): ?>checked <?php endif ?>/> AI
            </label>
            <div class="col-md-2 adminer-table-column-middle">
<?php if(true/*isset($this->field['on_update'])*/): ?>
                <select name="<?php echo $this->prefixFields ?>[on_update]" class="form-control<?php
                    if($this->field['_on_update_hidden_']): ?> readonly<?php endif ?>">
                    <option value="">(<?php echo \adminer\lang('ON UPDATE') ?>)</option>
<?php foreach($this->options['on_update'] as $group => $option): ?>
                    <option <?php if($this->field['on_update'] === $option): ?>selected<?php
                        endif ?>><?php echo $option ?></option>
<?php endforeach ?>
                </select>
<?php endif ?>
            </div>
            <div class="col-md-4 adminer-table-column-right">
                <input name="<?php echo $this->prefixFields ?>[comment]" class="form-control" value="<?php
                    echo $this->field['comment'] ?? '' ?>" placeholder="<?php echo \adminer\lang('Comment') ?>" />
            </div>
            <!-- End first line -->
            <!-- Start second line -->
            <div class="col-md-2 adminer-table-column-left second-line">
                <select name="<?php echo $this->prefixFields ?>[type]" class="form-control">
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
            <div class="col-md-1 adminer-table-column-middle second-line">
                <input name="<?php echo $this->prefixFields ?>[length]" class="form-control<?php
                    if($this->field['_length_required_']): ?> required<?php endif ?>" value="<?php
                    echo $this->field['length'] ?>" size="3">
            </div>
            <div class="col-md-2 adminer-table-column-middle second-line">
                <select name="<?php echo $this->prefixFields ?>[unsigned]" class="form-control<?php
                    if($this->field['_unsigned_hidden_']): ?> readonly<?php endif ?>">
                    <option value=""></option>
<?php if($this->unsigned): ?>
<?php foreach($this->unsigned as $option): ?>
                    <option <?php if($this->field['unsigned'] === $option): ?>selected<?php
                        endif ?>><?php echo $option ?></option>
<?php endforeach ?>
<?php endif ?>
                </select>
            </div>
            <label class="col-md-1 adminer-table-column-null second-line">
                <input type="checkbox" name="<?php echo $this->prefixFields ?>[null]" value="1" <?php
                    if($this->field['null']): ?>checked <?php endif ?>/> Null
            </label>
            <div class="col-md-2 adminer-table-column-middle second-line">
<?php if(true/*$this->foreign_keys*/): ?>
                <select name="<?php echo $this->prefixFields ?>[on_delete]" class="form-control<?php
                    if($this->field['_on_delete_hidden_']): ?> readonly<?php endif ?>">
                    <option value="">(<?php echo \adminer\lang('ON DELETE') ?>)</option>
<?php foreach($this->options['on_delete'] as $option): ?>
                    <option <?php if($this->field['on_delete'] === $option): ?>selected<?php
                        endif ?>><?php echo $option ?></option>
<?php endforeach ?>
                </select>
<?php endif ?>
            </div>
            <div class="col-md-2 adminer-table-column-default second-line">
                <div class="input-group">
                    <span class="input-group-addon">
                        <input type="checkbox" name="<?php echo $this->prefixFields ?>[has_default]" value="1" <?php
                            if($this->field['has_default']): ?>checked <?php endif ?>/>
                    </span>
                    <input name="<?php echo $this->prefixFields ?>[default]" class="form-control" value="<?php
                        echo $this->field['default'] ?? '' ?>" placeholder="<?php echo \adminer\lang('Default value') ?>">
                </div>
            </div>
            <div class="col-md-2 adminer-table-column-buttons second-line" data-index="<?php
                echo $this->index ?>">
<?php if($this->support['move_col']): ?>
                <button type="button" class="btn btn-primary btn-xs adminer-table-column-add">
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-primary btn-xs adminer-table-column-up">
                    <span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-primary btn-xs adminer-table-column-down">
                    <span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span>
                </button>
<?php endif ?>
<?php if($this->support['drop_col']): ?>
                <button type="button" class="btn btn-primary btn-xs adminer-table-column-del">
                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                </button>
<?php endif ?>
            </div>
            <!-- End second line -->
<?php if(isset($this->class)): ?>
        </div>
<?php endif ?>
