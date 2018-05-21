; (function ($, window, undefined) {
    'use strict';
    var options = {};

    var defaults = {
        title: function (api, el) {
            return el && (el.data('original-title') || el.attr('title'));
        },
        event: 'click',
        backdrop: true, //'false|true|static',
        button: false,
        keyboard: false,
        id: 'bmodel',
        styles: null,
        animation: "bounceInRight",
        content: null,
        url: null,
        container: 'body'
    };

    var easyModal = function (el, options) {
        var self = this;
        this.el = el;
        this.settings = $.extend({}, defaults, options);

        if ($.metadata && el) {
            this.settings = $.extend({}, this.settings, el.metadata({ type: 'html5' }));
        }

        this.settings.el = '#' + this.settings.id;

        if (this.settings.content === null) {
            this.settings.content = self.ajaxContent;
        }

        var event = self.settings.event;

        if (event == 'ready') {
            self.show();
        } else {
            el.off(this.settings.event);
            el.on(this.settings.event, function (e) {
                e.preventDefault();
                self.show();
            });
        }

        return this;
    };

    easyModal.prototype.show = function () {
        var self = this;
        $(self.settings.el).remove();
        $('.modal-backdrop').remove();

        self.start(self.el);
        var content = self.settings.content;

        if (typeof content === "function") {
            content = content(self, self.el);
        }
        self.content(content);

        return this;
    }

    easyModal.prototype.set = function (options) {
        this.settings = $.extend({}, this.settings, options);

        return this;
    }

    easyModal.prototype.ajaxContent = function (self, el) {
        var target = self.settings.url || el.attr('href');

        //add loading
        $.ajax({
            url: target,
            data: {
                format: 'html'
            },
            cache: false
        }).then(function (html) {
            self.content(html);
        }, function (xhr, status, error) {
            self.content(status + ': ' + error);
        });
    };

    easyModal.prototype.start = function (el) {
        var self = this;
        var settings = self.settings;
        var title = settings.title;
        
        var html = '<div class="modal modal-box ' + settings.styles + '" id="' + settings.id + '">';
        html += '   <div class="modal-dialog animated ' + settings.animation + '">';
        html += '       <div class="modal-content">';

        if (typeof title === "function") {
            title = title(self, el);
        }

        if (settings.button || title) {
            html += ' <div class="modal-header">';

            if (title) {
                html += ' <h5 class="modal-title">' + title + '</h5>';
            }

            if (settings.button) {
                html += '<button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>';
            }

            html += '           </div>';
        }
        html += '           <div class="modal-body"></div>';
        html += '       </div>';
        html += '   </div>';
        html += '</div>';

        $(settings.container).append(html);

        return this;
    };

    easyModal.prototype.content = function (html) {
        var self = this;

        $(self.settings.el + ' .modal-body').html(html);
        $(self.settings.el).modal({
            show: true,
            keyboard: this.settings.keyboard,
            backdrop: this.settings.backdrop
        });

        $(self.settings.el).on('hidden.bs.modal', function () {
            $(this).data('bs.modal', null);
            $('.modal-backdrop').remove();
        });

        onloadEvents();

        return this;
    };

    $.fn.easyModalShow = function (options) {
        if (!$.data(this, 'easymodals')) {
            return new easyModal(null, options);
        }
    }

    $.fn.easyModalHide = function() {
        setTimeout(function() {
            $('.modal').modal('hide');
        }, 1000);
    }

    $.fn.easyModal = function (options) {
        return this.each(function () {
            if (!$.data(this, 'easymodal')) {
                return new easyModal($(this), options);
            }
        });
    };

}(jQuery, this));
