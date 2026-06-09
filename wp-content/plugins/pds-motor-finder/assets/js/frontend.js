/******/ (() => { // webpackBootstrap
/*!*************************!*\
  !*** ./src/frontend.js ***!
  \*************************/
/**
 * PDS Motor Finder — Frontend
 *
 * Lógica de filtrado AJAX:
 * - Grupos con 2 opciones  → <select> nativo   (1 sola selección, OR implícito)
 * - Grupos con > 2 opciones → <details> + checkboxes (OR dentro del grupo)
 * - Grupos distintos → AND entre ellos
 */

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.pds-motor-finder').forEach(initFinder);
});
function initFinder(finder) {
  const ajaxUrl = finder.dataset.ajaxUrl;
  const nonce = finder.dataset.nonce;
  const postType = finder.dataset.postType;
  const taxonomy = finder.dataset.taxonomy;
  const usePagination = finder.dataset.pagination === 'true';
  const postsPerPage = parseInt(finder.dataset.postsPerPage, 10) || 12;
  let currentPage = 1;
  const resultsEl = finder.querySelector('.pds-mf__results-inner');
  const spinnerEl = finder.querySelector('.pds-mf__spinner');
  const resetBtn = finder.querySelector('.pds-mf__reset');
  const checkboxes = finder.querySelectorAll('.pds-mf__checkbox');
  const selects = finder.querySelectorAll('.pds-mf__select');
  const summaryEl = finder.querySelector('.pds-mf__active-summary');
  let debounceTimer = null;
  function scheduleUpdate() {
    currentPage = 1; // reset página al cambiar filtros
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fetchResults, 300);
  }

  // ── Escuchar cambios en checkboxes ──
  checkboxes.forEach(cb => cb.addEventListener('change', scheduleUpdate));

  // ── Escuchar cambios en selects ──
  selects.forEach(sel => sel.addEventListener('change', () => {
    sel.classList.toggle('pds-mf__select--active', sel.value !== '');
    scheduleUpdate();
  }));

  // ── Reset ──
  resetBtn.addEventListener('click', () => {
    checkboxes.forEach(cb => {
      cb.checked = false;
    });
    selects.forEach(sel => {
      sel.value = '';
      sel.classList.remove('pds-mf__select--active');
    });
    fetchResults();
  });

  // ── Resumen de filtros activos ──
  function updateSummary() {
    if (!summaryEl) return;
    const parts = [];

    // Selects con valor activo
    selects.forEach(sel => {
      if (!sel.value) return;
      const groupName = sel.options[0].text; // primera opción = nombre del grupo
      const valueName = sel.options[sel.selectedIndex].text;
      parts.push(`<strong>${groupName}:</strong> ${valueName}`);
    });

    // Checkboxes marcados, agrupados por padre
    const cbGroups = {};
    checkboxes.forEach(cb => {
      if (!cb.checked) return;
      const group = cb.closest('.pds-mf__group');
      const label = group ? group.querySelector('.pds-mf__group-label') : null;
      const groupName = label ? label.textContent.trim() : cb.dataset.parent;
      if (!cbGroups[groupName]) cbGroups[groupName] = [];
      cbGroups[groupName].push(cb.closest('.pds-mf__option').querySelector('span').textContent.trim());
    });
    for (const [groupName, values] of Object.entries(cbGroups)) {
      parts.push(`<strong>${groupName}:</strong> ${values.join(', ')}`);
    }
    summaryEl.innerHTML = parts.length ? '<h5 class="pds-mf__active-label">Active</h5>' + parts.join(' &nbsp;·&nbsp; ') : '';
    summaryEl.hidden = parts.length === 0;
  }

  // ── Función principal AJAX ──
  function fetchResults() {
    // Recoger grupos de checkboxes marcados
    const groups = {};
    checkboxes.forEach(cb => {
      if (!cb.checked) return;
      const parentId = cb.dataset.parent;
      if (!groups[parentId]) groups[parentId] = [];
      groups[parentId].push(cb.value);
    });

    // Recoger valores de selects con valor no vacío
    selects.forEach(sel => {
      if (!sel.value) return;
      const parentId = sel.dataset.parent;
      if (!groups[parentId]) groups[parentId] = [];
      groups[parentId].push(sel.value);
    });
    updateSummary();
    setLoading(true);
    const body = new URLSearchParams({
      action: 'pds_motor_filter',
      nonce: nonce,
      post_type: postType,
      taxonomy: taxonomy,
      filters: JSON.stringify(groups),
      pagination: usePagination ? 'true' : 'false',
      posts_per_page: postsPerPage,
      page: currentPage
    });
    fetch(ajaxUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: body.toString()
    }).then(r => r.json()).then(data => {
      if (data.success) {
        resultsEl.innerHTML = data.data.html;
        renderPagination(data.data.pagination_html);
      } else {
        resultsEl.innerHTML = '<p class="pds-mf-empty">No se encontraron resultados.</p>';
        renderPagination('');
      }
    }).catch(() => {
      resultsEl.innerHTML = '<p class="pds-mf-empty">Error al cargar los resultados.</p>';
    }).finally(() => {
      setLoading(false);
    });
  }

  // ── Paginación ──
  function attachPaginationListeners() {
    finder.querySelectorAll('.pds-mf__page-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        if (btn.disabled) return;
        currentPage = parseInt(btn.dataset.page, 10);
        fetchResults();
      });
    });
  }
  function renderPagination(html) {
    const paginationEl = finder.querySelector('.pds-mf__pagination');
    if (!html) {
      if (paginationEl) paginationEl.remove();
      return;
    }
    if (paginationEl) {
      paginationEl.outerHTML = html;
    } else {
      finder.querySelector('.pds-mf__results').insertAdjacentHTML('afterend', html);
    }
    attachPaginationListeners();
  }

  // Listeners para paginación server-rendered (carga inicial)
  attachPaginationListeners();
  function setLoading(isLoading) {
    const resultsWrapper = finder.querySelector('.pds-mf__results');
    resultsWrapper.setAttribute('aria-busy', isLoading ? 'true' : 'false');
    spinnerEl.style.display = isLoading ? 'block' : 'none';
  }
}
/******/ })()
;
//# sourceMappingURL=frontend.js.map