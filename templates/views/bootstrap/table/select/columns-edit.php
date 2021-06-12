<div class="portlet-body form">
    <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
<?php $i = 0; foreach($this->options['values'] as $value): ?>
        <div class="form-group">
            <div class="col-md-7">
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
        </div>
<?php $i++; endforeach ?>
        <!-- Empty line for new entry -->
        <div class="form-group">
            <div class="col-md-7">
                <select name="columns[<?php echo $i ?>][fun]" class="form-control">
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
                <select name="columns[<?php echo $i ?>][col]" class="form-control">
<?php foreach($this->options['columns'] as $column): ?>
                    <option><?php echo $column ?></option>
<?php endforeach ?>
                </select>
            </div>
        </div>
    </form>
</div>
