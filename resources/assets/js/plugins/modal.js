// AMD support
(function (factory) {
  'use strict';
  if (typeof define === 'function' && define.amd) {
    // using AMD; register as anon module
    define(['jquery'], factory);
  } else {
    // no AMD; invoke directly
    factory(typeof jQuery != 'undefined' ? jQuery : window.Zepto);
  }
})(function ($) {
  'use strict';
  var easyModal = function (el, options) {
    var self = this;
    this.el = el;
    this.settings = $.extend({}, $.fn.easyModal.defaults, options);

    if ($.metadata && el) {
      this.settings = $.extend({}, this.settings, el.metadata({ type: 'html5' }));
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
    let self = this;
    self.settings.el = '#' + self.settings.id;

    let el = self.el;

    if (self.settings.content === null) {
      self.settings.content = self.ajaxContent;
    }

    var url = self.settings.url || (el && el.attr('href'));

    if ((url == '' || url == undefined) && self.settings.event !== 'ready') {
      return false;
    }

    if (self.settings.cache) {
      self.settings.id = 'modal_' + btoa(url).replace('=');
      self.settings.el = '#' + self.settings.id;

      // check already exists
      let content = $(self.settings.el);
      if (content && content.length > 0) {
        self.enable(content);
        return this;
      }
    }

    $(self.settings.el).remove();
    $('.modal-backdrop').remove();

    self.start(self.el);
    let content = self.settings.content;

    if (typeof content === 'function') {
      content = content(self, self.el);
    }

    self.content(content).enable();

    return this;
  };

  easyModal.prototype.set = function (options) {
    this.settings = $.extend({}, this.settings, options);

    return this;
  };

  easyModal.prototype.ajaxContent = function (self, el) {
    var url = self.settings.url || (el && el.attr('href'));

    if (url == '' || url == undefined) {
      return false;
    }

    var data = self.settings.data || {};
    data = Object.assign({ format: 'html' }, data);

    if (self.settings.history) {
      window.history.pushState({ id: url }, url, url);
    }

    //add loading
    $.ajax({
      url: url,
      data: data,
      cache: false,
      method: self.settings.method || 'GET',
      headers: self.settings.headers || {},
    }).then(
      function (response, status, xhr) {
        var ct = xhr.getResponseHeader('content-type') || '';
        if (ct.indexOf('html') > -1) {
          self.content(response).enable();
        } else if (ct.indexOf('json') > -1) {
          let fragments = response.fragments;
          for (const [key, value] of Object.entries(fragments)) {
            //var fragment = self.form.find('[fragment=' + key + ']');
            self.content(value).enable();
          }
        }
      },
      function (xhr) {
        if (xhr.status === 401) {
          self.content('You are not allowed to access this. Please login').enable();
        } else {
          self.content(xhr.responseText).enable();
        }
      }
    );
  };

  easyModal.prototype.start = function (el) {
    var self = this;
    var settings = self.settings;
    var title = settings.title;

    if (settings.layout !== 'modal') {
      return self;
    }

    if (settings.size == 'small') {
      settings.styles = settings.styles + ' ' + 'modal-sm';
    }

    if (settings.size == 'medium') {
      settings.styles = settings.styles + ' ' + 'modal-md';
    }

    if (settings.size == 'large') {
      settings.styles = settings.styles + ' ' + 'modal-lg';
    }

    if (settings.size == 'full') {
      settings.styles = settings.styles + ' ' + 'modal-full-width';
    }

    if (settings.position == 'center') {
      settings.styles = settings.styles + ' ' + 'modal-dialog-centered';
    }

    if (settings.position == 'right') {
      settings.styles = settings.styles + ' ' + 'modal-dialog-right';
    }

    if (settings.position == 'left') {
      settings.styles = settings.styles + ' ' + 'modal-dialog-left';
    }

    if (settings.scrollable) {
      settings.styles = settings.styles + ' ' + 'modal-dialog-scrollable';
    }

    var html = '<div class="modal modal-blur fade modal-box ' + settings.styles + '" id="' + settings.id + '">';
    html += '   <div class="modal-dialog animated ' + settings.styles + ' ' + settings.animation + '">';
    html += '       <div class="modal-content">';

    if (typeof title === 'function') {
      title = title(self, el);
    }

    if (settings.close || title) {
      if (title) {
        html += ' <div class="modal-header">';
        html += ' <h5 class="modal-title">' + title + '</h5>';
      }

      if (settings.close) {
        html +=
          '<button type="button" class="close modal-close" data-dismiss="modal"  data-bs-dismiss="modal"></button>';
      }

      if (title) {
        html += '           </div>';
      }
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

    if (self.settings.layout == 'modal') {
      $(self.settings.el + ' .modal-body').html(html);
    } else {
      $(self.settings.container).html(html);
    }

    return this;
  };

  easyModal.prototype.enable = function () {
    var self = this;
    let el = self.settings.el;

    $(el).modal({
      keyboard: this.settings.keyboard,
      backdrop: this.settings.backdrop,
    });

    $(el).modal('show');

    $(el)
      .off('hidden.bs.modal')
      .on('hidden.bs.modal', function () {
        $(this).data('bs.modal', null);
        $('.modal-backdrop').remove();
        $('.is-invalid-feedback').remove();

        if (self.settings.history) {
          window.history.back();
        }

        if (self.settings.clean) {
          $(el).remove();
        }
      });

    $(document).trigger('ajax:loaded');
    $(document).trigger('ajax:modal:loaded');

    return this;
  };

  $.fn.easyModalShow = function (options) {
    return new easyModal(null, options);
  };

  $.fn.easyModalHide = function () {
    setTimeout(function () {
      $('.modal').modal('hide');
      $('body').css({ overflow: '', 'padding-right': '' }); //global fix for freezed screen
      $('.modal-backdrop').remove();
    }, 500);
  };

  $.fn.easyModal = function (options) {
    return this.each(function () {
      var self = $(this);

      var instance = self.data('easymodal');

      // destroy existing instance
      if (instance) {
        self.removeData('easymodal');
      }

      var instance = new easyModal(self, options);

      if (typeof options === 'string' && typeof instance[options] === 'function') {
        instance[options]();
      }

      self.data('easymodal', instance);

      return this;
    });
  };

  $.fn.easyModal.defaults = {
    title: function (api, el) {
      return el && el.data('title');
    },
    event: 'click',
    backdrop: 'static', //'false|true|static',
    close: true,
    keyboard: false,
    id: 'bmodel',
    styles: '',
    size: '',
    method: 'GET',
    position: 'right',
    clean: false,
    layout: 'modal',
    cache: false,
    scrollable: false,
    animation: 'bounceInRight',
    content: null,
    url: null,
    history: false,
    data: {},
    headers: {},
    container: 'body',
  };
});
