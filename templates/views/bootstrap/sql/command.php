<div class="col-md-12" id="adminer-command-details">
</div>
<div class="col-md-12">
    <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
        <div class="form-group">
            <textarea name="query" class="form-control" id="<?php echo
                $this->queryId ?>" rows="10" spellcheck="false" wrap="off"></textarea>
        </div>
        <div class="form-group">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-addon"><?php echo $this->labels['limit_rows'] ?></span>
                    <input type="number" name="limit" class="form-control" value="<?php echo $this->defaultLimit ?>" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="checkbox">
                    <label><input type="checkbox" name="error_stops" /><?php
                        echo $this->labels['error_stops'] ?></label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="checkbox">
                    <label><input type="checkbox" name="only_errors" /><?php
                        echo $this->labels['only_errors'] ?></label>
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
