import Echo from '/node_modules/laravel-echo';
import Pusher from '/node_modules/pusher-js';

if (window.env?.echo?.enabled && window.env?.userId) {
  window.Pusher = Pusher;

  window.Echo = new Echo({
    broadcaster: window.env?.echo?.driver || 'reverb',
    key: window.env?.echo?.key || '7e0aefc03f0b8246ea11dddf0e3b79da',
    wsHost: window.env?.echo?.host || window.location.hostname,
    wsPort: window.env?.echo?.port || 6001,
    wssHost: window.env?.echo?.host || window.location.hostname,
    wssPort: window.env?.echo?.port || 6001,
    forceTLS: window.env?.echo?.ssl || false,
    disableStats: window.env?.echo?.stats || true,
    enableTransports: ['ws', 'wss'],
    cluster: window.env?.echo?.cluster || 'mt1',
    authorizer: (channel, options) => {
      return {
        authorize: (socketId, callback) => {
          axios
            .post(window.env?.echo?.auth || '/broadcasting/auth', {
              socket_id: socketId,
              channel_name: channel.name,
            })
            .then((response) => {
              callback(null, response.data);
            })
            .catch((error) => {
              callback(error);
            });
        },
      };
    },
  });

  window.Echo.channel('notifications').listen('.message', (e) => {
    window.dispatchEvent(
      new CustomEvent('notification', {
        detail: e,
      })
    );
  });

  window.Echo.channel('notifications').listen('.celebration', (e) => {
    window.dispatchEvent(
      new CustomEvent('celebration', {
        detail: e,
      })
    );
  });

  window.Echo.private('notifications.' + window.env?.userId).listen('.celebration', (e) => {
    window.dispatchEvent(
      new CustomEvent('celebration', {
        detail: e,
      })
    );
  });

  window.Echo.channel('flash').listen('.message', (e) => {
    window.dispatchEvent(
      new CustomEvent('flash', {
        detail: e,
      })
    );
  });

  window.Echo.private('notifications.' + window.env?.userId).listen('.message', (e) => {
    window.dispatchEvent(
      new CustomEvent('notification', {
        detail: e,
      })
    );
  });

  window.Echo.private('App.Models.User.' + window.env?.user_id).notification((e) => {
    window.dispatchEvent(
      new CustomEvent('notification', {
        detail: e,
      })
    );
  });
}
