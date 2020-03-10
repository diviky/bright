function appJs() {

}

$(document).ready(function () {
    appJs();

    $(document).on('ajax:loaded', function (e, $this) {
        appJs();
    });

    $(document).on('ajax:modal:loaded', function (e) {
        $('[data-toggle="tooltip"]').tooltip('hide');
    });
});
