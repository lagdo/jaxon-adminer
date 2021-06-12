<div class="portlet-body form">
    <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
<?php $i = 0; foreach($this->options['values'] as $value): ?>
        <div class="form-group">
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
            <div class="col-md-6">
                <input class="form-control" name="where[<?php
                    echo $i ?>][val]" value="<?php echo $value['val'] ?>" />
            </div>
        </div>
<?php $i++; endforeach ?>
        <!-- Empty line for new entry -->
        <div class="form-group">
            <div class="col-md-4">
                <select name="where[<?php echo $i ?>][col]" class="form-control">
                    <option value="" selected>(<?php echo \adminer\lang('anywhere') ?>)</option>
<?php foreach($this->options['columns'] as $column): ?>
                    <option><?php echo $column ?></option>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="where[<?php echo $i ?>][op]" class="form-control">
<?php foreach($this->options['operators'] as $operator): ?>
                    <option><?php echo $operator ?></option>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-6">
                <input class="form-control" name="where[<?php echo $i ?>][val]" value="" />
            </div>
        </div>
    </form>
</div>
