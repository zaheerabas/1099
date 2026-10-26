/* taxmetter admin: article editor (Quill 2), image uploads, live counters and previews */
(function () {
  'use strict';
  var form = document.getElementById('post-form');
  var holder = document.getElementById('quill');
  if (!form || !holder || !window.Quill) return;
  var textarea = document.getElementById('content');

  function uploadImage(file, alt) {
    var fd = new FormData();
    fd.append('image', file);
    fd.append('alt', alt || '');
    return fetch('/admin/upload.php', { method: 'POST', body: fd, headers: { 'X-CSRF-Token': window.TM_CSRF }, credentials: 'same-origin' })
      .then(function (r) { return r.json().then(function (j) { if (!r.ok) throw new Error(j.error || 'Upload failed.'); return j; }); });
  }

  var status = document.createElement('p');
  status.className = 'ad-upload-status';
  status.setAttribute('role', 'status');
  holder.parentNode.insertAdjacentElement('afterend', status);

  function insertFiles(range, files) {
    var list = Array.prototype.filter.call(files, function (f) { return /^image\/(jpeg|png|webp|gif)$/.test(f.type); });
    if (!list.length) { status.textContent = 'Use a JPG, PNG, WebP or GIF image.'; return; }
    var index = range ? range.index : quill.getLength();
    status.textContent = 'Uploading ' + (list.length === 1 ? 'image' : list.length + ' images') + '…';
    list.reduce(function (p, file) {
      return p.then(function () {
        var alt = window.prompt('Describe this image for people who can’t see it (' + file.name + '). Leave empty if it’s only decoration.', '') || '';
        return uploadImage(file, alt.trim()).then(function (res) {
          quill.insertEmbed(index, 'image', res.url, 'user');
          if (alt.trim()) quill.formatText(index, 1, 'alt', alt.trim(), 'user');
          index += 1;
          quill.setSelection(index, 0, 'silent');
        });
      });
    }, Promise.resolve()).then(function () { status.textContent = 'Image added.'; form.dispatchEvent(new Event('tm:dirty')); })
      .catch(function (err) { status.textContent = err.message; });
  }

  var quill = new Quill(holder, {
    theme: 'snow',
    placeholder: 'Start writing…',
    modules: {
      toolbar: {
        container: [
          [{ header: [2, 3, false] }],
          ['bold', 'italic', 'underline', 'strike'],
          [{ list: 'ordered' }, { list: 'bullet' }],
          ['link', 'blockquote', 'code'],
          ['image'],
          ['clean']
        ],
        handlers: {
          image: function () {
            var input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/jpeg,image/png,image/webp,image/gif';
            input.onchange = function () { if (input.files.length) insertFiles(quill.getSelection(true), input.files); };
            input.click();
          }
        }
      },
      uploader: { mimetypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], handler: insertFiles }
    }
  });

  // Toolbar buttons get readable names for screen readers
  var names = { bold: 'Bold', italic: 'Italic', underline: 'Underline', strike: 'Strikethrough', link: 'Link', blockquote: 'Quote', code: 'Code', image: 'Image', clean: 'Clear formatting' };
  form.querySelectorAll('.ql-toolbar button').forEach(function (b) {
    var cls = Array.prototype.find.call(b.classList, function (c) { return c.indexOf('ql-') === 0; });
    var key = cls ? cls.slice(3) : '';
    var label = key === 'list' ? (b.value === 'ordered' ? 'Numbered list' : 'Bulleted list') : names[key];
    if (label) { b.setAttribute('aria-label', label); b.title = label; }
  });
  form.querySelectorAll('.ql-picker-label').forEach(function (l) { l.setAttribute('aria-label', 'Text style: heading or paragraph'); });
  quill.root.setAttribute('aria-labelledby', 'content-label');
  quill.root.setAttribute('role', 'textbox');
  quill.root.setAttribute('aria-multiline', 'true');

  if (textarea.value.trim()) {
    quill.clipboard.dangerouslyPasteHTML(0, textarea.value, 'silent');
    quill.history.clear();
  }

  // Word count and reading time
  var words = form.querySelector('[data-words]'), read = form.querySelector('[data-read]');
  function stats() {
    var t = quill.getText().trim();
    var n = t ? t.split(/\s+/).length : 0;
    words.textContent = n.toLocaleString() + (n === 1 ? ' word' : ' words');
    read.textContent = Math.max(1, Math.ceil(n / 225)) + ' min read';
  }
  quill.on('text-change', function (d, o, source) { stats(); if (source === 'user') form.dispatchEvent(new Event('tm:dirty')); });
  stats();

  // Put the editor's HTML into the form when saving
  form.addEventListener('submit', function () {
    var empty = quill.getText().trim() === '' && !quill.root.querySelector('img');
    textarea.value = empty ? '' : quill.getSemanticHTML().replace(/&nbsp;/g, ' ');
  });

  // Publish / save / unpublish buttons
  var intent = form.querySelector('[name="intent"]');
  form.querySelectorAll('[data-intent]').forEach(function (b) {
    b.addEventListener('click', function () { intent.value = b.getAttribute('data-intent'); });
  });
  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
      e.preventDefault();
      intent.value = 'save';
      if (form.requestSubmit) form.requestSubmit(); else form.submit();
    }
  });

  // Search result preview
  var title = document.getElementById('title'), slug = document.getElementById('slug');
  var seoT = document.getElementById('seo_title'), seoD = document.getElementById('seo_description'), ex = document.getElementById('excerpt');
  var pT = form.querySelector('[data-serp-title]'), pD = form.querySelector('[data-serp-desc]'), pS = form.querySelector('[data-serp-slug]');
  function serp() {
    pT.textContent = (seoT.value || title.value || 'Article title');
    pD.textContent = (seoD.value || ex.value || quill.getText().trim().slice(0, 155) || 'The summary or the first lines of the article appear here.');
    pS.textContent = slug.value || '…';
  }
  [title, slug, seoT, seoD, ex].forEach(function (f) { f.addEventListener('input', serp); f.addEventListener('change', serp); });
  quill.on('text-change', serp);
  serp();

  // Cover image: preview a new file, pick from media, or remove
  var img = form.querySelector('[data-cover-img]'), empty = form.querySelector('[data-cover-empty]');
  var file = form.querySelector('[data-cover-file]'), existing = form.querySelector('[data-cover-existing]');
  var remove = form.querySelector('[data-cover-remove]'), clear = form.querySelector('[data-cover-clear]');
  var alt = document.getElementById('cover_alt');
  function showCover(src) {
    img.hidden = !src; empty.hidden = !!src; clear.hidden = !src;
    if (src) img.src = src;
  }
  file.addEventListener('change', function () {
    if (!file.files.length) return;
    existing.value = ''; remove.value = '';
    showCover(URL.createObjectURL(file.files[0]));
  });
  form.querySelectorAll('[data-pick]').forEach(function (b) {
    b.addEventListener('click', function () {
      existing.value = b.getAttribute('data-pick'); remove.value = ''; file.value = '';
      if (!alt.value && b.getAttribute('data-pick-alt')) alt.value = b.getAttribute('data-pick-alt');
      showCover('/' + b.getAttribute('data-pick'));
      b.closest('details').open = false;
      form.dispatchEvent(new Event('tm:dirty'));
    });
  });
  clear.addEventListener('click', function () {
    remove.value = '1'; existing.value = ''; file.value = '';
    showCover('');
    form.dispatchEvent(new Event('tm:dirty'));
  });
})();
