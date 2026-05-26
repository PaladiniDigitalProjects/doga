(function($) {
    'use strict';

    let searchTimeout  = null;
    let currentRequest = null;

    $(document).ready(function() {

        // ── Overlay global (inyectado en wp_footer) ──
        const $globalOverlay = $('#ps-overlay-global');
        if ($globalOverlay.length) {
            initOverlay($globalOverlay);

            // Neutralizar el href del .search-nav en el DOM para
            // evitar que el bloque de navegación de WP lance la URL
            $('.search-nav a').each(function() {
                $(this)
                    .attr('data-ps-trigger', '')
                    .attr('href', '#')
                    .attr('role', 'button');
            });

            // Interceptar el item de menú .search-nav y triggers con data-ps-trigger
            $(document).on('click', '.search-nav a, .search-nav button, [data-ps-trigger]', function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                openOverlay($globalOverlay);
            });
        }

        // ── Shortcode triggers (ps-trigger legacy) ──
        $('.ps-trigger').each(function() {
            const $trigger  = $(this);
            const overlayId = $trigger.attr('aria-controls');
            const $overlay  = overlayId ? $('#' + overlayId) : $globalOverlay;
            if ($overlay && $overlay.length) {
                $trigger.on('click', function() {
                    openOverlay($overlay);
                });
            }
        });

        if (typeof psAjax !== 'undefined' && psAjax.diagnostic) {
            runDiagnosticChecklist();
        }
    });

    // ─────────────────────────────────────────────────────────
    // OVERLAY
    // ─────────────────────────────────────────────────────────

    function openOverlay($overlay) {
        $overlay.removeAttr('hidden');
        $('body').addClass('ps-overlay-open');
        // Marcar triggers vinculados como expandidos
        $('[aria-controls="' + $overlay.attr('id') + '"]').attr('aria-expanded', 'true');
        setTimeout(function() {
            $overlay.addClass('is-open');
            $overlay.find('.ps-search-input').focus();
        }, 10);
    }

    function initOverlay($overlay) {
        const $input     = $overlay.find('.ps-search-input');
        const $clearBtn  = $overlay.find('.ps-search-clear');
        const $closeBtn  = $overlay.find('.ps-overlay__close');
        const $backdrop  = $overlay.find('.ps-overlay__backdrop');
        const $grid      = $overlay.find('.ps-results-grid');
        const $loading   = $overlay.find('.ps-loading');
        const $noResults = $overlay.find('.ps-no-results');
        const $initial   = $overlay.find('.ps-initial-state');
        const minChars   = parseInt($input.data('min-chars')) || 3;

        function closeOverlay() {
            $overlay.removeClass('is-open');
            $('body').removeClass('ps-overlay-open');
            $('[aria-controls="' + $overlay.attr('id') + '"]').attr('aria-expanded', 'false');
            // También quitar aria-expanded del .search-nav a
            $('.search-nav a').attr('aria-expanded', 'false');
            setTimeout(function() {
                $overlay.attr('hidden', '');
                $input.val('');
                $clearBtn.hide();
                resetResults();
            }, 280);
        }

        function resetResults() {
            $loading.hide();
            $grid.empty();
            $noResults.hide();
            $initial.show();
        }

        // ── Cerrar: botón X, backdrop, Escape ──
        $closeBtn.on('click', closeOverlay);
        $backdrop.on('click', closeOverlay);
        $(document).on('keydown.psOverlay', function(e) {
            if (e.key === 'Escape' && !$overlay.attr('hidden')) {
                closeOverlay();
            }
        });

        // ── Input: búsqueda predictiva ──
        $input.on('input', function() {
            const query = $(this).val().trim();
            $clearBtn.toggle(query.length > 0);

            if (searchTimeout)  clearTimeout(searchTimeout);
            if (currentRequest) currentRequest.abort();

            if (query.length < minChars) {
                resetResults();
                return;
            }

            $loading.show();
            $grid.empty();
            $noResults.hide();
            $initial.hide();

            searchTimeout = setTimeout(function() {
                performSearch(query, $grid, $loading, $noResults);
            }, 280);
        });

        // ── Clear ──
        $clearBtn.on('click', function() {
            $input.val('').focus();
            $clearBtn.hide();
            resetResults();
        });
    }

    // ─────────────────────────────────────────────────────────
    // AJAX
    // ─────────────────────────────────────────────────────────

    function performSearch(query, $grid, $loading, $noResults) {
        const language = detectLanguage();

        currentRequest = $.ajax({
            url:  psAjax.ajaxurl,
            type: 'POST',
            data: {
                action:   'ps_search',
                nonce:    psAjax.nonce,
                query:    query,
                language: language,
            },
            success: function(response) {
                $loading.hide();
                if (response.success && response.data.results.length > 0) {
                    renderGrid(response.data.results, $grid, query);
                    $noResults.hide();
                } else {
                    $grid.empty();
                    $noResults.show();
                }
            },
            error: function(xhr) {
                if (xhr.statusText !== 'abort') {
                    $loading.hide();
                    $noResults.show();
                }
            },
            complete: function() {
                currentRequest = null;
            },
        });
    }

    // ─────────────────────────────────────────────────────────
    // RENDER GRID
    // ─────────────────────────────────────────────────────────

    function renderGrid(results, $grid, query) {
        $grid.empty();

        results.forEach(function(result) {
            const title   = escapeHtml(result.title || '');
            const url     = escapeHtml(result.url   || '#');
            const type    = escapeHtml(result.type  || '');
            const excerpt = result.excerpt
                ? escapeHtml(result.excerpt.length > 100
                    ? result.excerpt.substring(0, 100) + '…'
                    : result.excerpt)
                : '';

            const imgHtml = result.thumbnail
                ? '<div class="ps-card__img"><img src="' + escapeHtml(result.thumbnail) + '" alt="" loading="lazy"></div>'
                : '<div class="ps-card__img ps-card__img--placeholder"></div>';

            const html = [
                '<a class="ps-card" href="' + url + '">',
                    imgHtml,
                    '<div class="ps-card__body">',
                        '<h4 class="ps-card__title">' + highlightQuery(title, query) + '</h4>',
                        excerpt ? '<p class="ps-card__excerpt">' + excerpt + '</p>' : '',
                        '<span class="ps-card__type">' + type + '</span>',
                    '</div>',
                '</a>',
            ].join('');

            $grid.append(html);
        });
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────

    function detectLanguage() {
        if (typeof wpml_current_language !== 'undefined' && wpml_current_language) return wpml_current_language;
        if ($('body').data('lang'))  return $('body').data('lang');
        if ($('html').data('lang'))  return $('html').data('lang');
        if (document.documentElement.lang) return document.documentElement.lang.split('-')[0];
        if (typeof pll_current_language !== 'undefined') return pll_current_language;
        const parts = window.location.pathname.split('/').filter(Boolean);
        if (parts.length && /^[a-z]{2}$/i.test(parts[0])) return parts[0];
        return '';
    }

    function highlightQuery(text, query) {
        if (!query) return text;
        const regex = new RegExp('(' + escapeRegex(query) + ')', 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    function escapeHtml(text) {
        return String(text).replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];
        });
    }

    function escapeRegex(text) {
        return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
    }

    // ─────────────────────────────────────────────────────────
    // DIAGNÓSTICO (mantener para debugging)
    // ─────────────────────────────────────────────────────────

    function runDiagnosticChecklist() {
        var checks = [];
        if (typeof psAjax === 'undefined') {
            checks.push({ ok: false, name: 'psAjax', msg: 'No existe psAjax' });
            printChecks(checks);
            return;
        }
        checks.push({ ok: true, name: 'psAjax', msg: 'Objeto disponible' });
        checks.push({ ok: !!psAjax.ajaxurl, name: 'ajaxurl', msg: psAjax.ajaxurl || 'Falta' });
        checks.push({ ok: !!psAjax.nonce,   name: 'nonce',   msg: psAjax.nonce ? 'Presente' : 'Falta' });
        checks.push({ ok: $('.ps-trigger').length > 0, name: 'Trigger', msg: $('.ps-trigger').length + ' encontrado(s)' });

        $.ajax({
            url: psAjax.ajaxurl, type: 'POST',
            data: { action: 'ps_diagnostic', nonce: psAjax.nonce },
            success: function(r) {
                if (r.success && r.data.checks) {
                    $.each(r.data.checks, function(k, c) { checks.push({ ok: c.ok, name: c.name, msg: c.msg }); });
                    if (r.data.index_count > 0) {
                        checks.push({ ok: true, name: 'Índice', msg: r.data.index_count + ' elementos' });
                    }
                }
                printChecks(checks);
            },
            error: function(xhr) {
                checks.push({ ok: false, name: 'Diagnóstico AJAX', msg: xhr.statusText });
                printChecks(checks);
            },
        });
    }

    function printChecks(checks) {
        if (!console || !console.groupCollapsed) return;
        var failed = checks.filter(function(c) { return !c.ok; }).length;
        console.groupCollapsed(
            '%c[Predictive Search] ' + (failed ? failed + ' fallo(s)' : 'OK'),
            failed ? 'color:#c00;font-weight:bold' : 'color:#0a0'
        );
        checks.forEach(function(c) {
            console.log('%c' + (c.ok ? '✅' : '❌') + ' ' + c.name + ': ' + c.msg,
                c.ok ? 'color:#0a0' : 'color:#c00');
        });
        console.groupEnd();
    }

})(jQuery);
