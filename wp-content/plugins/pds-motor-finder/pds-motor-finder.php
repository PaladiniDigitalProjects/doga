<?php
/**
 * Plugin Name:  PDS Motor Finder
 * Plugin URI:   https://paladinidigital.com
 * Description:  Buscador y filtro de productos por taxonomía. Bloque Gutenberg con AJAX.
 * Version:      1.0.0
 * Author:       Paladini Digital Solutions
 * Text Domain:  pds-motor-finder
 */

defined( 'ABSPATH' ) || exit;

// ─────────────────────────────────────────────────────────────
// 1. REGISTRO DEL BLOQUE
// ─────────────────────────────────────────────────────────────

add_action( 'init', function() {
    register_block_type( __DIR__ . '/block.json' );
} );

// ─────────────────────────────────────────────────────────────
// 2. HELPER — HTML de la grid de resultados
// ─────────────────────────────────────────────────────────────

function pds_motor_finder_results_html( WP_Query $query ) : string {

    if ( ! $query->have_posts() ) {
        return '<p class="pds-mf-empty">' . esc_html__( 'No se encontraron motores con estos criterios.', 'pds-motor-finder' ) . '</p>';
    }

    $html = '<ul class="pds-mf__grid">';

    while ( $query->have_posts() ) {
        $query->the_post();

        $url   = esc_url( get_permalink() );
        $title = esc_html( get_the_title() );

        $thumb = has_post_thumbnail()
            ? get_the_post_thumbnail( null, 'medium', [ 'class' => 'pds-mf__card-img', 'alt' => $title ] )
            : '<div class="pds-mf__card-img pds-mf__card-img--placeholder"></div>';

        $html .= '<li class="pds-mf__card">';
        $html .= '<a href="' . $url . '" class="pds-mf__card-link">';
        $html .= $thumb;
        $html .= '<span class="pds-mf__card-title">' . $title . '</span>';
        $html .= '</a>';
        $html .= '</li>';
    }

    $html .= '</ul>';

    wp_reset_postdata();

    return $html;
}

// ─────────────────────────────────────────────────────────────
// 2b. HELPER — HTML de la paginación
// ─────────────────────────────────────────────────────────────

function pds_motor_finder_pagination_html( int $current, int $total ) : string {

    if ( $total <= 1 ) return '';

    $html = '<nav class="pds-mf__pagination" aria-label="' . esc_attr__( 'Páginas de resultados', 'pds-motor-finder' ) . '">';

    // Anterior
    $html .= sprintf(
        '<button class="pds-mf__page-btn pds-mf__page-prev" data-page="%d" %s aria-label="%s">&#8592;</button>',
        max( 1, $current - 1 ),
        $current <= 1 ? 'disabled' : '',
        esc_attr__( 'Página anterior', 'pds-motor-finder' )
    );

    // Números de página
    for ( $i = 1; $i <= $total; $i++ ) {
        $html .= sprintf(
            '<button class="pds-mf__page-btn pds-mf__page-num%s" data-page="%d" aria-current="%s">%d</button>',
            $i === $current ? ' is-active' : '',
            $i,
            $i === $current ? 'page' : 'false',
            $i
        );
    }

    // Siguiente
    $html .= sprintf(
        '<button class="pds-mf__page-btn pds-mf__page-next" data-page="%d" %s aria-label="%s">&#8594;</button>',
        min( $total, $current + 1 ),
        $current >= $total ? 'disabled' : '',
        esc_attr__( 'Página siguiente', 'pds-motor-finder' )
    );

    $html .= '</nav>';

    return $html;
}

// ─────────────────────────────────────────────────────────────
// 3. AJAX HANDLER — pds_motor_filter
//
// Recibe:
//   post_type  string
//   taxonomy   string
//   filters    JSON { parentId: [termId, termId, ...], ... }
//
// Lógica:
//   - Mismo grupo (mismo padre) → OR  (operator: IN)
//   - Grupos distintos          → AND (relation: AND)
// ─────────────────────────────────────────────────────────────

add_action( 'wp_ajax_pds_motor_filter',        'pds_motor_filter_handler' );
add_action( 'wp_ajax_nopriv_pds_motor_filter', 'pds_motor_filter_handler' );

function pds_motor_filter_handler() {

    check_ajax_referer( 'pds_motor_filter', 'nonce' );

    $post_type      = sanitize_key( $_POST['post_type']       ?? 'product' );
    $taxonomy       = sanitize_key( $_POST['taxonomy']        ?? 'motor-spec' );
    $raw            = stripslashes( $_POST['filters']         ?? '{}' );
    $filters        = json_decode( $raw, true );
    $pagination     = ( $_POST['pagination']                  ?? 'false' ) === 'true';
    $posts_per_page = absint( $_POST['posts_per_page']        ?? 12 );
    $page           = max( 1, absint( $_POST['page']          ?? 1 ) );

    // Construir tax_query
    $tax_query = [];

    if ( ! empty( $filters ) && is_array( $filters ) ) {

        $tax_query['relation'] = 'AND';

        foreach ( $filters as $parent_id => $term_ids ) {
            if ( empty( $term_ids ) ) continue;

            $term_ids = array_map( 'absint', $term_ids );

            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => $term_ids,
                'operator' => 'IN',
            ];
        }
    }

    if ( empty( $tax_query ) ) {
        $tax_query = [
            [
                'taxonomy' => $taxonomy,
                'operator' => 'EXISTS',
            ],
        ];
    }

    $query = new WP_Query( [
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => $pagination ? $posts_per_page : -1,
        'paged'          => $pagination ? $page : 1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'tax_query'      => $tax_query,
    ] );

    $total_pages = $pagination ? (int) $query->max_num_pages : 1;

    wp_send_json_success( [
        'html'            => pds_motor_finder_results_html( $query ),
        'pagination_html' => $pagination ? pds_motor_finder_pagination_html( $page, $total_pages ) : '',
        'total_pages'     => $total_pages,
        'current_page'    => $page,
    ] );
}

// ─────────────────────────────────────────────────────────────
// 4. RENDER core/post-terms → dl/dt/dd agrupado por padre
//    (plantilla de producto individual)
// ─────────────────────────────────────────────────────────────

add_filter( 'render_block', 'pds_render_motor_spec_grouped', 10, 2 );

function pds_render_motor_spec_grouped( $block_content, $block ) {

    if ( 'core/post-terms' !== ( $block['blockName'] ?? '' ) ) {
        return $block_content;
    }
    if ( 'motor-spec' !== ( $block['attrs']['term'] ?? '' ) ) {
        return $block_content;
    }

    $post_id = get_the_ID();
    if ( ! $post_id ) return $block_content;

    $terms = wp_get_post_terms( $post_id, 'motor-spec', [ 'hide_empty' => false ] );
    if ( is_wp_error( $terms ) || empty( $terms ) ) return $block_content;

    $child_terms = [];
    foreach ( $terms as $term ) {
        if ( (int) $term->parent !== 0 ) {
            $child_terms[ $term->parent ][] = $term;
        }
    }

    if ( empty( $child_terms ) ) return $block_content;

    $all_parents = get_terms( [
        'taxonomy'   => 'motor-spec',
        'parent'     => 0,
        'hide_empty' => false,
        'orderby'    => 'id',
        'order'      => 'ASC',
    ] );

    $html = '<div class="motor-specs-grouped">';
    foreach ( $all_parents as $parent ) {
        if ( empty( $child_terms[ $parent->term_id ] ) ) continue;
        $html .= '<div class="motor-spec-row">';
        $html .= '<span class="motor-spec-row__label">' . esc_html( $parent->name ) . '</span>';
        $html .= '<span class="motor-spec-row__value">';
        $values = array_map( fn( $t ) => esc_html( $t->name ), $child_terms[ $parent->term_id ] );
        $html .= implode( ', ', $values );
        $html .= '</span>';
        $html .= '</div>';
    }
    $html .= '</div>';

    return $html;
}
