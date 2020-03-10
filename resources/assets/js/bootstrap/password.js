function load_password() {
    $('[data-password]').password();

    $('[data-password-show]').each(function () {
        var parent = $(this).parent();
        parent.find('.ptxt').remove();
        parent.append('<span class="ptxt"></span>');
    });

    $(document).on('click', '.ptxt', function () {
        var parent = $(this).parent();
        var ptxt = parent.find('.ptxt');

        parent.find('[data-password-show]').attr('type', function (index, attr) {
            if (attr == 'password') {
                ptxt.addClass('active');
                return 'text';
            } else {
                ptxt.removeClass('active');
                return 'password';
            }
        });
    });
}