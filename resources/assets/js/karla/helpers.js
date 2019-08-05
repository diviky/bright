
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
            notify({ text: res.message, type: 'success', layout: 'topCenter' });
        } else if (res.status == 'INFO') {
            notify({ text: res.message, type: 'info', layout: 'topCenter' });
        } else {
            notify({ text: res.message, type: 'error', layout: 'topCenter' });
        }
    }

    if (res.login) {
        var link = 'login';
        link += (res.next) ? '?next=' + encodeURI(res.next) : '';

        res.link = {};
        res.link.url = link;
    }

    if (res.link && res.link.url) {
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
            notify({
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

    if ($this.data('remove')) {
        $this.fadeOut();
    }

    if ($this.data('render')) {
        var form = getForm($this);
        form.submit();
    }
}

function displayNoti(xhr, $this) {
    displayNoty(xhr.responseText, $this)
}

function parseJSON(response) {
    if ('object' === typeof response) {
        return response;
    }

    try {
        var res = JSON.parse(response);
    } catch (err) {
        var res = response;
        if ('object' !== typeof res) {
            res = {}
        }
    }

    return res;
}

function notify(message) {
    new Noty({
        text: message.text,
        type: message.type || 'error',
        layout: 'topRight',
        theme: 'nest',
        dismissQueue: false,
        timeout: (1 * 60 * 1000),
        progressBar: true,
        maxVisible: 1,
        killer: true
    }).show();
}

function trace(text) {
    if (window["console"] !== undefined) {
        console.log(text);
    }
}

function e(s) {
    var argv = Array.apply(null, arguments).slice(1);
    if ($.isArray(argv)) {
        $.each(argv, function (i) {
            s = s.replace(this, argv[i]);
        });
    }
    return s;
}
