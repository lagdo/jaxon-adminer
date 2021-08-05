<div class="col-md-12" id="adminer-command-details">
</div>
<div class="col-md-12">
    <form id="<?php echo $this->formId ?>">
        <div class="form-group row">
            <textarea name="query" class="form-control" id="<?php echo
                $this->queryId ?>" rows="10" spellcheck="false" wrap="on"><?php echo
                $this->query ?></textarea>
        </div>
        <div class="form-group row">
            <div class="col-md-3">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><?php echo $this->labels['limit_rows'] ?></span>
                    </div>
                    <input type="number" name="limit" class="form-control" value="<?php echo $this->defaultLimit ?>" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <div class="input-group-text"><input type="checkbox" name="error_stops" /></div>
                    </div>
                    <input type="text" class="form-control" placeholder="<?php echo $this->labels['error_stops'] ?>" readonly />
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <div class="input-group-text"><input type="checkbox" name="only_errors" /></div>
                    </div>
                    <input type="text" class="form-control" placeholder="<?php echo $this->labels['only_errors'] ?>" readonly />
                </div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-block" type="button" id="<?php
                    echo $this->btnId ?>" href="javascript:void(0)"><?php
                    echo $this->labels['execute'] ?></button>
            </div>
        </div>
    </form>
</div>
<div class="col-md-12" id="adminer-command-history">
</div>
<div class="col-md-12" id="adminer-command-results">
</div>
