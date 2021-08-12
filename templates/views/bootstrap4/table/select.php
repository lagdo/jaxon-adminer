<div class="row">
    <div class="col-md-12">
        <form id="<?php echo $this->formId ?>">
            <div class="form-group row">
                <div class="col-md-6">
                    <div class="btn-group d-flex" role="group">
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-secondary" id="<?php
                                echo $this->btnColumnsId ?>"><?php echo \adminer\lang('Columns') ?></button>
                        </div>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-secondary" id="<?php
                                echo $this->btnFiltersId ?>"><?php echo \adminer\lang('Filters') ?></button>
                        </div>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-secondary" id="<?php
                                echo $this->btnSortingId ?>"><?php echo \adminer\lang('Order') ?></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><?php echo \adminer\lang('Limit') ?></span>
                        </div>
                        <input type="number" name="limit" class="form-control" value="<?php
                            echo $this->options['limit']['value'] ?>" />
                        <span class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="<?php echo $this->btnLimitId ?>">
                                <i class="bi bi-check"></i>
                            </button>
                        </span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><?php echo \adminer\lang('Text length') ?></span>
                        </div>
                        <input type="number" name="text_length" class="form-control" value="<?php
                            echo $this->options['length']['value'] ?>" />
                        <span class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="<?php echo $this->btnLengthId ?>">
                                <i class="bi bi-check"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group row" style="display:none">
                <div class="col-md-4" id="adminer-table-select-columns-show">
<?php echo $this->render('adminer::views::table/select/columns-show', [
    'options' => $this->options['columns'],
]) ?>
                </div>
                <div class="col-md-4" id="adminer-table-select-filters-show">
<?php echo $this->render('adminer::views::table/select/filters-show', [
    'options' => $this->options['filters'],
]) ?>
                </div>
                <div class="col-md-4" id="adminer-table-select-sorting-show">
<?php echo $this->render('adminer::views::table/select/sorting-show', [
    'options' => $this->options['sorting'],
]) ?>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-9">
                    <pre id="<?php echo $this->txtQueryId ?>"><?php echo $this->query ?></pre>
                </div>
                <div class="col-md-3">
                    <div class="btn-group d-flex" role="group">
                        <button type="button" class="btn btn-outline-secondary w-100" id="<?php
                            echo $this->btnEditId ?>"><?php echo \adminer\lang('Edit') ?></button>
                        <button type="button" class="btn btn-primary w-100" id="<?php
                            echo $this->btnExecId ?>"><?php echo \adminer\lang('Execute') ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="row">
    <div class="col-md-12" id="adminer-table-select-pagination">
    </div>
    <div class="col-md-12" id="adminer-table-select-results">
    </div>
</div>
