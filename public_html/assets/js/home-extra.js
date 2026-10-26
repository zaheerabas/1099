/* taxmetter: home page sections below the hero (quick estimate, payment timeline, latest articles) */
(function () {
  'use strict';
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var money = function (n) { return '$' + Math.round(n).toLocaleString('en-US'); };

  /* ---------- Quick estimate (same maths as the calculator) ---------- */
  var est = document.getElementById('qe');
  if (est && window.TMTax) {
    var inc = est.querySelector('#qe-income'), exp = est.querySelector('#qe-exp');
    var incOut = est.querySelector('[data-qe-income]'), expOut = est.querySelector('[data-qe-exp]');
    var totalEl = est.querySelector('[data-qe-total]'), qEl = est.querySelector('[data-qe-q]'), setEl = est.querySelector('[data-qe-set]');
    var seBar = est.querySelector('[data-qe-se]'), incBar = est.querySelector('[data-qe-inc]');
    var seVal = est.querySelector('[data-qe-se-v]'), incVal = est.querySelector('[data-qe-inc-v]');
    var link = est.querySelector('[data-qe-link]');
    var shown = 0, raf = null;
    var base = { status: 'single', nec: 0, k: 0, misc: 0, int: 0, exp: 0, health: 0, w2: 0, wh: 0, paid: 0, sqft: 0, mi1: 0, mi2: 0, ret: 0, prior: 0, hi: false };

    function paint(to) {
      cancelAnimationFrame(raf);
      if (reduce) { shown = to; totalEl.textContent = money(to); return; }
      var from = shown, t0 = null;
      (function step(t) {
        if (!t0) t0 = t;
        var p = Math.min((t - t0) / 380, 1), e = 1 - Math.pow(1 - p, 3);
        shown = from + (to - from) * e;
        totalEl.textContent = money(shown);
        if (p < 1) raf = requestAnimationFrame(step);
      })(performance.now());
    }
    function fill(range) {
      var pct = (range.value - range.min) / (range.max - range.min) * 100;
      range.style.setProperty('--fill', pct + '%');
    }
    function update() {
      var status = est.querySelector('input[name="qe-status"]:checked').value;
      var v = Object.assign({}, base, { status: status, nec: +inc.value, exp: +exp.value });
      var r = window.TMTax.calculate(v);
      incOut.textContent = money(v.nec);
      expOut.textContent = money(v.exp);
      paint(r.total);
      qEl.textContent = money(r.quarterly);
      setEl.textContent = Math.round(r.setAside * 100) + '%';
      var t = r.total || 1;
      seBar.style.transform = 'scaleX(' + (r.seTax / t) + ')';
      incBar.style.transform = 'scaleX(' + (r.incomeTax / t) + ')';
      seVal.textContent = money(r.seTax);
      incVal.textContent = money(r.incomeTax);
      link.href = 'calculator.html?status=' + status + '&nec=' + v.nec + (v.exp ? '&exp=' + v.exp : '');
      fill(inc); fill(exp);
    }
    [inc, exp].forEach(function (r) { r.addEventListener('input', update); });
    est.querySelectorAll('input[name="qe-status"]').forEach(function (r) { r.addEventListener('change', update); });
    update();
  }

  /* ---------- 2026 payment timeline: where today sits, and the next due date ---------- */
  var tl = document.getElementById('tl');
  if (tl) {
    var today = new Date(); today.setHours(0, 0, 0, 0);
    var nodes = Array.prototype.slice.call(tl.querySelectorAll('[data-date]'));
    var dates = nodes.map(function (n) { var p = n.getAttribute('data-date').split('-'); return new Date(+p[0], +p[1] - 1, +p[2]); });
    var start = new Date(2026, 0, 1), end = dates[dates.length - 1];
    var pos = Math.max(0, Math.min(1, (today - start) / (end - start)));
    tl.style.setProperty('--today', (pos * 100).toFixed(2) + '%');
    var next = -1;
    dates.forEach(function (d, i) {
      if (d < today) nodes[i].classList.add('is-past');
      else if (next < 0) { next = i; nodes[i].classList.add('is-next'); }
    });
    var note = tl.querySelector('[data-tl-note]');
    if (next >= 0) {
      var days = Math.round((dates[next] - today) / 86400000);
      note.innerHTML = 'Next payment: <b>' + dates[next].toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) + '</b>, ' + (days === 0 ? 'due today.' : 'in ' + days + (days === 1 ? ' day.' : ' days.'));
    } else {
      note.innerHTML = 'All four 2026 payments are past. Anything still owed is due with your return by <b>April 15, 2027</b>.';
    }
    tl.classList.toggle('tl--done', next < 0);
    if (pos > 0 && pos < 1) tl.classList.add('tl--today');
  }

  /* ---------- Latest blog articles (shown only when the blog is installed and has articles) ---------- */
  var latest = document.getElementById('latest');
  if (latest && window.fetch) {
    fetch('/blog/latest.json', { headers: { Accept: 'application/json' } })
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function (data) {
        var posts = (data && data.posts) || [];
        if (!posts.length) return;
        var list = latest.querySelector('[data-latest-list]');
        var esc = function (s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; };
        list.innerHTML = posts.map(function (p, i) {
          var img = p.cover ? '<img src="' + esc(p.cover) + '" alt="" loading="lazy" width="640" height="400">' : '<span class="bl-ph" aria-hidden="true"></span>';
          return '<li class="lt-item' + (i === 0 ? ' lt-item--lead' : '') + '"><a href="' + esc(p.url) + '">' +
            '<span class="lt-img">' + img + '</span>' +
            '<span class="lt-meta">' + (p.category ? '<b>' + esc(p.category) + '</b>' : '') + '<span>' + esc(p.date) + '</span><span>' + esc(p.minutes) + ' min read</span></span>' +
            '<span class="lt-title">' + esc(p.title) + '</span>' +
            (i === 0 ? '<span class="lt-ex">' + esc(p.excerpt) + '</span>' : '') +
            '</a></li>';
        }).join('');
        latest.hidden = false;
        if (window.TMReveal) window.TMReveal(latest);
      })
      .catch(function () { /* blog not installed yet: the section stays hidden */ });
  }
})();
