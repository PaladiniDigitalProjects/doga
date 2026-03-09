<?php
/*
 * This is the child theme for Hello Elementor theme, generated with Generate Child Theme plugin by catchthemes.
 *
 * (Please see https://developer.wordpress.org/themes/advanced-topics/child-themes/#how-to-create-a-child-theme)
 */

// /*MAPS API*/
// function custom_enqueue_google_maps()
// {
//     wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyCM4NoENemERuGiXhDH-rt42qP0MGBPy_U', [], null, true);
// }
// add_action('wp_enqueue_scripts', 'custom_enqueue_google_maps');

require_once get_stylesheet_directory() . '/shortcodes/shortcodes.php';
require_once get_stylesheet_directory() . '/shortcodes/shortcode-2.php';
require_once get_stylesheet_directory() . '/shortcodes/dynamic-demo-template.php';
require_once get_stylesheet_directory() . '/shortcodes/ajax_response.php';

add_action('wp_enqueue_scripts', 'doga_enqueue_styles');
function doga_enqueue_styles()
{

    $time = rand(0, 100000);

    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . "/style.css?time=$time", array('parent-style'));

    wp_enqueue_style('custom-style', get_stylesheet_directory_uri() . "/assets/css/custom-style.css?time=$time", array('parent-style'));
    wp_enqueue_style('new-style', get_stylesheet_directory_uri() . "/assets/css/new-style.css?time=$time", array('parent-style'));
    wp_enqueue_style('vj-style', get_stylesheet_directory_uri() . "/assets/css/vj-style.css?time=$time", array('parent-style'));
    wp_enqueue_style('h-style', get_stylesheet_directory_uri() . "/assets/css/h-style.css?time=$time", array('parent-style'));
    wp_enqueue_style('sagar-style', get_stylesheet_directory_uri() . "/assets/css/s-style.css?time=$time", array('parent-style'));
    wp_enqueue_style('fancybox', get_stylesheet_directory_uri() . "/assets/css/plugin/fancybox/fancybox.css?time=$time", array('parent-style'));
    wp_enqueue_style('bootstrap-style', "https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css");
    wp_enqueue_style('swiper-style', "https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css");


    wp_enqueue_script('bootstrap-js', "https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js");
    wp_enqueue_script('swiper-js', "https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js");
    wp_enqueue_script('jquery');
    wp_enqueue_script('script', get_stylesheet_directory_uri() . "/assets/js/script.js?time=$time", null, true);
    wp_enqueue_script('gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', null, true);
    wp_enqueue_script('ScrollTrigger', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js', null, true);
    wp_enqueue_script('animation', get_stylesheet_directory_uri() . "/assets/js/animation.js?time=$time", null, true);
    wp_enqueue_script('slider', get_stylesheet_directory_uri() . "/assets/js/slider.js?time=$time", null, true);
    wp_enqueue_script('fancybox', get_stylesheet_directory_uri() . "/assets/css/plugin/fancybox/fancybox.umd.js?time=$time", null, true);
    wp_enqueue_script('product-filter', get_stylesheet_directory_uri() . "/assets/js/product-filter.js?time=$time", null, true);
    wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . "/assets/js/custom.js?time=$time", null, true);
}

// Enqueue Washer System Pump Filter JS
add_action('wp_enqueue_scripts', 'washer_ajax_filter_scripts');
function washer_ajax_filter_scripts()
{
    wp_enqueue_script('washer-ajax-script', get_stylesheet_directory_uri() . '/assets/js/washer-ajax.js?time=$time', array('jquery'), null, true);
    wp_localize_script('washer-ajax-script', 'ajax_object', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('washer_ajax_nonce'),
        'posts_per_pages' => get_field('posts_per_page'),
        'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
        'taxonomy_posts_per_page' => get_option('posts_per_page'),
        'theme_url' => get_stylesheet_directory_uri(),
    ));
}




/* ************** Washer System Type Of Pumps Filter ************** */

// // Enqueue Google Map API
// function enqueue_google_maps()
// {
//     wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key="AIzaSyClSR41R1EDmd-aiI1HSjhUEXMp_fC53Ns"', [], null, true);
// }
// add_action('wp_enqueue_scripts', 'enqueue_google_maps');

/*
 * Your code goes below
 */

// years dynamic
function currentYear($atts)
{
    return date('Y');
}
add_shortcode('year', 'currentYear');

// Allow SVG
function allow_svg($mimes)
{
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg');

function fix_mime_type_svg($data = null, $file = null, $filename = null, $mimes = null)
{
    $ext = isset($data['ext']) ? $data['ext'] : '';
    if (strlen($ext) < 1) {
        $exploded = explode('.', $filename);
        $ext = strtolower(end($exploded));
    }
    if ($ext === 'svg') {
        $data['type'] = 'image/svg+xml';
        $data['ext'] = 'svg';
    } elseif ($ext === 'svgz') {
        $data['type'] = 'image/svg+xml';
        $data['ext'] = 'svgz';
    }
    return $data;
}
add_filter('wp_check_filetype_and_ext', 'fix_mime_type_svg', 75, 4);

function fix_svg()
{
    echo '<style type="text/css">
        .attachment-266x266, .thumbnail img {
            width: 100% !important;
            height: auto !important;
        }
</style>';
}
add_action('admin_head', 'fix_svg');

//  allow span in acf Field 

function override_mce_options($initArray)
{
    $opts = '*[*]';
    $initArray['valid_elements'] = $opts;
    $initArray['extended_valid_elements'] = $opts;
    return $initArray;
}
add_filter('tiny_mce_before_init', 'override_mce_options');


//  CONTACT FORM 7 REMOVE P TAGE

add_filter('wpcf7_autop_or_not', '__return_false');

// Function for remove block editor widget style in apperance/widgets
function disable_block_widgets()
{
    remove_theme_support('widgets-block-editor');
}
add_action('after_setup_theme', 'disable_block_widgets');



/* Products Custom Post Type */

// Register Custom Post Type: Product
function register_product_post_type()
{

    $labels = array(
        'name' => _x('Products', 'Post Type General Name', 'textdomain'),
        'singular_name' => _x('Product', 'Post Type Singular Name', 'textdomain'),
        'menu_name' => __('Products', 'textdomain'),
        'name_admin_bar' => __('Product', 'textdomain'),
        'archives' => __('Product Archives', 'textdomain'),
        'attributes' => __('Product Attributes', 'textdomain'),
        'parent_item_colon' => __('Parent Product:', 'textdomain'),
        'all_items' => __('All Products', 'textdomain'),
        'add_new_item' => __('Add New Product', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'new_item' => __('New Product', 'textdomain'),
        'edit_item' => __('Edit Product', 'textdomain'),
        'update_item' => __('Update Product', 'textdomain'),
        'view_item' => __('View Product', 'textdomain'),
        'view_items' => __('View Products', 'textdomain'),
        'search_items' => __('Search Products', 'textdomain'),
        'not_found' => __('Not found', 'textdomain'),
        'not_found_in_trash' => __('Not found in Trash', 'textdomain'),
        'featured_image' => __('Featured Image', 'textdomain'),
        'set_featured_image' => __('Set featured image', 'textdomain'),
        'remove_featured_image' => __('Remove featured image', 'textdomain'),
        'use_featured_image' => __('Use as featured image', 'textdomain'),
        'insert_into_item' => __('Insert into product', 'textdomain'),
        'uploaded_to_this_item' => __('Uploaded to this product', 'textdomain'),
        'items_list' => __('Products list', 'textdomain'),
        'items_list_navigation' => __('Products list navigation', 'textdomain'),
        'filter_items_list' => __('Filter products list', 'textdomain'),
    );

    $args = array(
        'label' => __('Product', 'textdomain'),
        'description' => __('Custom post type for Products', 'textdomain'),
        'labels' => $labels,
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'taxonomies' => array(), // You can add custom taxonomies here
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-cart',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'product'),
        'exclude_from_search' => false,
        'publicly_queryable' => true,
    );

    register_post_type('product', $args);
}
add_action('init', 'register_product_post_type');

/* Products Custom Post Type */


// Register Produc-Line Custom Taxnomy
function register_product_line_taxonomy()
{

    $labels = array(
        'name' => _x('Product Lines', 'taxonomy general name', 'textdomain'),
        'singular_name' => _x('Product Line', 'taxonomy singular name', 'textdomain'),
        'search_items' => __('Search Product Lines', 'textdomain'),
        'all_items' => __('All Product Lines', 'textdomain'),
        'parent_item' => __('Parent Product Line', 'textdomain'),
        'parent_item_colon' => __('Parent Product Line:', 'textdomain'),
        'edit_item' => __('Edit Product Line', 'textdomain'),
        'update_item' => __('Update Product Line', 'textdomain'),
        'add_new_item' => __('Add New Product Line', 'textdomain'),
        'new_item_name' => __('New Product Line Name', 'textdomain'),
        'menu_name' => __('Product Lines', 'textdomain'),
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'public' => true, // <-- Important
        'publicly_queryable' => true, // <-- Important
        'rewrite' => array('slug' => 'product-line'),
        'show_in_rest' => true,
    );

    // Attach taxonomy to custom post type 'product'
    register_taxonomy('product-line', array('product'), $args);
}
add_action('init', 'register_product_line_taxonomy', 0);


// Register Custom Taxonomy: Product Category
function register_product_category_taxonomy()
{

    $labels = array(
        'name' => _x('Product Categories', 'taxonomy general name', 'textdomain'),
        'singular_name' => _x('Product Category', 'taxonomy singular name', 'textdomain'),
        'search_items' => __('Search Product Categories', 'textdomain'),
        'all_items' => __('All Product Categories', 'textdomain'),
        'parent_item' => __('Parent Product Category', 'textdomain'),
        'parent_item_colon' => __('Parent Product Category:', 'textdomain'),
        'edit_item' => __('Edit Product Category', 'textdomain'),
        'update_item' => __('Update Product Category', 'textdomain'),
        'add_new_item' => __('Add New Product Category', 'textdomain'),
        'new_item_name' => __('New Product Category Name', 'textdomain'),
        'menu_name' => __('Product Categories', 'textdomain'),
    );

    $args = array(
        'hierarchical' => true, // Like categories
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'rewrite' => array('slug' => 'product-category'),
        'show_in_rest' => true, // Enable Gutenberg/Elementor support
    );

    // Attach taxonomy to 'product' post type
    register_taxonomy('product-category', array('product'), $args);
}
add_action('init', 'register_product_category_taxonomy');

function register_product_attribute_taxonomy()
{

    $labels = array(
        'name' => _x('Product Attributes', 'taxonomy general name', 'textdomain'),
        'singular_name' => _x('Product Attribute', 'taxonomy singular name', 'textdomain'),
        'search_items' => __('Search Product Attributes', 'textdomain'),
        'all_items' => __('All Product Attributes', 'textdomain'),
        'parent_item' => __('Parent Product Attribute', 'textdomain'),
        'parent_item_colon' => __('Parent Product Attribute:', 'textdomain'),
        'edit_item' => __('Edit Product Attribute', 'textdomain'),
        'update_item' => __('Update Product Attribute', 'textdomain'),
        'add_new_item' => __('Add New Product Attribute', 'textdomain'),
        'new_item_name' => __('New Product Attribute Name', 'textdomain'),
        'menu_name' => __('Product Attributes', 'textdomain'),
    );

    $args = array(
        'hierarchical' => true, // Set to true for parent/child (like categories)
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true, // Enables support for Gutenberg
        'query_var' => true,
        'public' => false,
        'publicly_queryable' => false,
        'rewrite' => array('slug' => 'product-attribute'),
    );

    register_taxonomy('product-attribute', array('product'), $args);
}
add_action('init', 'register_product_attribute_taxonomy');


// Register Product Market Sector Custom Taxnomy
function register_product_market_sectors()
{

    $labels = array(
        'name' => _x('Product Market Sectors', 'taxonomy general name', 'textdomain'),
        'singular_name' => _x('Product Market Sector', 'taxonomy singular name', 'textdomain'),
        'search_items' => __('Search Product Market Sector', 'textdomain'),
        'all_items' => __('All Product Market Sector', 'textdomain'),
        'parent_item' => __('Parent Product Market Sector', 'textdomain'),
        'parent_item_colon' => __('Parent Product Market Sector:', 'textdomain'),
        'edit_item' => __('Edit Product Market Sector', 'textdomain'),
        'update_item' => __('Update Product Market Sector', 'textdomain'),
        'add_new_item' => __('Add New Product Market Sector', 'textdomain'),
        'new_item_name' => __('New Product Market Sector Name', 'textdomain'),
        'menu_name' => __('Product Market Sector', 'textdomain'),
    );

    $args = array(
        'hierarchical' => true, // Like categories
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'publicly_queryable' => false, // <-- Important
        'rewrite' => array('slug' => 'product-market-sector'),
        'show_in_rest' => true, // Enable Gutenberg/Elementor support
    );

    // Attach taxonomy to 'product' post type
    register_taxonomy('product-market-sector', array('product'), $args);

}
add_action('init', 'register_product_market_sectors');



// Bloquear términos concretos en el buscador
add_action( 'pre_get_posts', function( WP_Query $q ) {

    // Sólo en frontend, consulta principal y búsquedas
    if ( is_admin() || ! $q->is_main_query() || ! $q->is_search() ) {
        return;
    }

    // Lista de términos bloqueados (en minúsculas para comparar)
    $bloqueados = array( '319e', '319p' );

    $s_raw = $q->get( 's' );
    if ( ! is_string( $s_raw ) ) {
        return;
    }

    $s_trim = trim( $s_raw );
    $s_lc   = mb_strtolower( $s_trim );

    // --- 1) Bloqueo si la búsqueda es EXACTAMENTE uno de los términos ---
    if ( in_array( $s_lc, $bloqueados, true ) ) {
        // Forzar "sin resultados"
        $q->set( 'post__in', array( 0 ) );
        $q->set( 'no_found_rows', true );

        // Ataja aún más la consulta (opcional, mejora rendimiento)
        add_filter( 'posts_pre_query', function( $posts, WP_Query $query ) use ( $q ) {
            return ( $query === $q ) ? array() : $posts;
        }, 10, 2 );

        // (Opcional) marca para que el tema muestre un mensaje personalizado
        $q->set( 'blocked_search', true );
        return;
    }

    // --- 2) Si van mezclados, eliminar sólo esos tokens y continuar ---
    // \b asegura que sean palabras completas (no "7319E2")
    $pattern  = '/\b(319e|319p)\b/i';
    $s_clean  = trim( preg_replace( $pattern, ' ', $s_raw ) );

    // Si cambió la cadena...
    if ( $s_clean !== $s_raw ) {
        if ( $s_clean === '' ) {
            // --- 3) Tras limpiar quedó vacío → sin resultados ---
            $q->set( 'post__in', array( 0 ) );
            $q->set( 'no_found_rows', true );
            add_filter( 'posts_pre_query', function( $posts, WP_Query $query ) use ( $q ) {
                return ( $query === $q ) ? array() : $posts;
            }, 10, 2 );
            $q->set( 'blocked_search', true );
        } else {
            // Actualiza la búsqueda sin los términos bloqueados
            $q->set( 's', $s_clean );
        }
    }
});


/**
 * 1) Redirige el archivo del CPT 'product' a la página /products (301)
 */
add_action( 'template_redirect', function () {
    if ( is_post_type_archive( 'product' ) ) {
        $target = get_page_by_path( 'products' );
        $url    = $target ? get_permalink( $target->ID ) : trailingslashit( home_url( 'products' ) );
        wp_safe_redirect( $url, 301 );
        exit;
    }
});

/**
 * Helper: normaliza URLs para comparar sin sustos
 */
if ( ! function_exists( 'several_normalize_url' ) ) {
    function several_normalize_url( $url ) {
        if ( ! $url ) return '';
        // Quita query y fragmento
        $url = strtok( $url, '?#' );
        // Fuerza barra final para que coincida con los enlaces de WP
        $url = trailingslashit( untrailingslashit( $url ) );
        // Normaliza esquema/host en minúsculas
        $parts = wp_parse_url( $url );
        if ( empty( $parts['host'] ) ) return $url;
        $scheme = isset( $parts['scheme'] ) ? strtolower( $parts['scheme'] ) . '://' : '//';
        $host   = strtolower( $parts['host'] );
        $port   = isset( $parts['port'] ) ? ':' . $parts['port'] : '';
        $path   = isset( $parts['path'] ) ? $parts['path'] : '/';
        return trailingslashit( $scheme . $host . $port . $path );
    }
}

/**
 * 2) Yoast breadcrumbs:
 *    - La miga "Products" (archivo del CPT) apunta al término más específico de 'market-sector'
 *      si existe; si no, a /products. (Prioridad 15)
 */
add_filter( 'wpseo_breadcrumb_links', function( $links ) {

    $cpt = 'product';
    $tax = 'market-sector';

    $products_page = get_page_by_path( 'products' );
    $fallback_url  = $products_page ? get_permalink( $products_page->ID ) : trailingslashit( home_url( 'products' ) );

    if ( is_singular( $cpt ) || is_post_type_archive( $cpt ) ) {

        // URL por defecto
        $target_url = $fallback_url;

        if ( is_singular( $cpt ) ) {
            $terms = wp_get_post_terms( get_the_ID(), $tax, [ 'hide_empty' => false ] );

            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {

                // ¿Pertenece (directa o indirectamente) a 'drive-system-division'?
                $is_drive = false;
                foreach ( $terms as $t ) {
                    $p = $t;
                    $safety = 0;
                    while ( $p && ! is_wp_error( $p ) ) {
                        if ( isset( $p->slug ) && $p->slug === 'drive-system-division' ) {
                            $is_drive = true;
                            break 2; // salimos de ambos bucles
                        }
                        if ( empty( $p->parent ) ) break;
                        $p = get_term( $p->parent, $tax );
                        if ( ++$safety > 50 ) break;
                    }
                }

                if ( $is_drive ) {
                    // Regla especial: si es de Drive, "Products" → /products/
                    $target_url = $fallback_url;
                } else {
                    // Regla general: enlazar al término más profundo
                    $deepest  = null;
                    $maxDepth = -1;

                    foreach ( $terms as $t ) {
                        $depth = 0;
                        $p = $t;
                        while ( $p && ! is_wp_error( $p ) && $p->parent ) {
                            $p = get_term( $p->parent, $tax );
                            $depth++;
                            if ( $depth > 50 ) break; // safety
                        }
                        if ( $depth > $maxDepth ) {
                            $maxDepth = $depth;
                            $deepest  = $t;
                        }
                    }

                    if ( $deepest ) {
                        $term_link = get_term_link( $deepest, $tax );
                        if ( ! is_wp_error( $term_link ) ) {
                            $target_url = $term_link;
                        }
                    }
                }
            }
        }

        // Reemplaza la miga que representa el archivo del CPT
        $archive_url      = get_post_type_archive_link( $cpt );
        $archive_url_norm = function_exists('several_normalize_url') ? several_normalize_url( $archive_url ) : $archive_url;

        foreach ( $links as &$link ) {
            $link_url_norm = isset( $link['url'] )
                ? ( function_exists('several_normalize_url') ? several_normalize_url( $link['url'] ) : $link['url'] )
                : '';

            $is_archive =
                ( isset( $link['ptarchive'] ) && $link['ptarchive'] === $cpt ) ||
                ( $archive_url_norm && $link_url_norm && $link_url_norm === $archive_url_norm );

            if ( $is_archive ) {
                $link['url'] = $target_url;     // cambiamos solo el enlace
                // $link['text'] = 'Products';  // si quieres forzar el texto
                unset( $link['ptarchive'] );     // evita que Yoast lo regenere como archivo
            }
        }
        unset( $link );
    }

    return $links;
}, 15 );

/**
 * 3) Yoast breadcrumbs:
 *    Mapea ciertos términos padre a páginas personalizadas (con anchors, etc.). (Prioridad 20)
 *    Afecta a cualquier breadcrumb donde aparezcan esos términos.
 */
add_filter( 'wpseo_breadcrumb_links', function( $links ) {

    $tax = 'market-sector';

    // Construye las URLs destino correctamente (anchors incluidos)
    $map = [
        'wiper-system-division' => trailingslashit( home_url( 'wiper-systems-division' ) ) . '#wiper-system-markets',
        'drive-system-division' => trailingslashit( home_url( 'drive-systems-division' ) ),
        // 'otro-slug' => trailingslashit( home_url( 'otra-pagina' ) ) . '#ancla',
    ];

    foreach ( $map as $term_slug => $target_url ) {
        $term = get_term_by( 'slug', $term_slug, $tax );
        if ( ! $term || is_wp_error( $term ) ) {
            continue;
        }

        $term_archive_url = get_term_link( $term, $tax );
        if ( is_wp_error( $term_archive_url ) ) {
            continue;
        }

        $term_archive_norm = several_normalize_url( $term_archive_url );

        foreach ( $links as &$link ) {
            if ( empty( $link['url'] ) ) continue;
            $link_norm = several_normalize_url( $link['url'] );

            if ( $link_norm === $term_archive_norm ) {
                $link['url'] = $target_url; // p.ej. /wiper-systems-division/#wiper-system-markets
            }
        }
        unset( $link );
    }

    return $links;
}, 20 ); // prioridad 20: corre después del filtro anterior
?>