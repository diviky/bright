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

  $.extend({
    metadata: {
      defaults: {
        type: 'class',
        name: 'metadata',
        cre: /({.*})/,
        single: 'metadata',
      },
      setType: function (type, name) {
        this.defaults.type = type;
        this.defaults.name = name;
      },
      get: function (elem, opts) {
        var settings = $.extend({}, this.defaults, opts);
        // check for empty string in single property
        if (!settings.single.length) settings.single = 'metadata';

        var data = '{}';

        var getData = function (data) {
          if (typeof data != 'string') return data;

          if (data.indexOf('{') < 0) {
            data = eval('(' + data + ')');
          }
        };

        var getObject = function (data) {
          if (typeof data != 'string') return data;

          if (data.indexOf('{') < 0) {
            data = '{' + data + '}';
          }
          data = eval('(' + data + ')');
          return data;
        };

        if (settings.type == 'html5') {
          return $(elem).data();
        } else {
          if (settings.type == 'class') {
            var m = settings.cre.exec(elem.className);
            if (m) data = m[1];
          } else if (settings.type == 'elem') {
            if (!elem.getElementsByTagName) return;
            var e = elem.getElementsByTagName(settings.name);
            if (e.length) data = $.trim(e[0].innerHTML);
          } else if (elem.getAttribute != undefined) {
            var attr = elem.getAttribute(settings.name);
            if (attr) data = attr;
          }
          var object = getObject(data.indexOf('{') < 0 ? '{' + data + '}' : data);
        }

        return object;
      },
    },
  });

  /**
   * Returns the metadata object for the first member of the jQuery object.
   *
   * @name metadata
   * @descr Returns element's metadata object
   * @param Object opts An object contianing settings to override the defaults
   * @type jQuery
   * @cat Plugins/Metadata
   */
  $.fn.metadata = function (opts) {
    return $.metadata.get(this[0], opts);
  };
});
