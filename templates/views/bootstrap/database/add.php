            <div class="portlet-body form">
                <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
                    <div class="form-group">
                        <label for="name" class="col-md-3 control-label">Name</label>
                        <div class="col-md-8">
                            <input type="text" name="name" class="form-control" placeholder="Name" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="collation" class="col-md-3 control-label">Collation</label>
                        <div class="col-md-8">
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
                    </div>
                </form>
            </div>
