(function () {
  var TIMEOUT   = 1800;   // segundos — debe coincidir con $tiempo_limite en PHP
  var WARN_AT   = 300;    // mostrar aviso cuando quedan N segundos
  var remaining = TIMEOUT;
  var warned    = false;
  var interval;

  // Detectar ruta relativa al ping según desde dónde se carga la página
  function pingUrl() {
    var depth = (window.location.pathname.match(/\//g) || []).length - 1;
    var prefix = depth <= 1 ? '' : '../'.repeat(depth - 1);
    return prefix + 'auth/ping.php';
  }

  function formatTime(s) {
    var m = Math.floor(s / 60), sec = s % 60;
    return m + ':' + (sec < 10 ? '0' : '') + sec;
  }

  function updateCountdown() {
    var el = document.getElementById('sg-countdown');
    if (el) el.textContent = formatTime(remaining);
  }

  function showModal() {
    if (document.getElementById('sg-modal')) return;
    var html = [
      '<div id="sg-modal" style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:99999;display:flex;align-items:center;justify-content:center;">',
      '<div style="background:#fff;border-radius:16px;padding:2rem;max-width:360px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);font-family:Inter,sans-serif;">',
      '<div style="text-align:center;margin-bottom:1.25rem;">',
      '<div style="width:56px;height:56px;border-radius:50%;background:#fffbeb;color:#f59e0b;font-size:1.6rem;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">⏱</div>',
      '<h3 style="margin:0 0 .4rem;font-size:1.05rem;font-weight:700;color:#111827;">Tu sesión está por expirar</h3>',
      '<p style="margin:0;font-size:.85rem;color:#6b7280;">La sesión cerrará en <strong id="sg-countdown">' + formatTime(remaining) + '</strong> por inactividad.</p>',
      '</div>',
      '<div style="display:flex;gap:.75rem;">',
      '<button id="sg-continue" style="flex:1;background:#003da5;color:#fff;border:none;border-radius:8px;padding:.65rem;font-size:.9rem;font-weight:600;cursor:pointer;">Continuar sesión</button>',
      '<a id="sg-logout" href="../auth/cerrar_sesion.php" style="flex:1;background:#f1f5f9;color:#374151;border:none;border-radius:8px;padding:.65rem;font-size:.9rem;font-weight:600;cursor:pointer;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;">Cerrar sesión</a>',
      '</div>',
      '</div></div>'
    ].join('');
    document.body.insertAdjacentHTML('beforeend', html);

    document.getElementById('sg-continue').addEventListener('click', function () {
      keepAlive();
      var m = document.getElementById('sg-modal');
      if (m) m.remove();
      warned = false;
      remaining = TIMEOUT;
    });
  }

  function keepAlive() {
    fetch(pingUrl(), { credentials: 'same-origin' }).catch(function () {});
  }

  // Reset timer on user activity
  var activityTimer;
  ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(function (ev) {
    document.addEventListener(ev, function () {
      clearTimeout(activityTimer);
      activityTimer = setTimeout(function () {
        if (!warned) {
          keepAlive();
          remaining = TIMEOUT;
        }
      }, 2000);
    }, { passive: true });
  });

  interval = setInterval(function () {
    remaining--;
    if (remaining <= 0) {
      clearInterval(interval);
      window.location.href = (function () {
        var depth = (window.location.pathname.match(/\//g) || []).length - 1;
        return (depth <= 1 ? '' : '../'.repeat(depth - 1)) + 'auth/cerrar_sesion.php';
      })();
      return;
    }
    if (remaining <= WARN_AT && !warned) {
      warned = true;
      showModal();
    }
    if (warned) updateCountdown();
  }, 1000);
})();
