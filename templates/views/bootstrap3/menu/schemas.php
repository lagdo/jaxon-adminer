<?php if(isset($this->schemas) && ($this->schemas)): ?>
                <select class="form-control" id="adminer-schema-select">
<?php foreach($this->schemas as $schema): ?>
                    <option value="<?php echo $schema ?>"><?php echo $schema ?></option>
<?php endforeach ?>
                </select>
<?php endif ?>
