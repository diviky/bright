jQuery(document).ready(function ($) {
  // adds an effect called "image" to the validator
  $.tools.validator.addEffect(
    'feedback',
    function (errors, event) {
      $.each(errors, function (index, error) {
        error.input.removeClass('is-valid').addClass('is-invalid');
        error.input.parents('.form-group:first').find('.invalid-feedback').remove();
        error.input
          .parents('.form-group:first')
          .append('<div class="invalid-feedback show">' + error.messages[0] + '</div>');
      });
    },
    function (inputs) {
      var conf = this.getConf();
      inputs.removeClass(conf.errorClass).each(function () {
        $(this).removeClass('is-invalid').addClass('is-valid');
        $(this).parents('.form-group:first').find('.invalid-feedback').remove();
      });
    }
  );

  // adds an effect called "image" to the validator
  $.tools.validator.addEffect(
    'errors',
    function (errors, event) {
      $.each(errors, function (index, error) {
        error.input.removeClass('is-valid').addClass('is-invalid');
        error.input.parents('.form-group:first').find('.invalid-feedback').remove();
        error.input
          .parents('.form-group:first')
          .append('<div class="invalid-feedback show">' + error.messages[0] + '</div>');
      });
    },
    function (inputs) {
      var conf = this.getConf();
      inputs.removeClass(conf.errorClass).each(function () {
        $(this).removeClass('is-invalid');
        $(this).parents('.form-group:first').find('.invalid-feedback').remove();
      });
    }
  );

  $.tools.validator.addEffect(
    'state',
    function (errors, event) {
      $.each(errors, function (index, error) {
        error.input.removeClass('is-valid').addClass('is-invalid');
      });
    },
    function (inputs) {
      var conf = this.getConf();
      inputs.removeClass(conf.errorClass).each(function () {
        $(this).removeClass('is-invalid').addClass('is-valid');
      });
    }
  );

  $.tools.validator.addEffect('noty', function (errors, event) {
    $.each(errors, function (index, error) {
      notify({
        type: 'error',
        text: error.messages[0],
      });
      return false;
    });
  });
});
