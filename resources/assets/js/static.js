import $ from '/node_modules/jquery';
import * as Popper from '/node_modules/@popperjs/core';
import * as bootstrap from '/node_modules/bootstrap';
import NProgress from '/node_modules/nprogress';
import moment from '/node_modules/moment';
import Noty from '/node_modules/noty';
import axios from '/node_modules/axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const defineJQueryPlugin = (plugin) => {
  const name = plugin.NAME;
  const JQUERY_NO_CONFLICT = $.fn[name];
  $.fn[name] = plugin.jQueryInterface;
  $.fn[name].Constructor = plugin;
  $.fn[name].noConflict = () => {
    $.fn[name] = JQUERY_NO_CONFLICT;
    return plugin.jQueryInterface;
  };
};

window.jQuery = window.$ = $;
window.Popper = Popper;
window.NProgress = NProgress;
window.moment = moment;
window.Noty = Noty;

defineJQueryPlugin(bootstrap.Modal);
defineJQueryPlugin(bootstrap.Tooltip);
defineJQueryPlugin(bootstrap.Popover);
defineJQueryPlugin(bootstrap.Collapse);
