String.prototype.replaceAll = function (search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, "g"), replacement);
};

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(";");
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == " ") {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function removeCookie(cname) {
    document.cookie =
        cname + "=; expires = Thu, 01 Jan 1970 00:00:00 GMT;path=/";
}

function nl2br(str, is_xhtml) {
    if (typeof str === "undefined" || str === null) {
        return "";
    }
    var breakTag =
        is_xhtml || typeof is_xhtml === "undefined" ? "<br />" : "<br>";
    return (str + "").replace(
        /([^>\r\n]?)(\r\n|\n\r|\r|\n)/g,
        "$1" + breakTag + "$2"
    );
}

// load JSON files from url
function loadJSON(url, callback, headers) {
    $.ajax({
        url: url,
        method: "GET",
        dataType: "json",
        headers: headers,
        success: function (res) {
            callback(res);
        },
        fail: function () {
            callback(null, "error");
        },
        error: function () {
            callback(null, "error");
        },
    });
}

function uuid() {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);
    }
    return (
        s4() +
        s4() +
        "-" +
        s4() +
        "-" +
        s4() +
        "-" +
        s4() +
        "-" +
        s4() +
        s4() +
        s4()
    );
}

const loadDynamicScript = (url, id, callback) => {
    const existingScript = document.getElementById("scriptId");

    if (!existingScript) {
        const script = document.createElement("script");
        script.src = url; // URL for the third-party library being loaded.
        script.id = id; // e.g., googleMaps or stripe
        document.body.appendChild(script);

        script.onload = () => {
            if (callback) callback();
        };
    }

    if (existingScript && callback) callback();
};

function guid() {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);
    }
    return (
        s4() +
        s4() +
        "-" +
        s4() +
        "-" +
        s4() +
        "-" +
        s4() +
        "-" +
        s4() +
        s4() +
        s4()
    );
}

jQuery.fn.preventDoubleClick = function () {
    $(this).on("click", function (e) {
        var $el = $(this);
        if ($el.data("clicked")) {
            // Previously clicked, stop actions
            e.preventDefault();
            e.stopPropagation();
        } else {
            // Mark to ignore next click
            $el.data("clicked", true);
            // Unmark after 1 second
            window.setTimeout(function () {
                $el.removeData("clicked");
            }, 1000);
        }
    });
    return this;
};

RegExp.escape = function (s) {
    return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, "\\$&");
};
