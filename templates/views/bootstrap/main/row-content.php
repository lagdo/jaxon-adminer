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
<?php if(is_array($detail)): ?>
                            <td class="<?php
                                echo $detail['class'] ?>" data-value="<?php
                                echo $detail['value'] ?>"><a href="javascript:void(0)"><?php
                                echo $detail['label'] ?></a></td>
<?php else: ?>
                            <td><?php echo $detail ?></td>
<?php endif ?>
<?php endforeach ?>
                        </tr>
<?php endforeach ?>
                    </tbody>
                </table>
            </div>
