<div class="portlet-body form">
    <form id="<?php echo $this->formId ?>">
        <div class="form-group row">
            <div class="col-md-1 col-md-offset-9">
                <button type="button" class="btn btn-primary" id="<?php
                    echo $this->formId ?>-add" onclick="<?php echo $this->btnAdd ?>">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger" id="<?php
                    echo $this->formId ?>-del" onclick="<?php echo $this->btnDel ?>">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
<?php $i = 0; foreach($this->options['values'] as $value): ?>
        <div class="form-group row" id="<?php echo $this->formId ?>-item-<?php echo $i ?>">
            <div class="col-md-4">
                <select name="where[<?php echo $i ?>][col]" class="form-control">
                    <option value="" <?php if($value['col'] == ''): ?>selected<?php
                        endif ?>>(<?php echo \adminer\lang('anywhere') ?>)</option>
<?php foreach($this->options['columns'] as $column): ?>
                    <option <?php if($value['col'] == $column): ?>selected<?php
                        endif ?>><?php echo $column ?></option>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="where[<?php echo $i ?>][op]" class="form-control">
<?php foreach($this->options['operators'] as $operator): ?>
                    <option <?php if($value['op'] == $operator): ?>selected<?php
                        endif ?>><?php echo $operator ?></option>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-5">
                <input class="form-control" name="where[<?php
                    echo $i ?>][val]" value="<?php echo $value['val'] ?>" />
            </div>
            <div class="col-md-1" data-index="<?php echo $i ?>">
                <input type="checkbox" data-index="<?php echo $i ?>" class="<?php
                    echo $this->formId ?>-item-checkbox" />
            </div>
        </div>
<?php $i++; endforeach ?>
    </form>
    <!-- Empty line for new entry (must be outside the form) -->
    <div id="<?php echo $this->formId ?>-item-template" style="display:none">
        <div class="form-group row" id="<?php echo $this->formId ?>-item-__index__">
            <div class="col-md-4">
                <select name="where[__index__][col]" class="form-control">
                    <option value="" selected>(<?php echo \adminer\lang('anywhere') ?>)</option>
<?php foreach($this->options['columns'] as $column): ?>
                    <option><?php echo $column ?></option>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="where[__index__][op]" class="form-control">
<?php foreach($this->options['operators'] as $operator): ?>
                    <option><?php echo $operator ?></option>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-5">
                <input class="form-control" name="where[__index__][val]" value="" />
            </div>
            <div class="col-md-1" data-index="i">
                <input type="checkbox" data-index="__index__" class="<?php
                    echo $this->formId ?>-item-checkbox" />
            </div>
        </div>
    </div>
</div>
