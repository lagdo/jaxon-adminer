<div class="col-md-12" id="adminer-command-details">
</div>
<div class="col-md-12">
    <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
        <div class="form-group">
            <textarea name="query" class="form-control" id="<?php echo
                $this->queryId ?>" rows="10" spellcheck="false" wrap="off"></textarea>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label">Limit rows: </label>
            <div class="col-md-2">
                <input type="number" name="limit" class="form-control" value="0" />
            </div>
            <div class="col-md-3">
                <div class="checkbox">
                    <label><input type="checkbox" name="error_stops" />Stop on error</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="checkbox">
                    <label><input type="checkbox" name="only_errors" />Show only errors</label>
                </div>
            </div>
            <div class="col-md-2">
                <a class="btn btn-primary btn-block" id="<?php
                    echo $this->btnId ?>" href="javascript:void(0)">Execute</a>
            </div>
        </div>
    </form>
</div>
<div class="col-md-12" id="adminer-command-history">
</div>
<div class="col-md-12" id="adminer-command-results">
</div>
