(function ($) {
  var pluginName = 'easyRender';

  var defaults = {
    response_target: '[fragment]',
    total_target: '[ajax-total]',
    loadmore: '[ajax-more]',
    backdrop: 'backdrop',
    append: false,
    json: false,
    state: false,
    data: {},
  };

  var easyRender = function (element, options) {
    this.element = element;
    this.settings = $.extend({}, defaults, options);
    if ($.metadata) {
      this.settings = $.extend({}, this.settings, element.metadata({ type: 'class' }));
      this.settings = $.extend({}, this.settings, element.metadata({ type: 'html5' }));
    }
    if (element.is('form')) {
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
    var self = this;
    var form = self.form;
    var ps_loaded = false,
      oldtop = 0;

    $(form).on('click', '.ac-ajax-pagination li', function (e) {
      e.preventDefault();
      var page = $(this).data('page');
      let link = $(this).find('a:first').attr('href');

      if (!page) {
        return false;
      }

      if (link) {
        window.history.replaceState(null, null, link);
      }

      form.find('input[name=page]').val(page);
      self.settings.append = false;
      self.formSubmit();

      return false;
    });

    $(form).on('click', self.settings.loadmore, function (e) {
      e.preventDefault();
      var page = $(this).data('page');
      form.find('input[name=page]').val(page);
      self.settings.append = true;
      self.formSubmit();
      return false;
    });

    $(form).on('click', '[type=submit]', function (e) {
      e.preventDefault();
      form.find('input[name=page]').val(1);
      self.settings.append = false;
      self.formSubmit();
      return false;
    });

    $(window).on('scroll', function () {
      var offset = form.find(self.settings.loadmore).offset();
      var tops = offset ? offset.top : 0;
      if (isNaN(tops) || tops == 0) {
        return false;
      }
      if (oldtop != tops) {
        oldtop = tops;
        var p = form.find('input[name=page]').val();
        if (p < 3) {
          ps_loaded = false;
        }
      }
      if (!ps_loaded && $(window).scrollTop() + $(window).height() > tops) {
        ps_loaded = true;
        form.find(self.settings.loadmore).click();
      }
    });
  };

  easyRender.prototype.submit = function () {
    var self = this;

    self.form.submit(function (e) {
      e.preventDefault();
      self.formSubmit();
      return false;
    });
  };

  easyRender.prototype.formSubmit = function () {
    var self = this;
    var data = self.settings.data;
    data.format = 'html';

    self.form.se;

    var fromOptions = {
      beforeSubmit: function () {
        self.beforeSubmit();
      },
      success: function (response) {
        self.onSuccess(response);
      },
      data: data,
    };

    var options = $.extend({}, this.settings, fromOptions);
    this.element.ajaxSubmit(options);

    if (self.settings.state) {
      let params = self.form.formToArray();

      let url = self.form.attr('action');
      params = params.filter(function (el) {
        let attr = el.el.attributes;
        return el.name != '_token' && el.name != 'total' && !attr.history;
      });

      url += (url.indexOf('?') >= 0 ? '&' : '?') + $.param(params);

      if (url) {
        window.history.replaceState(null, null, url);
      }
    }
  };

  easyRender.prototype.beforeSubmit = function () {
    var self = this;

    var target = self.form.find(self.settings.response_target);

    target.addClass(self.settings.backdrop);
    self.form.find(self.settings.loadmore).next('.ac-load-more-loading').show();
    self.form.find(self.settings.loadmore).remove();
    return true;
  };

  easyRender.prototype.onSuccess = function (response) {
    var self = this;

    if (self.settings.json) {
      displayNoty(response, self.form);
    }

    // check the page number
    var page = self.form.find('input[name=page]').val();
    var append = page == 1 ? false : self.settings.append;

    let fragments = response.fragments || {};

    for (const [key, value] of Object.entries(fragments)) {
      var fragment = self.form.find('[fragment=' + key + ']');

      if (append) {
        self.form.find('.ac-load-more-remove').remove();
        fragment.append(value);
      } else {
        fragment.html(value);
        var total = self.form.find('[data-total]').data('total');

        if (total == '' || total == undefined) {
          var total = self.form.find('input[name=total]').val();
        }

        self.form.find('input[name=total]').val(total);
        self.form.find(self.settings.total_target).html(total);
      }
      fragment.removeClass(self.settings.backdrop);
    }

    self.callback('onComplete', response);
  };

  easyRender.prototype.callback = function (name, res) {
    $(document).trigger('ajax:loaded');

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
    // lower case
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
  };

  $.fn.easyRender = function (options) {
    return this.each(function () {
      if (!$.data(this, pluginName)) {
        $.data(this, pluginName, new easyRender($(this), options));
      }
    });
  };
})(jQuery);
