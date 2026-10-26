/* taxmetter: FAQ page search and deep links */
(function () {
  'use strict';
  var input = document.getElementById('fq-q');
  if (!input) return;
  var items = Array.prototype.slice.call(document.querySelectorAll('.fq-group .qa'));
  var groups = Array.prototype.slice.call(document.querySelectorAll('.fq-group'));
  var count = document.querySelector('[data-fq-count]');
  var none = document.querySelector('[data-fq-none]');
  var norm = function (s) { return s.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/[’']/g, ''); };
  var text = items.map(function (d) { return norm(d.textContent + ' ' + (d.getAttribute('data-k') || '')); });

  function filter() {
    var words = norm(input.value).trim().split(/\s+/).filter(Boolean);
    var shown = 0;
    items.forEach(function (d, i) {
      var hit = words.every(function (w) { return text[i].indexOf(w) > -1; });
      d.hidden = !hit;
      if (hit) shown++;
      if (words.length && hit && words.join('').length > 2) d.open = true;
      if (!words.length) d.open = false;
    });
    groups.forEach(function (g) { g.hidden = !g.querySelector('.qa:not([hidden])'); });
    none.hidden = shown > 0;
    count.textContent = words.length ? shown + (shown === 1 ? ' match' : ' matches') : '';
  }
  var t;
  input.addEventListener('input', function () { clearTimeout(t); t = setTimeout(filter, 120); });
  input.addEventListener('keydown', function (e) { if (e.key === 'Escape') { input.value = ''; filter(); } });

  // Keep the address bar in sync, so an open question can be shared
  items.forEach(function (d) {
    d.addEventListener('toggle', function () {
      if (d.open && history.replaceState) history.replaceState(null, '', '#' + d.id);
    });
  });
})();
