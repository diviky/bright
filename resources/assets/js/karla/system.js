
function getForm($this) {

    var form = null;
    if ($this.parents('form:eq(0)').length > 0) {
        var form = $this.parents('form:eq(0)');
    } else if ($this.parents().find('[ajax-form]').length > 0) {
        var form = $('[ajax-form]');
    } else {
        var form = $('[role="easyRender"]');
    }

    return form;
}

function isSuccess(status) {
    if (status == 'OK' || status == 'success' || status == 200) {
        return true;
    }

    return false;
}

function displayNoty(response, $this) {
    res = parseJSON(response);

    if (res.message) {
        if (isSuccess(res.status)) {
            noty({ text: res.message, type: 'success', layout: 'topCenter' });
        } else if (res.status == 'INFO') {
            noty({ text: res.message, type: 'info', layout: 'topCenter' });
        } else {
            noty({ text: res.message, type: 'error', layout: 'topCenter' });
        }
    }

    if (res.login) {
        var link = 'login';
        link += (res.next) ? '?next=' + encodeURI(res.next) : '';

        res.link = {};
        res.link.url = link;
    }

    if (res.link) {
        var opts = {
            url: res.link.url,
            event: 'ready',
        };

        if (res.link.options) {
            var opts = $.extend({}, res.link.options, opts);
        }

        $.fn.easyModalShow(opts);

        return true;
    }

    if (res.modal) {
        if (!res.modal.body) {
            noty({
                text: 'Invalid response from server. Please try again.',
                type: 'error'
            });
            return true;
        }

        var opts = {
            content: res.modal.body,
            event: 'ready',
        };

        if (res.modal.options) {
            var opts = $.extend({}, res.modal.options, opts);
        }

        $.fn.easyModalShow(opts);

        return true;
    }

    if (res.redirect) {
        var url = res.redirect;
        url = url.replace('&amp;', '&');
        setTimeout(function () {
            window.top.location.href = url;
        }, 1000);
    }

    var callback = $this.data('callback');

    if (typeof callback == 'function') {
        callback(res, $this);
    } else if (typeof window[callback] == 'function') {
        window[callback](res, $this);
    }

    if ($this.data('hide')) {
        $.fn.easyModalHide();
    }
}


function displayNoti(xhr, $this) {
    displayNoty(xhr.responseText, $this)
}


$(document).ready(function () {

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
        //data = parseJSON(data);
        //data.format = 'json';

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
    }

    if ($.fn.tokenfield) {
        $("[data-token]").livequery(function () {
            var $this = $(this);

            var autocomplete = {
                minLength: 2,
                delay: 100,
                source: function (request, response) {
                    $.ajax({
                        url: $this.data('token'),
                        data: { task: 'auto', q: request.term, format: 'json', options: $this.data('options') },
                        success: function (data) {
                            response(data);
                        }
                    });
                }
            };
            var tokens = [];
            if ($this.attr('tokens')) {
                try {
                    tokens = $.parseJSON($this.attr('tokens'));
                } catch (err) {
                    console.log(err);
                }
            }

            $this.tokenfield({
                tokens: tokens,
                autocomplete: autocomplete
            })
                .on('tokenfield:createtoken', function (event) {
                    var existingTokens = $(this).tokenfield('getTokens');
                    $.each(existingTokens, function (index, token) {
                        if (token.value === event.attrs.value) {
                            event.preventDefault();
                        }
                    });
                });
        });
    }
});
