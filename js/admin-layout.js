(function () {
  // Inject session-guard.js
  var sgLoaded = !!document.querySelector('script[src*="session-guard"]');
  if (!sgLoaded) {
    var sg = document.createElement('script');
    sg.src = '../js/session-guard.js';
    document.head.appendChild(sg);
  }

  // Inject sidebar CSS if not already loaded
  var cssHref = '../css/menu.css';
  var alreadyLoaded = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
    .some(function (l) { return l.href.indexOf('menu.css') !== -1; });
  if (!alreadyLoaded) {
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = cssHref;
    document.head.appendChild(link);
  }

  // Fetch sidebar HTML and inject
  fetch('../includes/admin-sidebar.php')
    .then(function (res) { return res.text(); })
    .then(function (html) {
      document.body.insertAdjacentHTML('afterbegin', html);

      // Hide the legacy top navbar (logo + "Inicio" button) if present
      var legacyNav = document.querySelector('body > nav.navbar');
      if (legacyNav) {
        legacyNav.style.display = 'none';
      }

      // Wrap all body children (except #sidebar) in #main-wrapper if it doesn't exist
      var wrapper = document.getElementById('main-wrapper');
      if (!wrapper) {
        wrapper = document.createElement('div');
        wrapper.id = 'main-wrapper';
        // Move every child that isn't the sidebar into the wrapper
        Array.from(document.body.children).forEach(function (child) {
          if (child.id !== 'sidebar') {
            wrapper.appendChild(child);
          }
        });
        document.body.appendChild(wrapper);
      }

      // Apply flex layout
      document.body.style.display = 'flex';
      document.body.style.minHeight = '100vh';
      document.body.style.margin = '0';

      wrapper.style.marginLeft = '256px';
      wrapper.style.flex = '1';
      wrapper.style.minWidth = '0';
      wrapper.style.overflowX = 'hidden';
      wrapper.style.display = 'flex';
      wrapper.style.flexDirection = 'column';
      wrapper.style.minHeight = '100vh';

      // Mark active sidebar link based on current pathname
      var currentPath = window.location.pathname;
      document.querySelectorAll('#sidebar .sb-link').forEach(function (link) {
        try {
          if (new URL(link.href).pathname === currentPath) {
            link.classList.add('active');
          }
        } catch (e) { /* ignore */ }
      });
    })
    .catch(function (err) {
      console.warn('admin-layout.js: no se pudo cargar el sidebar.', err);
    });
})();
