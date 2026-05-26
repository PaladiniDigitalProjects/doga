<?php
/**
 * Template: overlay.php
 *
 * Inyectado en wp_footer en todas las páginas.
 * El trigger es externo (ej: .search-nav a en el menú de navegación).
 */

$placeholder = __( 'Buscar...', 'predictive-search' );
$min_chars   = (int) get_option( 'ps_min_chars', 3 );
?>
<div
    class="ps-overlay"
    id="ps-overlay-global"
    role="dialog"
    aria-modal="true"
    aria-label="<?php esc_attr_e( 'Buscador', 'predictive-search' ); ?>"
    hidden
>
    <div class="ps-overlay__backdrop"></div>

    <div class="ps-overlay__panel">

        <div class="ps-overlay__header">
            <div class="ps-search-input-wrapper">
                <span class="ps-search-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                </span>
                <input
                    type="text"
                    class="ps-search-input"
                    placeholder="<?php echo esc_attr( $placeholder ); ?>"
                    autocomplete="off"
                    data-min-chars="<?php echo esc_attr( $min_chars ); ?>"
                    aria-label="<?php esc_attr_e( 'Buscar', 'predictive-search' ); ?>"
                />
                <button
                    type="button"
                    class="ps-search-clear"
                    style="display:none"
                    aria-label="<?php esc_attr_e( 'Limpiar búsqueda', 'predictive-search' ); ?>"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <button
                type="button"
                class="ps-overlay__close"
                aria-label="<?php esc_attr_e( 'Cerrar buscador', 'predictive-search' ); ?>"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="ps-overlay__body">
            <div class="ps-loading" style="display:none" aria-live="polite">
                <span class="ps-spinner" aria-hidden="true"></span>
                <span><?php esc_html_e( 'Buscando...', 'predictive-search' ); ?></span>
            </div>
            <div class="ps-results-grid" aria-live="polite"></div>
            <div class="ps-no-results" style="display:none">
                <p><?php esc_html_e( 'No se encontraron resultados.', 'predictive-search' ); ?></p>
            </div>
            <div class="ps-initial-state">
                <p><?php printf(
                    esc_html__( 'Escribe al menos %d caracteres para buscar.', 'predictive-search' ),
                    $min_chars
                ); ?></p>
            </div>
        </div>

    </div>
</div>
