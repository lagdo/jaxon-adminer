    <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
        <div class="form-group" id="<?php echo $this->tableId ?>">
            <label class="col-md-2">Table</label>
            <div class="col-md-4">
                <input type="text" name="name" class="form-control" placeholder="Name" />
            </div>
<?php if($this->engines): ?>
            <div class="col-md-3" style="padding-left:0">
                <select name="engine" class="form-control">
                    <option value="">(engine)</option>
<?php foreach($this->engines as $group => $engine): ?>
                    <option><?php echo $engine ?></option>
<?php endforeach ?>
                </select>
            </div>
<?php endif ?>
<?php if($this->collations): ?>
            <div class="col-md-3" style="padding-left:0">
                <select name="collation" class="form-control">
                    <option value="" selected>(collation)</option>
<?php foreach($this->collations as $group => $collations): ?>
                    <optgroup label="<?php echo $group ?>">
<?php foreach($collations as $collation): ?>
                        <option><?php echo $collation ?></option>
<?php endforeach ?>
                    </optgroup>
<?php endforeach ?>
                </select>
            </div>
<?php endif ?>
        </div>
        <div class="form-group">
            <label class="col-md-2">&nbsp;</label>
            <div class="col-md-2">
                <button type="button" class="btn btn-default btn-block" id="adminer-table-meta-cancel">Cancel</button>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-primary btn-block" id="adminer-table-add-column">Add column</button>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col-md-12"><label>Columns</label></div>
    </div>
