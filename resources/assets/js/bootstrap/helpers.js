
function load_helpers() {

    $(document).on('click', '[data-input]', function (e) {
        var $this = $(this);
        var name = $this.data('input');
        var val = $this.attr('value');
        $this.parents('.btn-group:first').find('button').removeClass('active');
        $this.addClass('active');

        $('input[name=' + name + ']').remove();

        $("<input>", {
            type: "hidden",
            name: name,
            value: val
        }).insertAfter($this);

        $(document).trigger('turbolinks:load');
        $(document).trigger('form:submit', [$(this)]);
    });


    $(document).on('change', '[data-display]', function () {
        var target = $(this).data('display');
        $("." + target).slideUp();
        if ($(this).is(':checked')) {
            $('#' + target).slideDown();
        } else {
            $('#' + target).slideUp();
        }
    });

    $(document).on('change', '[data-check-all]', function (e) {
        var $this = $(this);
        var target = $this.parents('table:first');
        var boxes = target.find('tbody input[type=checkbox]');

        if ($this.is(':checked')) {
            boxes.prop('checked', true);
            target.find('[data-on-checked]').show();
            target.find('[data-off-checked]').hide();

            var selected = target.find('tbody input[type=checkbox]:checked').length;
            target.find('[data-checked-count]').text(selected);

            //target.find('[data-on-checked] input[type=checkbox]').prop('checked', true);
            target.find('[data-on-checked] input[type=checkbox]').prop('checked', true);
        } else {
            boxes.prop('checked', false);
            target.find('[data-on-checked]').hide();
            target.find('[data-off-checked]').show();
            target.find('[data-on-checked] input[type=checkbox]').prop('checked', false);
        }
    });

    $(document).on('change', '[data-check-alls]', function (e) {
        var $this = $(this);
        var target = $this.parents('table:first');
        var boxes = target.find('tbody input[type=checkbox]');

        boxes.prop('checked', false);
        target.find('[data-on-checked]').hide();
        target.find('[data-off-checked]').show();

        target.find('[data-off-checked] input[type=checkbox]').prop('checked', false);
    });

    $(document).on('change', '[data-checked] input[type=checkbox]', function (e) {
        var $this = $(this);
        var target = $this.parents('table:first');
        var boxes = target.find('tbody input[type=checkbox]');

        var selected = target.find('tbody input[type=checkbox]:checked').length;
        target.find('[data-checked-count]').text(selected);

        if (selected > 0) {
            target.find('[data-on-checked]').show();
            target.find('[data-off-checked]').hide();
            target.find('[data-on-checked] input[type=checkbox]').prop('checked', true);
        } else {
            target.find('[data-on-checked]').hide();
            target.find('[data-off-checked]').show();
            target.find('[data-on-checked] input[type=checkbox]').prop('checked', false);
        }
    });


    $(document).on('click', '[date-prev]', function () {
        var input = $(this).parents('div:first').find('[date]');
        var current = input.val();
        var format = input.attr('date-format') || "dddd, MMM DD YYYY";

        if (current.indexOf(' - ') >= 0) {
            var days = current.split(' - ');
            var start = moment(days[0]);
            var end = moment(days[1]);
            var diff = end.diff(start, 'days');

            var prev = start.add(-diff, 'days').format(format);
            prev += " - ";
            prev += end.add(-diff, 'days').format(format);
        } else {
            var day = moment(current);
            var prev = day.add(-1, 'days').format(format);
        }

        input.val(prev);

        $(document).trigger('form:submit', [$(this)]);
    });

    $(document).on('click', '[date-next]', function () {
        var input = $(this).parents('div:first').find('[date]');
        var current = input.val();
        var format = input.attr('date-format') || "dddd, MMM DD YYYY";

        if (current.indexOf(' - ') >= 0) {
            var days = current.split(' - ');
            var start = moment(days[0]);
            var end = moment(days[1]);
            var diff = end.diff(start, 'days');

            var prev = start.add(diff, 'days').format(format);
            prev += " - ";
            prev += end.add(diff, 'days').format(format);
        } else {
            var day = moment(current);
            var prev = day.add(1, 'days').format(format);
        }

        input.val(prev);

        $(document).trigger('form:submit', [$(this)]);
    });

    $(document).on('click', '[data-toggle="menu"]', function (e) {
        var parent = $(this).parent();
        parent.addClass('has-child').toggleClass('open');

        if (parent.hasClass('open')) {
            $(this).next('ul').slideDown();
        } else {
            $(this).next('ul').slideUp();
        }

        e.preventDefault()
    });

    $(document).on('click', '[data-file]', function () {
        var target = $(this).data('file');
        $("#" + target).trigger('click');
    });

};