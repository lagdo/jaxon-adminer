                <div class="input-group mb-3">
                    <select class="form-control" id="adminer-dbname-select">
                        <option value=""></option>
<?php foreach($this->databases as $database): ?>
                        <option value="<?php echo $database ?>"><?php echo $database ?></option>
<?php endforeach ?>
                    </select>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary btn-select" id="adminer-dbname-select-btn">Show</button>
                    </div>
                </div>
