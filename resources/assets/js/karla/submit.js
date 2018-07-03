; (function ($) {
    var pluginName = 'easySubmit';

    var defaults = {
        target: '', // target element(s) to be updated with server response
        reload: false,
        return_url: null,
        validate: true,
        hide: false,
        reset: false,
        clear: false,
        render: true,
        onComplete: function () { },
        onFailed: function () { },
        inputEvent: 'blur' // change, blur
    };

    var easySubmit = function (element, options) {
        this.element = element;

        this.settings = $.extend({}, defaults, options);

        if ($.metadata) {
            this.settings = $.extend({}, this.settings, element.metadata({ type: 'html5' }));
        }

        if (element.is("form")) {
            this.form = element;
        } else {
            this.form = element.parents('form:eq(0)');
        }

        if (this.settings.validate == true) {
            this.validate();
        } else {
            var widget = this;
            this.form.submit(function () {
                widget.formSubmit();
            })
        }
    };

    easySubmit.prototype.validate = function () {
        var widget = this;

        this.form.validator(widget.settings).submit(function (e) {
            // client-side validation OK.
            if (!e.isDefaultPrevented()) {
                widget.event('onValidationSuccess', {});

                widget.form.attr({
                    valid: true
                });
                widget.formSubmit();
                return false;
            }
            widget.event('onValidationFail', {});
            e.preventDefault();
            widget.form.attr({
                valid: false
            });

            var msg = widget.form.attr('message') || 'Please correct the highlighted error(s)';
            notify({
                text: msg,
                type: 'error'
            });
        });
    };

    easySubmit.prototype.formSubmit = function () {
        var widget = this;
        var beforeSubmit = this.settings.beforeSubmit || this.beforeSubmit;
        var success = this.settings.success || this.success;
        var progress = this.settings.onProgress || this.progress;

        var fromOptions = {
            beforeSubmit: function () {
                widget.beforeSubmit();
            },
            success: function (response, code, xhr) {
                widget.success(response, code, xhr)
            },
            uploadProgress: function (event, position, total, percentComplete) {
                widget.progress(percentComplete, total, position);
            },
            complete: function (xhr) {
                widget.onComplete(xhr);
            },
            error: function (xhr) {
                var response = widget.parse(xhr.responseText);
                widget.onError(response);
            },
            format: 'json',
            data: {
                format: 'json'
            }
        }

        var options = $.extend({}, this.settings, fromOptions);
        this.element.ajaxSubmit(options);
    };

    easySubmit.prototype.beforeSubmit = function () {
        var confirm = this.form.data('confirm')
        if (undefined != confirm && '' != confirm) {
            if (!confirm(confirm)) {
                return false;
            }
        }
        this.addLoading();
        return true;
    };

    easySubmit.prototype.addLoading = function () {
        var opt = this.getTarget();

        if ('object' === typeof opt) {
            opt.attr('disabled', true);
            opt.addClass('btn-loading');
        }
    };

    easySubmit.prototype.removeLoading = function () {
        var opt = this.getTarget();

        if ('object' == typeof opt) {
            opt.attr('disabled', false);
            opt.removeClass('btn-loading');
        }
    };

    easySubmit.prototype.getTarget = function () {
        var element = this.element;
        if (element.is("form")) {
            var target = (element.find('.clicked').length) ? element.find('.clicked') : element.find('[type=submit]');
            if ('undefined' !== typeof element.data('submit-target')) {
                var target = $(element.data('submit-target'));
            }
        } else {
            var target = this;
        }

        return target;
    }

    easySubmit.prototype.progress = function (percentComplete, total, position) {
        if (this.settings.progress) {
            $(this.settings.progress).width(percentComplete + '%');
        }

        var fnc = this.form.find("#onProgress").val();
        if (fnc != '' && fnc != undefined) {
            var fn = window[fnc];
            if (typeof fn === 'function') {
                fn(percentComplete, total, position, this);
            }
        }
    };

    easySubmit.prototype.parse = function (response) {
        if ('object' === typeof response) {
            return response;
        }

        try {
            var res = JSON.parse(response);
        } catch (err) {
            var res = response;
            if ('object' !== typeof res) {
                res = null
            }
        }

        return res;
    }

    easySubmit.prototype.onComplete = function (xhr) {
        var widget = this;
        widget.removeLoading();

        //reload captcha
        $('.ac-captcha').each(function () {
            var src = $(this).attr('src');
            src += (src.indexOf('?')) ? '&' : '?';
            src += 'sid=' + Math.random();
            $(this).attr({
                src: src
            });
        });
    }

    easySubmit.prototype.success = function (response, code, xhr) {
        var widget = this;
        var res = widget.parse(response);

        if (res === false || res === null) {
            notify({
                text: 'Ooops.., We are unable to serve your request. Please try again.',
                type: 'error'
            });
            return true;
        }

        widget.event('onComplete', res);

        if (res.status == "OK" || res.status == "success" || res.status == 200) {
            widget.onSuccess(res, xhr)
        } else if (res.status == 'INFO') {
            if (res.message) {
                notify({
                    text: res.message,
                    type: 'information'
                });
            }
            widget.event('onInfo', res, xhr);
        } else {
            widget.onError(res, xhr)
        }

        if (widget.settings.target) {
            $(widget.settings.target).empty().show().html(message);
        }
    };

    easySubmit.prototype.onError = function (res, xhr) {
        var widget = this;

        if (res === false || res === null) {
            notify({
                text: 'Ooops.., We are unable to serve your request. Please try again.',
                type: 'error'
            });
            return true;
        }

        widget.event('onError', res, xhr);

        if ('undefined' === typeof res.status && res.redirect) {
            widget.redirect(res.redirect, 1000);
            return true;
        }

        if (res.errors) {
            widget.form.data("validator").invalidate(res.errors);
            notify({
                text: res.errors[Object.keys(res.errors)[0]][0],
                type: 'error'
            });
        }

        if (res.error) {
            notify({
                text: res.error.message,
                type: 'error'
            });
        }

        if (res.message) {
            notify({
                text: res.message,
                type: 'error'
            });
        }

    }

    easySubmit.prototype.onSuccess = function (res, xhr) {
        var widget = this;
        widget.event('onSuccess', res, xhr);

        if (res.login) {
            var link = 'login';
            link += (res.next) ? '?next=' + encodeURI(res.next) : '';

            res.link = {};
            res.link.url = link;
        }

        if (!res.preview) {
            var submit = widget.settings.submit;
            if (submit == 'parent') {
                widget.form.closest('form').submit();
            } else if (submit == 'render' || submit == true) {
                $('[role="krender"]').submit();
            } else if (submit) {
                $(widget.settings.submit).submit();
            }
        }

        if (res.message) {
            notify({
                text: res.message,
                type: 'success'
            });
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

        if (widget.settings.hide) {
            $.fn.easyModalHide();
        }

        if (widget.settings.reset) {
            widget.form.resetForm();
        }
        if (widget.settings.clear) {
            widget.form.clearForm();
        }

        if (widget.settings.render) {
            widget.render(widget.settings.render);
        }

        var delay = (res.message) ? 5000 : 1000;
        var redirect = widget.form.find('#return_url').val();
        if (widget.settings.return_url) {
            widget.redirect(return_url, delay);
        } else if (redirect != '' && redirect != undefined) {
            widget.redirect(redirect, delay);
        } else if (widget.settings.reload) {
            setTimeout("location.reload(true);", delay);
        }

        if (widget.settings.alert) {
            alert(res.message);
        }

        if (res.redirect) {
            widget.redirect(res.redirect, delay);
        }

        if (res.next) {
            widget.redirect(res.next, 1000);
        }
    }

    easySubmit.prototype.render = function (value) {
        var widget = this;

        if (value == 'parent') {
            var element = widget.form.closest('form');
        } else if (value == 'render' || value == true) {
            var element = $('[role="krender"]');
        } else if (value && value != false) {
            var element = $(value);
        }

        element.find('input[name=page]').val(1);
        element.submit();
    }

    easySubmit.prototype.redirect = function (url, time) {
        var t = (time) ? time : 0;
        var url = url.replace('&amp;', '&');
        setTimeout(function () {
            window.top.location.href = url;
        }, t);
    };

    easySubmit.prototype.event = function (name, res, xhr) {
        var widget = this;

        var fnc = widget.settings[name];
        if (fnc && 'undefined' !== typeof fnc) {
            if ('function' == typeof fnc) {
                fnc(res, widget, widget.settings);
            } else {
                var fn = window[fnc];
                if (typeof fn === 'function') {
                    fn(res, widget, widget.settings);
                }
            }
        }

        //lower case
        var fnn = name.toLowerCase();
        var fnc = widget.settings[fnn];
        if (fnc && 'undefined' !== typeof fnc) {
            if ('function' == typeof fnc) {
                fnc(res, widget, widget.settings);
            } else {
                var fn = window[fnc];
                if (typeof fn === 'function') {
                    fn(res, widget, widget.settings);
                }
            }
        }

        var fnc = widget.form.find("#" + name).val();
        if (fnc != '' && 'undefined' !== typeof fnc) {
            var fn = window[fnc];
            if (typeof fn === 'function') {
                fn(res, widget, widget.settings);
            }
        }
    };

    $.fn.easySubmit = function (options) {
        return this.each(function () {
            if (!$.data(this, pluginName)) {
                $.data(this, pluginName, new easySubmit($(this), options));
            }
        });
    };

})(jQuery);
