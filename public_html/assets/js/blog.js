/* taxmetter: blog pages. Instant search on the listing pages and "minutes left" on articles.
   Works on the static Vercel copy and on the PHP blog (where Enter still runs the full server search). */
(function () {
  'use strict';

  /* ---------- Instant search ---------- */
  var form = document.querySelector('[data-bl-search]');
  if (form) {
    var input = form.querySelector('input[type="search"]');
    var count = document.getElementById('bl-count');
    var empty = document.querySelector('[data-bl-empty]');
    var main = document.getElementById('main');
    // On the blog home, search the article index; on category pages, the card grid
    var items = [].slice.call(document.querySelectorAll('.bh-index__list > li, .bl-grid > li'));
    var lead = document.querySelector('.bh-feature');
    var norm = function (s) { return s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/\s+/g, ' ').trim(); };
    var texts = items.map(function (li) { return norm(li.textContent); });

    var run = function () {
      var q = norm(input.value);
      var words = q ? q.split(' ') : [];
      var shown = 0;
      items.forEach(function (li, i) {
        var hit = words.every(function (w) { return texts[i].indexOf(w) !== -1; });
        li.hidden = !hit;
        if (hit) shown++;
      });
      main.classList.toggle('is-searching', !!q);
      if (empty) empty.hidden = !q || shown > 0;
      count.textContent = q ? (shown === 1 ? '1 article matches' : shown + ' articles match') : '';
      if (lead) lead.hidden = !!q && document.querySelector('.bh-index__list') !== null;
    };

    if (items.length) {
      input.addEventListener('input', run);
      form.addEventListener('submit', function (e) {
        // The static copy has no server search; on the PHP blog an empty filter result falls through to the full search
        var visible = items.some(function (li) { return !li.hidden; });
        if (form.hasAttribute('data-static') || visible) {
          e.preventDefault();
          var first = items.filter(function (li) { return !li.hidden; })[0];
          if (first) first.scrollIntoView({ block: 'center', behavior: 'smooth' });
        }
      });
      if (input.value) run();
    }
    var clear = document.querySelector('[data-bl-clear]');
    if (clear) clear.addEventListener('click', function () { input.value = ''; run(); input.focus(); });

    // Press "/" anywhere to jump to the search box
    document.addEventListener('keydown', function (e) {
      if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) return;
      var t = e.target;
      if (t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName)) return;
      e.preventDefault();
      input.focus();
      input.select();
    });
    input.addEventListener('keydown', function (e) { if (e.key === 'Escape' && input.value) { input.value = ''; run(); } });
  }

  /* ---------- Article: minutes left to read ---------- */
  var left = document.querySelector('.ar-toc__left');
  var prose = document.querySelector('.ar-prose');
  if (left && prose) {
    var total = +left.getAttribute('data-left') || 1;
    var tick = false;
    var update = function () {
      tick = false;
      var r = prose.getBoundingClientRect();
      var read = Math.min(1, Math.max(0, (window.innerHeight * 0.6 - r.top) / r.height));
      var mins = Math.ceil(total * (1 - read));
      left.textContent = read >= 0.98 ? 'Finished. Nice work.' : read <= 0.02 ? total + ' min read' : (mins <= 1 ? 'Under a minute left' : mins + ' min left');
      left.classList.toggle('is-done', read >= 0.98);
    };
    window.addEventListener('scroll', function () { if (!tick) { tick = true; requestAnimationFrame(update); } }, { passive: true });
    update();
  }
})();
