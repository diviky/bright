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

  $('[data-dateranges]').off('apply.daterangepicker cancel.daterangepicker');

  $('[data-dateranges]').on('apply.daterangepicker', function (e, picker) {
    if (picker.autoUpdateInput == false) {
      $(this).val(picker.startDate.format(picker.locale.format) + ' - ' + picker.endDate.format(picker.locale.format));
    }

    var t = $(e.currentTarget);

    // Always trigger native DOM change event so Alpine.js can capture it
    const changeEvent = new Event('change', { bubbles: true });
    t[0].dispatchEvent(changeEvent);

    // Only trigger form submit when auto-submit is enabled
    if (typeof t.data('auto-submit') !== 'undefined' || typeof t.attr('auto-submit') !== 'undefined') {
      $(document).trigger('form:submit', [t]);
    }
  });

  $('[data-dateranges]').on('cancel.daterangepicker', function (e, picker) {
    var t = $(e.currentTarget);
    var defaultValue = t.attr('data-default') || t.data('default');

    // Keep UI in sync with backends that always apply a default when empty.
    if (defaultValue) {
      t.val(defaultValue);

      var parts = String(defaultValue).split(' - ');
      if (parts.length === 2) {
        picker.setStartDate(moment(parts[0], picker.locale.format));
        picker.setEndDate(moment(parts[1], picker.locale.format));
      }
    } else if (picker.autoUpdateInput == false) {
      t.val('');
    }

    // Always trigger native DOM change event so Alpine.js can capture it
    const changeEvent = new Event('change', { bubbles: true });
    t[0].dispatchEvent(changeEvent);

    // Only trigger form submit when auto-submit is enabled
    if (typeof t.data('auto-submit') !== 'undefined' || typeof t.attr('auto-submit') !== 'undefined') {
      $(document).trigger('form:submit', [t]);
    }
  });
};

$(document).on('form-pre-serialize', function (e, $form) {
  $($form)
    .find('[data-dateranges][data-default]')
    .each(function () {
      if (!$(this).val()) {
        $(this).val($(this).attr('data-default'));
      }
    });
});

window.load_dates = () => {
  load_dateranges();
  load_datetimepicker();
};
