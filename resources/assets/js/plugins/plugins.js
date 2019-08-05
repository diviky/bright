$.fn.selectText = function () {
    return $(this).each(function (index, el) {
        if (document.selection) {
            var range = document.body.createTextRange();
            range.moveToElementText(el);
            range.select();
        } else if (window.getSelection) {
            var range = document.createRange();
            range.selectNode(el);
            window.getSelection().addRange(range);
        }
    });
}

$(document).ready(function () {
    $(document).on('click', '[data-selection]', function (e) {
        $(this).selectText();
    });
});

; (function ($) {
    $.event.special.destroyed = {
        remove: function (o) {
            if (o.handler && o.type !== 'destroyed')
                o.handler()
        }
    }
})(jQuery);

$.cleanData = (function (orig) {
    return function (elems) {
        var events, elem, i;
        for (i = 0; (elem = elems[i]) != null; i++) {
            try {
                // Only trigger remove when necessary to save time
                events = $._data(elem, "events");
                if (events && events.remove) {
                    $(elem).triggerHandler("remove");
                }

                // Http://bugs.jquery.com/ticket/8235
            } catch (e) { }
        }
        orig(elems);
    };
})($.cleanData);

; (function ($) {
    $.each(['show', 'hide', 'fadeOut', 'fadeIn'], function (i, ev) {
        var el = $.fn[ev];
        $.fn[ev] = function () {
            var result = el.apply(this, arguments);
            result.promise().done(function () {
                this.triggerHandler(ev, [result]);
            })
            return result;
        };
    });
})(jQuery);

; (function ($) {
    $.each([":show", ":hide", ':fadeOut', ':fadeIn'], function () {
        var _oldFn = $.fn[this];
        $.fn[this] = function () {
            var hidden = this.find(":hidden").add(this.filter(":hidden"));
            var visible = this.find(":visible").add(this.filter(":visible"));
            var result = _oldFn.apply(this, arguments);
            hidden.filter(":visible").each(function () {
                $(this).triggerHandler("show");
            });
            visible.filter(":hidden").each(function () {
                $(this).triggerHandler("hide");
            });
            return result;
        }
    });
})(jQuery);
