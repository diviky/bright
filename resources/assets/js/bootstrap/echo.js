import Echo from '/node_modules/laravel-echo';
import Pusher from '/node_modules/pusher-js';

const supportedBroadcasters = ['reverb', 'pusher', 'ably', 'socket.io', 'null'];
const echoDriver = window.env?.echo?.driver || 'reverb';

if (window.env?.echo?.enabled && window.env?.userId && supportedBroadcasters.includes(echoDriver)) {
  window.Pusher = Pusher;

  const useTls = Boolean(window.env?.echo?.ssl);

  const echoConfig = {
    broadcaster: echoDriver,
    key: window.env?.echo?.key || '7e0aefc03f0b8246ea11dddf0e3b79da',
    wsHost: window.env?.echo?.host || window.location.hostname,
    wsPort: window.env?.echo?.port || 6001,
    wssHost: window.env?.echo?.host || window.location.hostname,
    wssPort: window.env?.echo?.port || 6001,
    forceTLS: useTls,
    disableStats: window.env?.echo?.stats || true,
    // Reverb on http://127.0.0.1:8080 only speaks ws://. Including wss on an
    // https page makes pusher-js try wss:// first, which closes immediately.
    enabledTransports: useTls ? ['ws', 'wss'] : ['ws'],
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
  };

  // Reverb is self-hosted. A `cluster` value makes pusher-js fall back to
  // sockjs.pusher.com when the websocket handshake fails.
  if (echoDriver === 'pusher' && window.env?.echo?.cluster) {
    echoConfig.cluster = window.env.echo.cluster;
  }

  window.Echo = new Echo(echoConfig);

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
