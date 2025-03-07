/**
 * @license
 * jQuery Tools Validator @VERSION - HTML5 is here. Now use it.
 *
 * NO COPYRIGHTS OR LICENSES. DO WHAT YOU LIKE.
 *
 * http://flowplayer.org/tools/form/validator/
 *
 * Since: Mar 2010
 * Date: @DATE
 */
/*jslint evil: true */
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

  $.tools = $.tools || { version: '@VERSION' };

  // globals
  var typeRe = /\[type=([a-z]+)\]/,
    numRe = /^-?[0-9]*(\.[0-9]+)?$/,
    dateInput = $.tools.dateinput,
    // http://net.tutsplus.com/tutorials/other/8-regular-expressions-you-should-know/
    emailRe = /^([a-z0-9_\.\-\+]+)@([\da-z\.\-]+)\.([a-z\.]{2,15})$/i,
    urlRe = /^(https?:\/\/)?[\da-z\.\-]+\.[a-z\.]{2,6}[#&+_\?\/\w \W\^\(\)\.\-=]*$/i,
    v;

  v = $.tools.validator = {
    conf: {
      grouped: false, // show all error messages at once inside the container
      effect: 'default', // show/hide effect for error message. only 'default' is built-in
      errorClass: 'is-invalid', // input field class name in case of validation error

      // when to check for validity?
      inputEvent: null, // change, blur, keyup, null
      errorInputEvent: 'keyup', // change, blur, keyup, null
      formEvent: 'submit', // submit, null

      lang: 'en', // default language for error messages
      message: '<div><em/></div>',
      messageAttr: 'data-message', // name of the attribute for overridden error message
      messageClass: 'is-invalid-feedback', // error message element's class name
      offset: [-5, 40],
      position: 'top left',
      singleError: false, // validate all inputs at once
      speed: 'normal', // message's fade-in speed
    },

    /* The Error Messages */
    messages: {
      '*': { en: 'This field should not be left blank.' },
    },

    localize: function (lang, messages) {
      $.each(messages, function (key, msg) {
        v.messages[key] = v.messages[key] || {};
        v.messages[key][lang] = msg;
      });
    },

    localizeFn: function (key, messages) {
      v.messages[key] = v.messages[key] || {};
      $.extend(v.messages[key], messages);
    },

    /**
     * Adds a new validator
     */
    fn: function (matcher, msg, fn) {
      // no message supplied
      if ($.isFunction(msg)) {
        fn = msg;

        // message(s) on second argument
      } else {
        if (typeof msg == 'string') {
          msg = { en: msg };
        }
        this.messages[matcher.key || matcher] = msg;
      }

      // check for "[type=xxx]" (not supported by jQuery)
      var test = typeRe.exec(matcher);
      if (test) {
        matcher = isType(test[1]);
      }

      // add validator to the arsenal
      fns.push([matcher, fn]);
    },

    /* Add new show/hide effect */
    addEffect: function (name, showFn, closeFn) {
      effects[name] = [showFn, closeFn];
    },
  };

  function bytesToMB(bytes) {
    if (bytes > 1073741824) return (bytes / 1024 / 1024 / 1024).toFixed(2) + ' GB';
    if (bytes > 1048576) return (bytes / 1024 / 1024).toFixed(2) + ' MB';
    if (bytes > 1024) return (bytes / 1024).toFixed(2) + ' KB';
    if (bytes < 1024) return bytes + ' ' + 'Bytes';
  }

  /* calculate error message position relative to the input */
  function getPosition(trigger, el, conf) {
    // Get the first element in the selector set
    el = $(el).first() || el;

    // get origin top/left position
    var top = trigger.offset().top,
      left = trigger.offset().left,
      pos = conf.position.split(/,?\s+/),
      y = pos[0],
      x = pos[1];

    top -= el.outerHeight() - conf.offset[0];
    left += trigger.outerWidth() + conf.offset[1];

    // iPad position fix
    if (/iPad/i.test(navigator.userAgent)) {
      top -= $(window).scrollTop();
    }

    // adjust Y
    var height = el.outerHeight() + trigger.outerHeight();
    if (y == 'center') {
      top += height / 2;
    }
    if (y == 'bottom') {
      top += height;
    }

    // adjust X
    var width = trigger.outerWidth();
    if (x == 'center') {
      left -= (width + el.outerWidth()) / 2;
    }
    if (x == 'left') {
      left -= width;
    }

    return { top: top, left: left };
  }

  function e(s) {
    var argv = Array.apply(null, arguments).slice(1);
    if ($.isArray(argv)) {
      $.each(argv, function (i) {
        s = s.replace(this, argv[i]);
      });
    }
    return s;
  }

  // $.is("[type=xxx]") or $.filter("[type=xxx]") not working in jQuery 1.3.2 or 1.4.2
  function isType(type) {
    function fn() {
      return this.getAttribute('type') == type;
    }
    fn.key = '[type="' + type + '"]';
    return fn;
  }

  var fns = [],
    effects = {
      default: [
        // show errors function
        function (errs) {
          var conf = this.getConf();

          // loop errors
          $.each(errs, function (i, err) {
            // add error class
            var input = err.input;
            input.addClass(conf.errorClass);

            // get handle to the error container
            var msg = input.data('msg.el');

            // create it if not present
            if (!msg) {
              msg = $(conf.message).addClass(conf.messageClass).appendTo(document.body);
              input.data('msg.el', msg);
            }

            input.on('remove hide', function (e) {
              var el = $(this).data('msg.el');
              if (el) {
                el.fadeOut();
              }
            });

            // clear the container
            msg.css({ visibility: 'hidden' }).find('p').remove();

            // populate messages
            $.each(err.messages, function (i, m) {
              $('<p/>').html(m).appendTo(msg);
            });

            // make sure the width is not full body width so it can be positioned correctly
            if (msg.outerWidth() == msg.parent().width()) {
              msg.add(msg.find('p')).css({ display: 'inline' });
            }

            // insert into correct position (relative to the field)
            var pos = getPosition(input, msg, conf);

            msg.css({ visibility: 'visible', position: 'absolute', top: pos.top, left: pos.left }).fadeIn(conf.speed);
          });

          // hide errors function
        },
        function (inputs) {
          var conf = this.getConf();
          inputs.removeClass(conf.errorClass).each(function () {
            var msg = $(this).data('msg.el');
            if (msg) {
              msg.css({ visibility: 'hidden' });
            }
          });
        },
      ],
    };

  /* sperial selectors */
  $.each('email,url,number'.split(','), function (i, key) {
    $.expr[':'][key] = function (el) {
      return el.getAttribute('type') === key;
    };
  });

  /*
        oninvalid() jQuery plugin.
        Usage: $("input:eq(2)").oninvalid(function() { ... });
    */
  $.fn.oninvalid = function (fn) {
    return this[fn ? 'on' : 'trigger']('OI', fn);
  };

  /******* built-in HTML5 standard validators *********/

  v.fn(':email,[data-type=email]', 'Please enter a valid email address', function (el, v) {
    return !v || emailRe.test(v);
  });

  v.fn(':url,[data-type=url]', 'Please enter a valid URL', function (el, v) {
    return !v || urlRe.test(v);
  });

  v.fn(':number,[data-type=number]', 'Please enter a numeric value.', function (el, v) {
    return !v || numRe.test(v);
  });

  v.fn("[data-type='mobile'], [type=mobile]", 'Please enter a valid mobile number.', function (el, v) {
    return !v || mobRe.test(v);
  });

  v.fn('[max]', e('Please enter a value no larger than $1'), function (el, v) {
    // skip empty values and dateinputs
    if (v === '' || (dateInput && el.is(':date'))) {
      return true;
    }

    var max = el.attr('max');
    return parseFloat(v) <= parseFloat(max) ? true : [max];
  });

  v.fn('[min]', e('Please enter a value of at least $1'), function (el, v) {
    // skip empty values and dateinputs
    if (v === '' || (dateInput && el.is(':date'))) {
      return true;
    }

    var min = el.attr('min');
    return parseFloat(v) >= parseFloat(min) ? true : [min];
  });

  v.fn('[length]', e('Please provide $1 character(s)'), function (el, v) {
    var min = el.attr('length');
    return !v || (v.length >= min ? true : [min]);
  });

  v.fn('[minlength]', e('Please provide at least $1 character(s)'), function (el, v) {
    var min = el.attr('minlength');
    return !v || (v.length >= min ? true : [min]);
  });

  v.fn('[maxlength]', e('Maximum $1 character allowed'), function (el, v) {
    var max = el.attr('maxlength');
    return !v || (v.length <= max ? true : [max]);
  });

  v.fn('[minwords]', e('Please provide at least $1 word(s)'), function (el, v) {
    if (v === '') {
      return true;
    }
    var min = el.attr('minwords');
    var wordcount = v.split(/\b[\s,\.-:;]*/).length;

    return wordcount >= min ? true : [min];
  });

  v.fn('[maxwords]', e('Maximum $1 word(s) allowed'), function (el, v) {
    if (v === '') {
      return true;
    }
    var max = el.attr('maxwords');
    var wordcount = v.split(/\b[\s,\.-:;]*/).length;

    return wordcount <= max ? true : [max];
  });

  v.fn('[required]', 'Please complete this mandatory field.', function (el, v) {
    v = $.trim(v);
    // if (el.attr('placeholder') != '' && el.attr('placeholder') != undefined) {
    //   if (el.attr('placeholder') == v) {
    //     return false;
    //   }
    // }

    if (el.attr('data-type') == 'group') {
      return true;
    }

    if (!el.is(':visible') && !el.hasClass('validatehidden')) {
      return true;
    }

    if (el.is(':checkbox')) {
      return el.is(':checked');
    }

    return !!v;
  });

  v.fn('[pattern]', function (el, v) {
    if (v === '') {
      return true;
    }

    var regex = el.attr('pattern');
    if (!regex) {
      return true;
    }

    if (v.charAt(0) == '/') {
      return new RegExp(regex).test(v);
    } else {
      try {
        return eval(regex).test(v);
      } catch (err) {
        return false;
      }
    }
  });

  v.fn(':radio[required]', 'Please select an option.', function (el) {
    var checked = false;
    var els = $("[name='" + el.attr('name') + "']").each(function (i, el) {
      if ($(el).is(':checked')) {
        checked = true;
      }
    });
    return checked ? true : false;
  });

  // Regular Expression to test whether the value is valid
  v.fn('[type=time]', 'Please supply a valid time', function (el, v) {
    return !v || /^\d\d:\d\d$/.test(v);
  });

  v.fn('[data-equals]', e('Value not equal with the $1 field'), function (el, v) {
    if (v === '') {
      return true;
    }
    var name = el.attr('data-equals'),
      field = this.getInputs().filter('[name=' + name + ']');
    return el.val() == field.val() ? true : [name];
  });

  v.fn('[data-notequals]', e('Value should not be equal to the $1 field'), function (el, v) {
    if (v === '') {
      return true;
    }
    var name = el.attr('data-notequals'),
      field = this.getInputs().filter('[name=' + name + ']');
    return el.val() != field.val() ? true : [name];
  });

  v.fn('[data-type=file],[type=file]', function (el, v) {
    if (v === '') {
      return true;
    }
    var ext = el.val().split('.').pop().toLowerCase();

    var allowed = el.data('accept');
    if (!allowed || allowed == undefined) return true;
    var allow = allowed.split(',');
    if ($.inArray(ext, allow) == -1) {
      return e('Please enter valid extensions $1', allowed);
    }
    var allowed_size = el.data('allowed-size');
    if (!allowed_size || allowed_size == undefined || allowed_size == 0) {
      return true;
    }
    var fileInput = el[0];
    var size = parseInt(fileInput.files[0].fileSize);

    if (size > allowed_size) {
      return e('File size is : $1 . Allowed size is $2', bytesToMB(size), bytesToMB(allowed_size));
    }

    return true;
  });

  v.fn('[data-type=group]', function (el, v) {
    if (el.is(':checkbox')) {
      var name = el.attr('name'),
        field = this.getInputs().filter('[name=' + name + ']:checked');
    } else {
      var field = el.val();
    }

    if (!field) return true;

    var len1 = el.attr('data-length');
    var max = el.attr('data-max');
    var min = el.attr('data-min');

    if (len1) {
      if (field.length != parseInt(len1)) return e('You have to select $1 value(s)!', len1);
    }

    if (max) {
      if (field.length > parseInt(max)) return e('You can select maximum $1 value(s)', max);
    }

    if (min) {
      if (field.length < parseInt(min)) return e('You have to select minimum $1 value(s)', min);
    }
    return true;
  });

  function Validator(inputs, form, conf) {
    // private variables
    var self = this,
      fire = form.add(self);

    // make sure there are input fields available
    inputs = inputs.not(':button, :image, :reset, :submit');

    // Prevent default Firefox validation
    form.attr('novalidate', 'novalidate');

    // utility function
    function pushMessage(to, matcher, returnValue) {
      // only one message allowed
      if (!conf.grouped && to.length) {
        return;
      }

      // the error message
      var msg;

      // substitutions are returned
      if (returnValue === false || $.isArray(returnValue)) {
        msg = v.messages[matcher.key || matcher] || v.messages['*'];
        msg = msg[conf.lang] || v.messages['*'].en;

        // substitution
        var matches = msg.match(/\$\d/g);

        if (matches && $.isArray(returnValue)) {
          $.each(matches, function (i) {
            msg = msg.replace(this, returnValue[i]);
          });
        }

        // error message is returned directly
      } else {
        msg = returnValue[conf.lang] || returnValue;
      }

      to.push(msg);
    }

    // API methods
    $.extend(self, {
      getConf: function () {
        return conf;
      },

      getForm: function () {
        return form;
      },

      getInputs: function () {
        return inputs;
      },

      reflow: function () {
        inputs.each(function () {
          var input = $(this),
            msg = input.data('msg.el');

          if (msg) {
            var pos = getPosition(input, msg, conf);
            msg.css({ top: pos.top, left: pos.left });
          }
        });
        return self;
      },

      /* @param e - for internal use only */
      invalidate: function (errs, e) {
        // errors are given manually: { fieldName1: 'message1', fieldName2: 'message2' }
        if (!e) {
          var errors = [];
          $.each(errs, function (key, val) {
            var input = inputs.filter("[name='" + key + "']");
            if (input.length) {
              // trigger HTML5 ininvalid event
              input.trigger('OI', [val]);
              errors.push({ input: input, messages: [val] });
            }
          });

          errs = errors;
          e = $.Event();
        }

        // onFail callback
        e.type = 'onFail';
        fire.trigger(e, [errs]);

        // call the effect
        if (!e.isDefaultPrevented()) {
          effects[conf.effect][0].call(self, errs, e);
        }

        return self;
      },

      reset: function (els) {
        els = els || inputs;
        els
          .removeClass(conf.errorClass)
          .each(function () {
            var msg = $(this).data('msg.el');
            if (msg) {
              msg.remove();
              $(this).data('msg.el', null);
            }
          })
          .off(conf.errorInputEvent + '.v' || '');

        var eff = effects[conf.effect];
        eff[1].call(self, els, e);

        return self;
      },

      destroy: function () {
        form.off(conf.formEvent + '.V reset.V');
        inputs.off(conf.inputEvent + '.V change.V');
        return self.reset();
      },

      //{{{  checkValidity() - flesh and bone of this tool

      /* @returns boolean */
      checkValidity: function (els, e) {
        els = els || inputs;
        els = els.not(':disabled');

        if (!els.length) {
          return true;
        }

        e = e || $.Event();

        // onBeforeValidate
        e.type = 'onBeforeValidate';
        fire.trigger(e, [els]);
        if (e.isDefaultPrevented()) {
          return e.result;
        }

        // container for errors
        var errs = [];

        // loop trough the inputs
        els.each(function () {
          // field and it's error message container
          var msgs = [],
            el = $(this).data('messages', msgs),
            event = dateInput && el.is(':date') ? 'onHide.v' : conf.errorInputEvent + '.v';

          // cleanup previous validation event
          el.off(event);

          // loop all validator functions
          $.each(fns, function () {
            var fn = this,
              match = fn[0];

            // match found
            if (el.filter(match).length) {
              // execute a validator function
              var returnValue = fn[1].call(self, el, el.val());

              // validation failed. multiple substitutions can be returned with an array
              if (returnValue !== true) {
                // onBeforeFail
                e.type = 'onBeforeFail';
                fire.trigger(e, [el, match]);
                if (e.isDefaultPrevented()) {
                  return false;
                }

                // overridden custom message
                var msg = el.attr(conf.messageAttr) || el.attr('message');
                if (msg && el.val()) {
                  msgs = [msg];
                  return false;
                } else {
                  pushMessage(msgs, match, returnValue);
                }
              }
            }
          });

          if (msgs.length) {
            errs.push({ input: el, messages: msgs });

            // trigger HTML5 ininvalid event
            el.trigger('OI', [msgs]);

            // begin validating upon error event type (such as keyup)
            if (conf.errorInputEvent) {
              el.on(event, function (e) {
                self.checkValidity(el, e);
              });
            }
          }

          if (conf.singleError && errs.length) {
            return false;
          }
        });

        // validation done. now check that we have a proper effect at hand
        var eff = effects[conf.effect];
        if (!eff) {
          throw 'Validator: cannot find effect "' + conf.effect + '"';
        }

        // errors found
        if (errs.length) {
          self.invalidate(errs, e);
          return false;

          // no errors
        } else {
          // call the effect
          eff[1].call(self, els, e);

          // onSuccess callback
          e.type = 'onSuccess';
          fire.trigger(e, [els]);

          els.off(conf.errorInputEvent + '.v');
        }

        return true;
      },
      //}}}
    });

    // callbacks
    $.each('onBeforeValidate,onBeforeFail,onFail,onSuccess'.split(','), function (i, name) {
      // configuration
      if (typeof conf[name] === 'function') {
        $(self).on(name, conf[name]);
      }

      // API methods
      self[name] = function (fn) {
        if (fn) {
          $(self).on(name, fn);
        }
        return self;
      };
    });

    // form validation
    if (conf.formEvent) {
      form.on(conf.formEvent + '.V', function (e) {
        if (!self.checkValidity(null, e)) {
          return e.preventDefault();
        }
        // Reset event type and target
        e.target = form;
        e.type = conf.formEvent;
      });
    }

    // form reset
    form.on('reset.V', function () {
      self.reset();
    });

    // disable browser's default validation mechanism
    if (inputs[0] && inputs[0].validity) {
      inputs.each(function () {
        this.oninvalid = function () {
          return false;
        };
      });
    }

    // Web Forms 2.0 compatibility
    if (form[0]) {
      form[0].checkValidity = self.checkValidity;
    }

    // input validation
    if (conf.inputEvent) {
      inputs.on(conf.inputEvent + '.V', function (e) {
        self.checkValidity($(this), e);
      });
    }

    // checkboxes and selects are checked separately
    inputs
      .filter(':checkbox, select')
      .filter('[required]')
      .on('change.V', function (e) {
        var el = $(this);
        if (this.checked || (el.is('select') && $(this).val())) {
          effects[conf.effect][1].call(self, el, e);
        }
      });

    // get radio groups by name
    inputs.filter(':radio[required]').on('change.V', function (e) {
      var els = $("[name='" + $(e.srcElement).attr('name') + "']");
      if (els != null && els.length != 0) {
        self.checkValidity(els, e);
      }
    });

    // reposition tooltips when window is resized
    $(window).resize(function () {
      self.reflow();
    });
  }

  // jQuery plugin initialization
  $.fn.validator = function (conf) {
    var instance = this.data('validator');

    // destroy existing instance
    if (instance) {
      instance.destroy();
      this.removeData('validator');
    }

    // configuration
    conf = $.extend(true, {}, v.conf, conf);

    // selector is a form
    if (this.is('form')) {
      return this.each(function () {
        var form = $(this);
        instance = new Validator(form.find(':input'), form, conf);
        form.data('validator', instance);

        return this;
      });
    } else {
      instance = new Validator(this, this.eq(0).closest('form'), conf);
      this.data('validator', instance);

      return this;
    }
  };
});
