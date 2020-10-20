function uuid() {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
    }
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() +
        s4() + s4();
}

var box = {
    confirm: function (message, callback) {
        if (message == '' || message == undefined) {
            return callback(true);
        }

        if (typeof bootbox !== 'undefined') {
            bootbox.confirm({
                message: message,
                callback: function (result) {
                    callback(result);
                }
            });
        } else {
            if (!confirm(message)) {
                return callback(false);
            }
            callback(true);
        }
    }
}
