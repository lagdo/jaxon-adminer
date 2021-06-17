<div class="portlet-body form">
    <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
        <div class="form-group">
            <div class="col-md-1 col-md-offset-9">
                <button type="button" class="btn btn-primary" id="<?php
                    echo $this->formId ?>-add" onclick="<?php echo $this->btnAdd ?>">
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                </button>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger" id="<?php
                    echo $this->formId ?>-del" onclick="<?php echo $this->btnDel ?>">
                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                </button>
            </div>
        </div>
<?php $i = 0; foreach($this->options['values'] as $value): ?>
        <div class="form-group" id="<?php echo $this->formId ?>-item-<?php echo $i ?>">
            <div class="col-md-6">
                <select name="columns[<?php echo $i ?>][fun]" class="form-control">
                    <option value="" <?php if($value['fun'] == ''): ?>selected<?php endif ?>></option>
                    <optgroup label="<?php echo \adminer\lang('Functions') ?>">
<?php foreach($this->options['functions'] as $function): ?>
                        <option <?php if($value['fun'] == $function): ?>selected<?php
                            endif ?>><?php echo $function ?></option>
<?php endforeach ?>
                    </optgroup>
                    <optgroup label="<?php echo \adminer\lang('Aggregation') ?>">
<?php foreach($this->options['grouping'] as $grouping): ?>
                        <option <?php if($value['fun'] == $grouping): ?>selected<?php
                            endif ?>><?php echo $grouping ?></option>
<?php endforeach ?>
                    </optgroup>
                </select>
            </div>
            <div class="col-md-5">
                <select name="columns[<?php echo $i ?>][col]" class="form-control">
<?php foreach($this->options['columns'] as $column): ?>
                    <option <?php if($value['col'] == $column): ?>selected<?php
                            endif ?>><?php echo $column ?></option>
<?php endforeach ?>
                </select>
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
        <div class="form-group" id="<?php echo $this->formId ?>-item-__index__">
            <div class="col-md-6">
                <select name="columns[__index__][fun]" class="form-control">
                    <option value="" selected></option>
                    <optgroup label="<?php echo \adminer\lang('Functions') ?>">
<?php foreach($this->options['functions'] as $function): ?>
                        <option><?php echo $function ?></option>
<?php endforeach ?>
                    </optgroup>
                    <optgroup label="<?php echo \adminer\lang('Aggregation') ?>">
<?php foreach($this->options['grouping'] as $grouping): ?>
                        <option><?php echo $grouping ?></option>
<?php endforeach ?>
                    </optgroup>
                </select>
            </div>
            <div class="col-md-5">
                <select name="columns[__index__][col]" class="form-control">
<?php foreach($this->options['columns'] as $column): ?>
                    <option><?php echo $column ?></option>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-1" data-index="i">
                <input type="checkbox" data-index="__index__" class="<?php
                    echo $this->formId ?>-item-checkbox" />
            </div>
        </div>
    </div>
</div>
