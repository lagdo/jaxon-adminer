<table class="table table-bordered">
    <thead>
        <tr>
<?php foreach($this->headers as $header): ?>
            <th><?php if(is_array($header)) echo $header['key'] ?></th>
<?php endforeach ?>
        </tr>
    </thead>
    <tbody>
<?php foreach($this->results as $results): ?>
        <tr>
            <th></th>
<?php foreach($results as $result): ?>
            <td><?php echo $result['val'] ?></td>
<?php endforeach ?>
        </tr>
<?php endforeach ?>
    </tbody>
</table>
