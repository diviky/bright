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

  var Validate = function (element, options) {
    var self = this;

    this.element = element;
    self.settings = $.extend({}, $.fn.validate.defaults, options);

    if ($.metadata) {
      self.settings = $.extend({}, self.settings, element.metadata({ type: 'html5' }));
    }

    if (element.is('form')) {
      self.form = element;
    } else {
      self.form = element.parents('form:eq(0)');
    }

    self.validate();

    return self;
  };

  Validate.prototype.validator = function () {
    var self = this;
    if (!self.form.data('validator')) {
      self.form.validator(self.settings);
    }

    return self.form.data('validator');
  };

  Validate.prototype.isValid = function () {
    var self = this;
    return self.validator()?.checkValidity();
  };

  Validate.prototype.validate = function () {
    var self = this;

    self.validator();

    self.form.submit(function (e) {
      e.preventDefault();
      // client-side validation OK.
      if (!e.isDefaultPrevented()) {
        self.form.attr({
          valid: true,
        });

        return true;
      }

      self.form.attr({
        valid: false,
      });

      return false;
    });
  };

  $.fn.validate = function (options) {
    return this.each(function () {
      var self = $(this);

      var instance = self.data('validate');

      // destroy existing instance
      if (instance) {
        instance.destroy();
        self.removeData('validate');
      }

      var instance = new Validate(self, options);

      if (typeof options === 'string' && typeof instance[options] === 'function') {
        instance[options]();
      }

      self.data('validate', instance);

      return this;
    });
  };

  $.fn.validate.defaults = {
    inputEvent: 'keyup blur change', // change, blur, keyup, null
    errorInputEvent: 'keyup', // change, blur, keyup, null
    effect: 'errors',
  };
});
