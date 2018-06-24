; (function ($) {
    var pluginName = 'easyRender';

    var defaults = {
        response_target: '[ajax-content]',
        total_target: '[ajax-total]',
        loadmore: '.ac-load-more',
        overlayclass: 'render-overlay',
        append: false,
        data: {}
    };

    var easyRender = function (element, options) {
        this.element = element;
        this.settings = $.extend({}, defaults, options);
        if ($.metadata) {
            this.settings = $.extend({}, this.settings, element.metadata({
                type: 'class'
            }));
            this.settings = $.extend({}, this.settings, element.metadata({
                type: 'html5'
            }));
        }
        if (element.is("form")) {
            this.form = element;
        } else {
            this.form = element.parents('form:eq(0)');
        }

        if (!this.form) {
            return false;
        }

        this.init();
        this.submit();
    };

    easyRender.prototype.init = function () {
        var widget = this;
        var ps_loaded = false
            , oldtop = 0;

        $(widget.form).on('click', ".ac-ajax-pagination li", function (e) {
            e.preventDefault();
            var page = $(this).data('page');

            if (!page) {
                return false;
            }
            widget.form.find('input[name=page]').val(page);
            widget.settings.append = false;
            widget.formSubmit();
            return false;
        });

        $(widget.form).on('click', widget.settings.loadmore, function (e) {
            e.preventDefault();
            var page = $(this).data('page');
            widget.form.find('input[name=page]').val(page);
            widget.settings.append = true;
            widget.formSubmit();
            return false;
        });

        $(widget.form).on('click', "[type=submit]", function (e) {
            e.preventDefault();
            widget.form.find('input[name=page]').val(1);
            widget.settings.append = false;
            widget.formSubmit();
            return false;
        });

        $(window).scroll(function () {
            var offset = widget.form.find(widget.settings.loadmore).offset();
            var tops = (offset) ? offset.top : 0;
            if (isNaN(tops) || tops == 0) {
                return false;
            }
            if (oldtop != tops) {
                oldtop = tops;
                var p = widget.form.find('input[name=page]').val();
                if (p < 3) {
                    ps_loaded = false;
                }
            }
            if (!ps_loaded && $(window).scrollTop() + $(window).height() > tops) {
                ps_loaded = true;
                widget.form.find(widget.settings.loadmore).click();
            }
        });
    };

    easyRender.prototype.submit = function () {
        var widget = this;

        widget.form.submit(function (e) {
            e.preventDefault();
            widget.formSubmit();
            return false;
        });
    }

    easyRender.prototype.formSubmit = function () {
        var widget = this;
        var data = widget.settings.data;
        data.format = 'html';

        var fromOptions = {
            beforeSubmit: function () {
                widget.beforeSubmit();
            },
            success: function (response) {
                widget.onSuccess(response);
            },
            data: data
        }

        var options = $.extend({}, this.settings, fromOptions);
        this.element.ajaxSubmit(options);
    };

    easyRender.prototype.beforeSubmit = function () {
        var widget = this;

        var target = widget.form.find(widget.settings.response_target);

        target.addClass(widget.settings.overlayclass);
        widget.form.find(widget.settings.loadmore).next('.ac-load-more-loading').show();
        widget.form.find(widget.settings.loadmore).remove();
        return true;
    };

    easyRender.prototype.onSuccess = function (response) {
        var widget = this;

        this.callback('onComplete', response);

        //check the page number
        var page = widget.form.find('input[name=page]').val();
        var append = (page == 1) ? false : widget.settings.append;

        var target = widget.form.find(widget.settings.response_target);
        if (append) {
            widget.form.find(".ac-load-more-remove").remove();
            target.append(response);
        } else {

            target.html(response);
            var total = widget.form.find('[data-total]').data('total');

            if (total == '' || total == undefined) {
                var total = widget.form.find('input[name=total]').val();
            }

            widget.form.find("input[name=total]").val(total);
            widget.form.find(widget.settings.total_target).html(total);
        }
        target.removeClass(widget.settings.overlayclass);
    };

    easyRender.prototype.callback = function (name, res) {

        $(document).trigger('ajax:loaded');

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

    $.fn.easyRender = function (options) {
        return this.each(function () {
            if (!$.data(this, pluginName)) {
                $.data(this, pluginName, new easyRender($(this), options));
            }
        });
    };

})(jQuery);
