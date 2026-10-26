/* taxmetter: home page hero (step dots, count-up, graph scrub, card tilt, pause motion) */
(function () {
  var hero = document.getElementById('ew-hero');
  if (!hero) return;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    /* ---- Progress dots (auto-advance with a fill bar) ---- */
    var stepsWrap = hero.querySelector('.ew-steps');
    var steps = hero.querySelectorAll('.ew-step');
    var active = 0, timer = null, paused = false, userPicked = false;
    var stepNames = ['Add', 'Calculate', 'Save'];
    var words = hero.querySelectorAll('.ew-word');
    var lines = hero.querySelectorAll('.ew-tagline > span');
    // which card goes with each step: Add -> 1099 forms, Calculate -> tax answers, Save -> tax estimate
    var stepCards = [
      hero.querySelector('.ew-card--connect'),
      hero.querySelector('.ew-card--learn'),
      hero.querySelector('.ew-card--earn')
    ];
    function setActive(index) {
      active = index;
      steps.forEach(function (btn, i) {
        btn.classList.toggle('is-active', i === index);
        btn.classList.toggle('is-done', i < index);
        btn.setAttribute('aria-label', 'Step ' + (i + 1) + ' of ' + steps.length + ': ' + stepNames[i]);
        if (i === index) btn.setAttribute('aria-current', 'step'); else btn.removeAttribute('aria-current');
      });
      words.forEach(function (w, i) { w.classList.toggle('is-active', i === index); });
      lines.forEach(function (l, i) { l.classList.toggle('is-active', i === index); });
      stepCards.forEach(function (c, i) { if (c) c.classList.toggle('is-spot', i === index); });
    }
    function stopDots() { clearInterval(timer); timer = null; stepsWrap.classList.remove('is-auto'); }
    function startDots() {
      if (reduce || paused || userPicked || timer) return;
      stepsWrap.classList.add('is-auto');
      setActive(active);
      timer = setInterval(function () {
        if (!hero.classList.contains('is-offscreen')) setActive((active + 1) % steps.length);
      }, 3000);
    }
    steps.forEach(function (btn, i) {
      btn.addEventListener('click', function () { userPicked = true; stopDots(); setActive(i); });
    });
    setActive(0);

    /* ---- Count-up numbers ---- */
    function countUp(el) {
      var target = parseFloat(el.getAttribute('data-count'));
      var decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
      var prefix = el.getAttribute('data-prefix') || '';
      var suffix = el.getAttribute('data-suffix') || '';
      var delay = parseInt(el.getAttribute('data-delay') || '0', 10);
      function fmt(v) {
        return prefix + v.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }) + suffix;
      }
      el.textContent = fmt(0);
      setTimeout(function () {
        var start = null;
        function frame(t) {
          if (!start) start = t;
          var p = Math.min((t - start) / 1500, 1);
          el.textContent = fmt(target * (1 - Math.pow(1 - p, 4)));
          if (p < 1) requestAnimationFrame(frame);
        }
        requestAnimationFrame(frame);
      }, delay);
    }

    /* ---- Tax graph: scrub to see tax owed by each quarter ---- */
    (function () {
      var plot = hero.querySelector('.ew-plot');
      if (!plot) return;
      var svg = plot.querySelector('svg');
      var line = plot.querySelector('.ew-earn-line');
      var tip = plot.querySelector('.ew-tip');
      var tipVal = tip.querySelector('b'), tipDay = tip.querySelector('span');
      var guide = plot.querySelector('.ew-guide');
      var dots = [plot.querySelector('.ew-pt-halo'), plot.querySelector('.ew-pt-dot'), plot.querySelector('.ew-pulse')];
      var days = ['Q1', 'Q2', 'Q3', 'Q4'];
      var TOTAL = 12037; // full-year tax shown on the card (top of the graph, y = 20)
      var dayEls = hero.querySelectorAll('.ew-day');
      function markDay(i) { dayEls.forEach(function (d, k) { d.classList.toggle('is-on', k === i); }); }
      var samples = [], total = line.getTotalLength();
      for (var k = 0; k <= 150; k++) { var pt = line.getPointAtLength(total * k / 150); samples.push({ x: pt.x, y: pt.y }); }
      function moveTo(x, y) {
        dots.forEach(function (c) { c.setAttribute('cx', x); c.setAttribute('cy', y); });
        guide.setAttribute('x1', x); guide.setAttribute('x2', x); guide.setAttribute('y1', y);
      }
      function nearest(x) {
        var best = samples[0];
        for (var i = 1; i < samples.length; i++) { if (Math.abs(samples[i].x - x) < Math.abs(best.x - x)) best = samples[i]; }
        return best;
      }
      function onMove(e) {
        var r = svg.getBoundingClientRect();
        var x = Math.max(0, Math.min(300, (e.clientX - r.left) / r.width * 300));
        var p = nearest(x);
        moveTo(p.x, p.y);
        var value = Math.max(0, Math.min(TOTAL, TOTAL * (140 - p.y) / 120));
        tipVal.textContent = '$' + Math.round(value).toLocaleString('en-US');
        var di = Math.round(p.x / 300 * (days.length - 1));
        tipDay.textContent = days[di];
        markDay(di);
        tip.style.left = (p.x / 300 * 100) + '%';
        // As a share of the plot's height, so it lines up at any page zoom
        tip.style.top = (p.y / 140 * r.height / plot.getBoundingClientRect().height * 100) + '%';
        plot.classList.add('is-scrub');
      }
      function onLeave() { plot.classList.remove('is-scrub'); moveTo(200, 50); markDay(2); }
      svg.addEventListener('pointermove', onMove);
      svg.addEventListener('pointerdown', onMove);
      svg.addEventListener('pointerleave', onLeave);
      svg.addEventListener('pointercancel', onLeave);
    })();

    /* ---- Card tilt + light (mouse only) ---- */
    if (finePointer && !reduce) {
      hero.querySelectorAll('.ew-card').forEach(function (card) {
        card.addEventListener('pointermove', function (e) {
          var r = card.getBoundingClientRect();
          var px = (e.clientX - r.left) / r.width, py = (e.clientY - r.top) / r.height;
          card.classList.add('is-tilting');
          card.style.setProperty('--ry', ((px - .5) * 7).toFixed(2) + 'deg');
          card.style.setProperty('--rx', ((.5 - py) * 7).toFixed(2) + 'deg');
          card.style.setProperty('--gx', (px * 100).toFixed(1) + '%');
          card.style.setProperty('--gy', (py * 100).toFixed(1) + '%');
        });
        var releaseTimer;
        card.addEventListener('pointerleave', function () {
          card.classList.remove('is-tilting');
          card.classList.add('is-releasing');
          card.style.setProperty('--rx', '0deg');
          card.style.setProperty('--ry', '0deg');
          clearTimeout(releaseTimer);
          releaseTimer = setTimeout(function () { card.classList.remove('is-releasing'); }, 650);
        });
      });
    }

    /* ---- Pause / play all looping motion ---- */
    var motionBtn = hero.querySelector('.ew-motion');
    var motionLabel = hero.querySelector('.ew-motion__label');
    if (motionBtn) {
      motionBtn.addEventListener('click', function () {
        paused = !paused;
        hero.classList.toggle('is-paused', paused);
        motionBtn.setAttribute('aria-pressed', paused ? 'true' : 'false');
        motionLabel.textContent = paused ? 'Play motion' : 'Pause motion';
        if (paused) stopDots(); else startDots();
      });
    }

    /* ---- Start when visible; pause loops while off-screen ---- */
    function start() {
      if (hero.classList.contains('is-in')) return;
      hero.classList.add('is-in');
      if (reduce) return;
      hero.querySelectorAll('[data-count]').forEach(countUp);
      setTimeout(startDots, 1800);
    }
    if (reduce || !('IntersectionObserver' in window)) {
      start();
    } else {
      new IntersectionObserver(function (entries) {
        var visible = entries[0].isIntersecting;
        hero.classList.toggle('is-offscreen', !visible);
        if (visible) start();
      }, { threshold: 0.12 }).observe(hero.querySelector('.ew-home-hero') || hero);
    }

})();
