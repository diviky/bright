function load_dates() {
  $('[data-datetime]').datetimepicker({
    // format: 'dddd, MMM DD, YYYY h:m:s A',
    icons: {
      time: 'fa fa-clock-o',
      date: 'fa fa-calendar',
      up: 'fe fe-arrow-up',
      down: 'fe fe-arrow-down',
      previous: 'fe fe-chevron-left',
      next: 'fe fe-chevron-right',
      today: 'fe fe-calendar',
      clear: 'fe fe-trash',
      close: 'fe fe-remove'
    }
  });


  $('[data-time]').datetimepicker({
    format: 'LT',
    icons: {
      time: 'fa fa-clock-o',
      date: 'fa fa-calendar',
      up: 'fe fe-arrow-up',
      down: 'fe fe-arrow-down',
      previous: 'fe fe-chevron-left',
      next: 'fe fe-chevron-right',
      today: 'fe fe-calendar',
      clear: 'fe fe-trash',
      close: 'fe fe-remove'
    }
  });

  $('[data-datepick]').datetimepicker({
    format: 'dddd, MMM DD, YYYY',
    icons: {
      time: 'fa fa-clock-o',
      date: 'fa fa-calendar',
      up: 'fe fe-arrow-up',
      down: 'fe fe-arrow-down',
      previous: 'fe fe-chevron-left',
      next: 'fe fe-chevron-right',
      today: 'fe fe-calendar',
      clear: 'fe fe-trash',
      close: 'fe fe-remove'
    }
  });

  $('[data-datepicker]').datetimepicker({
    format: 'dddd, MMM DD, YYYY',
    icons: {
      time: 'fa fa-clock-o',
      date: 'fa fa-calendar',
      up: 'fe fe-arrow-up',
      down: 'fe fe-arrow-down',
      previous: 'fe fe-chevron-left',
      next: 'fe fe-chevron-right',
      today: 'fe fe-calendar',
      clear: 'fe fe-trash',
      close: 'fe fe-remove'
    }
  });

  $('[data-date]').datetimepicker({
    format: 'dddd, MMM DD, YYYY',
    icons: {
      time: 'fa fa-clock-o',
      date: 'fa fa-calendar',
      up: 'fe fe-arrow-up',
      down: 'fe fe-arrow-down',
      previous: 'fe fe-chevron-left',
      next: 'fe fe-chevron-right',
      today: 'fe fe-calendar',
      clear: 'fe fe-trash',
      close: 'fe fe-remove'
    }
  });

  $('[data-stepping-time]').datetimepicker({
    format: 'LT',
    stepping: 30,
    icons: {
      time: 'fa fa-clock-o',
      date: 'fa fa-calendar',
      up: 'fe fe-arrow-up',
      down: 'fe fe-arrow-down',
      previous: 'fe fe-chevron-left',
      next: 'fe fe-chevron-right',
      today: 'fe fe-calendar',
      clear: 'fe fe-trash',
      close: 'fe fe-remove'
    }
  });

  $('[data-dateranges]').daterangepicker({
    opens: 'right',
    timePickerSeconds: true,
    alwaysShowCalendars: true,
    autoUpdateInput: false,
    maxDate: moment(),
    locale: {format: 'MMM DD, YYYY'},
    ranges: {
      'Today': [moment(), moment()],
      'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Last 7 Days': [moment().subtract(6, 'days'), moment()],
      'Last 30 Days': [moment().subtract(29, 'days'), moment()],
      'This Month': [moment().startOf('month'), moment().endOf('month')],
      'Last Month': [
        moment().subtract(1, 'month').startOf('month'),
        moment().subtract(1, 'month').endOf('month')
      ]
    }
  });

  $('[data-dateranges], [data-daterange]')
      .on('apply.daterangepicker', function(e, picker) {
        if (picker.autoUpdateInput == false) {
          $(this).val(
              picker.startDate.format(picker.locale.format) + ' - ' +
              picker.endDate.format(picker.locale.format));
        }

        var t = $(e.currentTarget);
        if (typeof t.data('auto-submit') != undefined) {
          t.trigger('change');
          $(document).trigger('form:submit', [t]);
        }
      });

  $('[data-dateranges], [data-daterange]')
      .on('cancel.daterangepicker', function(e, picker) {
        if (picker.autoUpdateInput == false) {
          $(this).val('');
        }
      });
}