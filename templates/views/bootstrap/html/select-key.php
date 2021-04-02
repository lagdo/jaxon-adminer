<select class="form-control">
<?php foreach($this->options as $value => $label): ?>
    <option value="<?php echo htmlentities($value) ?>"><?php echo $label ?></option>
<?php endforeach ?>
</select>
