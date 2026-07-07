/* ------------------------------------------------------------------ *
 * Global UI behaviour
 * Loaded on every authenticated page (after Bootstrap's bundle).
 * Everything here is delegated / guarded, so it is safe to run on
 * pages that don't have the elements it looks for.
 * ------------------------------------------------------------------ */
(function () {
    'use strict';

    // --- Live search: auto-submit filter forms as the user types ---------
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

        form.querySelectorAll('select').forEach(function (select) {
            select.addEventListener('change', function () { form.submit(); });
        });
    });

    // Put the cursor at the end of an autofocused search box after reload.
    var liveInput = document.querySelector('form[data-live-search] input[autofocus]');
    if (liveInput) {
        var value = liveInput.value;
        liveInput.value = '';
        liveInput.value = value;
    }

    // --- Show / hide password --------------------------------------------
    // Any [data-password-toggle="<input id>"] button flips its field.
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

    // --- Dropdowns inside scrolling tables -------------------------------
    // Let action menus overflow a horizontally-scrolling table instead of
    // being clipped by it.
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
