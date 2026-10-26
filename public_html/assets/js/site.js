/* taxmetter: header menu, sliding nav highlight, scroll states (every page) */
(function () {
  var hero = document.getElementById('ew-hero');
  if (!hero) return;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    /* ---- Header: menu, sliding nav highlight, scroll states ---- */
    var header = hero.querySelector('.ew-header');
    var burger = hero.querySelector('.ew-burger');
    var menu = hero.querySelector('.ew-menu');
    var mobileQuery = window.matchMedia('(max-width: 720px)');
    function closeMenu() {
      if (!menu || !menu.classList.contains('is-open')) return;
      menu.classList.remove('is-open');
      burger.setAttribute('aria-expanded', 'false');
      burger.setAttribute('aria-label', 'Open menu');
    }
    if (burger && menu) {
      burger.addEventListener('click', function () {
        var open = menu.classList.toggle('is-open');
        burger.setAttribute('aria-expanded', open ? 'true' : 'false');
        burger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        if (open) header.classList.remove('is-hidden');
      });
      menu.addEventListener('click', function (e) { if (e.target.closest('a')) closeMenu(); });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && menu.classList.contains('is-open')) { closeMenu(); burger.focus(); }
      });
      document.addEventListener('click', function (e) { if (!e.target.closest('.ew-header')) closeMenu(); });
      mobileQuery.addEventListener ? mobileQuery.addEventListener('change', closeMenu) : mobileQuery.addListener(closeMenu);
    }

    // Sliding highlight behind the hovered / current link (desktop)
    var nav = hero.querySelector('.ew-nav');
    if (nav) {
      var navLinks = nav.querySelectorAll('a:not(.ew-login--sheet)');
      var current = nav.querySelector('a[aria-current="page"]');
      var placePill = function (a, instant) {
        if (!a || mobileQuery.matches) { nav.classList.remove('has-pill'); return; }
        if (instant) nav.classList.add('no-anim');
        nav.style.setProperty('--x', a.offsetLeft + 'px');
        nav.style.setProperty('--w', a.offsetWidth + 'px');
        nav.classList.add('has-pill');
        if (instant) requestAnimationFrame(function () { nav.classList.remove('no-anim'); });
      };
      navLinks.forEach(function (a) {
        a.addEventListener('mouseenter', function () { placePill(a); });
        a.addEventListener('focus', function () { placePill(a); });
      });
      nav.addEventListener('mouseleave', function () { placePill(current); });
      nav.addEventListener('focusout', function (e) { if (!nav.contains(e.relatedTarget)) placePill(current); });
      window.addEventListener('resize', function () { placePill(current, true); });
      if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { placePill(current, true); });
      placePill(current, true);
    }

    // Scrolled pill, hide on scroll down / show on scroll up
    if (header) {
      var lastY = window.pageYOffset, ticking = false;
      var onScroll = function () {
        ticking = false;
        var y = window.pageYOffset;
        header.classList.toggle('is-scrolled', y > 8);
        var menuOpen = menu && menu.classList.contains('is-open');
        if (y > lastY + 4 && y > 240 && !menuOpen && !header.contains(document.activeElement)) header.classList.add('is-hidden');
        else if (y < lastY - 4 || y <= 240) header.classList.remove('is-hidden');
        lastY = y;
      };
      window.addEventListener('scroll', function () {
        if (!ticking) { ticking = true; requestAnimationFrame(onScroll); }
      }, { passive: true });
      header.addEventListener('focusin', function () { header.classList.remove('is-hidden'); });
      onScroll();
    }


    /* ---- Open a question when the page is opened at its link (#id on a <details>) ---- */
    function openFromHash() {
      var id = location.hash.slice(1);
      var t = id && document.getElementById(id);
      if (t && t.tagName === 'DETAILS') { t.open = true; t.scrollIntoView({ block: 'start', behavior: reduce ? 'auto' : 'smooth' }); }
    }
    window.addEventListener('hashchange', openFromHash);
    if (document.readyState === 'complete') openFromHash(); else window.addEventListener('load', openFromHash);

    /* ---- Sections rise in as they scroll into view (skipped for reduced motion) ---- */
    var revealIO = (!reduce && 'IntersectionObserver' in window) ? new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('is-visible'); revealIO.unobserve(en.target); } });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.08 }) : null;
    window.TMReveal = function (el) { if (revealIO) revealIO.observe(el); else el.classList.add('is-visible'); };
    document.querySelectorAll('[data-reveal]').forEach(window.TMReveal);

    /* ---- Images: a shimmer placeholder until each picture has loaded ---- */
    document.querySelectorAll('main img').forEach(function (img) {
      if (img.complete && img.naturalWidth) return;
      var box = img.parentElement;
      box.classList.add('sk-media');
      var done = function () { box.classList.remove('sk-media'); box.classList.add('is-loaded'); };
      img.addEventListener('load', done, { once: true });
      img.addEventListener('error', done, { once: true });
    });

    /* ---- Small confirmation message, e.g. "Link copied" ---- */
    var toastEl = null, toastTimer;
    window.TMToast = function (msg) {
      if (!toastEl) {
        toastEl = document.createElement('div');
        toastEl.className = 'ew-toast';
        toastEl.setAttribute('role', 'status');
        toastEl.setAttribute('aria-live', 'polite');
        document.body.appendChild(toastEl);
      }
      toastEl.textContent = msg;
      toastEl.classList.add('is-on');
      clearTimeout(toastTimer);
      toastTimer = setTimeout(function () { toastEl.classList.remove('is-on'); }, 2400);
    };

    /* ---- Back to top, on long pages once the reader is well down ---- */
    if (document.documentElement.scrollHeight > window.innerHeight * 3) {
      var top = document.createElement('button');
      top.type = 'button';
      top.className = 'ew-top';
      top.setAttribute('aria-label', 'Back to top');
      top.innerHTML = '<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5"/><path d="m6 11 6-6 6 6"/></svg>';
      document.body.appendChild(top);
      top.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
        var skip = document.querySelector('.ew-brand'); if (skip) skip.focus({ preventScroll: true });
      });
      var topTick = false;
      window.addEventListener('scroll', function () {
        if (topTick) return; topTick = true;
        requestAnimationFrame(function () { topTick = false; top.classList.toggle('is-on', window.pageYOffset > window.innerHeight * 1.5); });
      }, { passive: true });
    }

    /* ---- Content pages: play the entrance once (the home page starts its own) ---- */
    if (!document.querySelector('.ew-home-hero')) requestAnimationFrame(function () { hero.classList.add('is-in'); });

})();
