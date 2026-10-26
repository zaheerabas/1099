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

    // Scrolled pill, hide on scroll down / show on scroll up, progress bar
    if (header) {
      var bar = header.querySelector('.ew-progress i');
      var lastY = window.pageYOffset, ticking = false;
      var onScroll = function () {
        ticking = false;
        var y = window.pageYOffset;
        header.classList.toggle('is-scrolled', y > 8);
        var max = document.documentElement.scrollHeight - window.innerHeight;
        if (bar) bar.style.setProperty('--p', max > 0 ? Math.min(1, y / max).toFixed(4) : 0);
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


    /* ---- Content pages: play the entrance once (the home page starts its own) ---- */
    if (!document.querySelector('.ew-home-hero')) requestAnimationFrame(function () { hero.classList.add('is-in'); });

})();
