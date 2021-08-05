            <div class="portlet-body form">
                <form id="<?php echo $this->formId ?>">
                    <div class="form-group">
                        <label class="col-form-label" for="name">Name</label>
                        <input type="text" name="name" class="form-control" value="<?php
                            echo $this->view['name'] ?>" placeholder="Name" />
                    </div>
                    <div class="form-group">
                        <label class="col-form-label" for="select">SQL query</label>
                        <textarea name="select" class="form-control" rows="10" spellcheck="false" wrap="on"><?php
                            echo $this->view['select'] ?></textarea>
                    </div>
<?php if($this->materializedview): ?>
                    <div class="form-group">
                        <label class="col-form-label" for="materialized">Materialized</label>
                        <input type="checkbox" name="materialized" <?php
                            if($this->view['materialized']): ?>checked <?php endif ?>/>
                    </div>
<?php endif ?>
                </form>
            </div>
