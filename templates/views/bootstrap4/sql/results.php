<?php foreach($this->results as $result): ?>
            <div class="row">
<?php if(count($result['errors']) > 0): ?>
                <div class="card border-danger w-100">
                    <div class="card-header border-danger">
                        <?php echo $result['query'] ?>
                    </div>
                    <div class="card-body text-danger" style="padding:5px 15px">
<?php foreach($result['errors'] as $error): ?>
                        <p style="margin:0"><?php echo $error ?></p>
<?php endforeach ?>
                    </div>
                </div>
<?php endif ?>
<?php if(count($result['messages']) > 0): ?>
                <div class="card border-success w-100">
                    <div class="card-header border-success">
                        <?php echo $result['query'] ?>
                    </div>
                    <div class="card-body text-success" style="padding:5px 15px">
<?php foreach($result['messages'] as $message): ?>
                        <p style="margin:0"><?php echo $message ?></p>
<?php endforeach ?>
                    </div>
                </div>
<?php endif ?>

<?php if(($result['select'])): ?>
                <div class="table-responsive" style="padding-top:5px">
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
