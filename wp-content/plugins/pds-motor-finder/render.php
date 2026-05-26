<?php
/**
 * Render server-side del bloque pds-motor-finder/filter.
 *
 * Variables disponibles:
 *   $attributes  — atributos del bloque (postType, taxonomy)
 *   $content     — innerBlocks (no usamos)
 *   $block       — instancia WP_Block
 *
 * Lógica de controles:
 *   - Grupos en $SELECT_GROUPS  → <select> nativo (valor único, OR implícito)
 *   - Resto de grupos           → <details> colapsable + checkboxes (multi OR)
 *   - Grupos distintos          → AND entre ellos
 */

$post_type = sanitize_key( $attributes['postType'] ?? 'product' );
$taxonomy  = sanitize_key( $attributes['taxonomy']  ?? 'motor-spec' );

/**
 * Slugs de grupos padre que deben renderizarse como <select> (valor único).
 * Son rangos numéricos / booleanos donde no tiene sentido combinar valores.
 * Filtro disponible: 'pds_motor_finder_select_groups'
 */
$select_groups = apply_filters( 'pds_motor_finder_select_groups', [
    'n1-no-load-speed-rpm',   // N1 No Load Speed rpm (±10%)
    'nominal-speed-rpm',       // Nominal Speed (rpm)
    'nominal-speed-rad-s',     // Nominal Speed (rad/s)
    'nominal-torque-nm',       // Nominal Torque (Nm)
    'stall-torque-nm',         // Stall Torque (Nm)
    'power-w',                 // Power (W)
    'hall-sensor',             // Hall Sensor
    'ip-level',                // IP Level
] );

// Grupos padre de la taxonomía
$parent_terms = get_terms( [
    'taxonomy'   => $taxonomy,
    'parent'     => 0,
    'hide_empty' => true,
    'orderby'    => 'id',
    'order'      => 'ASC',
] );

if ( is_wp_error( $parent_terms ) || empty( $parent_terms ) ) {
    echo '<p class="pds-mf-empty">' . esc_html__( 'No hay specs disponibles.', 'pds-motor-finder' ) . '</p>';
    return;
}
?>

<div
    class="pds-motor-finder wp-block-pds-motor-finder-filter"
    data-post-type="<?php echo esc_attr( $post_type ); ?>"
    data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
    data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
    data-nonce="<?php echo esc_attr( wp_create_nonce( 'pds_motor_filter' ) ); ?>"
>

    <?php /* ── FILTROS ── */ ?>
    <div class="pds-mf__filters" role="search" aria-label="<?php esc_attr_e( 'Filtrar motores', 'pds-motor-finder' ); ?>">

        <?php foreach ( $parent_terms as $parent ) :
            $children = get_terms( [
                'taxonomy'   => $taxonomy,
                'parent'     => $parent->term_id,
                'hide_empty' => true,
                'orderby'    => 'id',
                'order'      => 'ASC',
            ] );
            if ( is_wp_error( $children ) || empty( $children ) ) continue;

            $is_select = in_array( $parent->slug, $select_groups, true );
        ?>

        <?php if ( $is_select ) : ?>
        <?php /* ── SELECT nativo (grupos numéricos / booleanos) ── */ ?>
        <div
            class="pds-mf__group pds-mf__group--select"
            data-parent-id="<?php echo esc_attr( $parent->term_id ); ?>"
        >
            <label
                class="pds-mf__group-label"
                for="pds-sel-<?php echo esc_attr( $parent->term_id ); ?>"
            >
                <?php echo esc_html( $parent->name ); ?>
            </label>
            <select
                id="pds-sel-<?php echo esc_attr( $parent->term_id ); ?>"
                class="pds-mf__select"
                data-parent="<?php echo esc_attr( $parent->term_id ); ?>"
            >
                <option value=""><?php esc_html_e( 'All', 'pds-motor-finder' ); ?></option>
                <?php foreach ( $children as $child ) : ?>
                <option
                    value="<?php echo esc_attr( $child->term_id ); ?>"
                    data-slug="<?php echo esc_attr( $child->slug ); ?>"
                >
                    <?php echo esc_html( $child->name ); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php else : ?>
        <?php /* ── Checkboxes planos (grupos categóricos) ── */ ?>
        <div
            class="pds-mf__group pds-mf__group--checks"
            data-parent-id="<?php echo esc_attr( $parent->term_id ); ?>"
        >
            <span class="pds-mf__group-label"><?php echo esc_html( $parent->name ); ?></span>
            <?php foreach ( $children as $child ) : ?>
            <label class="pds-mf__option">
                <input
                    type="checkbox"
                    class="pds-mf__checkbox"
                    value="<?php echo esc_attr( $child->term_id ); ?>"
                    data-parent="<?php echo esc_attr( $parent->term_id ); ?>"
                    data-slug="<?php echo esc_attr( $child->slug ); ?>"
                >
                <span><?php echo esc_html( $child->name ); ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php endforeach; ?>

        <div class="pds-mf__actions">
            <button type="button" class="pds-mf__reset">
                <?php esc_html_e( 'Reset', 'pds-motor-finder' ); ?>
            </button>
        </div>

    </div>

    <?php /* ── RESULTADOS ── */ ?>
    <div class="pds-mf__results" aria-live="polite" aria-busy="false">
        <div class="pds-mf__results-inner">
            <?php
            $initial = new WP_Query( [
                'post_type'      => $post_type,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
                'tax_query'      => [
                    [
                        'taxonomy' => $taxonomy,
                        'operator' => 'EXISTS',
                    ],
                ],
            ] );

            echo pds_motor_finder_results_html( $initial );
            ?>
        </div>
        <div class="pds-mf__spinner" aria-hidden="true"></div>
    </div>

</div>
