window.load_intl = () => {
  function setCountryCode($this, val) {
    $this.next('input:hidden').remove();
    var name = 'code';
    $('<input>', {
      type: 'hidden',
      name: name,
      value: val,
    }).insertAfter($this);
  }

  $('[type=tel]').each(function () {
    var $this = $(this);
    let intl = $this[0];

    var iti = intlTelInput(intl, {
      nationalMode: false,
      initialCountry: 'auto',
      preferredCountries: ['in', 'us', 'gb', 'ae'],
      geoIpLookup: function (callback) {
        $.get('https://ipinfo.io', function () {}, 'jsonp').always(function (resp) {
          var countryCode = resp && resp.country ? resp.country : '';
          callback(countryCode);
        });
      },
    });

    iti.promise.then(function () {
      var v = iti.getSelectedCountryData();
      setCountryCode($this, v.iso2);
    });

    intl.addEventListener('countrychange', function (e) {
      var val = iti.getSelectedCountryData().iso2;
      setCountryCode($this, val);
    });
  });
};
