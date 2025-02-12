window.load_dates = () => {
  load_dateranges();
  load_datetimepicker();
};

window.load_datetimepicker = () => {
  if (!tempusDominus) {
    console.info('npm install @eonasdan/tempus-dominus --save');
    return;
  }

  document.querySelectorAll('[data-datetime]').forEach((container) => {
    let data = {
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
      localization: {
        format: 'MMM dd, yyyy h:mm T',
      },
    };
    new tempusDominus.TempusDominus(container, data);
  });

  document.querySelectorAll('[data-time]').forEach((container) => {
    let data = {
      dateRange: false,
      keepInvalid: true,
      multipleDatesSeparator: ' - ',
      display: {
        sideBySide: false,
        viewMode: 'clock',
        components: {
          calendar: false,
          date: false,
          month: false,
          year: false,
          decades: false,
        },
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
      localization: {
        format: 'h:mm T',
      },
    };

    new tempusDominus.TempusDominus(container, data);
  });

  document.querySelectorAll('[data-date]').forEach((container) => {
    let data = {
      dateRange: false,
      keepInvalid: true,
      multipleDatesSeparator: ' - ',
      display: {
        sideBySide: false,
        viewMode: 'calendar',
        components: {
          calendar: true,
          date: true,
          month: true,
          year: true,
          decades: true,
          clock: false,
          hours: false,
          minutes: false,
          seconds: false,
        },
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
      localization: {
        format: 'LL',
      },
    };
    new tempusDominus.TempusDominus(container, data);
  });

  document.querySelectorAll('[data-daterange]').forEach((container) => {
    let data = {
      dateRange: true,
      keepInvalid: true,
      multipleDatesSeparator: ' - ',
      display: {
        sideBySide: false,
        viewMode: 'calendar',
        components: {
          calendar: true,
          date: true,
          month: true,
          year: true,
          decades: true,
          clock: false,
          hours: false,
          minutes: false,
          seconds: false,
        },
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
      localization: {
        format: 'LL',
      },
    };
    new tempusDominus.TempusDominus(container, data);
  });
};

window.load_dateranges = () => {
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
};
