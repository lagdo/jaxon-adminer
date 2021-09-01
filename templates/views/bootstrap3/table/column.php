<?php if(isset($this->class)): ?>
        <div class="form-group row <?php echo $this->class ?>" data-index="<?php
            echo $this->index ?>" id="<?php echo sprintf('%s-%02d', $this->class, $this->index) ?>">
<?php endif ?>
        <div class="col-md-12"><div class="row">
            <!-- Start first line -->
            <div class="col-md-3 adminer-table-column-left">
                <input class="form-control column-name" name="<?php
                    echo $this->prefixFields ?>[field]" placeholder="<?php
                    echo $this->trans->lang('Name') ?>" data-field="field" value="<?php
                    echo $this->field['field'] ?>" data-maxlength="64" autocapitalize="off" />
                <input type="hidden" name="<?php echo $this->prefixFields ?>[orig]" value="<?php
                    echo $this->field['field'] ?>" data-field="orig" />
            </div>
            <div class="col-md-2 adminer-table-column-middle">
                <select class="form-control" name="<?php
                    echo $this->prefixFields ?>[collation]" data-field="collation"<?php
                    if($this->field['_collation_hidden_']): ?> readonly<?php endif ?>>
                    <option value="">(<?php echo $this->trans->lang('collation') ?>)</option>
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
            <label class="col-md-1 adminer-table-column-null" for="auto_increment_col">
                <input type="radio" name="auto_increment_col" value="<?php echo ($this->index + 1) ?>" <?php
                    if($this->field['auto_increment']): ?>checked <?php endif ?>/> AI
            </label>
            <div class="col-md-2 adminer-table-column-middle">
<?php if(true/*isset($this->field['on_update'])*/): ?>
                <select class="form-control" name="<?php
                    echo $this->prefixFields ?>[on_update]" data-field="on_update"<?php
                    if($this->field['_on_update_hidden_']): ?> readonly<?php endif ?>>
                    <option value="">(<?php echo $this->trans->lang('ON UPDATE') ?>)</option>
<?php foreach($this->options['on_update'] as $group => $option): ?>
                    <option <?php if($this->field['on_update'] === $option): ?>selected<?php
                        endif ?>><?php echo $option ?></option>
<?php endforeach ?>
                </select>
<?php endif ?>
            </div>
            <div class="col-md-4 adminer-table-column-right">
                <input class="form-control" name="<?php
                    echo $this->prefixFields ?>[comment]" data-field="comment" value="<?php
                    echo $this->field['comment'] ?? '' ?>" placeholder="<?php
                    echo $this->trans->lang('Comment') ?>" />
            </div>
            <!-- End first line -->
            <!-- Start second line -->
            <div class="col-md-2 adminer-table-column-left second-line">
                <select class="form-control" name="<?php
                    echo $this->prefixFields ?>[type]" data-field="type">
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
                <input class="form-control" name="<?php
                    echo $this->prefixFields ?>[length]" placeholder="<?php
                    echo $this->trans->lang('Length') ?>" data-field="length"<?php
                    if($this->field['_length_required_']): ?> required<?php endif ?> value="<?php
                    echo $this->field['length'] ?>" size="3">
            </div>
            <div class="col-md-2 adminer-table-column-middle second-line">
                <select class="form-control" name="<?php
                    echo $this->prefixFields ?>[unsigned]" data-field="unsigned"<?php
                    if($this->field['_unsigned_hidden_']): ?> readonly<?php endif ?>>
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
                <input type="checkbox" value="1" name="<?php
                    echo $this->prefixFields ?>[null]" data-field="null" <?php
                    if($this->field['null']): ?>checked <?php endif ?>/> Null
            </label>
            <div class="col-md-2 adminer-table-column-middle second-line">
<?php if(true/*$this->foreignKeys*/): ?>
                <select class="form-control" name="<?php
                    echo $this->prefixFields ?>[on_delete]" data-field="on_delete"<?php
                    if($this->field['_on_delete_hidden_']): ?> readonly<?php endif ?>>
                    <option value="">(<?php echo $this->trans->lang('ON DELETE') ?>)</option>
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
                        <input type="checkbox" value="1" name="<?php
                            echo $this->prefixFields ?>[has_default]" data-field="has_default" <?php
                            if($this->field['has_default']): ?>checked <?php endif ?>/>
                    </span>
                    <input class="form-control" name="<?php
                        echo $this->prefixFields ?>[default]" data-field="default" value="<?php
                        echo $this->field['default'] ?? '' ?>" placeholder="<?php
                        echo $this->trans->lang('Default value') ?>">
                </div>
            </div>
            <div class="col-md-2 second-line">
                <div class="btn-group adminer-table-column-buttons" role="group" data-index="<?php echo $this->index ?>">
<?php if($this->support['move_col']): ?>
                    <button type="button" class="btn btn-primary btn-sm adminer-table-column-add">
                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                    </button>
                    <!-- <button type="button" class="btn btn-primary btn-sm adminer-table-column-up">
                        <span class="glyphicon glyphicon-arrow-up" aria-hidden="true"></span>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm adminer-table-column-down">
                        <span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span>
                    </button> -->
<?php endif ?>
<?php if($this->support['drop_col']): ?>
                    <button type="button" class="btn btn-primary btn-sm adminer-table-column-del">
                        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                    </button>
<?php endif ?>
                </div>
            </div>
            <!-- End second line -->
        </div></div>
<?php if(isset($this->class)): ?>
        </div>
<?php endif ?>
