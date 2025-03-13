(() => {
  'use strict';

  const getStoredTheme = () => localStorage.getItem('theme');
  const setStoredTheme = (theme) => localStorage.setItem('theme', theme);

  const getPreferredTheme = () => {
    const storedTheme = getStoredTheme();
    if (storedTheme) {
      return storedTheme;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  };

  const setTheme = (theme) => {
    if (theme === 'auto') {
      theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    document.documentElement.setAttribute('data-bs-theme', theme);
    document.documentElement.setAttribute('data-mode', theme);
    document.documentElement.classList.add(theme);

    setCookie('theme-color-scheme', theme);
  };

  setTheme(getPreferredTheme());

  const showActiveTheme = (theme, focus = false) => {
    const btnToActive = document.querySelector(`[data-bs-theme-active="${theme}"]`);

    if (!btnToActive) {
      return;
    }

    document.querySelectorAll('[data-bs-theme-active]').forEach((element) => {
      element.classList.remove('show');
      element.classList.add('hide');
    });

    btnToActive.classList.remove('hide');
    btnToActive.classList.add('show');
  };

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    const storedTheme = getStoredTheme();
    if (storedTheme !== 'light' && storedTheme !== 'dark') {
      setTheme(getPreferredTheme());
    }
  });

  window.addEventListener('DOMContentLoaded', () => {
    showActiveTheme(getPreferredTheme());

    document.querySelectorAll('[data-bs-theme-value]').forEach((toggle) => {
      toggle.addEventListener('click', () => {
        const theme = toggle.getAttribute('data-bs-theme-value');
        setStoredTheme(theme);
        setTheme(theme);
        showActiveTheme(theme, true);
        window.dispatchEvent(new CustomEvent('color-scheme-changed', { detail: theme }));
      });
    });
  });
})();
