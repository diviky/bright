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

  $.fn.validate = function (options) {
    var defaults = {
      message: '<div><em/><div/>',
      grouped: false, // show all error messages at once inside the container
      inputEvent: 'keyup blur', // change, blur, keyup, null
      errorInputEvent: 'keyup', // change, blur, keyup, null
      //effect        : 'image',
      formEvent: 'submit', // submit, null
    };
    options = $.extend(defaults, options);
    return this.each(function () {
      var obj = $(this);
      var opt = $.metadata ? $.extend({}, options, obj.metadata({ type: 'html5' })) : options; // metadata plugin support (applied on link element)
      if (obj.is('form')) {
        var form = obj;
      } else {
        var form = obj.closest('form');
      }
      return $.fn.validate.isValid(form, opt);
    });
  };

  $.fn.validate.isValid = function (form, options) {
    form.validator(options).submit(function (e) {
      if (!e.isDefaultPrevented()) {
        $(form).attr({
          valid: true,
        });
        e.preventDefault();
        return true;
      }
      $(form).attr({
        valid: false,
      });
      return false;
    });
  };
});
