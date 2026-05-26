<?php
function my_theme_enqueue_assets() {
    // Parent theme stylesheet.
    $parent_handle = 'parent-style';
    wp_enqueue_style(
        $parent_handle,
        get_template_directory_uri() . '/style.css',
        [],
        wp_get_theme( get_template() )->get( 'Version' )
    );

    // Child theme stylesheet.
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        [ $parent_handle ],
        wp_get_theme()->get( 'Version' )
    );

    // Extra child CSS (estils.css).
    wp_enqueue_style(
        'child-estils',
        get_stylesheet_directory_uri() . '/assets/css/estils.css',
        [ $parent_handle ],
        wp_get_theme()->get( 'Version' )
    );



    // Ensure jQuery is available.
    wp_enqueue_script( 'jquery' );

    // Main JS, loaded in footer, depends on jQuery.
    wp_enqueue_script(
        'child-main-js',
        get_stylesheet_directory_uri() . '/assets/js/main.js',
        [ 'jquery' ],
        '1.0.0',
        true
    );

}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_assets' );


/* REGISTER NEWS STYLE */

function prefix_register_block_styles() {
	register_block_style(
		array( 'core/button' ),
		array(
			'name'         => 'button-icon-right',
			'label'        => __( 'Icon Right', 'PDS' ),
		)
	);

    register_block_style(
		array( 'core/button' ),
		array(
			'name'         => 'button-icon-right-bottom',
			'label'        => __( 'Icon Right Bottom', 'PDS' ),
		)
	);

	register_block_style(
		array( 'core/button' ),
		array(
			'name'         => 'button-icon-left',
			'label'        => __( 'Icon Left', 'PDS' ),
		)
	);

    register_block_style(
		array( 'core/button' ),
		array(
			'name'         => 'button-tag',
			'label'        => __( 'Tag', 'PDS' ),
		)
	);

	register_block_style(
		array( 'core/list' ),
		array(
			'name'         => 'list-clean',
			'label'        => __( 'Llistat net', 'PDS' ),
		)
	);
	register_block_style(
		array( 'core/list' ),
		array(
			'name'         => 'list-ratllat',
			'label'        => __( 'Llistat underline', 'PDS' ),
		)
	);
}
add_action( 'init', 'prefix_register_block_styles' );


/* ADD ADMIN AND LOGIN STYLES */

function wpdocs_enqueue_custom_admin_style() {
	wp_register_style( 'custom_wp_admin_css', get_template_directory_uri() . '-child/assets/css/admin-styles.css', false, '1.0.0' );
	wp_enqueue_style( 'custom_wp_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'wpdocs_enqueue_custom_admin_style' );

function login_stylesheet() {
    wp_enqueue_style( 'custom-login', get_stylesheet_directory_uri() . '/assets/css/login-styles.css' );
}
add_action( 'login_enqueue_scripts', 'login_stylesheet' );


/* LOGIN H1 URL */

function my_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'my_login_logo_url' );

function my_login_logo_url_title() {
    return 'Your Site Name and Info';
}
add_filter( 'login_headertext', 'my_login_logo_url_title' );


/* EXCLUDE HSOJD CATEGORY */

add_filter('get_the_terms', 'ocultar_categoria_ohsjd', 10, 3);

function ocultar_categoria_ohsjd($terms, $post_id, $taxonomy) {
    if (!empty($terms) && is_array($terms)) {
        foreach ($terms as $key => $term) {
            if ($term->slug === 'ohsjd' || $term->name === 'OHSJD') {
                unset($terms[$key]);
            }
        }
        
        $terms = array_values($terms);
    }
    return $terms;
}
add_theme_support( 'wp-block-styles' );

function my_acf_block_render_callback( $block ) {
	$slug = str_replace('acf/', '', $block['name']);
	// include a template part from within the "template-parts/block" folder
	if( file_exists( get_theme_file_path("/template-parts/block/content-{$slug}.php") ) ) {
		include( get_theme_file_path("/template-parts/block/content-{$slug}.php") );
	}
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
        'name' => _x('Products', 'Post Type General Name', 'PDP'),
        'singular_name' => _x('Product', 'Post Type Singular Name', 'PDP'),
        'menu_name' => __('Products', 'PDP'),
        'name_admin_bar' => __('Product', 'PDP'),
        'archives' => __('Product Archives', 'PDP'),
        'attributes' => __('Product Attributes', 'PDP'),
        'parent_item_colon' => __('Parent Product:', 'PDP'),
        'all_items' => __('All Products', 'PDP'),
        'add_new_item' => __('Add New Product', 'PDP'),
        'add_new' => __('Add New', 'PDP'),
        'new_item' => __('New Product', 'PDP'),
        'edit_item' => __('Edit Product', 'PDP'),
        'update_item' => __('Update Product', 'PDP'),
        'view_item' => __('View Product', 'PDP'),
        'view_items' => __('View Products', 'PDP'),
        'search_items' => __('Search Products', 'PDP'),
        'not_found' => __('Not found', 'PDP'),
        'not_found_in_trash' => __('Not found in Trash', 'PDP'),
        'featured_image' => __('Featured Image', 'PDP'),
        'set_featured_image' => __('Set featured image', 'PDP'),
        'remove_featured_image' => __('Remove featured image', 'PDP'),
        'use_featured_image' => __('Use as featured image', 'PDP'),
        'insert_into_item' => __('Insert into product', 'PDP'),
        'uploaded_to_this_item' => __('Uploaded to this product', 'PDP'),
        'items_list' => __('Products list', 'PDP'),
        'items_list_navigation' => __('Products list navigation', 'PDP'),
        'filter_items_list' => __('Filter products list', 'PDP'),
    );

    $args = array(
        'label' => __('Product', 'PDP'),
        'description' => __('Custom post type for Products', 'PDP'),
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
        'name' => _x('Product Lines', 'taxonomy general name', 'PDP'),
        'singular_name' => _x('Product Line', 'taxonomy singular name', 'PDP'),
        'search_items' => __('Search Product Lines', 'PDP'),
        'all_items' => __('All Product Lines', 'PDP'),
        'parent_item' => __('Parent Product Line', 'PDP'),
        'parent_item_colon' => __('Parent Product Line:', 'PDP'),
        'edit_item' => __('Edit Product Line', 'PDP'),
        'update_item' => __('Update Product Line', 'PDP'),
        'add_new_item' => __('Add New Product Line', 'PDP'),
        'new_item_name' => __('New Product Line Name', 'PDP'),
        'menu_name' => __('Product Lines', 'PDP'),
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
        'name' => _x('Product Categories', 'taxonomy general name', 'PDP'),
        'singular_name' => _x('Product Category', 'taxonomy singular name', 'PDP'),
        'search_items' => __('Search Product Categories', 'PDP'),
        'all_items' => __('All Product Categories', 'PDP'),
        'parent_item' => __('Parent Product Category', 'PDP'),
        'parent_item_colon' => __('Parent Product Category:', 'PDP'),
        'edit_item' => __('Edit Product Category', 'PDP'),
        'update_item' => __('Update Product Category', 'PDP'),
        'add_new_item' => __('Add New Product Category', 'PDP'),
        'new_item_name' => __('New Product Category Name', 'PDP'),
        'menu_name' => __('Product Categories', 'PDP'),
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
        'name' => _x('Product Attributes', 'taxonomy general name', 'PDP'),
        'singular_name' => _x('Product Attribute', 'taxonomy singular name', 'PDP'),
        'search_items' => __('Search Product Attributes', 'PDP'),
        'all_items' => __('All Product Attributes', 'PDP'),
        'parent_item' => __('Parent Product Attribute', 'PDP'),
        'parent_item_colon' => __('Parent Product Attribute:', 'PDP'),
        'edit_item' => __('Edit Product Attribute', 'PDP'),
        'update_item' => __('Update Product Attribute', 'PDP'),
        'add_new_item' => __('Add New Product Attribute', 'PDP'),
        'new_item_name' => __('New Product Attribute Name', 'PDP'),
        'menu_name' => __('Product Attributes', 'PDP'),
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
        'name' => _x('Product Market Sectors', 'taxonomy general name', 'PDP'),
        'singular_name' => _x('Product Market Sector', 'taxonomy singular name', 'PDP'),
        'search_items' => __('Search Product Market Sector', 'PDP'),
        'all_items' => __('All Product Market Sector', 'PDP'),
        'parent_item' => __('Parent Product Market Sector', 'PDP'),
        'parent_item_colon' => __('Parent Product Market Sector:', 'PDP'),
        'edit_item' => __('Edit Product Market Sector', 'PDP'),
        'update_item' => __('Update Product Market Sector', 'PDP'),
        'add_new_item' => __('Add New Product Market Sector', 'PDP'),
        'new_item_name' => __('New Product Market Sector Name', 'PDP'),
        'menu_name' => __('Product Market Sector', 'PDP'),
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



// PEOPLE POST TYPE

function cptui_register_my_cpts() {

	/**
	 * Post Type: People.
	 */

	$labels = [
		"name" => esc_html__( "People", "PDS" ),
		"singular_name" => esc_html__( "People", "PDS" ),
	];

	$args = [
		"label" => esc_html__( "People", "PDS" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => false,
		"rewrite" => [ "slug" => "people", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-universal-access",
		"supports" => [ "title", "thumbnail", "excerpt", "page-attributes" ],
		"show_in_graphql" => false,
	];

	register_post_type( "people", $args );

	/**
	 * Post Type: Cases Study.
	 */

	$labels = [
		"name" => esc_html__( "Cases Study", "PDS" ),
		"singular_name" => esc_html__( "Case", "PDS" ),
	];

	$args = [
		"label" => esc_html__( "Cases Study", "PDS" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => "case-studies",
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => false,
		"rewrite" => [ "slug" => "case_study", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor", "thumbnail", "excerpt" ],
		"show_in_graphql" => false,
	];

	register_post_type( "case_study", $args );

	/**
	 * Post Type: Resources.
	 * Migrado de ACF Post Types a código (2026-05-21).
	 */

	$labels = [
		"name"                     => esc_html__( "Resources", "PDS" ),
		"singular_name"            => esc_html__( "Resource", "PDS" ),
		"menu_name"                => esc_html__( "Resources", "PDS" ),
		"all_items"                => esc_html__( "All Resources", "PDS" ),
		"edit_item"                => esc_html__( "Edit Resource", "PDS" ),
		"view_item"                => esc_html__( "View Resource", "PDS" ),
		"view_items"               => esc_html__( "View Resources", "PDS" ),
		"add_new_item"             => esc_html__( "Add New Resource", "PDS" ),
		"add_new"                  => esc_html__( "Add New Resource", "PDS" ),
		"new_item"                 => esc_html__( "New Resource", "PDS" ),
		"parent_item_colon"        => esc_html__( "Parent Resource:", "PDS" ),
		"search_items"             => esc_html__( "Search Resources", "PDS" ),
		"not_found"                => esc_html__( "No resources found", "PDS" ),
		"not_found_in_trash"       => esc_html__( "No resources found in Trash", "PDS" ),
		"archives"                 => esc_html__( "Resource Archives", "PDS" ),
		"attributes"               => esc_html__( "Resource Attributes", "PDS" ),
		"insert_into_item"         => esc_html__( "Insert into resource", "PDS" ),
		"uploaded_to_this_item"    => esc_html__( "Uploaded to this resource", "PDS" ),
		"filter_items_list"        => esc_html__( "Filter resources list", "PDS" ),
		"filter_by_date"           => esc_html__( "Filter resources by date", "PDS" ),
		"items_list_navigation"    => esc_html__( "Resources list navigation", "PDS" ),
		"items_list"               => esc_html__( "Resources list", "PDS" ),
		"item_published"           => esc_html__( "Resource published.", "PDS" ),
		"item_published_privately" => esc_html__( "Resource published privately.", "PDS" ),
		"item_reverted_to_draft"   => esc_html__( "Resource reverted to draft.", "PDS" ),
		"item_scheduled"           => esc_html__( "Resource scheduled.", "PDS" ),
		"item_updated"             => esc_html__( "Resource updated.", "PDS" ),
		"item_link"                => esc_html__( "Resource Link", "PDS" ),
		"item_link_description"    => esc_html__( "A link to a resource.", "PDS" ),
	];

	$args = [
		"label"                => esc_html__( "Resources", "PDS" ),
		"labels"               => $labels,
		"description"          => "",
		"public"               => true,
		"publicly_queryable"   => true,
		"show_ui"              => true,
		"show_in_rest"         => true,
		"rest_base"            => "",
		"rest_controller_class"=> "WP_REST_Posts_Controller",
		"rest_namespace"       => "wp/v2",
		"has_archive"          => true,
		"show_in_menu"         => true,
		"show_in_nav_menus"    => true,
		"show_in_admin_bar"    => true,
		"delete_with_user"     => false,
		"exclude_from_search"  => false,
		"capability_type"      => "post",
		"map_meta_cap"         => true,
		"hierarchical"         => true,
		"can_export"           => true,
		"rewrite"              => [ "slug" => "resources", "with_front" => true ],
		"query_var"            => true,
		"menu_position"        => 5,
		"menu_icon"            => "dashicons-rest-api",
		"supports"             => [ "title", "author", "editor", "excerpt", "thumbnail", "custom-fields" ],
	];

	register_post_type( "resources", $args );

	/**
	 * Post Type: Market.
	 * Migrado de ACF Post Types a código (2026-05-21).
	 */
	register_post_type( "market", [
		"label"                => esc_html__( "Markets", "PDS" ),
		"labels"               => [
			"name"                     => esc_html__( "Markets", "PDS" ),
			"singular_name"            => esc_html__( "Market", "PDS" ),
			"menu_name"                => esc_html__( "Markets", "PDS" ),
			"all_items"                => esc_html__( "All Markets", "PDS" ),
			"edit_item"                => esc_html__( "Edit Market", "PDS" ),
			"view_item"                => esc_html__( "View Market", "PDS" ),
			"view_items"               => esc_html__( "View Markets", "PDS" ),
			"add_new_item"             => esc_html__( "Add New Market", "PDS" ),
			"add_new"                  => esc_html__( "Add New Market", "PDS" ),
			"new_item"                 => esc_html__( "New Market", "PDS" ),
			"parent_item_colon"        => esc_html__( "Parent Market:", "PDS" ),
			"search_items"             => esc_html__( "Search Markets", "PDS" ),
			"not_found"                => esc_html__( "No markets found", "PDS" ),
			"not_found_in_trash"       => esc_html__( "No markets found in Trash", "PDS" ),
			"archives"                 => esc_html__( "Market Archives", "PDS" ),
			"attributes"               => esc_html__( "Market Attributes", "PDS" ),
			"insert_into_item"         => esc_html__( "Insert into market", "PDS" ),
			"uploaded_to_this_item"    => esc_html__( "Uploaded to this market", "PDS" ),
			"filter_items_list"        => esc_html__( "Filter markets list", "PDS" ),
			"filter_by_date"           => esc_html__( "Filter markets by date", "PDS" ),
			"items_list_navigation"    => esc_html__( "Markets list navigation", "PDS" ),
			"items_list"               => esc_html__( "Markets list", "PDS" ),
			"item_published"           => esc_html__( "Market published.", "PDS" ),
			"item_published_privately" => esc_html__( "Market published privately.", "PDS" ),
			"item_reverted_to_draft"   => esc_html__( "Market reverted to draft.", "PDS" ),
			"item_scheduled"           => esc_html__( "Market scheduled.", "PDS" ),
			"item_updated"             => esc_html__( "Market updated.", "PDS" ),
			"item_link"                => esc_html__( "Market Link", "PDS" ),
			"item_link_description"    => esc_html__( "A link to a market.", "PDS" ),
		],
		"description"          => "",
		"public"               => true,
		"publicly_queryable"   => true,
		"show_ui"              => true,
		"show_in_rest"         => true,
		"rest_base"            => "",
		"rest_controller_class"=> "WP_REST_Posts_Controller",
		"rest_namespace"       => "wp/v2",
		"has_archive"          => false,
		"show_in_menu"         => true,
		"show_in_nav_menus"    => true,
		"show_in_admin_bar"    => true,
		"delete_with_user"     => false,
		"exclude_from_search"  => false,
		"capability_type"      => "post",
		"map_meta_cap"         => true,
		"hierarchical"         => true,
		"can_export"           => true,
		"rewrite"              => [ "slug" => "market", "with_front" => true ],
		"query_var"            => true,
		"menu_position"        => 7,
		"menu_icon"            => "dashicons-admin-site-alt2",
		"supports"             => [ "title", "author", "editor", "excerpt", "thumbnail", "custom-fields" ],
	] );

	/**
	 * Post Type: Location.
	 * Migrado de ACF Post Types a código (2026-05-21).
	 */
	register_post_type( "location", [
		"label"                => esc_html__( "Locations", "PDS" ),
		"labels"               => [
			"name"                     => esc_html__( "Locations", "PDS" ),
			"singular_name"            => esc_html__( "Location", "PDS" ),
			"menu_name"                => esc_html__( "Locations", "PDS" ),
			"all_items"                => esc_html__( "All Locations", "PDS" ),
			"edit_item"                => esc_html__( "Edit Location", "PDS" ),
			"view_item"                => esc_html__( "View Location", "PDS" ),
			"view_items"               => esc_html__( "View Locations", "PDS" ),
			"add_new_item"             => esc_html__( "Add New Location", "PDS" ),
			"add_new"                  => esc_html__( "Add New Location", "PDS" ),
			"new_item"                 => esc_html__( "New Location", "PDS" ),
			"parent_item_colon"        => esc_html__( "Parent Location:", "PDS" ),
			"search_items"             => esc_html__( "Search Locations", "PDS" ),
			"not_found"                => esc_html__( "No locations found", "PDS" ),
			"not_found_in_trash"       => esc_html__( "No locations found in Trash", "PDS" ),
			"archives"                 => esc_html__( "Location Archives", "PDS" ),
			"attributes"               => esc_html__( "Location Attributes", "PDS" ),
			"insert_into_item"         => esc_html__( "Insert into location", "PDS" ),
			"uploaded_to_this_item"    => esc_html__( "Uploaded to this location", "PDS" ),
			"filter_items_list"        => esc_html__( "Filter locations list", "PDS" ),
			"filter_by_date"           => esc_html__( "Filter locations by date", "PDS" ),
			"items_list_navigation"    => esc_html__( "Locations list navigation", "PDS" ),
			"items_list"               => esc_html__( "Locations list", "PDS" ),
			"item_published"           => esc_html__( "Location published.", "PDS" ),
			"item_published_privately" => esc_html__( "Location published privately.", "PDS" ),
			"item_reverted_to_draft"   => esc_html__( "Location reverted to draft.", "PDS" ),
			"item_scheduled"           => esc_html__( "Location scheduled.", "PDS" ),
			"item_updated"             => esc_html__( "Location updated.", "PDS" ),
			"item_link"                => esc_html__( "Location Link", "PDS" ),
			"item_link_description"    => esc_html__( "A link to a location.", "PDS" ),
		],
		"description"          => "",
		"public"               => true,
		"publicly_queryable"   => false,
		"show_ui"              => true,
		"show_in_rest"         => true,
		"rest_base"            => "",
		"rest_controller_class"=> "WP_REST_Posts_Controller",
		"rest_namespace"       => "wp/v2",
		"has_archive"          => false,
		"show_in_menu"         => true,
		"show_in_nav_menus"    => true,
		"show_in_admin_bar"    => true,
		"delete_with_user"     => false,
		"exclude_from_search"  => false,
		"capability_type"      => "post",
		"map_meta_cap"         => true,
		"hierarchical"         => true,
		"can_export"           => true,
		"rewrite"              => [ "slug" => "location", "with_front" => true, "feeds" => true ],
		"query_var"            => true,
		"menu_position"        => 8,
		"menu_icon"            => "dashicons-location-alt",
		"supports"             => [ "title", "author", "editor", "excerpt", "thumbnail", "custom-fields" ],
	] );

	/**
	 * Post Type: Sostenibilidad.
	 * Migrado de ACF Post Types a código (2026-05-21).
	 */
	register_post_type( "sostenibilidad", [
		"label"                => esc_html__( "Sostenibilidad", "PDS" ),
		"labels"               => [
			"name"                     => esc_html__( "Sostenibilidad", "PDS" ),
			"singular_name"            => esc_html__( "Sostenibilidad", "PDS" ),
			"menu_name"                => esc_html__( "Sostenibilidad", "PDS" ),
			"all_items"                => esc_html__( "All Sostenibilidad", "PDS" ),
			"edit_item"                => esc_html__( "Edit Sostenibilidad", "PDS" ),
			"view_item"                => esc_html__( "View Sostenibilidad", "PDS" ),
			"view_items"               => esc_html__( "View Sostenibilidad", "PDS" ),
			"add_new_item"             => esc_html__( "Add New Sostenibilidad", "PDS" ),
			"add_new"                  => esc_html__( "Add New Sostenibilidad", "PDS" ),
			"new_item"                 => esc_html__( "New Sostenibilidad", "PDS" ),
			"parent_item_colon"        => esc_html__( "Parent Sostenibilidad:", "PDS" ),
			"search_items"             => esc_html__( "Search Sostenibilidad", "PDS" ),
			"not_found"                => esc_html__( "No sostenibilidad found", "PDS" ),
			"not_found_in_trash"       => esc_html__( "No sostenibilidad found in Trash", "PDS" ),
			"archives"                 => esc_html__( "Sostenibilidad Archives", "PDS" ),
			"attributes"               => esc_html__( "Sostenibilidad Attributes", "PDS" ),
			"insert_into_item"         => esc_html__( "Insert into sostenibilidad", "PDS" ),
			"uploaded_to_this_item"    => esc_html__( "Uploaded to this sostenibilidad", "PDS" ),
			"filter_items_list"        => esc_html__( "Filter sostenibilidad list", "PDS" ),
			"filter_by_date"           => esc_html__( "Filter sostenibilidad by date", "PDS" ),
			"items_list_navigation"    => esc_html__( "Sostenibilidad list navigation", "PDS" ),
			"items_list"               => esc_html__( "Sostenibilidad list", "PDS" ),
			"item_published"           => esc_html__( "Sostenibilidad published.", "PDS" ),
			"item_published_privately" => esc_html__( "Sostenibilidad published privately.", "PDS" ),
			"item_reverted_to_draft"   => esc_html__( "Sostenibilidad reverted to draft.", "PDS" ),
			"item_scheduled"           => esc_html__( "Sostenibilidad scheduled.", "PDS" ),
			"item_updated"             => esc_html__( "Sostenibilidad updated.", "PDS" ),
			"item_link"                => esc_html__( "Sostenibilidad Link", "PDS" ),
			"item_link_description"    => esc_html__( "A link to a sostenibilidad.", "PDS" ),
		],
		"description"          => "",
		"public"               => true,
		"publicly_queryable"   => false,
		"show_ui"              => true,
		"show_in_rest"         => true,
		"rest_base"            => "",
		"rest_controller_class"=> "WP_REST_Posts_Controller",
		"rest_namespace"       => "wp/v2",
		"has_archive"          => false,
		"show_in_menu"         => true,
		"show_in_nav_menus"    => true,
		"show_in_admin_bar"    => true,
		"delete_with_user"     => false,
		"exclude_from_search"  => false,
		"capability_type"      => "post",
		"map_meta_cap"         => true,
		"hierarchical"         => false,
		"can_export"           => true,
		"rewrite"              => [ "slug" => "sostenibilidad", "with_front" => true ],
		"query_var"            => true,
		"menu_icon"            => "dashicons-admin-site-alt2",
		"supports"             => [ "title", "editor", "thumbnail", "custom-fields" ],
	] );

	/**
	 * Post Type: Event.
	 * Migrado de ACF Post Types a código (2026-05-21).
	 */
	register_post_type( "event", [
		"label"                => esc_html__( "Events", "PDS" ),
		"labels"               => [
			"name"                     => esc_html__( "Events", "PDS" ),
			"singular_name"            => esc_html__( "Event", "PDS" ),
			"menu_name"                => esc_html__( "Events", "PDS" ),
			"all_items"                => esc_html__( "All Events", "PDS" ),
			"edit_item"                => esc_html__( "Edit Event", "PDS" ),
			"view_item"                => esc_html__( "View Event", "PDS" ),
			"view_items"               => esc_html__( "View Events", "PDS" ),
			"add_new_item"             => esc_html__( "Add New Event", "PDS" ),
			"add_new"                  => esc_html__( "Add New Event", "PDS" ),
			"new_item"                 => esc_html__( "New Event", "PDS" ),
			"parent_item_colon"        => esc_html__( "Parent Event:", "PDS" ),
			"search_items"             => esc_html__( "Search Events", "PDS" ),
			"not_found"                => esc_html__( "No events found", "PDS" ),
			"not_found_in_trash"       => esc_html__( "No events found in Trash", "PDS" ),
			"archives"                 => esc_html__( "Event Archives", "PDS" ),
			"attributes"               => esc_html__( "Event Attributes", "PDS" ),
			"insert_into_item"         => esc_html__( "Insert into event", "PDS" ),
			"uploaded_to_this_item"    => esc_html__( "Uploaded to this event", "PDS" ),
			"filter_items_list"        => esc_html__( "Filter events list", "PDS" ),
			"filter_by_date"           => esc_html__( "Filter events by date", "PDS" ),
			"items_list_navigation"    => esc_html__( "Events list navigation", "PDS" ),
			"items_list"               => esc_html__( "Events list", "PDS" ),
			"item_published"           => esc_html__( "Event published.", "PDS" ),
			"item_published_privately" => esc_html__( "Event published privately.", "PDS" ),
			"item_reverted_to_draft"   => esc_html__( "Event reverted to draft.", "PDS" ),
			"item_scheduled"           => esc_html__( "Event scheduled.", "PDS" ),
			"item_updated"             => esc_html__( "Event updated.", "PDS" ),
			"item_link"                => esc_html__( "Event Link", "PDS" ),
			"item_link_description"    => esc_html__( "A link to an event.", "PDS" ),
		],
		"description"          => "",
		"public"               => true,
		"publicly_queryable"   => true,
		"show_ui"              => true,
		"show_in_rest"         => true,
		"rest_base"            => "",
		"rest_controller_class"=> "WP_REST_Posts_Controller",
		"rest_namespace"       => "wp/v2",
		"has_archive"          => false,
		"show_in_menu"         => true,
		"show_in_nav_menus"    => true,
		"show_in_admin_bar"    => true,
		"delete_with_user"     => false,
		"exclude_from_search"  => false,
		"capability_type"      => "post",
		"map_meta_cap"         => true,
		"hierarchical"         => true,
		"can_export"           => true,
		"rewrite"              => [ "slug" => "event", "with_front" => true ],
		"query_var"            => true,
		"menu_position"        => 5,
		"menu_icon"            => "dashicons-megaphone",
		"supports"             => [ "title", "author", "editor", "excerpt", "thumbnail", "custom-fields" ],
	] );
}

add_action( 'init', 'cptui_register_my_cpts' );

/* TAXONOMÍAS: Countries, Department, Markets */

function pds_register_taxonomies() {

	/**
	 * Taxonomy: Countries.
	 */
	register_taxonomy( "countries", [ "location" ], [
		"label"               => esc_html__( "Countries", "PDS" ),
		"labels"              => [
			"name"          => esc_html__( "Countries", "PDS" ),
			"singular_name" => esc_html__( "Country", "PDS" ),
		],
		"public"              => true,
		"publicly_queryable"  => true,
		"hierarchical"        => false,
		"show_ui"             => true,
		"show_in_menu"        => true,
		"show_in_nav_menus"   => true,
		"query_var"           => true,
		"rewrite"             => [ 'slug' => 'countries', 'with_front' => true ],
		"show_admin_column"   => false,
		"show_in_rest"        => true,
		"show_tagcloud"       => false,
		"rest_base"           => "countries",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace"      => "wp/v2",
		"show_in_quick_edit"  => false,
		"sort"                => false,
	] );

	/**
	 * Taxonomy: Department.
	 */
	register_taxonomy( "department", [ "case_study" ], [
		"label"               => esc_html__( "Departments", "PDS" ),
		"labels"              => [
			"name"          => esc_html__( "Departments", "PDS" ),
			"singular_name" => esc_html__( "Department", "PDS" ),
		],
		"public"              => true,
		"publicly_queryable"  => true,
		"hierarchical"        => true,
		"show_ui"             => true,
		"show_in_menu"        => true,
		"show_in_nav_menus"   => true,
		"query_var"           => true,
		"rewrite"             => [ 'slug' => 'department', 'with_front' => true, 'hierarchical' => true ],
		"show_admin_column"   => false,
		"show_in_rest"        => true,
		"show_tagcloud"       => false,
		"rest_base"           => "department",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace"      => "wp/v2",
		"show_in_quick_edit"  => true,
		"sort"                => true,
	] );

	/**
	 * Taxonomy: Market Sectors.
	 * Migrada de ACF Taxonomies a código (2026-05-21).
	 */
	register_taxonomy( "market-sector", [ "market", "product" ], [
		"label"               => esc_html__( "Market Sectors", "PDS" ),
		"labels"              => [
			"name"              => esc_html__( "Market Sectors", "PDS" ),
			"singular_name"     => esc_html__( "Market Sector", "PDS" ),
			"menu_name"         => esc_html__( "Market Sectors", "PDS" ),
			"all_items"         => esc_html__( "All Market Sectors", "PDS" ),
			"edit_item"         => esc_html__( "Edit Market Sector", "PDS" ),
			"view_item"         => esc_html__( "View Market Sector", "PDS" ),
			"update_item"       => esc_html__( "Update Market Sector", "PDS" ),
			"add_new_item"      => esc_html__( "Add New Market Sector", "PDS" ),
			"new_item_name"     => esc_html__( "New Market Sector Name", "PDS" ),
			"parent_item"       => esc_html__( "Parent Market Sector", "PDS" ),
			"parent_item_colon" => esc_html__( "Parent Market Sector:", "PDS" ),
			"search_items"      => esc_html__( "Search Market Sectors", "PDS" ),
			"not_found"         => esc_html__( "No market sector found", "PDS" ),
			"no_terms"          => esc_html__( "No market sectors", "PDS" ),
			"filter_by_item"    => esc_html__( "Filter by market sector", "PDS" ),
			"items_list_navigation" => esc_html__( "Market Sector list navigation", "PDS" ),
			"items_list"        => esc_html__( "Market Sector list", "PDS" ),
			"back_to_items"     => esc_html__( "← Go to market sectors", "PDS" ),
			"item_link"         => esc_html__( "Market Sector Link", "PDS" ),
			"item_link_description" => esc_html__( "A link to a market sector", "PDS" ),
		],
		"public"              => true,
		"publicly_queryable"  => true,
		"hierarchical"        => true,
		"show_ui"             => true,
		"show_in_menu"        => true,
		"show_in_nav_menus"   => true,
		"query_var"           => true,
		"rewrite"             => [ 'slug' => 'market-sector', 'with_front' => true, 'hierarchical' => true ],
		"show_admin_column"   => true,
		"show_in_rest"        => true,
		"show_tagcloud"       => true,
		"rest_base"           => "market-sector",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace"      => "wp/v2",
		"show_in_quick_edit"  => true,
		"sort"                => false,
	] );

	/**
	 * Taxonomy: Download Category.
	 * Migrada de ACF Taxonomies a código (2026-05-21).
	 */
	register_taxonomy( "download-category", [ "resources" ], [
		"label"               => esc_html__( "Downloads Categories", "PDS" ),
		"labels"              => [
			"name"              => esc_html__( "Downloads Categories", "PDS" ),
			"singular_name"     => esc_html__( "Download Category", "PDS" ),
			"menu_name"         => esc_html__( "Downloads", "PDS" ),
			"all_items"         => esc_html__( "All Download Categories", "PDS" ),
			"edit_item"         => esc_html__( "Edit Download Category", "PDS" ),
			"view_item"         => esc_html__( "View Download Category", "PDS" ),
			"update_item"       => esc_html__( "Update Download Category", "PDS" ),
			"add_new_item"      => esc_html__( "Add New Download Category", "PDS" ),
			"new_item_name"     => esc_html__( "New Download Category Name", "PDS" ),
			"parent_item"       => esc_html__( "Parent Download Category", "PDS" ),
			"parent_item_colon" => esc_html__( "Parent Download Category:", "PDS" ),
			"search_items"      => esc_html__( "Search Download Categories", "PDS" ),
			"not_found"         => esc_html__( "No download categories found", "PDS" ),
			"no_terms"          => esc_html__( "No download categories", "PDS" ),
			"filter_by_item"    => esc_html__( "Filter by download category", "PDS" ),
			"items_list_navigation" => esc_html__( "Download categories list navigation", "PDS" ),
			"items_list"        => esc_html__( "Download categories list", "PDS" ),
			"back_to_items"     => esc_html__( "← Go to download categories", "PDS" ),
			"item_link"         => esc_html__( "Download Category Link", "PDS" ),
			"item_link_description" => esc_html__( "A link to a download category", "PDS" ),
		],
		"public"              => true,
		"publicly_queryable"  => false,
		"hierarchical"        => true,
		"show_ui"             => true,
		"show_in_menu"        => true,
		"show_in_nav_menus"   => true,
		"query_var"           => true,
		"rewrite"             => [ 'slug' => 'download-category', 'with_front' => true, 'hierarchical' => false ],
		"show_admin_column"   => true,
		"show_in_rest"        => true,
		"show_tagcloud"       => true,
		"rest_base"           => "download-category",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace"      => "wp/v2",
		"show_in_quick_edit"  => true,
		"sort"                => false,
	] );

	/**
	 * Taxonomy: Motor Spec.
	 * Especificaciones técnicas de motores para filtrado.
	 * Términos importados desde Google Sheet (2026-05-26).
	 * Script de reimportación: ~/wp-deploy/scripts/doga-import-motor-specs.php
	 */
	register_taxonomy( 'motor-spec', [ 'product' ], [
		'label'               => __( 'Motor Specs', 'pds-motor-finder' ),
		'labels'              => [
			'name'              => __( 'Motor Specs', 'pds-motor-finder' ),
			'singular_name'     => __( 'Motor Spec', 'pds-motor-finder' ),
			'menu_name'         => __( 'Motor Specs', 'pds-motor-finder' ),
			'all_items'         => __( 'All Motor Specs', 'pds-motor-finder' ),
			'edit_item'         => __( 'Edit Motor Spec', 'pds-motor-finder' ),
			'add_new_item'      => __( 'Add New Motor Spec', 'pds-motor-finder' ),
			'new_item_name'     => __( 'New Motor Spec Name', 'pds-motor-finder' ),
			'search_items'      => __( 'Search Motor Specs', 'pds-motor-finder' ),
			'parent_item'       => __( 'Parent Spec', 'pds-motor-finder' ),
			'parent_item_colon' => __( 'Parent Spec:', 'pds-motor-finder' ),
			'not_found'         => __( 'No motor specs found', 'pds-motor-finder' ),
		],
		'hierarchical'        => true,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_admin_column'   => true,
		'show_in_rest'        => true,
		'show_tagcloud'       => false,
		'query_var'           => true,
		'rewrite'             => [ 'slug' => 'motor-spec', 'with_front' => false ],
		'rest_base'           => 'motor-spec',
	] );

	/**
	 * Taxonomy: Markets.
	 */
	register_taxonomy( "market", [ "post", "page" ], [
		"label"               => esc_html__( "Markets", "PDS" ),
		"labels"              => [
			"name"          => esc_html__( "Markets", "PDS" ),
			"singular_name" => esc_html__( "Market", "PDS" ),
		],
		"public"              => true,
		"publicly_queryable"  => true,
		"hierarchical"        => true,
		"show_ui"             => true,
		"show_in_menu"        => true,
		"show_in_nav_menus"   => true,
		"query_var"           => true,
		"rewrite"             => [ 'slug' => 'market', 'with_front' => true ],
		"show_admin_column"   => true,
		"show_in_rest"        => true,
		"show_tagcloud"       => false,
		"rest_base"           => "market",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"rest_namespace"      => "wp/v2",
		"show_in_quick_edit"  => true,
		"sort"                => true,
	] );
}
add_action( 'init', 'pds_register_taxonomies' );



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





/* DISPLAY PAGE TEMPLATE IN COSOLE LOG */

function show_template() {
	global $template;
    $output = "<script>console.log('TEMPLATE in USE:". $template ."');</script>";
    echo $output;
}
add_action('wp_footer', 'show_template');


/* EDIT PAGE */

edit_post_link( __( 'Editar', 'PDP' ), '<p>', '</p>', null, 'btn btn-primary btn-edit-post-link' );
add_filter('the_content', 'mycontent');
add_filter('avf_template_builder_content', 'mycontent');

function mycontent( $content ) {
	if( is_singular() && is_user_logged_in() ) {
		$content = $content . '<div class="btn btn-primary edit-post-link" style="background-color:red; display:block; border-radius:50%; width:100px; height:100px; position: fixed; right:2rem; text-decoration: none; bottom:2rem; z-index:999; font-family:Arial, Sans-serif;"><a style="display:block; text-align:center; line-height:100px; color:white;" href="' . get_edit_post_link( get_the_ID(), 'Editar') . '">Editar</a></div>';
	}
	return $content;
}



/* CONVERSION */

add_action('wpforms_wp_footer_end', function () {
    ?>
    <script>
      (function($){
        var fired = false;
  
        // Form ID: 14905
        $('#wpforms-form-14905').on('wpformsAjaxSubmitSuccess', function () {
          if (fired) return; // evita doble conteo
          fired = true;
  
          if (typeof gtag === 'function') {
            gtag('event', 'conversion', {
              'send_to': 'AW-17980477511/sMwqCP-Q9P8bEMeg4f1C'
            });
          }
        });
      })(jQuery);
    </script>
    <?php
  }, 10);


  add_action('wpforms_wp_footer_end', function () {
    ?>
    <script>
      (function($){
        var fired = false;
  
        // WPForms Form ID: 14992
        $('#wpforms-form-14992').on('wpformsAjaxSubmitSuccess', function () {
          if (fired) return; // evita doble conteo
          fired = true;
  
          if (typeof gtag === 'function') {
            gtag('event', 'conversion', {
              'send_to': 'AW-17980477511/nDu_CPzY9IUcEMeg4f1C'
            });
          }
        });
      })(jQuery);
    </script>
    <?php
  }, 10);


/* NEWS USER ROLE */

    add_role(
        'forms_writer',
        'Forms Editor',
        array(
            'read'         => true,
            'edit_posts'   => true,
            'delete_posts' => false,
            'edit_others_posts' => false,
            'publish_posts' => false,
            'read_private_posts' => false,
            'edit_private_posts' => false,
            'delete_private_posts' => false,
            'edit_published_posts' => false,
            'delete_published_posts' => false,
            'edit_others_pages' => true,
        )
    );
