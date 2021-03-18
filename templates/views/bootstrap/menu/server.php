            <div class="input-group">
                <select class="form-control" id="adminer-dbname-select">
                    <option value=""></option>
<?php foreach($this->databases as $database): ?>
                    <option value="<?php echo $database ?>"><?php echo $database ?></option>
<?php endforeach ?>
                </select>
                <span class="input-group-btn">
                    <button type="button" class="btn btn-primary" onclick="<?php echo $this->select ?>; return false">Connect</button>
                </span>
            </div>
