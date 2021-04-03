            <div class="row">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <?php echo $this->query ?>
                    </div>
<?php if(($this->message)): ?>
                    <div class="panel-body">
                        <?php echo $this->message ?>
                    </div>
<?php else: ?>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
<?php foreach($this->headers as $header): ?>
                                <th><?php echo $header ?></th>
<?php endforeach ?>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach($this->details as $details): ?>
                            <tr>
<?php foreach($details as $detail): ?>
                                <td><?php echo $detail ?></td>
<?php endforeach ?>
                            </tr>
<?php endforeach ?>
                        </tbody>
                    </table>
<?php endif ?>
                </div>
            </div>
