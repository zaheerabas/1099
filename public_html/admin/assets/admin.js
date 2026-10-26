/* taxmetter admin: shared behaviour for every admin page */
(function () {
  'use strict';

  // Phone menu
  var menuBtn = document.querySelector('.ad-menu');
  var side = document.getElementById('ad-side');
  if (menuBtn && side) {
    menuBtn.addEventListener('click', function () {
      var open = side.classList.toggle('is-open');
      menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && side.classList.contains('is-open')) { side.classList.remove('is-open'); menuBtn.setAttribute('aria-expanded', 'false'); menuBtn.focus(); }
    });
  }

  // Confirm before destructive actions
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-confirm]');
    if (b && !window.confirm(b.getAttribute('data-confirm'))) { e.preventDefault(); e.stopImmediatePropagation(); }
  }, true);

  // Copy buttons
  document.querySelectorAll('[data-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
      var text = b.getAttribute('data-copy'), label = b.textContent;
      var done = function () { b.textContent = 'Copied'; setTimeout(function () { b.textContent = label; }, 1800); };
      if (navigator.clipboard && window.isSecureContext) navigator.clipboard.writeText(text).then(done, function () { window.prompt('Copy:', text); });
      else window.prompt('Copy:', text);
    });
  });

  // Bulk selection on the Articles page
  var bulk = document.querySelector('[data-bulk]');
  if (bulk) {
    var all = bulk.querySelector('[data-check-all]');
    var boxes = function () { return Array.prototype.slice.call(bulk.querySelectorAll('input[name="ids[]"]')); };
    var count = bulk.querySelector('.ad-bulk__count');
    var update = function () {
      var n = boxes().filter(function (b) { return b.checked; }).length;
      count.textContent = n ? n + ' selected' : '';
      if (all) { all.checked = n && n === boxes().length; all.indeterminate = n > 0 && n < boxes().length; }
    };
    if (all) all.addEventListener('change', function () { boxes().forEach(function (b) { b.checked = all.checked; }); update(); });
    bulk.addEventListener('change', function (e) { if (e.target.name === 'ids[]') update(); });
    bulk.addEventListener('submit', function (e) {
      if (e.submitter && e.submitter.hasAttribute('form')) return;   // single-row delete buttons
      var action = bulk.querySelector('[name="action"]').value;
      var n = boxes().filter(function (b) { return b.checked; }).length;
      if (!action) { e.preventDefault(); alert('Choose what to do with the selected articles.'); return; }
      if (!n) { e.preventDefault(); alert('Select at least one article.'); return; }
      if (action === 'delete' && !confirm('Delete ' + n + (n === 1 ? ' article' : ' articles') + '? This can’t be undone.')) e.preventDefault();
    });
  }

  // Character counters: data-count="recommended length"
  document.querySelectorAll('[data-count]').forEach(function (f) {
    var limit = parseInt(f.getAttribute('data-count'), 10);
    var out = document.createElement('span');
    out.className = 'ad-counter';
    out.setAttribute('aria-live', 'polite');
    f.insertAdjacentElement('afterend', out);
    var upd = function () {
      var n = f.value.length;
      out.textContent = n + ' / ' + limit + ' characters' + (n > limit ? ', may be cut off' : '');
      out.classList.toggle('is-over', n > limit);
    };
    f.addEventListener('input', upd); upd();
  });

  // Web address (slug) follows the title until it's edited by hand
  var slugify = function (s) {
    return s.toLowerCase().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 120);
  };
  window.TMslugify = slugify;
  document.querySelectorAll('[data-slug]').forEach(function (slug) {
    var src = document.getElementById('title') || document.querySelector('[data-slug-source]');
    var locked = slug.getAttribute('data-locked') === '1' && slug.value !== '';
    slug.addEventListener('input', function () { locked = slug.value !== ''; });
    slug.addEventListener('blur', function () { slug.value = slugify(slug.value); slug.dispatchEvent(new Event('change')); });
    if (src) src.addEventListener('input', function () { if (!locked) { slug.value = slugify(src.value); slug.dispatchEvent(new Event('change')); } });
  });

  // Media page: show how many files were picked, and accept drops
  var drop = document.querySelector('[data-drop-input]');
  if (drop) {
    var label = drop.closest('.ad-drop'), out = document.querySelector('[data-drop-count]');
    var show = function () { var n = drop.files.length; out.textContent = n ? n + (n === 1 ? ' image chosen' : ' images chosen') : 'No images chosen'; };
    drop.addEventListener('change', show);
    ['dragenter', 'dragover'].forEach(function (t) { label.addEventListener(t, function (e) { e.preventDefault(); label.classList.add('is-over'); }); });
    ['dragleave', 'drop'].forEach(function (t) { label.addEventListener(t, function () { label.classList.remove('is-over'); }); });
    label.addEventListener('drop', function (e) { e.preventDefault(); drop.files = e.dataTransfer.files; show(); });
  }

  // Warn before leaving a form with unsaved changes
  document.querySelectorAll('[data-dirty-check]').forEach(function (form) {
    var dirty = false;
    form.addEventListener('input', function () { dirty = true; });
    form.addEventListener('change', function () { dirty = true; });
    form.addEventListener('tm:dirty', function () { dirty = true; });
    form.addEventListener('submit', function () { dirty = false; });
    window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
  });
})();
