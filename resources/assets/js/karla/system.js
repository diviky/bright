function karlaSystemJs() {
    $(document).on('click', '[data-href]', function (e) {
        e.preventDefault();

        var $this = $(this);

        $.ajax({
            url: $(this).data('href'),
            data: { format: 'json' },
            complete: function (xhr) {
                displayNoti(xhr, $this);
            }
        });
    });

    $(document).on('click', '[data-post]', function (e) {
        e.preventDefault();

        var $this = $(this);
        var data = $this.data('post-data');

        $.ajax({
            url: $(this).data('post'),
            data: data,
            method: "POST",
            complete: function (xhr) {
                displayNoti(xhr, $this);
            }
        });
    });

    //common delete script
    $(document).on('click', '[data-delete], [data-action="delete"]', function (e) {
        if (!confirm('Are you sure want to delete?')) {
            return false;
        }

        var $this = $(this);
        var tag = $this.data('tag') || 'tr';
        var parent = $this.parents(tag + ':eq(0)');
        var link = $(this).attr('href') || $this.data('delete');

        $.ajax({
            url: link,
            data: { format: 'json' },
            complete: function (xhr) {
                var res = parseJSON(xhr.responseText);
                if (isSuccess(res.status)) {
                    parent.remove();
                    $('[ajax-total]').html(parseInt($('[ajax-total]').text()) - 1);
                }

                displayNoti(xhr, $this);
            }
        });

        e.preventDefault();
    });

    //common delete script
    $(document).on('click', '[data-ajax]', function (e) {

        var $this = $(this);
        var link = $(this).attr('href') || $this.data('ajax');

        $.ajax({
            url: link,
            data: { format: 'json' },
            complete: function (xhr) {
                displayNoti(xhr, $this);
            }
        });

        e.preventDefault();
    });

    //common status change script
    $(document).on('click', '.ac-action-status a', function (e) {
        var $this = $(this);
        var parent = $this.parent('.ac-action-status');

        $.ajax({
            url: parent.data('link'),
            data: { status: $this.data('status'), format: 'json' },
            complete: function (xhr) {
                var res = parseJSON(xhr.responseText);
                if (isSuccess(res.status)) {
                    parent.find('a:hidden').show();
                    $this.hide();
                }

                displayNoti(xhr, $this);
            }
        });

        e.preventDefault();
    });

    //common status change script
    $(document).on('change', '.ac-action-status select', function (e) {
        var $this = $(this);
        var parent = $this.parent('.ac-action-status');
        var v = $(this).val();

        $.ajax({
            url: parent.data('link'),
            data: { status: v, format: 'json' },
            complete: function (xhr) {
                displayNoti(xhr, $this);
            }
        });

        e.preventDefault();
    });

    //common delete script
    $(document).on('click', '[ajax-confirm]', function (e) {
        if (!confirm('Are you sure want to proceed?'))
            return false;

        var $this = $(this);

        $.ajax({
            url: $(this).attr('href'),
            data: { format: 'json' },
            complete: function (xhr) {
                displayNoti(xhr, $this);
            }
        });

        e.preventDefault();
    });

    $(document).on("click", "[ajax-reset]", function (e) {
        var form = getForm($(this));
        form[0].reset();
        e.preventDefault();
        form.submit();
    });

    $(document).on('click', "[ajax-export]", function (e) {
        var $this = $(this);
        var form = getForm($this);
        var base = $this.attr('base-href');

        if (base === undefined) {
            $this.attr('base-href', $this.attr('href'));
        }

        var url = $this.attr('base-href');
        var separator = url.indexOf('?') > 0 ? '&' : '?';

        $this.attr('href', url + separator + form.serialize());
    });

    $(document).on('click', '[data-task]', function (e) {
        var $this = $(this);
        var form = getForm($this);

        var task = $this.data('task');
        var name = $this.data('task-name') || 'task';
        var input = form.find("input[name='" + name + "']");

        if (input.length > 0) {
            var oldtask = input.attr('task', input.val());
            input.val(task);
        } else {
            $('<input/>', {
                name: name,
                value: task,
                type: 'hidden'
            }).appendTo(form);
        }

        $(document).trigger('form:reset', $this);

        if ($this.attr('type') != "submit") {
            form.submit();
            e.preventDefault();
        }
    });

    $(document).on('click', '[data-order]', function (e) {
        e.preventDefault();

        var name = $(this).attr('data-order');
        var order = $(this).attr('data-order-type') || 'ASC';
        $('[data-order]').removeClass('ordering');
        $(this).addClass('ordering');

        var task = 1;
        var page = 1;

        if (new RegExp('ASC').test(order)) {
            task = 0;
        }

        if (task == 1) {
            $(this).attr('data-order-type', 'ASC');
            $(this).removeClass('desc').addClass('asc');
        } else {
            $(this).removeClass('asc').addClass('desc');
            $(this).attr('data-order-type', 'DESC');
        }
        var form = getForm($(this));;

        if (form.find('.ac-sort-name').length > 0) {
            $('.ac-sort-name').val(name);
        } else {
            $('<input/>', {
                class: 'ac-sort-name',
                name: 'sort',
                value: name,
                type: 'hidden'
            }).appendTo(form);
        }

        if (form.find('.ac-sort-order').length > 0) {
            $('.ac-sort-order').val(order);
        } else {
            $('<input/>', {
                class: 'ac-sort-order',
                name: 'order',
                value: order,
                type: 'hidden'
            }).appendTo(form);
        }

        form.find("input[name='page']").val(page);
        $('#page').val(page);
        form.submit();
    });

    // Drag and drop sortable
    if ($.fn.sortable) {
        var _gridSortHelper = function (e, ui) {
            ui.children().each(function () {
                $(this).width($(this).width());
            });
            return ui;
        };

        var _gridSortUpdateHandler = function (e, ui) {
            var form = getForm($(this));
            var page = 1;

            if (form.find('.ac-task-input').length > 0) {
                $('.ac-task-input').val('sorting');
            } else {
                $('<input/>', {
                    class: 'ac-task-input',
                    name: 'task',
                    value: 'sorting',
                    type: 'hidden'
                }).appendTo(form);
            }

            form.find("input[name='page']").val(page);
            $('#page').val(page);

            form.submit();
            return ui;
        };

        $('.table_sortable_body').sortable({
            //containment: '.ac-ui-sortable',
            connectWith: ['.table_sortable_body'],
            handle: '[sortable]',
            opacity: 0.6,
            helper: _gridSortHelper,
            update: _gridSortUpdateHandler
        }).disableSelection();

        $('[sortables]').sortable({
            connectWith: ['.table_sortable_body'],
            handle: '[sortable]',
            opacity: 0.6
        }).disableSelection();
    }

    $("[notchecked]").each(function () {
        $(this).prev('[dummy-checkbox]').remove();
        $(this).before('<input type="hidden" name="' + $(this).attr("name") + '" value="' + $(this).attr("notchecked") + '"  dummy-checkbox="true"/>');
    });

    $(document).on('change', '[notchecked]', function () {
        $(this).prev('[dummy-checkbox]').remove();
        $(this).before('<input type="hidden" name="' + $(this).attr("name") + '" value="' + $(this).attr("notchecked") + '"  dummy-checkbox="true"/>');
    });

    $(document).on("click", "[data-prevent]", function (e) {
        e.preventDefault();
        return false;
    });

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        }
    });

    $(document).on("click", ".noty_close", function (e) {
        $(this)
            .parents(".noty_bar:first")
            .slideUp("slow");
    });

    $(document).on("click", "div.is-invalid-feedback", function () {
        $(this).fadeOut();
    });

    $(document).on('form:reset', function (e) {
        var form = getForm($(this));
        form.find("input[name=page]").val(1);
        $('#page').val(1);

        var total = form.find('[data-total]').data('total');

        if (total == '' || total == undefined) {
            var total = form.find('input[name=total]').val();
        }

        form.find("input[name=total]").val(total);
        form.find('[ajax-total]').html(total);
    });

    $(document).on("click", "[data-filter]", function () {
        $(document).trigger('form:reset', $(this));
    });

    $(document).on("keyup blur", "[data-slug]", function (e) {
        var target = $(this).data("slug");
        var realTarget = "[data-slug-" + target + "]";

        var value = $(this).val();
        value = $.trim(value);
        value = value
            .replace(/[^a-z0-9-]/gi, "-")
            .replace(/-+/g, "-")
            .replace(/^-|-$/g, "");
        value = value.toLowerCase();

        $(realTarget).val(value);
    });

    $(document).on("keyup blur", "[data-copy]", function (e) {
        var target = $(this).data("copy");
        var realTarget = "[data-copy-" + target + "]";

        $(realTarget).val(value);
    });

    $(document).on("click", "[data-add]", function (e) {
        var target = $(this).parents('table:first');
        var clone = target.find("tfoot:first tr").clone();
        target.find('tbody:first').append(clone);

        e.preventDefault();
    });

    $(document).on("click", "[data-remove]", function (e) {
        $(this)
            .parents("tr:eq(0)")
            .remove();

        e.preventDefault();
    });

    $(document).on('change', ".custom-file-input", function () {
        var files = $(this).prop("files");

        if (files.length > 0) {
            var name = files[0]['name'];
            $(this).parent().find('.custom-file-label').html(name);
        }
    });

    //prevent hash url
    $(document).on("click", 'a[href="#"]', function (e) {
        e.preventDefault();
    });

    $(document).on("click", "[role=login]", function (e) {
        if (is_user_logged_in) {
            return true;
        }

        e.preventDefault();
        var $this = $(this);
        var url = $this.attr("href") ? $this.attr("href") : "/login";

        $.fn.easyModalShow({
            url: url,
            event: 'ready'
        });
    });

    if ($.support.pjax) {
        $(document).on("click", "[data-pjax] a, a[data-pjax]", function (e) {
            if ($(this).data('nojax')) {
                return true;
            }

            var container = $(this).data("pjax-container") || "[data-pjax-container]";
            $(this).parents("[data-pjax]").find("a").removeClass("active");
            $(this).addClass("active");

            $.pjax.click(e, { container: container });
        });

        $(document).on('pjax:end', function () {
            $(document).trigger('ajax:loaded');
        });
    }
};
