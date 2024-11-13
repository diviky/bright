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
  $.each(['show', 'hide', 'fadeOut', 'fadeIn'], function (i, ev) {
    var el = $.fn[ev];
    $.fn[ev] = function () {
      var result = el.apply(this, arguments);
      result.promise().done(function () {
        this.triggerHandler(ev, [result]);
      });
      return result;
    };
  });
});

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
  $.each([':show', ':hide', ':fadeOut', ':fadeIn'], function () {
    var _oldFn = $.fn[this];
    $.fn[this] = function () {
      var hidden = this.find(':hidden').add(this.filter(':hidden'));
      var visible = this.find(':visible').add(this.filter(':visible'));
      var result = _oldFn.apply(this, arguments);
      hidden.filter(':visible').each(function () {
        $(this).triggerHandler('show');
      });
      visible.filter(':hidden').each(function () {
        $(this).triggerHandler('hide');
      });
      return result;
    };
  });
});
