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

$post_type      = sanitize_key( $attributes['postType']     ?? 'product' );
$taxonomy       = sanitize_key( $attributes['taxonomy']      ?? 'motor-spec' );
$title          = wp_kses_post( $attributes['title']         ?? '' );
$description    = wp_kses_post( $attributes['description']   ?? '' );
$pagination     = (bool) ( $attributes['pagination']         ?? false );
$posts_per_page = (int)  ( $attributes['postsPerPage']       ?? 12 );

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

// Orden explícito: hall-sensor aparece después de ip-level
$slug_order = [
    'n1-no-load-speed-rpm',
    'nominal-speed-rpm',
    'nominal-speed-rad-s',
    'nominal-torque-nm',
    'stall-torque-nm',
    'power-w',
    'ip-level',
    'hall-sensor',
    'voltage-nominal',
    'commutation',
    'controller',
];
usort( $parent_terms, function( $a, $b ) use ( $slug_order ) {
    $pos_a = array_search( $a->slug, $slug_order, true );
    $pos_b = array_search( $b->slug, $slug_order, true );
    $pos_a = $pos_a === false ? 99 : $pos_a;
    $pos_b = $pos_b === false ? 99 : $pos_b;
    return $pos_a - $pos_b;
} );
?>

<div
    class="pds-motor-finder wp-block-pds-motor-finder-filter"
    data-post-type="<?php echo esc_attr( $post_type ); ?>"
    data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
    data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
    data-nonce="<?php echo esc_attr( wp_create_nonce( 'pds_motor_filter' ) ); ?>"
    data-pagination="<?php echo $pagination ? 'true' : 'false'; ?>"
    data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>"
>

    <?php
    // Pre-separar términos en selects y checkboxes (manteniendo el orden de $slug_order)
    $select_terms   = [];
    $checkbox_terms = [];
    foreach ( $parent_terms as $parent ) {
        if ( in_array( $parent->slug, $select_groups, true ) ) {
            $select_terms[] = $parent;
        } else {
            $checkbox_terms[] = $parent;
        }
    }
    ?>

    <?php /* ── HEADER: título + botón Reset en la misma línea ── */ ?>
    <div class="pds-mf__header" role="search" aria-label="<?php esc_attr_e( 'Filtrar motores', 'pds-motor-finder' ); ?>">
        <?php if ( $title ) : ?>
        <h2 class="pds-mf__title"><?php echo $title; ?></h2>
        <?php else : ?>
        <span></span>
        <?php endif; ?>
        <button type="button" class="pds-mf__reset">
            <?php esc_html_e( 'Reset', 'pds-motor-finder' ); ?>
        </button>
    </div>

    <?php if ( $description ) : ?>
    <p class="pds-mf__description"><?php echo $description; ?></p>
    <?php endif; ?>

    <?php /* ── SELECTS: grid de 4 columnas ── */ ?>
    <?php if ( ! empty( $select_terms ) ) : ?>
    <div class="pds-mf__filters pds-mf__filters--selects">
        <?php foreach ( $select_terms as $parent ) :
            $children = get_terms( [
                'taxonomy'   => $taxonomy,
                'parent'     => $parent->term_id,
                'hide_empty' => true,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ] );
            if ( is_wp_error( $children ) || empty( $children ) ) continue;
        ?>
        <div class="pds-mf__group pds-mf__group--select" data-parent-id="<?php echo esc_attr( $parent->term_id ); ?>">
            <select
                id="pds-sel-<?php echo esc_attr( $parent->term_id ); ?>"
                class="pds-mf__select"
                data-parent="<?php echo esc_attr( $parent->term_id ); ?>"
            >
                <option value=""><?php echo esc_html( $parent->name ); ?></option>
                <?php foreach ( $children as $child ) : ?>
                <option value="<?php echo esc_attr( $child->term_id ); ?>" data-slug="<?php echo esc_attr( $child->slug ); ?>">
                    <?php echo esc_html( $child->name ); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php /* ── CHECKBOXES: grid de 4 columnas ── */ ?>
    <?php if ( ! empty( $checkbox_terms ) ) : ?>
    <div class="pds-mf__filters pds-mf__filters--checks">
        <?php foreach ( $checkbox_terms as $parent ) :
            $children = get_terms( [
                'taxonomy'   => $taxonomy,
                'parent'     => $parent->term_id,
                'hide_empty' => true,
                'orderby'    => 'name',
                'order'      => $parent->slug === 'hall-sensor' ? 'DESC' : 'ASC',
            ] );
            if ( is_wp_error( $children ) || empty( $children ) ) continue;
        ?>
        <div class="pds-mf__group pds-mf__group--checks" data-parent-id="<?php echo esc_attr( $parent->term_id ); ?>">
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
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php /* ── RESULTADOS ── */ ?>
    <div class="pds-mf__results" aria-live="polite" aria-busy="false">
        <div class="pds-mf__results-inner">
            <?php
            $initial = new WP_Query( [
                'post_type'      => $post_type,
                'post_status'    => 'publish',
                'posts_per_page' => $pagination ? $posts_per_page : -1,
                'paged'          => 1,
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

    <?php if ( $pagination && $initial->max_num_pages > 1 ) : ?>
    <?php echo pds_motor_finder_pagination_html( 1, $initial->max_num_pages ); ?>
    <?php endif; ?>

    <p class="pds-mf__active-summary" hidden aria-live="polite"></p>

</div>
