/**
 * PDS Motor Finder — Frontend
 *
 * Lógica de filtrado AJAX:
 * - Grupos con 2 opciones  → <select> nativo   (1 sola selección, OR implícito)
 * - Grupos con > 2 opciones → <details> + checkboxes (OR dentro del grupo)
 * - Grupos distintos → AND entre ellos
 */

document.addEventListener( 'DOMContentLoaded', () => {

    document.querySelectorAll( '.pds-motor-finder' ).forEach( initFinder );

} );

function initFinder( finder ) {

    const ajaxUrl   = finder.dataset.ajaxUrl;
    const nonce     = finder.dataset.nonce;
    const postType  = finder.dataset.postType;
    const taxonomy  = finder.dataset.taxonomy;

    const resultsEl  = finder.querySelector( '.pds-mf__results-inner' );
    const spinnerEl  = finder.querySelector( '.pds-mf__spinner' );
    const resetBtn   = finder.querySelector( '.pds-mf__reset' );
    const checkboxes = finder.querySelectorAll( '.pds-mf__checkbox' );
    const selects    = finder.querySelectorAll( '.pds-mf__select' );

    let debounceTimer = null;

    function scheduleUpdate() {
        clearTimeout( debounceTimer );
        debounceTimer = setTimeout( fetchResults, 300 );
    }

    // ── Escuchar cambios en checkboxes ──
    checkboxes.forEach( cb => cb.addEventListener( 'change', scheduleUpdate ) );

    // ── Escuchar cambios en selects ──
    selects.forEach( sel => sel.addEventListener( 'change', scheduleUpdate ) );

    // ── Reset ──
    resetBtn.addEventListener( 'click', () => {
        checkboxes.forEach( cb  => { cb.checked = false; } );
        selects.forEach(    sel => { sel.value  = '';    } );
        fetchResults();
    } );

    // ── Función principal AJAX ──
    function fetchResults() {

        // Recoger grupos de checkboxes marcados
        const groups = {};
        checkboxes.forEach( cb => {
            if ( ! cb.checked ) return;
            const parentId = cb.dataset.parent;
            if ( ! groups[ parentId ] ) groups[ parentId ] = [];
            groups[ parentId ].push( cb.value );
        } );

        // Recoger valores de selects con valor no vacío
        selects.forEach( sel => {
            if ( ! sel.value ) return;
            const parentId = sel.dataset.parent;
            if ( ! groups[ parentId ] ) groups[ parentId ] = [];
            groups[ parentId ].push( sel.value );
        } );

        setLoading( true );

        const body = new URLSearchParams( {
            action:    'pds_motor_filter',
            nonce:     nonce,
            post_type: postType,
            taxonomy:  taxonomy,
            filters:   JSON.stringify( groups ),
        } );

        fetch( ajaxUrl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    body.toString(),
        } )
        .then( r => r.json() )
        .then( data => {
            if ( data.success ) {
                resultsEl.innerHTML = data.data.html;
            } else {
                resultsEl.innerHTML = '<p class="pds-mf-empty">No se encontraron resultados.</p>';
            }
        } )
        .catch( () => {
            resultsEl.innerHTML = '<p class="pds-mf-empty">Error al cargar los resultados.</p>';
        } )
        .finally( () => {
            setLoading( false );
        } );
    }

    function setLoading( isLoading ) {
        const resultsWrapper = finder.querySelector( '.pds-mf__results' );
        resultsWrapper.setAttribute( 'aria-busy', isLoading ? 'true' : 'false' );
        spinnerEl.style.display = isLoading ? 'block' : 'none';
    }
}

