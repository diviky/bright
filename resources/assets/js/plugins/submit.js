(function ($) {
    function easySubmit(element, options) {
        this.element = element;
        var self = this;

        self.settings = $.extend({}, $.fn.easySubmit.defaults, options);

        if ($.metadata) {
            self.settings = $.extend({}, self.settings, element.metadata({ type: 'html5' }));
        }

        if (element.is('form')) {
            self.form = element;
        } else {
            self.form = element.parents('form:eq(0)');
        }

        self.form.off();

        if (self.settings.validate == true) {
            self.validateAndSubmit();
        } else {
            self.form.submit(function () {
                self.formSubmit();
            });
        }
    }

    $.extend(easySubmit.prototype, {
        validateAndSubmit: function (submit = true) {
            var self = this;

            self.form.validator(self.settings).submit(function (e) {
                // client-side validation OK.
                if (!e.isDefaultPrevented()) {
                    self.event('onValidationSuccess', {});

                    self.form.attr({
                        valid: true,
                    });

                    if (submit) {
                        self.formSubmit();
                    }

                    return false;
                }

                e.preventDefault();
                self.event('onValidationFail', {});
                self.form.attr({
                    valid: false,
                });

                var msg = self.form.attr('message') || 'Please correct the highlighted error(s)';
                notify({
                    text: msg,
                    type: 'error',
                });
            });
        },

        validate: function () {
            var self = this;
            let validator = self.form.data('validator') || self.form.validator(self.settings);

            return validator.checkValidity();
        },

        formSubmit: function () {
            var self = this;

            var fromOptions = {
                beforeSubmit: function () {
                    let next = true;
                    let fnc = self.settings.beforesubmit;
                    if (fnc != '' && fnc != undefined) {
                        var fn = window[fnc];
                        if (typeof fn === 'function') {
                            next = fn(self);
                        }
                    }

                    if (next) {
                        self.beforeSubmit(self);
                    }
                },
                success: function (response, code, xhr) {
                    let next = true;
                    let fnc = self.settings.success;

                    if (fnc != '' && fnc != undefined) {
                        var fn = window[fnc];
                        if (typeof fn === 'function') {
                            next = fn(response, code, xhr);
                        }
                    }

                    if (next) {
                        self.success(response, code, xhr);
                    }
                },
                uploadProgress: function (e, position, total, percentComplete) {
                    let next = true;

                    let fnc = self.settings.progress;

                    if (fnc != '' && fnc != undefined) {
                        var fn = window[fnc];
                        if (typeof fn === 'function') {
                            next = fn(percentComplete, total, position);
                        }
                    }

                    if (next) {
                        self.progress(percentComplete, total, position);
                    }
                },
                complete: function (xhr) {
                    let next = true;
                    let fnc = self.settings.complete;
                    if (fnc != '' && fnc != undefined) {
                        var fn = window[fnc];
                        if (typeof fn === 'function') {
                            next = fn(xhr, self);
                        }
                    }

                    if (next) {
                        self.onComplete(xhr.responseText, xhr.statusCode, xhr);
                    }
                },
                error: function (xhr) {
                    var response = self.parse(xhr.responseText);
                    self.onError(response);
                },
                format: 'json',
                data: {
                    format: 'json',
                },
            };

            var options = $.extend({}, this.settings, fromOptions);
            this.element.ajaxSubmit(options);
        },

        beforeSubmit: function (self) {
            var confirm = this.form.data('confirm');
            if (undefined != confirm && '' != confirm) {
                if (!confirm(confirm)) {
                    return false;
                }
            }
            this.addLoading();
            return true;
        },

        addLoading: function () {
            var opt = this.getTarget();

            if ('object' === typeof opt) {
                opt.attr('disabled', true);
                opt.addClass('btn-loading');
            }
        },

        removeLoading: function () {
            var opt = this.getTarget();

            if ('object' == typeof opt) {
                opt.attr('disabled', false);
                opt.removeClass('btn-loading');
            }
        },

        getTarget: function () {
            var element = this.element;
            if (element.is('form')) {
                var target = element.find('.clicked').length ? element.find('.clicked') : element.find('[type=submit]');
                if ('undefined' !== typeof element.data('submit-target')) {
                    var target = $(element.data('submit-target'));
                }
            } else {
                var target = this;
            }

            return target;
        },

        progress: function (percentComplete, total, position) {
            if (this.settings.progress) {
                $(this.settings.progress).width(percentComplete + '%');
            }

            var fnc = this.form.find('#onProgress').val();
            if (fnc != '' && fnc != undefined) {
                var fn = window[fnc];
                if (typeof fn === 'function') {
                    fn(percentComplete, total, position, this);
                }
            }
        },

        parse: function (response) {
            if ('object' === typeof response) {
                return response;
            }

            try {
                var res = JSON.parse(response);
            } catch (err) {
                var res = response;
                if ('object' !== typeof res) {
                    res = null;
                }
            }

            return res;
        },

        onComplete: function (response, code, xhr) {
            var self = this;
            var res = self.parse(response);
            self.removeLoading();

            //reload captcha
            $('.ac-captcha').each(function () {
                var src = $(this).attr('src');
                src += src.indexOf('?') ? '&' : '?';
                src += 'sid=' + Math.random();
                $(this).attr({
                    src: src,
                });
            });

            if (res && self.settings.placeholder) {
                let content = res.content ? res.content : res.message;
                $(self.settings.placeholder).empty().show().html(content);
            }
        },

        success: function (response, code, xhr) {
            var self = this;
            var res = self.parse(response);

            if (res === false || res === null) {
                notify({
                    text: 'Ooops.., We are unable to serve your request. Please try again.',
                    type: 'error',
                });
                return true;
            }

            self.event('onComplete', res);

            if (res.status == 'OK' || res.status == 'success' || res.status == 200) {
                self.onSuccess(res, xhr);
            } else if (res.status == 'INFO') {
                if (res.message) {
                    notify({
                        text: res.message,
                        type: 'information',
                    });
                }
                self.event('onInfo', res, xhr);
            } else {
                self.onError(res, xhr);
            }

            if (self.settings.target) {
                if (res.content) {
                    $(self.settings.target).empty().show().html(res.content);
                } else {
                    $(self.settings.target).empty().show().html(res.message);
                }
            }
        },

        onError: function (res, xhr) {
            var self = this;

            if (res === false || res === null) {
                notify({
                    text: 'Ooops.., We are unable to serve your request. Please try again.',
                    type: 'error',
                });
                return true;
            }

            self.event('onError', res, xhr);

            if ('undefined' === typeof res.status && res.redirect) {
                self.redirect(res.redirect, 1000);
                return true;
            }

            if (res.errors) {
                self.form.data('validator').invalidate(res.errors);
                notify({
                    text: res.errors[Object.keys(res.errors)[0]][0],
                    type: 'error',
                });
            } else if (res.error) {
                notify({
                    text: res.error.message,
                    type: 'error',
                });
            } else if (res.message) {
                notify({
                    text: res.message,
                    type: 'error',
                });
            }
        },

        onSuccess: function (res, xhr) {
            var self = this;
            self.event('onSuccess', res, xhr);

            if (!res.preview) {
                var submit = self.settings.submit;
                if (submit == 'parent') {
                    self.form.closest('form').submit();
                } else if (submit == 'render' || submit == true) {
                    $('[role="krender"]').submit();
                } else if (submit) {
                    $(self.settings.submit).submit();
                }
            }

            if (typeof res.form !== 'object') {
                res.form = {};
            }

            if (res.message) {
                notify({
                    text: res.message,
                    type: 'success',
                });
            }

            var callback = res.callback;
            if (typeof callback === 'function') {
                callback(res, self);
            } else if (typeof window[callback] === 'function') {
                window[callback](res, self);
            }

            if (res.modal) {
                if (!res.modal.body) {
                    notify({
                        text: 'Invalid response from server. Please try again.',
                        type: 'error',
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

                if (typeof res.modal.form !== 'object') {
                    res.modal.form = {};
                }

                if (res.modal.form.hide) {
                    $.fn.easyModalHide();
                }

                if (res.modal.form.reset) {
                    self.form.resetForm();
                }

                if (res.modal.form.clear) {
                    self.form.clearForm();
                }

                return true;
            }

            if (res.login) {
                var link = 'login';
                link += res.next ? '?next=' + encodeURI(res.next) : '';

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

            if (self.settings.hide || res.form.hide) {
                $.fn.easyModalHide();
            }

            if (self.settings.reset || res.form.reset) {
                self.form.resetForm();
            }

            if (self.settings.clear || res.form.clear) {
                self.form.clearForm();
            }

            if (self.settings.render) {
                self.render(self.settings.render);
            }

            if (res.form.render) {
                self.render(res.form.render);
            }

            var delay = res.message ? 2000 : 1000;
            var redirect = self.form.find('#return_url').val();
            if (self.settings.return_url) {
                self.redirect(return_url, delay);
            } else if (redirect != '' && redirect != undefined) {
                self.redirect(redirect, delay);
            } else if (self.settings.reload) {
                setTimeout('location.reload(true);', delay);
            }

            if (self.settings.alert || res.form.alert) {
                alert(res.message);
            }

            if (res.redirect) {
                self.redirect(res.redirect, delay);
            }

            if (res.next) {
                self.redirect(res.next, 1000);
            }
        },

        render: function (value) {
            var self = this;

            if (value == 'parent') {
                var element = self.form.closest('form');
            } else if (value == 'render' || value == true) {
                var element = $('[role="krender"]');
            } else if (value && value != false) {
                var element = $(value);
            }

            //element.find('input[name=page]').val(1);
            element.submit();
        },

        redirect: function (url, time) {
            var t = time ? time : 0;
            var url = url.replace('&amp;', '&');
            setTimeout(function () {
                window.top.location.href = url;
            }, t);
        },

        event: function (name, res, xhr) {
            var self = this;

            var fnc = self.settings[name];
            if (fnc && 'undefined' !== typeof fnc) {
                if ('function' == typeof fnc) {
                    fnc(res, self, self.settings);
                } else {
                    var fn = window[fnc];
                    if (typeof fn === 'function') {
                        fn(res, self, self.settings);
                    }
                }
            }

            //lower case
            var fnn = name.toLowerCase();
            var fnc = self.settings[fnn];
            if (fnc && 'undefined' !== typeof fnc) {
                if ('function' == typeof fnc) {
                    fnc(res, self, self.settings);
                } else {
                    var fn = window[fnc];
                    if (typeof fn === 'function') {
                        fn(res, self, self.settings);
                    }
                }
            }

            var fnc = self.form.find('#' + name).val();
            if (fnc != '' && 'undefined' !== typeof fnc) {
                var fn = window[fnc];
                if (typeof fn === 'function') {
                    fn(res, self, self.settings);
                }
            }
        },
    });

    $.fn.easySubmit = function (options) {
        return this.each(function () {
            var self = $(this);

            var instance = self.data('easySubmit');

            // destroy existing instance
            if (instance) {
              self.removeData('easySubmit');
            }

            var instance = new easySubmit(self, options);

            if (typeof options === 'string' && typeof instance[options] === 'function') {
                instance[options]();
            }

            self.data('easySubmit', instance);

            return this;
        });
    };

    $.fn.easySubmit.defaults = {
        target: '', // target element(s) to be updated with server response
        reload: false,
        return_url: null,
        validate: true,
        hide: false,
        reset: false,
        clear: false,
        render: true,
        placeholder: null,
        onComplete: function () {},
        onFailed: function () {},
        inputEvent: 'blur', // change, blur
    };
})(jQuery);
