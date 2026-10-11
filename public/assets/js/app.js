(function () {
    'use strict';

    document.querySelectorAll('form[data-live-search]').forEach(function (form) {
        var status = form.querySelector('[data-live-status]');
        var timer;

        form.querySelectorAll('input[type="text"], input[type="search"]').forEach(function (input) {
            input.addEventListener('input', function () {
                clearTimeout(timer);
                if (status) status.textContent = 'Searching…';
                timer = setTimeout(function () { form.submit(); }, 400);
            });
        });

        form.querySelectorAll('select, input[type="date"], input[type="datetime-local"]').forEach(function (control) {
            control.addEventListener('change', function () { form.submit(); });
        });
    });

    var liveInput = document.querySelector('form[data-live-search] input[autofocus]');
    if (liveInput) {
        var value = liveInput.value;
        liveInput.value = '';
        liveInput.value = value;
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-password-toggle]');
        if (!btn) return;

        var input = document.getElementById(btn.getAttribute('data-password-toggle'));
        if (!input) return;

        var reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';

        var icon = btn.querySelector('i');
        if (icon) icon.className = reveal ? 'bi bi-eye-slash' : 'bi bi-eye';
        btn.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
    });

    (function () {
        var overlay, imgEl, captionEl, group = [], index = 0;

        function build() {
            overlay = document.createElement('div');
            overlay.className = 'lightbox';
            overlay.innerHTML =
                '<button class="lightbox__close" aria-label="Close">&times;</button>' +
                '<button class="lightbox__nav lightbox__nav--prev" aria-label="Previous">&#8249;</button>' +
                '<figure class="lightbox__figure">' +
                '<img class="lightbox__img" alt="">' +
                '<figcaption class="lightbox__caption"></figcaption>' +
                '</figure>' +
                '<button class="lightbox__nav lightbox__nav--next" aria-label="Next">&#8250;</button>';
            document.body.appendChild(overlay);

            imgEl = overlay.querySelector('.lightbox__img');
            captionEl = overlay.querySelector('.lightbox__caption');

            overlay.querySelector('.lightbox__close').addEventListener('click', close);
            overlay.querySelector('.lightbox__nav--prev').addEventListener('click', function (e) {
                e.stopPropagation();
                step(-1);
            });
            overlay.querySelector('.lightbox__nav--next').addEventListener('click', function (e) {
                e.stopPropagation();
                step(1);
            });
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay || e.target.classList.contains('lightbox__figure')) close();
            });
        }

        function show() {
            var link = group[index];
            imgEl.src = link.getAttribute('href');
            imgEl.alt = link.getAttribute('data-caption') || 'Screenshot';
            captionEl.textContent = (link.getAttribute('data-caption') || '') +
                ' (' + (index + 1) + ' of ' + group.length + ')';
            overlay.classList.toggle('lightbox--single', group.length < 2);
        }

        function step(delta) {
            index = (index + delta + group.length) % group.length;
            show();
        }

        function open(link) {
            var container = link.closest('[data-lightbox-group]') || document;
            group = Array.prototype.slice.call(container.querySelectorAll('[data-lightbox]'));
            index = Math.max(0, group.indexOf(link));

            if (!overlay) build();
            show();
            overlay.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function close() {
            if (!overlay) return;
            overlay.classList.remove('is-open');
            document.body.style.overflow = '';
            imgEl.src = '';
        }

        document.addEventListener('click', function (e) {
            var link = e.target.closest('[data-lightbox]');
            if (!link) return;
            e.preventDefault();
            open(link);
        });

        document.addEventListener('keydown', function (e) {
            if (!overlay || !overlay.classList.contains('is-open')) return;
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowLeft') step(-1);
            if (e.key === 'ArrowRight') step(1);
        });
    })();

    (function () {
        var overlay, frame, image, titleEl;

        function build() {
            overlay = document.createElement('div');
            overlay.className = 'file-preview';
            overlay.innerHTML =
                '<div class="file-preview__bar">' +
                    '<span class="file-preview__title"></span>' +
                    '<span class="file-preview__actions">' +
                        '<a class="file-preview__open" target="_blank" rel="noopener" title="Open in new tab">' +
                            '<i class="bi bi-box-arrow-up-right"></i></a>' +
                        '<button type="button" class="file-preview__close" aria-label="Close preview">' +
                            '<i class="bi bi-x-lg"></i></button>' +
                    '</span>' +
                '</div>' +
                '<div class="file-preview__body">' +
                    '<iframe title="File preview"></iframe>' +
                    '<img alt="File preview">' +
                '</div>';

            document.body.appendChild(overlay);
            frame = overlay.querySelector('iframe');
            image = overlay.querySelector('img');
            titleEl = overlay.querySelector('.file-preview__title');

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay || e.target.closest('.file-preview__close')) close();
            });
        }

        function open(link) {
            if (!overlay) build();

            var isImage = link.dataset.previewKind === 'image';
            titleEl.textContent = link.dataset.previewTitle || 'Preview';
            overlay.querySelector('.file-preview__open').href = link.href;

            frame.src = isImage ? '' : link.href;
            image.src = isImage ? link.href : '';
            frame.hidden = isImage;
            image.hidden = !isImage;

            overlay.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function close() {
            if (!overlay) return;
            overlay.classList.remove('is-open');
            document.body.style.overflow = '';
            frame.src = '';
            image.src = '';
        }

        document.addEventListener('click', function (e) {
            var link = e.target.closest('[data-file-preview]');
            if (!link) return;
            e.preventDefault();
            open(link);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay && overlay.classList.contains('is-open')) close();
        });
    })();

    document.querySelectorAll('[data-bulk-scope]').forEach(function (scope) {
        var bar = scope.querySelector('[data-bulk-bar]');
        var count = scope.querySelector('[data-bulk-count]');
        var all = scope.querySelector('[data-bulk-all]');

        var boxes = function () {
            return Array.prototype.slice
                .call(scope.querySelectorAll('[data-bulk-item]'))
                .filter(function (c) { return !c.disabled; });
        };
        var chosen = function () {
            return boxes().filter(function (c) { return c.checked; });
        };

        function refresh() {
            var sel = chosen();
            if (count) count.textContent = sel.length;
            if (bar) bar.classList.toggle('d-none', sel.length === 0);
            if (all) {
                var every = boxes();
                all.checked = every.length > 0 && every.every(function (c) { return c.checked; });
            }
        }

        scope.addEventListener('change', function (e) {
            if (e.target.hasAttribute('data-bulk-item')) {
                refresh();
            } else if (e.target.hasAttribute('data-bulk-all')) {
                boxes().forEach(function (c) { c.checked = e.target.checked; });
                refresh();
            }
        });

        scope.addEventListener('click', function (e) {
            var clear = e.target.closest('[data-bulk-clear]');
            if (clear) {
                boxes().forEach(function (c) { c.checked = false; });
                if (all) all.checked = false;
                refresh();
                return;
            }

            var btn = e.target.closest('[data-bulk-submit]');
            if (!btn) return;

            var ids = chosen().map(function (c) { return c.value; });
            if (!ids.length) return;

            var ask = btn.dataset.bulkConfirm;
            if (ask && !confirm(ask.replace('{n}', ids.length))) return;

            var form = document.getElementById(btn.dataset.bulkForm);
            if (!form) return;

            var slot = form.querySelector('[data-bulk-ids]');
            if (!slot) return;
            slot.innerHTML = '';
            ids.forEach(function (id) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = btn.dataset.bulkField;
                input.value = id;
                slot.appendChild(input);
            });
            form.submit();
        });

        refresh();
    });

    document.addEventListener('show.bs.dropdown', function (e) {
        var wrap = e.target.closest('.table-responsive');
        if (wrap) {
            wrap.dataset.prevOverflow = wrap.style.overflow;
            wrap.style.overflow = 'visible';
        }
    });
    document.addEventListener('hide.bs.dropdown', function (e) {
        var wrap = e.target.closest('.table-responsive');
        if (wrap) {
            wrap.style.overflow = wrap.dataset.prevOverflow || '';
        }
    });
})();
