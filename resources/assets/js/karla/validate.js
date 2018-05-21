;(function($) {
    $.fn.validate = function(options) {
        var defaults = {
            message: '<div><em/><div/>',
            grouped: false, // show all error messages at once inside the container
            inputEvent: 'keyup blur', // change, blur, keyup, null
            errorInputEvent: 'keyup', // change, blur, keyup, null
            //effect        : 'image',
            formEvent: 'submit' // submit, null
        }
        options = $.extend(defaults, options);
        return this.each(function() {
            var obj = $(this);
            var opt = $.metadata ? $.extend({}, options, obj.metadata({type:'html5'})) : options; // metadata plugin support (applied on link element)
            if (obj.is("form")) {
                var form = this;
            } else {
                var form = $(this).closest('form');
            }
            return $.fn.validate.isValid(form, opt);
        });
    };
    $.fn.validate.isValid = function(form, options) {
        $(form).validator(options).submit(function(e) {
            if (!e.isDefaultPrevented()) {
                $(form).attr({
                    valid: true
                });
                e.preventDefault();
                return true;
            }
            $(form).attr({
                valid: false
            });
            return false;
        });
    };
})(jQuery);

jQuery(document).ready(function($) {
    // adds an effect called "image" to the validator
    $.tools.validator.addEffect("image", function(errors, event) {
        $.each(errors, function(index, error) {
            error.input.next('.ac-input-msg').remove();
            error.input.after('<span class="ui-input-err ac-input-msg" title="' + error.messages[0] + '">&nbsp;</span>');
        });
    }, function(inputs) {
        var conf = this.getConf();
        inputs.removeClass(conf.errorClass).each(function() {
            $(this).next('.ac-input-msg').remove();
            $(this).after('<span class="ui-input-ok ac-input-msg">&nbsp;</span>');
        });
    });
    $.tools.validator.addEffect("noty", function(errors, event) {
        $.each(errors, function(index, error) {
            noty({
                type: 'error',
                text: error.messages[0]
            });
            return false;
        });
    });

});
