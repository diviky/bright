function load_charts() {
  $(document).on('submit', '[data-toggle="chart"]', function(e) {
    e.preventDefault();

    var id = $(this).data('id');
    var url = $(this).prop('action');
    var chartfn = id + '_update';

    var params =
        {method: 'POST', url: url, dataType: 'json', data: $(this).serialize()}

    var fn = window[chartfn];

    $.ajax(params).done(function(data) {
      if (typeof fn === 'function') {
        fn(data);
      }
    });
  });

  $(document).on('click', '[data-input-val]', function(e) {
    var $this = $(this);
    var name = $this.data('input-val');
    var val = $this.attr('value');
    $this.parents('.btn-group:first').find('button').removeClass('active');
    $this.addClass('active');

    $('input[name=' + name + ']').remove();

    $('<input>', {type: 'hidden', name: name, value: val}).insertAfter($this);

    $(this).parents('form:first').submit();
  });
}
