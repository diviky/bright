import { Livewire, Alpine } from '/vendor/livewire/livewire/dist/livewire.esm';
import mask from '/node_modules/@alpinejs/mask';
import anchor from '/node_modules/@alpinejs/anchor';
import slug from '/node_modules/alpinejs-slug';
import Clipboard from '/node_modules/@ryangjchandler/alpine-clipboard';
import Sortable from '/node_modules/sortablejs';

import './livewire/initialized';
import './livewire/alpine';
import './livewire/directives';
import './livewire/sortable';

Alpine.plugin(mask);
Alpine.plugin(anchor);
Alpine.plugin(slug);
Alpine.plugin(
  Clipboard.configure({
    onCopy: () => {
      notify({
        text: 'Copied!',
        type: 'info',
      });
    },
  })
);

window.Sortable = Sortable;
window.Livewire = Livewire;
Livewire.start();
