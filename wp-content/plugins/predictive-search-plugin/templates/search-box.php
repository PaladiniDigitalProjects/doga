<?php
/**
 * Template: search-box.php
 *
 * Dos elementos renderizados:
 *   1. .ps-trigger  — botón lupa que abre el overlay
 *   2. .ps-overlay  — panel fullscreen con input + grid de resultados
 *
 * Uso vía shortcode: [predictive_search] o [predictive_search placeholder="..." min_chars="2"]
 */

if ( isset( $atts ) ) {
    $placeholder = isset( $atts['placeholder'] ) ? $atts['placeholder'] : __( 'Buscar...', 'predictive-search' );
    $min_chars   = isset( $atts['min_chars'] )   ? (int) $atts['min_chars'] : 3;
} elseif ( isset( $instance ) ) {
    $placeholder = ! empty( $instance['placeholder'] ) ? $instance['placeholder'] : __( 'Buscar...', 'predictive-search' );
    $min_chars   = ! empty( $instance['min_chars'] )   ? (int) $instance['min_chars'] : 3;
} else {
    $placeholder = __( 'Buscar...', 'predictive-search' );
    $min_chars   = 3;
}

// ID único para este overlay (soporte de múltiples instancias en la misma página)
static $ps_instance = 0;
$ps_instance++;
$overlay_id = 'ps-overlay-' . $ps_instance;
?>

<?php /* ── TRIGGER (lupa) ── */ ?>
<button
    class="ps-trigger"
    aria-label="<?php esc_attr_e( 'Abrir buscador', 'predictive-search' ); ?>"
    aria-expanded="false"
    aria-controls="<?php echo esc_attr( $overlay_id ); ?>"
    type="button"
>
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <circle cx="11" cy="11" r="8"/>
        <path d="m21 21-4.35-4.35"/>
    </svg>
</button>

<?php /* ── OVERLAY ── */ ?>
<div
    class="ps-overlay"
    id="<?php echo esc_attr( $overlay_id ); ?>"
    role="dialog"
    aria-modal="true"
    aria-label="<?php esc_attr_e( 'Buscador', 'predictive-search' ); ?>"
    hidden
>
    <div class="ps-overlay__backdrop"></div>

    <div class="ps-overlay__panel">

        <?php /* ── Cabecera con input ── */ ?>
        <div class="ps-overlay__header">

            <div class="ps-search-input-wrapper">
                <span class="ps-search-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                    aria-label="<?php esc_attr_e( 'Limpiar', 'predictive-search' ); ?>"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>

        </div>

        <?php /* ── Cuerpo: resultados ── */ ?>
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
