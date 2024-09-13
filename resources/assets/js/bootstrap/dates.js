function load_dates() {
  load_dateranges();
  load_datetimepicker();
}

function load_datetimepicker() {
  if (!tempusDominus) {
    console.info('npm install @eonasdan/tempus-dominus --save');
    return;
  }

  let options = {
    dateRange: false,
    keepInvalid: true,
    multipleDatesSeparator: ' - ',
    display: {
      sideBySide: false,
      icons: {
        time: 'ti ti-clock',
        date: 'ti ti-calendar-month',
        up: 'ti ti-arrow-up',
        down: 'ti ti-arrow-down',
        previous: 'ti ti-chevron-left',
        next: 'ti ti-chevron-right',
        today: 'ti ti-calendar',
        clear: 'ti ti-x',
        close: 'ti ti-square-x',
      },
    },
  };

  $('[data-datetime]').each(function () {
    let $this = $(this);
    let data = {
      localization: {
        format: 'MMM dd, yyyy h:mm T',
      },
    };
    new tempusDominus.TempusDominus($this[0], { ...options, ...data });
  });

  $('[data-time]').each(function () {
    let $this = $(this);
    let data = {
      localization: {
        format: 'LT',
      },
    };
    new tempusDominus.TempusDominus($this[0], { ...data, ...options });
  });

  $('[data-datepicker]').each(function () {
    let $this = $(this);
    let data = {
      localization: {
        format: 'LL',
      },
    };
    new tempusDominus.TempusDominus($this[0], { ...options, ...data });
  });

  $('[data-rangepicker]').each(function () {
    let $this = $(this);
    let data = {
      dateRange: true,
      localization: {
        format: 'LL',
      },
    };
    new tempusDominus.TempusDominus($this[0], { ...options, ...data });
  });
}

function load_dateranges() {
  if (!$.fn.daterangepicker) {
    console.info('npm install daterangepicker --save');
    return;
  }

  $('[data-dateranges]').daterangepicker({
    opens: 'right',
    timePickerSeconds: true,
    alwaysShowCalendars: true,
    autoUpdateInput: false,
    maxDate: moment(),
    locale: { format: 'MMM DD, YYYY' },
    ranges: {
      Today: [moment(), moment()],
      Yesterday: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
      'Last 7 Days': [moment().subtract(6, 'days'), moment()],
      'Last 30 Days': [moment().subtract(29, 'days'), moment()],
      'This Month': [moment().startOf('month'), moment().endOf('month')],
      'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
    },
  });

  $('[data-dateranges]').on('apply.daterangepicker', function (e, picker) {
    if (picker.autoUpdateInput == false) {
      $(this).val(picker.startDate.format(picker.locale.format) + ' - ' + picker.endDate.format(picker.locale.format));
    }

    var t = $(e.currentTarget);
    if (typeof t.data('auto-submit') !== 'undefined') {
      t.trigger('change');
      $(document).trigger('form:submit', [t]);
    }
  });

  $('[data-dateranges], [data-daterange]').on('cancel.daterangepicker', function (e, picker) {
    if (picker.autoUpdateInput == false) {
      $(this).val('');
    }
  });
}
