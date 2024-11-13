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
        },
      });
    } else {
      if (!confirm(message)) {
        return callback(false);
      }
      callback(true);
    }
  },
};

window.box = box;
