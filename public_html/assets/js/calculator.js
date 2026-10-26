/* taxmetter: 1099 tax calculator page. The maths is in tax-engine.js. */
(function () {
  var hero = document.getElementById('ew-hero');
  if (!hero) return;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var TAX = window.TMTax.TAX, calculate = window.TMTax.calculate;
    /* ================= Page wiring ================= */
    var form = document.getElementById('cx-form');
    if (form) {
      var $ = function (id) { return document.getElementById(id); };
      var money = function (n) { return (n < 0 ? '−$' : '$') + Math.round(Math.abs(n)).toLocaleString('en-US'); };
      var moneyFields = ['nec','k','misc','int','exp','health','w2','wh','paid','ret','prior'];
      var countFields = ['sqft','mi1','mi2'];
      var allFields = moneyFields.concat(countFields);
      var num = function (id) { var x = parseFloat(($(id).value || '').replace(/[^0-9.]/g, '')); return isFinite(x) && x > 0 ? x : 0; };

      function read() {
        var v = { status: form.querySelector('input[name="status"]:checked').value, hi: $('hi').checked };
        allFields.forEach(function (id) { v[id] = num(id); });
        return v;
      }

      // Count the big number from its old value to the new one
      var shown = 0, raf = null;
      function animateTotal(to) {
        var el = $('r-total'), dock = $('d-total'), from = shown;
        cancelAnimationFrame(raf);
        if (reduce || Math.abs(to - from) < 1) { shown = to; el.textContent = dock.textContent = money(to); return; }
        var start = null;
        (function frame(t) {
          if (!start) start = t;
          var p = Math.min((t - start) / 450, 1), e = 1 - Math.pow(1 - p, 3);
          shown = from + (to - from) * e;
          el.textContent = dock.textContent = money(shown);
          if (p < 1) raf = requestAnimationFrame(frame);
        })(performance.now());
      }

      function render() {
        var v = read(), r = calculate(v);
        var hasIncome = r.gross1099 > 0 || v.w2 > 0 || v.int > 0;
        animateTotal(r.total);
        $('cx-dock').classList.toggle('is-empty', !(r.total > 0));

        $('r-sub').textContent = hasIncome
          ? (Math.round(r.effective * 1000) / 10) + '% of your total income. Your top bracket is ' + Math.round(r.marginal * 100) + '%.'
          : 'Add your 1099 income to see your estimate.';

        $('r-split').hidden = !(r.total > 0);
        var t = r.total || 1;
        $('b-se').style.width = (r.seTax / t * 100) + '%';
        $('b-inc').style.width = (r.incomeTax / t * 100) + '%';
        $('b-add').style.width = (r.addl / t * 100) + '%';
        $('r-se').textContent = money(r.seTax);
        $('r-inc').textContent = money(r.incomeTax);
        $('r-add').textContent = money(r.addl);
        $('r-add-row').hidden = !(r.addl > 0);

        // Spread what's still owed over the due dates that haven't passed yet
        var stillOwed = Math.max(0, r.total - v.wh - v.paid);
        var perDate = remaining.length ? stillOwed / remaining.length : 0;
        if (remaining.length === dates.length) {
          $('r-q-label').textContent = 'Pay each quarter';
        } else if (remaining.length) {
          $('r-q-label').textContent = 'Next payment, due ' + remaining[0].label.replace(/, \d{4}$/, '');
        } else {
          $('r-q-label').textContent = 'Left to pay by Apr 15, 2027';
          perDate = stillOwed;
        }
        $('r-q').textContent = money(perDate);
        var nextShown = false;
        $('r-dates').innerHTML = dates.map(function (d) {
          var cls = '', pill = 'Upcoming', amt = money(perDate);
          if (d.past) { cls = 'is-past'; pill = 'Passed'; amt = '—'; }
          else if (!nextShown) { nextShown = true; cls = 'is-next'; pill = 'Next'; }
          return '<li class="' + cls + '"><span class="q">' + d.q + '</span><span class="d">' + d.label +
                 '</span><span class="a">' + (r.total > 0 ? amt : '') + '</span><span class="cx-pill">' + pill + '</span></li>';
        }).join('');

        // Penalty-safe minimum
        var safeLeft = Math.max(0, r.safeHarbor - v.wh - v.paid);
        $('r-safe').hidden = !(hasIncome && r.afterWithholding >= 1000);
        $('r-safe').innerHTML = safeLeft > 0
          ? 'To avoid an underpayment penalty, pay at least <b>' + money(safeLeft) + '</b> more for 2026' +
            (v.prior > 0 ? ', based on ' + (r.safeHarbor < r.total * 0.9 - 0.5 ? 'your 2025 tax.' : '90% of this year’s tax.') : ' (90% of this year’s tax). Add your 2025 tax in step 4 to check if a lower amount works.')
          : 'Your withholding and payments already cover the minimum needed to avoid an underpayment penalty.';

        // What an extra $1,000 of expenses would save
        if (r.profit > 1000) {
          var r2 = calculate(Object.assign({}, v, { exp: v.exp + 1000 }));
          var save = r.total - r2.total;
          $('r-tip').hidden = !(save > 0);
          $('r-tip').innerHTML = 'Each extra <b>$1,000</b> of business expenses would lower your tax by about <b>' + money(save) + '</b>.';
        } else { $('r-tip').hidden = true; }
        $('r-set').textContent = Math.round(r.setAside * 100) + '%';

        $('r-owe-row').hidden = !(r.paid > 0);
        $('r-paid').textContent = money(r.paid);
        $('r-owe').textContent = r.owe >= 0 ? money(r.owe) : money(-r.owe) + ' refund';

        var notes = [];
        if (r.ret < v.ret) notes.push('Retirement contributions are limited to your net business profit, so only ' + money(r.ret) + ' is counted.');
        if (hasIncome && r.afterWithholding < 1000) notes.push('You expect to owe less than $1,000 after withholding, so you may not need to make quarterly payments.');
        if (r.profit > 0 && r.seBase === 0) notes.push('Your net earnings are under $400, so no self-employment tax applies.');
        if (r.profit < 0) notes.push('Your expenses are higher than your 1099 income, so this shows a business loss.');
        if (r.qbiLimited) notes.push('At your income the 20% business income deduction may be limited. A tax professional can confirm the exact amount.');
        $('r-note').hidden = !notes.length;
        $('r-note').textContent = notes.join(' ');

        var rows = [
          ['1099 income (NEC, K, MISC)', money(r.gross1099)],
          ['Business expenses', money(-r.expenses)],
          ['Net business profit', money(r.profit), 'sum'],
          ['Self-employment tax (15.3% of 92.35% of profit)', money(r.seTax)],
          ['W-2 wages and interest', money(v.w2 + v.int)],
          ['Half of self-employment tax', money(-r.halfSE)]
        ];
        if (r.health > 0) rows.push(['Health insurance deduction', money(-r.health)]);
        if (r.ret > 0) rows.push(['Retirement contributions', money(-r.ret)]);
        rows.push(['Adjusted gross income', money(r.agi), 'sum'],
                  ['Standard deduction', money(-r.std)],
                  ['Business income deduction (20%)', money(-r.qbi)],
                  ['Taxable income', money(r.taxable), 'sum'],
                  ['Federal income tax', money(r.incomeTax)]);
        if (r.addl > 0) rows.push(['Additional Medicare tax (0.9%)', money(r.addl)]);
        rows.push(['Total estimated tax', money(r.total), 'sum']);
        $('r-how').innerHTML = rows.map(function (x) {
          return '<tr' + (x[2] ? ' class="is-sum"' : '') + '><td>' + x[0] + '</td><td>' + x[1] + '</td></tr>';
        }).join('');
      }

      // Due dates: which are still ahead of today
      var today = new Date(); today.setHours(0, 0, 0, 0);
      var dates = TAX.dueDates.map(function (d) {
        var p = d[1].split('-'), date = new Date(+p[0], +p[1] - 1, +p[2]);
        return { q: d[0], date: date, past: date < today,
                 label: date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) };
      });
      var remaining = dates.filter(function (d) { return !d.past; });

      // Inputs: keep digits only, add thousands separators on blur
      allFields.forEach(function (id) {
        var el = $(id);
        el.addEventListener('input', function () {
          var clean = el.value.replace(moneyFields.indexOf(id) > -1 ? /[^0-9.]/g : /[^0-9]/g, '');
          var parts = clean.split('.');
          if (parts.length > 2) clean = parts[0] + '.' + parts.slice(1).join('');
          if (clean !== el.value.replace(/,/g, '')) el.value = clean;
          render();
        });
        el.addEventListener('focus', function () { el.value = el.value.replace(/,/g, ''); });
        el.addEventListener('blur', function () {
          var x = num(id);
          el.value = x ? x.toLocaleString('en-US', { maximumFractionDigits: 2 }) : '';
        });
      });
      form.querySelectorAll('input[name="status"]').forEach(function (r) { r.addEventListener('change', render); });
      $('hi').addEventListener('change', render);

      /* ---- Remember answers on this device, and share them as a link ---- */
      var KEY = 'taxmetter-1099-' + TAX.year;
      function snapshot() {
        var o = { status: form.querySelector('input[name="status"]:checked').value };
        if ($('hi').checked) o.hi = 1;
        allFields.forEach(function (id) { var x = num(id); if (x) o[id] = x; });
        return o;
      }
      function restore(o) {
        if (!o) return;
        var st = form.querySelector('input[name="status"][value="' + o.status + '"]');
        if (st) st.checked = true;
        $('hi').checked = !!(+o.hi);
        allFields.forEach(function (id) {
          var x = parseFloat(o[id]);
          $(id).value = isFinite(x) && x > 0 ? x.toLocaleString('en-US', { maximumFractionDigits: 2 }) : '';
        });
      }
      function save() { try { localStorage.setItem(KEY, JSON.stringify(snapshot())); } catch (e) {} }
      var fromUrl = null;
      try {
        var qs = new URLSearchParams(location.search);
        if (qs.has('status') || allFields.some(function (id) { return qs.has(id); })) {
          fromUrl = {}; qs.forEach(function (val, key) { fromUrl[key] = val; });
        }
      } catch (e) {}
      if (fromUrl) restore(fromUrl);
      else { try { restore(JSON.parse(localStorage.getItem(KEY) || 'null')); } catch (e) {} }
      form.addEventListener('input', save);
      form.addEventListener('change', save);

      var statusTimer;
      function flash(msg) {
        $('cx-status').textContent = msg;
        clearTimeout(statusTimer);
        statusTimer = setTimeout(function () { $('cx-status').textContent = ''; }, 3000);
      }
      $('cx-copy').addEventListener('click', function () {
        var o = snapshot(), q = Object.keys(o).map(function (k) { return k + '=' + encodeURIComponent(o[k]); }).join('&');
        var url = location.origin + location.pathname + '?' + q;
        var done = function () { flash('Link copied. Anyone with it sees these numbers.'); };
        if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(url).then(done, function () { window.prompt('Copy this link:', url); });
        else window.prompt('Copy this link:', url);
      });
      $('cx-print').addEventListener('click', function () { window.print(); });
      form.addEventListener('submit', function (e) { e.preventDefault(); });
      $('cx-reset').addEventListener('click', function () {
        allFields.forEach(function (id) { $(id).value = ''; });
        form.querySelector('input[value="single"]').checked = true;
        $('hi').checked = false;
        try { localStorage.removeItem(KEY); } catch (e) {}
        if (location.search && history.replaceState) history.replaceState(null, '', location.pathname + location.hash);
        render();
        $('nec').focus();
      });
      $('cx-go').addEventListener('click', function (e) {
        e.preventDefault();
        var res = $('cx-result');
        res.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
        res.focus({ preventScroll: true });
      });

      // Mobile summary bar: hide it while the result card is on screen
      var dock = $('cx-dock'), result = $('cx-result');
      dock.querySelector('a').addEventListener('click', function (e) {
        e.preventDefault();
        result.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
      });
      if ('IntersectionObserver' in window) {
        var seen = new Set();
        var io = new IntersectionObserver(function (en) {
          en.forEach(function (e) { if (e.isIntersecting) seen.add(e.target); else seen.delete(e.target); });
          dock.classList.toggle('is-away', seen.size > 0);
        }, { threshold: 0.15 });
        io.observe(result);
        document.querySelectorAll('.ew-foot, .cx-faq').forEach(function (el) { io.observe(el); });
      }

      // Open a question when the page is opened at its link (e.g. from the hero's question bubbles)
      function openFromHash() {
        var t = location.hash && document.getElementById(location.hash.slice(1));
        if (t && t.tagName === 'DETAILS') { t.open = true; t.scrollIntoView({ block: 'start' }); }
      }
      window.addEventListener('hashchange', openFromHash);

      render();
      requestAnimationFrame(function () { hero.classList.add('is-in'); openFromHash(); });
    }

})();
