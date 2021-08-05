<?php foreach($this->results as $result): ?>
            <div class="row">
<?php if(count($result['errors']) > 0): ?>
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <?php echo $result['query'] ?>
                    </div>
                    <div class="panel-body" style="padding:5px 15px">
<?php foreach($result['errors'] as $error): ?>
                        <p style="margin:0"><?php echo $error ?></p>
<?php endforeach ?>
                    </div>
                </div>
<?php endif ?>
<?php if(count($result['messages']) > 0): ?>
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <?php echo $result['query'] ?>
                    </div>
                    <div class="panel-body" style="padding:5px 15px">
<?php foreach($result['messages'] as $message): ?>
                        <p style="margin:0"><?php echo $message ?></p>
<?php endforeach ?>
                    </div>
                </div>
<?php endif ?>

<?php if(($result['select'])): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
<?php foreach($result['select']['headers'] as $header): ?>
                                <th><?php echo $header ?></th>
<?php endforeach ?>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach($result['select']['details'] as $details): ?>
                            <tr>
<?php foreach($details as $detail): ?>
                                <td><?php echo $detail ?></td>
<?php endforeach ?>
                            </tr>
<?php endforeach ?>
                        </tbody>
                    </table>
                </div>
<?php endif ?>
            </div>
<?php endforeach ?>
