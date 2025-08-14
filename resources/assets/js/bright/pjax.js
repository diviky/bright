window.brightPjax = () => {
  const prefetchCache = {};
  const hoverTimers = {};
  const prefetchPromises = {};
  const PREFETCH_DELAY = 200; // ms delay before starting prefetch (increased to reduce accidental triggers)
  const MAX_CACHE_SIZE = 50; // Maximum number of cached pages
  const CACHE_EXPIRY_TIME = 10000; // 10 seconds in milliseconds
  const PJAX_LINK_SELECTOR = '[data-pjax] a, a[data-pjax]';

  // Global prefetch configuration
  let globalPrefetchEnabled = true;

  function cleanupCache() {
    const cacheKeys = Object.keys(prefetchCache);
    if (cacheKeys.length > MAX_CACHE_SIZE) {
      // Remove oldest entries (simple FIFO)
      const toRemove = cacheKeys.slice(0, cacheKeys.length - MAX_CACHE_SIZE);
      toRemove.forEach((key) => delete prefetchCache[key]);
    }
  }

  function isExpired(cacheEntry) {
    return Date.now() - cacheEntry.timestamp > CACHE_EXPIRY_TIME;
  }

  function cleanupExpiredCache() {
    Object.keys(prefetchCache).forEach((url) => {
      if (isExpired(prefetchCache[url])) {
        delete prefetchCache[url];
      }
    });
  }

  function prefetchPage(url, container) {
    // Clean up expired cache before checking
    cleanupExpiredCache();

    if (prefetchCache[url] || prefetchPromises[url]) return; // already prefetched or prefetching

    // Create timeout controller for browsers that don't support AbortSignal.timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 2000);

    // Mark as prefetching to avoid duplicate requests
    prefetchPromises[url] = fetch(url, {
      method: 'GET',
      headers: {
        'X-PJAX': 'true',
        'X-PJAX-Container': container,
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'text/html, application/xhtml+xml, application/xml;q=0.9, */*;q=0.8',
      },
      signal: controller.signal,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.text();
      })
      .then((data) => {
        clearTimeout(timeoutId);
        // Store the raw HTML response in cache (don't apply it yet)
        prefetchCache[url] = {
          content: data,
          timestamp: Date.now(),
        };
        delete prefetchPromises[url];
        cleanupCache();

        // Set expiry timer for this cache entry
        setTimeout(() => {
          if (prefetchCache[url]) {
            delete prefetchCache[url];
          }
        }, CACHE_EXPIRY_TIME);
      })
      .catch((error) => {
        clearTimeout(timeoutId);
        delete prefetchPromises[url];
        if (error.name !== 'AbortError') {
          console.warn('PJAX prefetch failed for:', url, error.message);
        }
      });
  }

  // -------------------------
  // Helper utilities
  // -------------------------
  function getCachedEntry(url) {
    if (!prefetchCache[url]) return null;
    if (isExpired(prefetchCache[url])) {
      delete prefetchCache[url];
      return null;
    }
    return prefetchCache[url];
  }

  function applyHtmlToContainer(html, container) {
    let $response = $('<div>').html(html);
    let $containerContent = $response.find(container);

    if ($containerContent.length) {
      $(container).html($containerContent.html());
    } else {
      $(container).html(html);
    }
  }

  function triggerPjaxLifecycle(html, container, url) {
    $(document).trigger('pjax:success', [html, 'success', null, { container: container, url: url }]);
    $(document).trigger('pjax:end');
  }

  function consumeCacheAndNavigate(url, container) {
    const entry = getCachedEntry(url);
    if (!entry) return false;

    const html = entry.content;
    window.history.pushState({}, '', url);

    applyHtmlToContainer(html, container);
    delete prefetchCache[url];
    triggerPjaxLifecycle(html, container, url);
    return true;
  }

  $(document).on('mouseenter', PJAX_LINK_SELECTOR, function (e) {
    if ($(this).data('nojax') || $(this).attr('nojax')) {
      return true;
    }

    const url = $(this).attr('href');

    if (!url || url === '#' || url === '') {
      return true;
    }

    // Check if prefetch is enabled (per-element or global setting)
    const elementPrefetch = $(this).attr('prefetch');
    let prefetchEnabled;

    if (elementPrefetch === 'true' || elementPrefetch === '') {
      // Explicitly enabled or empty attribute
      prefetchEnabled = true;
    } else if (elementPrefetch === 'false') {
      // Explicitly disabled
      prefetchEnabled = false;
    } else if (elementPrefetch === undefined) {
      // No prefetch attribute, use global setting
      prefetchEnabled = globalPrefetchEnabled;
    } else {
      // Any other value, default to global setting
      prefetchEnabled = globalPrefetchEnabled;
    }

    if (!prefetchEnabled) return true;

    const container = $(this).data('pjax-container') || '[data-pjax-container]';

    // Check if cache exists and is not expired
    if (prefetchCache[url] && !isExpired(prefetchCache[url])) return;
    if (prefetchPromises[url]) return;
    if (!url || !container) return;

    // Start hover timer with increased delay to reduce accidental triggers
    hoverTimers[url] = setTimeout(() => {
      prefetchPage(url, container);
      delete hoverTimers[url];
    }, PREFETCH_DELAY);
  });

  $(document).on('mouseleave', PJAX_LINK_SELECTOR, function () {
    const url = $(this).attr('href');

    // Clear hover timer
    if (hoverTimers[url]) {
      clearTimeout(hoverTimers[url]);
      delete hoverTimers[url];
    }
  });

  $(document).on('click', PJAX_LINK_SELECTOR, function (e) {
    if ($(this).data('nojax') || $(this).attr('nojax')) {
      return true;
    }

    let url = $(this).attr('href');

    if (!url || url === '#' || url === '') {
      return;
    }

    const container = $(this).data('pjax-container') || '[data-pjax-container]';
    $(this).parents('[data-pjax]').find('a').removeClass('active');
    $(this).addClass('active');

    // Check if cache exists and is not expired
    if (getCachedEntry(url)) {
      e.preventDefault();
      consumeCacheAndNavigate(url, container);
    } else if (prefetchPromises[url]) {
      // Prefetch is in progress, wait for it to complete
      e.preventDefault();

      prefetchPromises[url]
        .then((data) => {
          // Use the prefetched data
          if (!consumeCacheAndNavigate(url, container)) {
            // Prefetch failed or expired, fall back to regular PJAX
            window.location.href = url;
          }
        })
        .catch((error) => {
          // Prefetch failed, fall back to regular navigation
          console.warn('Prefetch failed during click, falling back to regular navigation:', error);
          window.location.href = url;
        });
    } else {
      // Clean up expired cache if it exists
      if (prefetchCache[url] && isExpired(prefetchCache[url])) {
        delete prefetchCache[url];
      }

      $.pjax.click(e, {
        container: container,
        url: url,
        timeout: 15000,
      });
    }
  });

  $(document).on('pjax:end', function (xhr) {
    $(document).trigger('ajax:loaded');
  });

  // Expose methods for debugging and manual cache management
  return {
    getCache: () => prefetchCache,
    clearCache: () => {
      Object.keys(prefetchCache).forEach((key) => delete prefetchCache[key]);
    },
    prefetch: prefetchPage,
    get prefetchEnabled() {
      return globalPrefetchEnabled;
    },
    set prefetchEnabled(value) {
      globalPrefetchEnabled = !!value;
    },
  };
};
