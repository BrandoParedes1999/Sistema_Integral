(function () {
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

      // Apply layout to body and main wrapper
      document.body.style.display = 'flex';

      var wrapper = document.getElementById('main-wrapper');
      if (wrapper) {
        wrapper.style.marginLeft = '256px';
        wrapper.style.flex = '1';
        wrapper.style.display = 'flex';
        wrapper.style.flexDirection = 'column';
        wrapper.style.minHeight = '100vh';
      }

      // Mark active link based on current pathname
      var currentPath = window.location.pathname;
      var links = document.querySelectorAll('#sidebar .sb-link');
      links.forEach(function (link) {
        try {
          var linkPath = new URL(link.href).pathname;
          if (linkPath === currentPath) {
            link.classList.add('active');
          }
        } catch (e) {
          // ignore invalid URLs
        }
      });
    })
    .catch(function (err) {
      console.warn('admin-layout.js: no se pudo cargar el sidebar.', err);
    });
})();
