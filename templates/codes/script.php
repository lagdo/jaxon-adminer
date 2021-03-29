jaxon.adminer = {
    countTableCheckboxes: function(checkboxId) {
        $('#adminer-table-' + checkboxId + '-count').html($('.adminer-table-' + checkboxId + ':checked').length);
    },
    selectTableCheckboxes: function(checkboxId) {
        $('#adminer-table-' + checkboxId + '-all').change(function() {
            $('.adminer-table-' + checkboxId).prop('checked', this.checked);
            jaxon.adminer.countTableCheckboxes(checkboxId);
        });
        $('.adminer-table-' + checkboxId).change(function() {
            jaxon.adminer.countTableCheckboxes(checkboxId);
        });
    }
}
