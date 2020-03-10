function load_intl() {
    var intl = document.querySelector("[type=tel]");

    if (intl) {
        var iti = intlTelInput(intl, {
            nationalMode: false,
            initialCountry: "auto",
            preferredCountries: ['in', 'us', 'gb', 'ae'],
            geoIpLookup: function (callback) {
                $.get('https://ipinfo.io', function () { }, "jsonp").always(function (resp) {
                    var countryCode = (resp && resp.country) ? resp.country : "";
                    callback(countryCode);
                });
            },
        });

        iti.promise.then(function () {
            var v = iti.getSelectedCountryData();
            setCountryCode($(intl), v.iso2);
        });

        intl.addEventListener("countrychange", function (e) {
            var $this = $(e.currentTarget);
            var val = iti.getSelectedCountryData().iso2;
            setCountryCode($this, val);
        });

        function setCountryCode($this, val) {
            $this.next('input:hidden').remove();
            var name = 'code';
            $("<input>", {
                type: "hidden",
                name: name,
                value: val
            }).insertAfter($this);
        }
    }
}