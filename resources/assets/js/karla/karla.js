jQuery.fn.bstooltip = jQuery.fn.tooltip;

function karlaJs() {
    jQuery('[role="validate"]').validate();
    jQuery('[role="validator"]').validator();
    jQuery('[role="krender"]').easyRender();
    jQuery('[role="ksubmit"]').easySubmit();
    jQuery('[tooltip=modal], [role="modal"]').easyModal();

    $(document).bstooltip({
        html: true,
        selector: '[data-toggle="tooltip"]'
    });

    $(document).popover({
        html: true,
        selector: '[data-toggle="popover"]'
    });

    if ($.fn.lazyload) {
        $("img[data-original]").lazyload({
            effect: "fadeIn"
        });
    }

    if ($.fn.slimscroll) {
        $("[role=scroll]").slimscroll({
            height: 'auto',
            railVisible: true,
            size: '5px',
            wheelStep: 10
        });
    }

    if (typeof ClipboardJS === 'function') {
        var clipboard = new ClipboardJS('[data-clipboard]');
        clipboard.on('success', function (e) {
            e.clearSelection();
            $('.tooltip-inner').html('Copied!');
            $(e.trigger).tooltip('update')
        });
        clipboard.on('error', function (e) {
            noty({ text: 'Error!', type: "info" });
        });
    }

    if ($.fn.select2) {
        $('[data-select]').select2({
            minimumResultsForSearch: 10
        });

        $("[tokenizer]").select2({
            tags: true,
            tokenSeparators: [',', ' ']
        });

        $('[data-select-ajax]').each(function () {
            var $this = $(this);
            var url = $this.data('select-ajax');

            $this.select2({
                minimumInputLength: 3,
                maximumInputLength: 20,
                ajax: {
                    url: url,
                    delay: 250,
                    processResults: function (data) {
                        // Tranforms the top-level key of the response object from 'items' to 'results'
                        return {
                            results: data.rows
                        };
                    }
                }
            });
        });
    }

}

function karlaBootJs() {
    $(document).on('click', '[data-poload]', function (e) {
        var $this = $(this);
        //$this.off('hover');
        $.get($this.data('poload'), { format: 'html' }, function (d) {
            $this.popover("dispose")
            $this.popover({
                content: d,
                html: true,
                placement: 'bottom'
            }).popover('show');
        });

        $this.on("show.bs.popover", function (e) {
            $("[data-poload]").not(e.target).popover("dispose");
            $(document).trigger('ajax:loaded');
        });

        e.preventDefault();
    });


    $('body').on('click', function (e) {
        $('[data-original-title]').each(function () {
            // hide any open popovers when the anywhere else in the body is clicked
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                $(this).popover('hide');
            }
        });
    });

    $(document).on('click', '[data-popover]', function (e) {
        var $this = $(this);
        var d = $($this.data('popover')).html();

        $this.popover({
            content: d,
            html: true,
            placement: 'bottom'
        }).popover('show');

        $this.on("show.bs.popover", function (e) {
            $("[data-popover]").not(e.target).popover("dispose");
        });

        e.preventDefault();
    });

    $(document).on('click', '[data-toggle="tabs"] a', function (e) {
        e.preventDefault();
        $(this).tab('show');
        window.location.hash = this.hash;
        var scrollmem = $('body').scrollTop() || $('html').scrollTop();
        $('html,body').scrollTop(scrollmem);
    });

    var hash = window.location.hash;
    if (hash) {
        $('[data-toggle="tabs"] a[href="' + hash + '"]').tab('show');
        $('[data-toggle="tab"][href="' + hash + '"]').tab('show');
    }

    $(window).on('hashchange', function () {
        var hash = window.location.hash;
        if (hash) {
            $('[data-toggle="tabs"], a[href="' + hash + '"]').tab('show');
            $('[data-toggle="tab"][href="' + hash + '"]').tab('show');
        }
    });

    $(document).on('click', '[data-toggle="sidebar"]', function () {
        $(this).toggleClass('collapsed');
        var target = $(this).data('target');
        $(target).toggleClass('in');
    });

    $(document).on('click', '[data-toggle="backdrop"]', function () {
        var parent = $(this).parent();
        $(parent).removeClass('in');

        var target = parent.attr('id');
        $('[data-target="#' + target + '"]').addClass('collapsed');
    });

    $(document).on('click', '#search', function () {
        $(this).parents('div:first').toggleClass('active');
    });

    $('[data-bootstrap-select]').find('li').click(function (e) {
        e.preventDefault();
        var param = $(this).data("param");
        var concept = $(this).text();
        $(this).parent('div').find('[data-concept]').text(concept);
        $(this).parent('div').find('[data-param]').val(param);
    });

    if ($.fn.waypoint) {
        $('[data-waypoint]').livequery(function () {
            var $this = $(this);
            $this.waypoint({
                handler: function (direction) {
                    $this.toggleClass('navbar-inverse', direction == 'down');
                    $this.toggleClass('navbar-default', direction == 'up');
                    $this.toggleClass('sticky', direction == 'down');
                    $('body').toggleClass('sticky', direction == 'down');
                },
                offset: -50
            });
        });
    }

    $(window).on('scroll', function () {
        var scroll = $(window).scrollTop();
        $('[data-sticky]').each(function (index, el) {
            var height = parseInt($(this).data('sticky')) || 200;
            if (scroll < height) {
                $(this).removeClass("sticky");
                $('body').removeClass("sticky");
            } else {
                $(this).addClass("sticky");
                $('body').addClass("sticky");
            }
        });
        return false;
    });

    $(document).on('click', '[data-task-checkbox]', function (e) {
        e.preventDefault();

        var $this = $(this);
        var form = getForm($this);

        var task = $this.data('task-checkbox');
        var name = $this.data('task-name') || 'task';
        var input = form.find("input[name='" + name + "']");

        //check the checkbox
        $this.parents('tr:first').find('input[type="checkbox"]').attr('checked', true);

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

        form.submit();
    });


    var timer;
    $(document).on('change', '[auto-submit]', function () {
        var delay = 1000;

        if (timer) {
            clearTimeout(timer);
        }

        var form = getForm($(this));
        timer = setTimeout(function () {
            form.submit();
        }, delay);

    });

    $(document).on('keyup', '[auto-keyup-submit]', function () {
        var val = $.trim($(this).val());
        var min = 3;
        var delay = 1000;
        var form = getForm($(this));

        if (val.length == 0) {
            form.submit();
        }

        if (val.length < min) {
            return false;
        }

        if (timer) {
            clearTimeout(timer);
        }

        $(document).trigger('form:reset', $(this));

        timer = setTimeout(function () {
            return form.submit();
        }, delay);
    });

    $(document).on('click', '.dropdown-select a', function () {
        var dropdown = $(this).parents('.dropdown');
        var target = dropdown.find('.dropdown-toggle');
        target.prev('input').val($(this).data('value')).trigger('change');

        target.html($(this).html());
    });

    $('[data-interval]').each(function () {
        var $this = $(this);
        var val = parseInt($this.data('interval'));

        if (val > 0) {
            val = val * 1000;
            setInterval(function () {
                var form = getForm($this);
                form.submit();
            }, val);
        }
    });
}