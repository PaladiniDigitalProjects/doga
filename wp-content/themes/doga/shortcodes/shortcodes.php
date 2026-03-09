<?php
/* ************** About Mission core Values Cards ************** */
function render_mission_value_cards()
{
    ob_start();
    $a_mission_n_value_card_details = get_field('a_mission_n_value_card_details'); ?>
    <?php if (!empty($a_mission_n_value_card_details)): ?>
        <div class="wrapper">
            <div class="left">
                <div class="cards">
                    <?php foreach ($a_mission_n_value_card_details as $mission_cards):
                        $mvc_title = $mission_cards['mvc_title'];
                        $mvc_description = $mission_cards['mvc_description'];
                        ?>
                        <?php if ($mvc_title || $mvc_description): ?>
                            <div class="card">
                                <?php echo $mvc_title ? '<h4>' . $mvc_title . '</h4>' : ''; ?>
                                <?php echo apply_filters('the_content', $mvc_description); ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}
add_shortcode('mission_value_cards', 'render_mission_value_cards');

/* ************** About Mission core Values Cards ************** */


/* ************** Custom Map Based On Locations ************** */
function custom_map_using_locations()
{
    ob_start();
    ?>

   <!-- <script 
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAy8rVP5EEk2cjiQLtOkF8ekHdQ0Ek_zuU&callback=initMap" 
  async 
  defer>
</script> -->

<script 
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAeXttmfhODi5_XScPUT3W--RdH9mY5JBw_zuU&callback=initMap" 
  async 
  defer>
</script>

<!-- AIzaSyAeXttmfhODi5_XScPUT3W--RdH9mY5JBw -->






    <div id="locations-map-container"
        class="locations-map-container <?php echo (!is_front_page()) ? 'location-map-info-with-image' : ''; ?>"
        style="display: flex;">
        <div id="locations-map" class="location-map" style="width: 100%; height: 500px;"></div>
        <div id="location-info-panel" class="location-info-panel "
            style="width: 30%; padding: 20px; background-color: #f5f5f5;">
            <!-- Content will be dynamically filled here -->
        </div>
    </div>
    <script>
    /**
     * Ajusta el zoom y centro del mapa según el tamaño de la pantalla
     */
    function setResponsiveMapView(map) {
        const isMobile = window.innerWidth <= 991;
        map.setZoom(isMobile ? 1.2 : 2);
        map.setCenter(isMobile ? { lat: 15, lng: 20 } : { lat: 20, lng: 0 });
    }

    /**
     * Inicializa el mapa con Google Maps
     */
    function initMap() {
        const map = new google.maps.Map(document.getElementById("locations-map"), {
            zoom: 2,
            center: { lat: 20, lng: 0 },
            mapTypeControl: false,
            fullscreenControl: false,
            streetViewControl: false,
            zoomControl: true,
            disableDefaultUI: true,
            styles: [
                { elementType: "geometry", stylers: [{ color: "#1d2c4d" }] },
                { elementType: "labels.text.fill", stylers: [{ color: "#8ec3b9" }] },
                { elementType: "labels.text.stroke", stylers: [{ color: "#1a3646" }] },
                {
                    featureType: "administrative.country",
                    elementType: "geometry.stroke",
                    stylers: [{ color: "#4b6878" }]
                },
                {
                    featureType: "landscape.natural",
                    elementType: "geometry",
                    stylers: [{ color: "#023e58" }]
                },
                {
                    featureType: "poi",
                    elementType: "geometry",
                    stylers: [{ color: "#283d6a" }]
                },
                {
                    featureType: "road",
                    elementType: "geometry",
                    stylers: [{ color: "#304a7d" }]
                },
                {
                    featureType: "water",
                    elementType: "geometry",
                    stylers: [{ color: "#0e1626" }]
                }
            ]
        });

        // Ajuste inicial y en redimensionar pantalla
        setResponsiveMapView(map);
        window.addEventListener("resize", () => setResponsiveMapView(map));

        const geocoder = new google.maps.Geocoder();
        const markers = [];
        const headquartersTitle = "Spain";
        let activeMarker = null;
        const bounds = new google.maps.LatLngBounds();

        const icons = {
            white: { url: "/wp-content/themes/doga/assets/images/white-map-marker.png" },
            red: { url: "/wp-content/themes/doga/assets/images/red-map-marker.png" }
        };

        // Datos generados por PHP
        const locations = <?php
            $county_locations = [];
            $query_args = [
                'post_type'      => 'location',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'ASC',
            ];

            $locations = new WP_Query($query_args);
            if ($locations->have_posts()):
                while ($locations->have_posts()): $locations->the_post();
                    $title = get_the_title();
                    $thumbnail_image = get_the_post_thumbnail_url();
                    $ld_country_offices_locations = get_field('ld_country_offices_locations', get_the_ID());

                    if (!empty($ld_country_offices_locations)):
                        foreach ($ld_country_offices_locations as $ls_country_offices_data):
                            $col_address = $ls_country_offices_data['col_address'];

                            // Solo agregar si hay dirección
                            if (!empty($col_address)) {
                                $county_locations[] = [
                                    'title'        => $title,
                                    'company_name' => $ls_country_offices_data['col_company_name'],
                                    'address'      => $col_address,
                                    'phone'        => $ls_country_offices_data['col_phone_number'],
                                    'email'        => $ls_country_offices_data['col_email_address'],
                                    'image'        => $ls_country_offices_data['col_image']['url'] ?? $thumbnail_image,
                                ];
                            }
                        endforeach;
                    endif;
                endwhile;
                wp_reset_postdata();
            endif;

            echo json_encode($county_locations, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        ?>;

        /**
         * Actualiza el panel de información lateral
         */
        function updateInfoPanel(data) {
            const panel = document.getElementById("location-info-panel");
            const cleanPhone = data.phone ? data.phone.replace(/[^+\d]/g, '') : '';

            panel.innerHTML = `
                ${data.title || data.company_name ? `
                    <div class="location-title-block">
                        <h2>${data.title}</h2>
                        ${data.company_name ? `<h4>${data.company_name}</h4>` : ''}
                    </div>
                ` : ''}

                ${data.address || data.phone || data.email ? `
                    <div class="location-addres-contact-details-block">
                        ${data.address ? `
                            <div class="location-addres-details">
                                <img src="/wp-content/themes/doga/assets/images/white-map-marker.png" alt="location" />
                                <p>${data.address}</p>
                            </div>
                        ` : ''}
                        ${data.phone ? `
                            <div class="location-contact-details">
                                <img src="/wp-content/themes/doga/assets/images/phone-icon.png" alt="phone" />
                                <a href="tel:${cleanPhone}">${data.phone}</a>
                            </div>
                        ` : ''}
                        ${data.email ? `
                            <div class="location-contact-details">
                                <img src="/wp-content/themes/doga/assets/images/email-icon.png" alt="email" />
                                <a href="mailto:${data.email}">${data.email}</a>
                            </div>
                        ` : ''}
                    </div>
                ` : ''}

                ${data.image ? `
                    <div class="location-thumbnail-img">
                        <img src="${data.image}" class="img-fluid" alt="${data.title}" />
                    </div>
                ` : ''}
            `;
        }

        // Validación inicial
        if (!locations || locations.length === 0) {
            console.warn('No locations found to display on map');
            document.getElementById("location-info-panel").innerHTML = '<p>No locations available</p>';
            return;
        }

        // Procesar ubicaciones
        locations.forEach(data => {
            if (!data.address || data.address.trim() === '') {
                console.warn(`Skipping location ${data.title}: No address provided`);
                return;
            }

            geocoder.geocode({ address: data.address }, (results, status) => {
                if (status === 'OK' && results && results.length > 0) {
                    const position = results[0].geometry.location;
                    const isHQ = data.title === headquartersTitle;

                    const marker = new google.maps.Marker({
                        map,
                        position,
                        title: data.title,
                        icon: isHQ ? icons.red : icons.white,
                    });

                    marker.addListener("click", () => {
                        markers.forEach(m => m.setIcon(icons.white));
                        marker.setIcon(icons.red);
                        activeMarker = marker;
                        updateInfoPanel(data);
                    });

                    if (isHQ) {
                        map.setCenter(position);
                        updateInfoPanel(data);
                        activeMarker = marker;
                    }

                    markers.push(marker);
                    bounds.extend(position);
                } else {
                    console.warn(`Geocode failed for ${data.title}: ${status}`);
                }
            });
        });
    }

   
</script>

    <?php
    return ob_get_clean();
}
add_shortcode('custom_map_shortcode', 'custom_map_using_locations');
/* ************** Custom Map Based On Locations ************** */


/* ************** Gravity Form Submit button filter ************** */
add_filter('gform_submit_button', 'custom_dynamic_submit_button_with_label', 10, 2);
function custom_dynamic_submit_button_with_label($button, $form)
{
    // Extract the label text from the original <input> tag using regex
    preg_match('/value=[\'"]([^\'"]+)[\'"]/', $button, $matches);
    $button_text = isset($matches[1]) ? $matches[1] : 'Submit';
    // Build a custom <button> using the form ID
    $button_id = 'gform_submit_button_' . $form['id'];
    return "<button class='button cf-form-field-button' id='{$button_id}'><span>{$button_text}</span></button>";
}
/* ************** Gravity Form Submit button filter ************** */
/* ************** Product Category : Custom Taxonomy Terms ************** */
function get_product_category_custom_taxnomy_terms()
{
    ob_start();
    $select_product_page_link = get_field('select_product_page_link', get_the_ID());
    
    // Define el orden específico de IDs que quieres mostrar primero
    $priority_ids = array(58, 61, 62, 532); // Cambia estos IDs por los que necesites
    
    // Obtener términos con IDs específicos en orden
    $priority_terms = get_terms(
        array(
            'taxonomy' => 'product-category',
            'hide_empty' => false,
            'include' => $priority_ids,
            'orderby' => 'include',
            'parent' => 0,
        )
    );
    
    // Obtener todos los demás términos (excluyendo los prioritarios)
    $remaining_terms = get_terms(
        array(
            'taxonomy' => 'product-category',
            'hide_empty' => false,
            'exclude' => $priority_ids,
            'orderby' => 'name', // Orden alfabético para el resto
            'order' => 'ASC',
            'parent' => 0,
        )
    );
    
    // Combinar arrays: primero los prioritarios, después el resto
    $product_category_parent_terms = array_merge(
        is_array($priority_terms) ? $priority_terms : array(),
        is_array($remaining_terms) ? $remaining_terms : array()
    );
    
    if (!empty($product_category_parent_terms)):
        ?>
        <div class="product-category-terms">
            <?php foreach ($product_category_parent_terms as $parent_term): ?>
                <?php
                $term_id = $parent_term->term_id;
                $term_url = get_term_link($term_id);
                $term_name = $parent_term->name;
                $term_description = $parent_term->description;
                $pc_thumbnail_image = get_field('pc_thumbnail_image', 'product-category_' . $term_id);
                $child_terms = get_terms(
                    array(
                        'taxonomy' => 'product-category',
                        'hide_empty' => false,
                        'orderby' => 'name', 
                        'order' => 'ASC',
                        'parent' => $term_id,
                    ),
                );
                ?>
                <?php if (!empty($child_terms)): ?>
                    <div class="product-gradient-border">
                        <div class="product-category-card product-category-card-primary">
                            <div class="heading">
                                <div class="title">
                                    <h5>
                                        <?php echo $term_name; ?>
                                    </h5>
                                </div>
                                <?php if ($term_description): ?>
                                    <div class="desc">
                                        <?php echo apply_filters('the_content', $term_description); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="common-link-card-container">
                                <?php foreach ($child_terms as $child_term): ?>
                                    <?php
                                    $child_term_id = $child_term->term_id;
                                    $child_term_url = get_term_link($child_term_id);
                                    $child_term_name = $child_term->name;
                                    $pc_thumbnail_image = get_field('pc_thumbnail_image', 'product-category_' . $child_term_id);
                                    ?>
                                    <a href="<?php echo $child_term_url //echo $select_product_page_link; //echo $child_term_url; 
                                                            ?>" class="common-link-card common-link-card-primary">
                                        <div class="common-link-card-bg"
                                            style="background:url('<?php echo $pc_thumbnail_image['url']; ?>') no-repeat center; background-size: cover">
                                        </div>
                                        <div class="common-link-card-content">
                                            <div class="title">
                                                <h5>
                                                    <?php echo $child_term_name; ?>
                                                </h5>
                                            </div>
                                            <div class="arrow-btn-container">
                                                <div class="arrow-btn-link">
                                                    <figure>
                                                        <img src="/wp-content/themes/doga/assets/images/product-btn-link-icon.svg"
                                                            alt="arrow">
                                                    </figure>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="product-gradient-border">
                        <div class="product-category-card product-category-card-secondary">
                            <div class="heading">
                                <div class="title">
                                    <h5>
                                        <?php echo $term_name; ?>
                                    </h5>
                                </div>
                                <?php if ($term_description): ?>
                                    <div class="desc">
                                        <?php echo apply_filters('the_content', $term_description); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($pc_thumbnail_image): ?>
                                <a href="<?php echo $term_url;  //echo $select_product_page_link;   
                                                    ?>" class="common-link-card common-link-card-secondary">
                                    <div class="common-link-card-bg"
                                        style="background:url('<?php echo $pc_thumbnail_image['url']; ?>') no-repeat center; background-size: cover">
                                    </div>
                                    <div class="common-link-card-content">
                                        <div class="arrow-btn-container">
                                            <div class="arrow-btn-link">
                                                <figure>
                                                    <img src="/wp-content/themes/doga/assets/images/product-btn-link-icon.svg" alt="arrow">
                                                </figure>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
    endif;
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('product_category_custom_taxnomy_terms', 'get_product_category_custom_taxnomy_terms');
/* ************** Product Category : Custom Taxonomy Terms ************** */
/* ************** Chose Your product section - product page ************** */
function choose_your_product_and_sub_category()
{
    ob_start();
    $exclude_motor_term = array('motors');
    $product_category_parent_terms = get_terms(
        array(
            'taxonomy' => 'product-category',
            'hide_empty' => false,
            'parent' => 0,
        ),
    );
    $parent_terms_data = '';
    $child_terms_data = '';
    $pdp_choose_your_product_title = get_field('pdp_choose_your_product_title', 'options');
    $default_img = get_stylesheet_directory_uri() . '/assets/images/wiper-system-product-default-img.jpg';
    if (!empty($product_category_parent_terms)):
        ?>
        <div class="choose-your-product">
            <div class="hero-product d-none d-sm-block"
                style="background:url('/wp-content/themes/doga/assets/images/hero-product.png') no-repeat;">
            </div>
            <div class="hero-product d-block d-sm-none"
                style="background:url('/wp-content/themes/doga/assets/images/hero-product-sm.png') no-repeat;">
            </div>
            <div class="choose-your-product-bg">
                <figure>
                    <img src="/wp-content/themes/doga/assets/images/choose-your-product-bg.png" alt="choose-your-product-bg">
                </figure>
            </div>
            <div class="accordion" id="chooseYourProduct">
                <div class="choose-your-product-content">
                    <div class="heading" data-bs-toggle="collapse" data-bs-target="#collapseContent" aria-expanded="true"
                        aria-controls="collapseContent">
                        <div class="title">
                            <h5><?php echo ($pdp_choose_your_product_title) ? $pdp_choose_your_product_title : 'Choose your product'; ?>
                            </h5>
                        </div>
                    </div>
                    <div id="collapseContent" class="accordion-collapse collapse show" data-bs-parent="#chooseYourProduct">
                        <div class="choose-your-product-inner-content">
                            <div class="row-content">
                                <div class="row product-page-choose-your-product">
                                    <?php $child_terms_data_counter = 1; ?>
                                    <?php foreach ($product_category_parent_terms as $parent_term): ?>
                                        <?php
                                        $parent_term_id = $parent_term->term_id;
                                        $parent_term_url = get_term_link($parent_term_id);
                                        $parent_term_name = $parent_term->name;
                                        $parent_term_slug = $parent_term->slug;
                                        $pc_thumbnail_image = get_field('pc_thumbnail_image', 'product-category_' . $parent_term_id);
                                        $parent_term_thumbnail_img_url = ($pc_thumbnail_image) ? $pc_thumbnail_image['url'] : $default_img;
                                        $child_terms = get_terms(
                                            array(
                                                'taxonomy' => 'product-category',
                                                'hide_empty' => false,
                                                'parent' => $parent_term_id,
                                            ),
                                        );
                                        if (!empty($child_terms)):
                                            foreach ($child_terms as $child_term):
                                                $child_term_id = $child_term->term_id;
                                                $child_term_url = get_term_link($child_term_id);
                                                $child_term_name = $child_term->name;
                                                $child_term_slug = $child_term->slug;
                                                $child_term_thumbnail_image = get_field('pc_thumbnail_image', 'product-category_' . $child_term_id);
                                                $selected_child_term = ($child_terms_data_counter == 1) ? 'selected-product-category-term-filter' : '';
                                                $thumbnail_img_url = ($child_term_thumbnail_image) ? $child_term_thumbnail_image['url'] : $default_img;
                                                $child_terms_data .= '<div class="col-12 col-md-6">';
                                                $child_terms_data .= '<div class="your-product-card" data-term-id="' . $child_term_id . '" data-term-slug="' . $child_term_slug . '" >';
                                                if ($thumbnail_img_url):
                                                    $child_terms_data .= '<div class="product-card-bg" style="background:url(' . $thumbnail_img_url . ') no-repeat center; background-size: cover">';
                                                    $child_terms_data .= '</div>';
                                                endif;
                                                $child_terms_data .= '<div class="your-product-card-anchor" >';
                                                $child_terms_data .= '<div class="title">';
                                                $child_terms_data .= '<h6>' . esc_html($child_term_name) . '</h6>';
                                                $child_terms_data .= '</div>';
                                                $child_terms_data .= '<div class="btn-container">';
                                                $child_terms_data .= '<a href="' . $child_term_url . '" data-term-id="' . $child_term_id . '" data-term-slug="' . $child_term_slug . '" class="filter-btn ' . $selected_child_term . '"></a>';
                                                $child_terms_data .= '</div>';
                                                $child_terms_data .= '</div>';
                                                $child_terms_data .= '</div>';
                                                $child_terms_data .= '</div>';
                                                $child_terms_data_counter++;
                                            endforeach;
                                        endif;
                                        if (in_array($parent_term_slug, $exclude_motor_term)):
                                            continue;
                                        endif;
                                        /* Parent Terms Data  */
                                        $parent_terms_data .= '<div class="col-12 col-md-6">';
                                        $parent_terms_data .= '<div class="your-product-card" data-term-slug="' . $parent_term_slug . '" data-term-id="' . $parent_term_id . '" >';
                                        if ($parent_term_thumbnail_img_url):
                                            $parent_terms_data .= '<div class="product-card-bg" style="background:url(' . $parent_term_thumbnail_img_url . ') no-repeat center; background-size: cover">';
                                            $parent_terms_data .= '</div>';
                                        endif;
                                        $parent_terms_data .= '<div  class="your-product-card-anchor" >';
                                        $parent_terms_data .= '<div class="title">';
                                        $parent_terms_data .= '<h6>' . esc_html($parent_term_name) . '</h6>';
                                        $parent_terms_data .= '</div>';
                                        $parent_terms_data .= '<div class="btn-container">';
                                        $parent_terms_data .= '<a  href="' . $parent_term_url . '"  data-term-slug="' . $parent_term_slug . '" data-term-id="' . $parent_term_id . '" class="filter-btn"></a>';
                                        $parent_terms_data .= '</div>';
                                        $parent_terms_data .= '</div>';
                                        $parent_terms_data .= '</div>';
                                        $parent_terms_data .= '</div>';
                                        /* Parent Terms Data  */
                                        $terms_data_counter++;
                                        ?>
                                    <?php endforeach; ?>
                                    <?php echo $child_terms_data; ?>
                                    <?php echo $parent_terms_data; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="product-category-slider hide-product-sub-category-slider product-sub-category-slider">
            <div class="product-category-slider-content">
                <div class="swiper productCategorySlider" id="">
                    <div class="swiper-wrapper child-category-slider">
                        <!-- Slider Data -->
                    </div>
                </div>
            </div>
        </div>
        <?php
        wp_reset_postdata();
    endif;
    return ob_get_clean();
}
add_shortcode("product_page_choose_your_product_and_sub_category", "choose_your_product_and_sub_category");
/* ************** Chose Your product section - product page ************** */
/* ************** Filter & Product Listing - product page ************** */
function product_listing_with_filter()
{
    $pdp_filter_title = get_field('pdp_filter_title', 'options');
    $pdp_search_name_title = get_field('pdp_search_name_title', 'options');
    $speed_filter_title = get_field('speed_filter_title', 'options');
    $speed_filter_data = get_field('speed_filter_data', 'options');
    $torque_filter_title = get_field('torque_filter_title', 'options');
    $torque_filter_data = get_field('torque_filter_data', 'options');
    $voltage_filter_title = get_field('voltage_filter_title', 'options');
    $voltage_filter_data = get_field('voltage_filter_data', 'options');
    $weight_filter_title = get_field('weight_filter_title', 'options');
    $weight_filter_data = get_field('weight_filter_data', 'options');
    $pdp_see_product_title = get_field('pdp_see_product_title', 'options');
    $pdp_speed_title = get_field('pdp_speed_title', 'options');
    $pdp_torque_title = get_field('pdp_torque_title', 'options');
    $pdp_voltage_title = get_field('pdp_voltage_title', 'options');
    $pdp_weight_title = get_field('pdp_weight_title', 'options');
    $pdp_nominal_torque_title = get_field('pdp_nominal_torque_title', 'options');
    $posts_per_page = get_field('posts_per_page', get_the_ID());
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $query_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'order' => 'ASC',
        'orderby' => 'title',
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
        'tax_query' => [
            'relation' => 'OR',
            [
                'taxonomy' => 'product-market-sector',
                'field' => 'slug',
                'terms' => 'drive-system',
            ],
        ],
    );
    $products_query = new WP_Query($query_args);
    ob_start();
    if ($products_query->have_posts()):
        ?>
        <div class="product-listing-with-filter">
            <?php if ($speed_filter_data || $torque_filter_data || $voltage_filter_data || $weight_filter_data || $pdp_search_name_title): ?>
                <div class="filter-wrapper">
                    <?php if ($pdp_filter_title): ?>
                        <div class="heading">
                            <h5>
                                <?php echo $pdp_filter_title; ?>
                            </h5>
                        </div>
                    <?php endif; ?>
                    <div class="filter-container">
                        <div class="filter-left-block">
                            <div class="accordion" id="filterAcco">
                                <div class="filter-button-container products-meta-data-filter">
                                    <!-- Speed Filter Start -->
                                    <?php if (!empty($speed_filter_data)): ?>
                                        <div class="filter-button">
                                            <?php if ($speed_filter_title): ?>
                                                <h6 class="title collapsed" data-bs-toggle="collapse" data-bs-target="#collapseOne"
                                                    aria-expanded="false" aria-controls="collapseOne">
                                                    <?php echo $speed_filter_title; ?>
                                                </h6>
                                            <?php endif; ?>
                                            <div id="collapseOne" class="accordion-collapse collapse speed-filter"
                                                data-bs-parent="#filterAcco">
                                                <div class="accordion-body">
                                                    <div class="filter-item-parent product-speed-meta-data">
                                                        <?php foreach ($speed_filter_data as $speed_filter_data_item): ?>
                                                            <?php
                                                            $speed_filter_value = $speed_filter_data_item['value'];
                                                            $speed_filter_title = $speed_filter_data_item['label'];
                                                            ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="speed-filter-<?php echo $speed_filter_value; ?>"
                                                                    value="<?php echo $speed_filter_value; ?>">
                                                                <label class="form-check-label"
                                                                    for="speed-filter-<?php echo $speed_filter_value; ?>"
                                                                    value="<?php echo $speed_filter_value; ?>"><?php echo $speed_filter_title; ?></label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Speed Filter End -->
                                    <!-- Torque Filter Start -->
                                    <?php if (!empty($torque_filter_data)): ?>
                                        <div class="filter-button">
                                            <?php if ($torque_filter_title): ?>
                                                <h6 class="title collapsed" data-bs-toggle="collapse" data-bs-target="#collapseTwo"
                                                    aria-expanded="false" aria-controls="collapseTwo">
                                                    <?php echo $torque_filter_title; ?>
                                                </h6>
                                            <?php endif; ?>
                                            <div id="collapseTwo" class="accordion-collapse collapse torque-fitler"
                                                data-bs-parent="#filterAcco">
                                                <div class="accordion-body">
                                                    <div class="filter-item-parent product-torque-meta-data">
                                                        <?php foreach ($torque_filter_data as $torque_filter_item): ?>
                                                            <?php
                                                            $torque_filter_title = $torque_filter_item['label'];
                                                            $torque_filter_value = $torque_filter_item['value'];
                                                            ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input"
                                                                    id="torque-filter<?php echo $torque_filter_value; ?>" type="checkbox"
                                                                    value="<?php echo $torque_filter_value; ?>">
                                                                <label class="form-check-label"
                                                                    for="torque-filter<?php echo $torque_filter_value; ?>">
                                                                    <?php echo $torque_filter_title; ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Torque Filter End -->
                                    <!-- Voltage Filter Sart -->
                                    <?php if (!empty($voltage_filter_title)): ?>
                                        <div class="filter-button">
                                            <?php if ($voltage_filter_title): ?>
                                                <h6 class="title collapsed" data-bs-toggle="collapse" data-bs-target="#collapseThree"
                                                    aria-expanded="false" aria-controls="collapseThree">
                                                    <?php echo $voltage_filter_title; ?>
                                                </h6>
                                            <?php endif; ?>
                                            <div id="collapseThree" class="accordion-collapse collapse voltage-filter"
                                                data-bs-parent="#filterAcco">
                                                <div class="accordion-body">
                                                    <div class="filter-item-parent product-voltage-meta-data">
                                                        <?php foreach ($voltage_filter_data as $voltage_filter_data_item): ?>
                                                            <?php
                                                            $voltage_filter_title = $voltage_filter_data_item['label'];
                                                            $voltage_filter_vlaue = $voltage_filter_data_item['value'];
                                                            ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input"
                                                                    id="voltage-data-<?php echo $voltage_filter_vlaue; ?>" type="checkbox"
                                                                    value="<?php echo $voltage_filter_vlaue; ?>">
                                                                <label class="form-check-label"
                                                                    for="voltage-data-<?php echo $voltage_filter_vlaue; ?>">
                                                                    <?php echo $voltage_filter_title; ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Voltage Filter End -->
                                    <!-- Weight Filter Start -->
                                    <?php if (!empty($weight_filter_data)): ?>
                                        <div class="filter-button">
                                            <?php if ($weight_filter_title): ?>
                                                <h6 class="title collapsed" data-bs-toggle="collapse" data-bs-target="#collapseFour"
                                                    aria-expanded="false" aria-controls="collapseFour">
                                                    <?php echo $weight_filter_title; ?>
                                                </h6>
                                            <?php endif; ?>
                                            <div id="collapseFour" class="accordion-collapse collapse weight-filter"
                                                data-bs-parent="#filterAcco">
                                                <div class="accordion-body">
                                                    <div class="filter-item-parent product-weight-meta-data">
                                                        <?php foreach ($weight_filter_data as $weight_filter_data_item): ?>
                                                            <?php
                                                            $weight_filter_title = $weight_filter_data_item['label'];
                                                            $weight_filter_vlaue = $weight_filter_data_item['value'];
                                                            ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input"
                                                                    id="weight-filter-<?php echo $weight_filter_vlaue; ?>" type="checkbox"
                                                                    value="<?php echo $weight_filter_vlaue; ?>">
                                                                <label class="form-check-label"
                                                                    for="weight-filter-<?php echo $weight_filter_vlaue; ?>">
                                                                    <?php echo $weight_filter_title; ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Weight Filter End -->
                                </div>
                            </div>
                        </div>
                        <div class="filter-right-block">
                            <div class="search-container product-serch-block">
                                <input type="text"
                                    placeholder="<?php echo ($pdp_search_name_title) ? $pdp_search_name_title : 'Search by name or code'; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="products-wrapper drive-system-product-listing-grid">
                <?php while ($products_query->have_posts()):
                    $products_query->the_post(); ?>
                    <?php
                    $post_id = get_the_ID();
                    $post_url = get_the_permalink($post_id);
                    $post_title = get_the_title();
                    $pd_product_slider_images = get_field('pd_product_slider_images', $post_id);
                    $pd_minimum_speed = get_field('pd_minimum_speed', $post_id);
                    $pd_maximum_speed = get_field('pd_maximum_speed', $post_id);
                    $categories = get_the_terms(get_the_ID(), 'product-category');
                    $pd_torque = get_field('pd_torque', $post_id);
                    $pd_weight = get_field('pd_weight', $post_id);
                    $pd_voltage_details = get_field('pd_voltage_details', $post_id);
                    $pd_minimum_nominal_torque = get_field('pd_minimum_nominal_torque', $post_id);
                    $pd_maximum_nominal_torque = get_field('pd_maximum_nominal_torque', $post_id);
                    $product_attributes = wp_get_post_terms($post_id, 'product-attribute');
                    ?>
                    <div class="product-row">
                        <?php if (!empty($pd_product_slider_images)): ?>
                            <div class="product-image-slider">
                                <div class="swiper product-listing-slider">
                                    <div class="swiper-wrapper">
                                        <?php foreach ($pd_product_slider_images as $product_slide_img): ?>
                                            <?php
                                            $pdi_image = $product_slide_img['pdi_image'];
                                            if ($pdi_image):
                                                ?>
                                                <div class="swiper-slide">
                                                    <div class="product-slider-card">
                                                        <figure>
                                                            <img src="<?php echo $pdi_image['url']; ?>"
                                                                alt="<?php echo $pdi_image['alt']; ?>">
                                                        </figure>
                                                    </div>
                                                </div>
                                                <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </div>
                                    <div class="swiper-btn-container">
                                        <div class="swiper-button-prev p-image-slider-prev">
                                            <figure>
                                                <img src="/wp-content/themes/doga/assets/images/wiper-slider-prev-arrow.svg"
                                                    alt="slider-prev-arrow">
                                            </figure>
                                        </div>
                                        <div class="swiper-button-next p-image-slider-next">
                                            <figure>
                                                <img src="/wp-content/themes/doga/assets/images/wiper-slider-next-arrow.svg"
                                                    alt="slider-next-arrow">
                                            </figure>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="product-info-card">
                            <div class="heading">
                                <h4>
                                    <a href="<?php echo $post_url; ?>">
                                        <?php echo $post_title; ?>
                                        <?php echo $categories; ?>
                                    </a>
                                </h4>
                            </div>
                            <?php if ($pd_minimum_speed || $pd_maximum_speed || $pd_torque || $pd_weight || !empty($pd_voltage_details) || $pd_minimum_nominal_torque || $pd_maximum_nominal_torque): ?>
                                <div class="product-info-main">
                                    <?php if ($pd_minimum_speed || $pd_maximum_speed): ?>
                                        <div class="product-detail">
                                            <?php if ($pdp_speed_title): ?>
                                                <div class="title">
                                                    <h6>
                                                        <?php echo $pdp_speed_title; ?>
                                                    </h6>
                                                </div>
                                            <?php endif; ?>
                                            <div class="desc">
                                                <?php echo ($pd_minimum_speed) ? apply_filters('the_content', $pd_minimum_speed) : ''; ?>
                                                <span>-</span>
                                                <?php echo ($pd_maximum_speed) ? apply_filters('the_content', $pd_maximum_speed) : ''; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($pd_torque): ?>
                                        <div class="product-detail">
                                            <?php if ($pdp_torque_title): ?>
                                                <div class="title">
                                                    <h6>
                                                        <?php echo $pdp_torque_title; ?>
                                                    </h6>
                                                </div>
                                            <?php endif; ?>
                                            <div class="desc">
                                                <span>
                                                    < </span>
                                                        <?php echo apply_filters('the_content', $pd_torque); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($pd_voltage_details)): ?>
                                        <div class="product-detail">
                                            <?php if ($pdp_voltage_title): ?>
                                                <div class="title">
                                                    <h6>
                                                        <?php echo $pdp_voltage_title; ?>
                                                    </h6>
                                                </div>
                                            <?php endif; ?>
                                            <div class="desc voltage-desc">
                                                <?php foreach ($pd_voltage_details as $voltage_data): ?>
                                                    <?php
                                                    $voltage = $voltage_data['voltage'];
                                                    if ($voltage):
                                                        ?>
                                                        <?php echo apply_filters('the_content', $voltage); ?>
                                                        <span>/</span>
                                                        <?php
                                                    endif;
                                                endforeach;
                                                ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($pd_weight): ?>
                                        <div class="product-detail">
                                            <?php if ($pdp_weight_title): ?>
                                                <div class="title">
                                                    <h6>
                                                        <?php echo $pdp_weight_title; ?>
                                                    </h6>
                                                </div>
                                            <?php endif; ?>
                                            <div class="desc">
                                                <?php echo apply_filters('the_content', $pd_weight); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($pd_minimum_nominal_torque || $pd_maximum_nominal_torque): ?>
                                        <div class="product-detail">
                                            <?php if ($pdp_nominal_torque_title): ?>
                                                <div class="title">
                                                    <h6>
                                                        <?php echo $pdp_nominal_torque_title; ?>
                                                    </h6>
                                                </div>
                                            <?php endif; ?>
                                            <div class="desc">
                                                <?php echo ($pd_minimum_nominal_torque) ? $pd_minimum_nominal_torque : ''; ?>
                                                <span>-</span>
                                                <?php echo ($pd_maximum_nominal_torque) ? $pd_maximum_nominal_torque : ''; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($product_attributes) || $post_url): ?>
                                <div class="product-info-footer">
                                    <?php if (!empty($product_attributes)): ?>
                                        <div class="tags-container">
                                            <?php foreach ($product_attributes as $product_attribute): ?>
                                                <?php
                                                $term_name = $product_attribute->name;
                                                ?>
                                                <div class="tag">
                                                    <?php echo apply_filters('the_content', $term_name); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="btn-container">
                                        <div class="see-product-btn">
                                            <a href="<?php echo $post_url; ?>">
                                                <?php echo $pdp_see_product_title; ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="product-custom-pagination products-page">
                <?php
                $total_pages = $products_query->max_num_pages;
                $current_page = max(1, $paged);
                if ($total_pages > 1):
                    $prev_disabled = ($current_page <= 1) ? 'disabled' : '';
                    $next_disabled = ($current_page >= $total_pages) ? 'disabled' : '';
                    $prev_page = $current_page - 1;
                    $next_page = $current_page + 1;
                    $pagination_links = paginate_links(array(
                        'total' => $total_pages,
                        'current' => $current_page,
                        'format' => 'page/%#%',
                        'type' => 'array', // returns an array of links
                        'show_all' => false,
                        'mid_size' => 2,
                        'end_size' => 1,
                        'prev_next' => false, // we're handling arrows manually
                    ));
                    echo '<div class="custom-pagination">';
                    // Previous arrow
                    echo '<a class="pagination-prev ' . $prev_disabled . '" href="' . ($prev_disabled ? '#' : esc_url(get_pagenum_link($prev_page))) . '">';
                    echo '<img src="' . get_stylesheet_directory_uri() . '/assets/images/prev-arrow.svg" alt="Previous">';
                    echo '</a>';
                    // Number links
                    if (is_array($pagination_links)) {
                        foreach ($pagination_links as $link) {
                            echo '<div class="pagination-number">' . $link . '</div>';
                        }
                    }
                    // Next arrow
                    echo '<a class="pagination-next ' . $next_disabled . '" href="' . ($next_disabled ? '#' : esc_url(get_pagenum_link($next_page))) . '">';
                    echo '<img src="' . get_stylesheet_directory_uri() . '/assets/images/next-arrow.svg" alt="Next">';
                    echo '</a>';
                    echo '</div>';
                endif;
                ?>
            </div>
        </div>
        <?php
        wp_reset_postdata();
    endif;
    return ob_get_clean();
}
add_shortcode("product_listing_with_filter_shortcode", "product_listing_with_filter");
/* ************** Filter & Product Listing - product page ************** */
/* ************** Wiper System Market Single Page Product Icon List Detail Shortcode ************** */
function get_wiper_market_detail()
{
    ob_start();
    $get_current_market_term_object = get_queried_object();
    $get_current_market_term_id = $get_current_market_term_object->term_id;
    $md_vehicles_with_advanced_technology_detail = get_field('md_vehicles_with_advanced_technology_detail', 'market-sector_' . $get_current_market_term_id);
    if (!empty($md_vehicles_with_advanced_technology_detail)):
        ?>
        <div class="wiper-market-detail">
            <div class="row">
                <?php foreach ($md_vehicles_with_advanced_technology_detail as $data): ?>
                    <?php
                    $md_vatd_icon = $data['md_vatd_icon'];
                    $md_vatd_title = $data['md_vatd_title'];
                    $md_vatd_short_description = $data['md_vatd_short_description'];
                    ?>
                    <?php if ($md_vatd_title || $md_vatd_short_description): ?>
                        <div class="col-6 col-lg-3">
                            <div class="market-detail-card">
                                <?php if ($md_vatd_icon): ?>
                                    <div class="image-content">
                                        <div class="img-container">
                                            <figure>
                                                <img src="<?php echo $md_vatd_icon['url']; ?>" alt="<?php echo $md_vatd_icon['alt']; ?>">
                                            </figure>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($md_vatd_title): ?>
                                    <div class="title">
                                        <h5>
                                            <?php echo $md_vatd_title; ?>
                                        </h5>
                                    </div>
                                <?php endif; ?>
                                <?php if ($md_vatd_short_description): ?>
                                    <div class="desc">
                                        <p>
                                            <?php echo $md_vatd_short_description; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <script>
            jQuery(document).ready(function ($) {
                $('.wiper-market-vehicles-section').addClass('show');
            });
        </script>
        <?php
    endif;
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode("wiper_market_single_post_icon_list_details", "get_wiper_market_detail");
/* ************** Wiper System Market Single Page Product Icon List Detail Shortcode ************** */
/* ************** Wiper System Market Single Page Our System Shortcode ************** */
function get_wiper_market_our_systems_products()
{
    ob_start();
    $get_current_market_term_object = get_queried_object();
    $get_current_market_term_id = $get_current_market_term_object->term_id;
    $default_img = get_stylesheet_directory_uri() . '/assets/images/wiper-system-product-default-img.jpg';
    $query_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'ASC',
        'orderby' => 'title',
        'tax_query' => [
            [
                'taxonomy' => 'market-sector',
                'field' => 'term-id',
                'terms' => $get_current_market_term_id,
            ],
        ],
    );
    $query = new WP_Query($query_args);
    if ($query->have_posts()):
        ?>
        <div class="wiper-market-our-systems-products">
            <div class="row">
                <?php $query_data_counter = 1; ?>
                <?php while ($query->have_posts()):
                    $query->the_post(); ?>
                    <?php
                    $post_ID = get_the_ID();
                    $post_url = get_the_permalink($post_ID);
                    $post_title = get_the_title($post_ID);
                    $pd_product_slider_images = get_field('pd_product_slider_images', $post_ID);
                    $post_excerpt = get_the_excerpt($post_ID);
                    $post_content = get_the_content($post_ID);
                    ?>
                    <div class="col-12 col-lg-6">
                        <div class="m-our-systems-products-card">
                            <?php if ((!empty($pd_product_slider_images)) || $default_img): ?>
                                <div class="slider-container">
                                    <div class="swiper" id="ourSystemsSlider">
                                        <div class="swiper-wrapper">
                                            <?php if (!empty($pd_product_slider_images)): ?>
                                                <?php foreach ($pd_product_slider_images as $product_image): ?>
                                                    <?php $pdi_image = $product_image['pdi_image']; ?>
                                                    <?php if ($pdi_image): ?>
                                                        <div class="swiper-slide">
                                                            <div class="image-content">
                                                                <figure>
                                                                    <?php
                                                                    $url = $pdi_image['url'];
                                                                    $alt = $pdi_image['alt'];
                                                                    $ext = pathinfo($url, PATHINFO_EXTENSION);
                                                                    if (strtolower($ext) === 'mp4'): ?>
                                                                        <video src="<?php echo $url; ?>" autoplay muted loop playsinline
                                                                            style="width:100%;height:auto;display:block;"></video>
                                                                    <?php else: ?>
                                                                        <img src="<?php echo $url; ?>" alt="<?php echo $alt; ?>">
                                                                    <?php endif; ?>
                                                                </figure>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="swiper-slide">
                                                    <div class="image-content">
                                                        <figure>
                                                            <img src="<?php echo $default_img; ?>" alt="<?php echo $post_title; ?>">
                                                        </figure>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="swiper-btn-container">
                                            <div class="swiper-button-prev m-our-systems-prev">
                                                <figure>
                                                    <img src="/wp-content/themes/doga/assets/images/wiper-slider-prev-arrow.svg"
                                                        alt="arrow-icon">
                                                </figure>
                                            </div>
                                            <div class="swiper-button-next m-our-systems-next">
                                                <figure>
                                                    <img src="/wp-content/themes/doga/assets/images/wiper-slider-next-arrow.svg"
                                                        alt="arrow-icon">
                                                </figure>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="m-our-systems-details">
                                <div class="heading">
                                    <div class="title">
                                        <h5>
                                            <a href="<?php echo $post_url; ?>">
                                                <?php echo $post_title; ?>
                                            </a>
                                        </h5>
                                    </div>
                                    <?php if (trim(get_the_content($post_ID)) != ''): ?>
                                        <div class="desc">
                                            <p>
                                                <?php echo $post_content; ?>
                                            </p>
                                        </div>
                                    <?php else: ?>
                                        <div class="desc">
                                            <p>
                                                &nbsp;
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="btn-container">
                                    <div class="see-product-btn">
                                        <a href="<?php echo $post_url; ?>">See Product</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $query_data_counter++; ?>
                <?php endwhile; ?>
            </div>
        </div>
        <script>
            jQuery(document).ready(function ($) {
                $('.our-system-block').addClass('show');
            });
        </script>
        <?php
        wp_reset_postdata();
    endif;
    return ob_get_clean();
}
add_shortcode("wiper_system_market_our_system_products", "get_wiper_market_our_systems_products");
/* ************** Wiper System Market Single Page Our System Shortcode ************** */
/* ************** Plastic System Division Product Line Terms Shortcode ************** */
function get_plastic_system_division_terms()
{
    ob_start();
    $plastic_system_terms = get_terms(
        array(
            'taxonomy' => 'product-line',
            'hide_empty' => false,
            'order' => 'DESC',
            'parent' => 0
        ),
    );
    $default_img = '/wp-content/themes/doga/assets/images/product-line-default-img.png';
    $plastic_system_terms = array_filter($plastic_system_terms, function ($term) {
        return $term->slug !== 'washer-tanks';
    });
    if (!empty($plastic_system_terms)):
        ?>
        <!-- Product Lines Block Start -->
        <div class="product-lines-block">
            <div class="common-product-line-content">
                <div class="row">
                    <?php
                    foreach ($plastic_system_terms as $plastic_system_term):
                        $term_id = $plastic_system_term->term_id;
                        $term_name = $plastic_system_term->name;
                        $term_url = get_term_link($term_id);
                        $thumbnail_image = get_field('pl_thumbnail_image', 'product-line_' . $term_id);
                        ?>
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="common-product-line-card">
                                <div class="common-product-line-bg"
                                    style="background:url('<?php echo ($thumbnail_image) ? $thumbnail_image['url'] : $default_img; ?>') no-repeat center; background-size: cover">
                                </div>
                                <a href="<?php echo $term_url; ?>" class="common-product-line-link">
                                    <div class="d-flex align-items-center justify-content-between w-100">
                                        <div class="title">
                                            <h6>
                                                <?php echo $term_name; ?>
                                            </h6>
                                        </div>
                                        <div class="arrow-container">
                                            <figure>
                                                <img src="/wp-content/themes/doga/assets/images/market-grid-arrow.svg"
                                                    alt="arrow-icon">
                                            </figure>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <?php
                    endforeach;
                    ?>
                </div>
            </div>
        </div>
        <!-- Product Lines Block End -->
        <?php
    endif;
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode("plastic_system_division_terms", "get_plastic_system_division_terms");
/* ************** Plastic System Division Product Line Terms Shortcode ************** */
/* ************** Washer System Page Types of Pumps Shortcode Filter Shortcode ************** */
function get_washer_system_types_of_pumps()
{
    ob_start();
    $wsd_more_information_title = get_field('wsd_more_information_title', 'options');
    $current_term_object = get_queried_object();
    $term_id = $current_term_object->term_id;
    $default_img = '/wp-content/themes/doga/assets/images/product-line-default-img.png';
    $washer_system_child_terms = get_terms(
        array(
            'taxonomy' => 'product-line',
            'hide_empty' => false,
            'parent' => $term_id,
        ),
    );
    $query_args = array(
        'post_type' => 'product',
        'posts_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'ASC',
        'orderby' => 'title',
        'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => 'product-line',
                'filed' => 'term_id',
                'terms' => 84,
            ],
        ],
    );
    $query = new WP_Query($query_args);
    if (!empty($washer_system_child_terms)):
        ?>
        <div class="washer-system-pumps">
            <!-- Washer System Pumps Slider Start -->
            <div class="washer-system-pumps-slider">
                <div class="swiper washer-pump-slider ">
                    <div class="swiper-wrapper">
                        <?php $data_counter = 1; ?>
                        <?php foreach ($washer_system_child_terms as $child_term): ?>
                            <?php
                            $term_id = $child_term->term_id;
                            $term_name = $child_term->name;
                            $thumbnail_image = get_field('pl_thumbnail_image', 'product-line_' . $term_id);
                            ?>
                            <div class="swiper-slide">
                                <div class="washer-system-link-card washer-types-of-pumps-card">
                                    <div class="washer-link-card-bg"
                                        style="background:url('<?php echo ($thumbnail_image) ? $thumbnail_image['url'] : $default_img; ?>') no-repeat center; background-size: cover">
                                    </div>
                                    <a class="washer-system-pump-link <?php echo ($data_counter == 1) ? 'active' : ''; ?>"
                                        data-term-id="<?php echo $term_id; ?>" href="javascript:;">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <div class="title">
                                                <p>
                                                    <?php echo $term_name; ?>
                                                </p>
                                            </div>
                                            <div class="arrow-container">
                                                <figure>
                                                    <img src="/wp-content/themes/doga/assets/images/market-grid-arrow.svg"
                                                        alt="arrow-icon">
                                                </figure>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <?php $data_counter++; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- Washer System Pumps Slider End -->
            <!-- Washer System Product Cards Start -->
            <div class="washer-system-product-cards">
                <div class="washer-system-product-cards-block">
                    <div class="common-product-card-content">
                        <div class="row  washer-system-types-of-pumps">
                            <?php while ($query->have_posts()):
                                $query->the_post(); ?>
                                <?php
                                $post_id = get_the_ID();
                                $post_title = get_the_title();
                                $post_url = get_permalink($post_id);
                                $thumbnail_image = get_the_post_thumbnail_url($post_id) ? get_the_post_thumbnail_url($post_id) : '/wp-content/themes/doga/assets/images/washer-pump-thumbnail-img.png';
                                $washer_system_product_shrot_description = get_field('washer_system_product_shrot_description');
                                ?>
                                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                                    <div class="common-product-card">
                                        <div class="image-content">
                                            <div class="img-container">
                                                <figure>
                                                    <img src="<?php echo $thumbnail_image; ?>" alt="<?php echo $post_title; ?>">
                                                </figure>
                                            </div>
                                        </div>
                                        <div class="text-content">
                                            <div class="product-detail">
                                                <div class="product-name">
                                                    <h6>
                                                        <?php echo $post_title; ?>
                                                    </h6>
                                                </div>
                                                <?php if ($washer_system_product_shrot_description): ?>
                                                    <div class="detail">
                                                        <div class="desc descriptoin">
                                                            <?php
                                                            echo $washer_system_product_shrot_description;
                                                            ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="btn">
                                                <a href="<?php echo($post_url); ?>" class="more-info-btn">
                                                    <?php echo ($wsd_more_information_title) ? $wsd_more_information_title : 'More Information'; ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Washer System Product Cards End -->
        </div>
        <?php
    endif;
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode("washer_system_types_of_pumps", "get_washer_system_types_of_pumps");
/* ************** Washer System Page Types of Pumps Shortcode Filter Shortcode ************** */
/* ************** Washer System Product Line Select Types of Liters Pumps Shortcode ************** */
function get_types_of_washer_system_pumps()
{
    ob_start();
    $wsd_select_liters_capacity_title = get_field('wsd_select_liters_capacity_title', 'options');
    $wsd_washer_tank_system_liters_details = get_field('wsd_washer_tank_system_liters_details', 'options');
    $washer_system_product_liter_suffix_text = get_field('washer_system_product_liter_suffix_text', 'options');
    $wsd_ability_title = get_field('wsd_ability_title', 'options');
    $wsd_type_of_plug_title = get_field('wsd_type_of_plug_title', 'options');
    $wsd_number_of_bombs_title = get_field('wsd_number_of_bombs_title', 'options');
    $wsd_level_sensor_title = get_field('wsd_level_sensor_title', 'options');
    $wsd_more_information_title = get_field('wsd_more_information_title', 'options');
    $pdp_voltage_title = get_field('pdp_voltage_title', 'options');
    $washer_system_category_object = get_queried_object();
    $current_term_id = $washer_system_category_object->term_id;
    $query_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'ASC',
        'orderby' => 'title',
        'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => 'product-line',
                'field' => 'term_id',
                'terms' => 122,
            ],
        ],
    );
    $query = new WP_Query($query_args);
    if ($query->have_posts()):
        ?>
        <div class="washer-tank-capacity-parent">
            <div class="washer-tank-heading">
                <?php if ($wsd_select_liters_capacity_title): ?>
                    <div class="title">
                        <h5>
                            <?php echo $wsd_select_liters_capacity_title; ?>
                        </h5>
                    </div>
                <?php endif; ?>
                <div class="select-washer-tank">
                    <select name="select-washer-tank" id="select-washer-tank" class="washer-select-filter">
                        <?php foreach ($wsd_washer_tank_system_liters_details as $wsd_washer_tank_system_liters_detail): ?>
                            <?php
                            $label = $wsd_washer_tank_system_liters_detail['label'];
                            $value = $wsd_washer_tank_system_liters_detail['value'];
                            ?>
                            <option value="<?php echo $value; ?>">
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="products-wrapper wash-tank-select-leters-filter-listing-grid">
                <?php while ($query->have_posts()):
                    $query->the_post(); ?>
                    <?php
                    $post_id = get_the_ID();
                    $post_url = get_the_permalink($post_id);
                    $post_title = get_the_title();
                    $pd_product_slider_images = get_field('pd_product_slider_images', $post_id);
                    $pd_voltage_details = get_field('pd_voltage_details', $post_id);
                    $espd_ability = get_field('espd_ability', $post_id);
                    $espd_type_of_plug = get_field('espd_type_of_plug', $post_id);
                    $espd_number_of_bombs = get_field('espd_number_of_bombs', $post_id);
                    $espd_level_sensor = get_field('espd_level_sensor', $post_id);
                    $product_attributes = wp_get_post_terms($post_id, 'product-attribute');
                    $diameter_cap = get_field('diameter_cap', $post_id);
                    ?>
                    <div class="product-row">
                        <?php if (!empty($pd_product_slider_images)): ?>
                            <div class="product-image-slider">
                                <div class="swiper product-listing-slider">
                                    <div class="swiper-wrapper">
                                        <?php foreach ($pd_product_slider_images as $product_slide_img): ?>
                                            <?php
                                            $pdi_image = $product_slide_img['pdi_image'];
                                            if ($pdi_image):
                                                ?>
                                                <div class="swiper-slide">
                                                    <div class="product-slider-card">
                                                        <figure>
                                                            <img src="<?php echo $pdi_image['url']; ?>"
                                                                alt="<?php echo $pdi_image['alt']; ?>">
                                                        </figure>
                                                    </div>
                                                </div>
                                                <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </div>
                                    <div class="swiper-btn-container">
                                        <div class="swiper-button-prev p-image-slider-prev">
                                            <figure>
                                                <img src="/wp-content/themes/doga/assets/images/wiper-slider-prev-arrow.svg"
                                                    alt="slider-prev-arrow">
                                            </figure>
                                        </div>
                                        <div class="swiper-button-next p-image-slider-next">
                                            <figure>
                                                <img src="/wp-content/themes/doga/assets/images/wiper-slider-next-arrow.svg"
                                                    alt="slider-next-arrow">
                                            </figure>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="product-info-card">
                            <div class="heading">
                                <h4>
                                    <a href="<?php echo $post_url; ?>">
                                        <?php echo $post_title; ?>
                                    </a>
                                </h4>
                            </div>
                            <div class="product-info-main">
                                <?php if ($espd_type_of_plug): ?>
                                    <div class="product-detail">
                                        <?php if ($wsd_type_of_plug_title): ?>
                                            <div class="title">
                                                <h6>
                                                    <?php echo $wsd_type_of_plug_title; ?>
                                                </h6>
                                            </div>
                                        <?php endif; ?>
                                        <div class="desc">
                                            <?php echo apply_filters('the_content', $espd_type_of_plug); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($diameter_cap): ?>
                                    <div class="product-detail">

                                            <div class="title">
                                                <h6>
                                                    Diameter cap
                                                </h6>
                                            </div>
                                        <div class="desc">
                                            <?php echo apply_filters('the_content', $diameter_cap); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($espd_number_of_bombs): ?>
                                    <div class="product-detail">
                                        <?php if ($wsd_number_of_bombs_title): ?>
                                            <div class="title">
                                                <h6>
                                                    <?php echo $wsd_number_of_bombs_title; ?>
                                                </h6>
                                            </div>
                                        <?php endif; ?>
                                        <div class="desc">
                                            <?php echo apply_filters('the_content', $espd_number_of_bombs); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($espd_level_sensor): ?>
                                    <div class="product-detail">
                                        <?php if ($wsd_level_sensor_title): ?>
                                            <div class="title">
                                                <h6>
                                                    <?php echo $wsd_level_sensor_title; ?>
                                                </h6>
                                            </div>
                                        <?php endif; ?>
                                        <div class="desc">
                                            <?php echo apply_filters('the_content', $espd_level_sensor); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($pd_voltage_details)): ?>
                                    <div class="product-detail">
                                        <?php if ($pdp_voltage_title): ?>
                                            <div class="title">
                                                <h6>
                                                    <?php echo $pdp_voltage_title; ?>
                                                </h6>
                                            </div>
                                        <?php endif; ?>
                                        <div class="desc">
                                            <?php
                                            $count = count($pd_voltage_details);
                                            $i = 0;
                                            foreach ($pd_voltage_details as $voltage_data):
                                                $voltage = $voltage_data['voltage'];
                                                if ($voltage):
                                                    echo apply_filters('the_content', $voltage);
                                                    if (++$i < $count) {
                                                        echo '<span>/</span>';
                                                    }
                                                endif;
                                            endforeach;
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php
                            $post_id = get_the_ID();
                            $product_attributes = get_the_terms($post_id, 'product-attribute');
                            // Asegúrate de que $post_url esté definido antes, ej:
                            // $post_url = get_field( 'post_url', $post_id );
                            ?>
                            <?php if (
                                (!empty($product_attributes) && !is_wp_error($product_attributes))
                                || !empty($post_url)
                            ): ?>
                                <div class="product-info-footer">
                                    <!-- Solo mostramos tags si existen -->
                                    <?php if (!empty($product_attributes) && !is_wp_error($product_attributes)): ?>
                                        <div class="tags-container">
                                            <?php foreach ($product_attributes as $product_attribute): ?>
                                                <div class="tag">
                                                    <?php
                                                    // Escapamos nombre y aplicamos filtros
                                                    echo apply_filters('the_content', esc_html($product_attribute->name));
                                                    ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Solo mostramos el botón si hay URL definida -->
                                    <?php if (!empty($post_url)): ?>
                                        <div class="btn-container">
                                            <div class="see-product-btn">
                                                <a href="<?php echo esc_url($post_url); ?>">
                                                    <?php echo esc_html($wsd_more_information_title); ?>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php
        wp_reset_postdata();
    endif;
    return ob_get_clean();
}
add_shortcode("types_of_washer_system_pumps", "get_types_of_washer_system_pumps");
/* ************** Washer System Product Line Select Types of Liters Pumps Shortcode ************** */
/* ************** Types Of Tanks Filter Shortcode ************** */
function get_types_of_tanks()
{
    $wsd_more_information_title = get_field('wsd_more_information_title', 'options');
    $default_img = get_stylesheet_directory_uri() . '/assets/images/product-line-default-img.png';
    $current_term_object = get_queried_object();
    $term_id = $current_term_object->term_id;
    $tanks_system_child_terms = get_terms(
        array(
            'taxonomy' => 'product-line',
            'hide_empty' => false,
            'parent' => $term_id,
        ),
    );
    $query_args = array(
        'post_type' => 'product',
        'posts_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'ASC',
        'orderby' => 'title',
        'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => 'product-line',
                'filed' => 'term_id',
                'terms' => 88,
            ],
        ],
    );
    $query = new WP_Query($query_args);
    ob_start();
    if (!empty($tanks_system_child_terms)):
        ?>
        <div class="types-of-tanks">
            <!-- Common Plastic Type Slider Start -->
            <div class="common-plastic-type-slider">
                <div class="common-plastic-type-slider-block">
                    <div class="swiper common-plastic-slider">
                        <div class="swiper-wrapper">
                            <?php
                            $data_counter = 1;
                            foreach ($tanks_system_child_terms as $tank_system_child_term): ?>
                                <?php
                                $term_ID = $tank_system_child_term->term_id;
                                $term_name = $tank_system_child_term->name;
                                $thumbnail_image = get_field('pl_thumbnail_image', 'product-line_' . $term_ID);
                                ?>
                                <div class="swiper-slide">
                                    <div class="common-plastic-link-card types-of-tank-card">
                                        <div class="common-plastic-bg"
                                            style="background:url('<?php echo ($thumbnail_image) ? $thumbnail_image['url'] : $default_img; ?>') no-repeat center; background-size: cover">
                                        </div>
                                        <a href="javascript:;"
                                            class="common-plastic-link <?php echo ($data_counter == 1) ? 'active' : ''; ?>"
                                            data-term-id="<?php echo $term_ID; ?>">
                                            <div class="d-flex align-items-center justify-content-between w-100">
                                                <div class="title">
                                                    <h5>
                                                        <?php echo $term_name; ?>
                                                    </h5>
                                                </div>
                                                <div class="arrow-container">
                                                    <figure>
                                                        <img src="/wp-content/themes/doga/assets/images/market-grid-arrow.svg"
                                                            alt="arrow-icon">
                                                    </figure>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <?php
                                $data_counter++;
                            endforeach; ?>
                        </div>
                        <div class="swiper-pagination plastic-slider-pagination d-block d-md-none"></div>
                    </div>
                </div>
            </div>
            <!-- Common Plastic Type Slider End -->
            <div class="washer-system-product-cards">
                <div class="washer-system-product-cards-block">
                    <div class="common-product-card-content">
                        <div class="row  tanks-system-type-of-tanks-listing">
                            <?php while ($query->have_posts()):
                                $query->the_post(); ?>
                                <?php
                                $post_id = get_the_ID();
                                $post_title = get_the_title();
                                $post_url = get_permalink($post_id);
                                $thumbnail_image = get_the_post_thumbnail_url($post_id) ? get_the_post_thumbnail_url($post_id) : '/wp-content/themes/doga/assets/images/washer-pump-thumbnail-img.png';
                                $washer_system_product_shrot_description = get_field('washer_system_product_shrot_description');
                                ?>
                                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                                    <div class="common-product-card">
                                        <div class="image-content">
                                            <div class="img-container">
                                                <figure>
                                                    <img src="<?php echo $thumbnail_image; ?>" alt="<?php echo $post_title; ?>">
                                                </figure>
                                            </div>
                                        </div>
                                        <div class="text-content">
                                            <div class="product-detail">
                                                <div class="product-name">
                                                    <h6>
                                                        <?php echo $post_title; ?>
                                                    </h6>
                                                </div>
                                                <?php if ($washer_system_product_shrot_description): ?>
                                                    <div class="detail">
                                                        <div class="desc descriptoin">
                                                            <?php
                                                            echo $washer_system_product_shrot_description;
                                                            ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="btn">
                                                <a href="#contact-us" class="more-info-btn">
                                                    <?php echo ($wsd_more_information_title) ? $wsd_more_information_title : 'More Information'; ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        wp_reset_postdata();
    endif;
    return ob_get_clean();
}
add_shortcode("types_of_tanks", "get_types_of_tanks");
/* ************** Types Of Tanks Filter Shortcode ************** */
/* ************** Types Of Accessories Filter Shortcode ************** */
function get_types_of_accessories()
{
    ob_start();
    $wsd_more_information_title = get_field('wsd_more_information_title', 'options');
    $default_img = get_stylesheet_directory_uri() . '/assets/images/product-line-default-img.png';
    $current_term_object = get_queried_object();
    $term_id = $current_term_object->term_id;
    $accessories_terms = get_terms(
        array(
            'taxonomy' => 'product-line',
            'hide_empty' => false,
            'parent' => $term_id,
        ),
    );
    $query_args = array(
        'post_type' => 'product',
        'posts_status' => 'publish',
        'posts_per_page' => -1,
        'order' => 'ASC',
        'orderby' => 'title',
        'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => 'product-line',
                'filed' => 'term_id',
                'terms' => 96,
            ],
        ],
    );
    $query = new WP_Query($query_args);
    if (!empty($accessories_terms)):
        ?>
        <!-- Common Plastic Type Slider Start ( Accessories Page ) -->
        <div class="common-plastic-type-slider">
            <div class="common-plastic-type-slider-block">
                <div class="swiper acce-plastic-slider">
                    <div class="swiper-wrapper">
                        <?php $data_counter = 1; ?>
                        <?php foreach ($accessories_terms as $accessories_term): ?>
                            <?php
                            $term_ID = $accessories_term->term_id;
                            $term_name = $accessories_term->name;
                            $thumbnail_image = get_field('pl_thumbnail_image', 'product-line_' . $term_ID);
                            ?>
                            <div class="swiper-slide">
                                <div class="common-plastic-link-card  accessories-term-card">
                                    <div class="common-plastic-bg"
                                        style="background:url('<?php echo ($thumbnail_image) ? $thumbnail_image['url'] : $default_img; ?>') no-repeat center; background-size: cover">
                                    </div>
                                    <a href="javascript:;"
                                        class="common-plastic-link <?php echo ($data_counter == 1) ? 'active' : ''; ?>"
                                        data-term-id="<?php echo $term_ID; ?>">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <div class="title">
                                                <h5>
                                                    <?php echo $term_name; ?>
                                                </h5>
                                            </div>
                                            <div class="arrow-container">
                                                <figure>
                                                    <img src="/wp-content/themes/doga/assets/images/market-grid-arrow.svg"
                                                        alt="arrow-icon">
                                                </figure>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <?php $data_counter++; ?>
                        <?php endforeach; ?>
                        <!-- <div class="swiper-slide">
                            <div class="common-plastic-link-card">
                                <div class="common-plastic-bg" style="background:url('/wp-content/themes/doga/assets/images/m-image.png') no-repeat center; background-size: cover"></div>
                                <a href="javascript:;" class="common-plastic-link">
                                    <div class="d-flex align-items-center justify-content-between w-100">
                                        <div class="title">
                                            <h5>CHECK VALVES</h5>
                                        </div>
                                        <div class="arrow-container">
                                            <figure>
                                                <img src="/wp-content/themes/doga/assets/images/market-grid-arrow.svg" alt="arrow-icon">
                                            </figure>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="common-plastic-link-card">
                                <div class="common-plastic-bg" style="background:url('/wp-content/themes/doga/assets/images/m-image.png') no-repeat center; background-size: cover"></div>
                                <a href="javascript:;" class="common-plastic-link">
                                    <div class="d-flex align-items-center justify-content-between w-100">
                                        <div class="title">
                                            <h5>CONNECTORS</h5>
                                        </div>
                                        <div class="arrow-container">
                                            <figure>
                                                <img src="/wp-content/themes/doga/assets/images/market-grid-arrow.svg" alt="arrow-icon">
                                            </figure>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="common-plastic-link-card">
                                <div class="common-plastic-bg" style="background:url('/wp-content/themes/doga/assets/images/m-image.png') no-repeat center; background-size: cover"></div>
                                <a href="javascript:;" class="common-plastic-link">
                                    <div class="d-flex align-items-center justify-content-between w-100">
                                        <div class="title">
                                            <h5>HOSES</h5>
                                        </div>
                                        <div class="arrow-container">
                                            <figure>
                                                <img src="/wp-content/themes/doga/assets/images/market-grid-arrow.svg" alt="arrow-icon">
                                            </figure>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="swiper-slide">
                            <div class="common-plastic-link-card">
                                <div class="common-plastic-bg" style="background:url('/wp-content/themes/doga/assets/images/m-image.png') no-repeat center; background-size: cover"></div>
                                <a href="javascript:;" class="common-plastic-link">
                                    <div class="d-flex align-items-center justify-content-between w-100">
                                        <div class="title">
                                            <h5>GROMMETS</h5>
                                        </div>
                                        <div class="arrow-container">
                                            <figure>
                                                <img src="/wp-content/themes/doga/assets/images/market-grid-arrow.svg" alt="arrow-icon">
                                            </figure>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div> -->
                    </div>
                    <div class="swiper-pagination">
                        <div class="swiper-button-prev acce-plastic-slider-prev"></div>
                        <div class="swiper-button-next acce-plastic-slider-next"></div>
                    </div>
                    <div class="swiper-pagination acce-plastic-slider-pagination"></div>
                </div>
            </div>
        </div>
        <!-- Common Plastic Type Slider End ( Accessories Page ) -->
        <div class="washer-system-product-cards">
            <div class="washer-system-product-cards-block">
                <div class="common-product-card-content">
                    <div class="row  accessories-listing-data">
                        <?php while ($query->have_posts()):
                            $query->the_post(); ?>
                            <?php
                            $post_id = get_the_ID();
                            $post_title = get_the_title();
                            $post_url = get_permalink($post_id);
                            $thumbnail_image = get_the_post_thumbnail_url($post_id) ? get_the_post_thumbnail_url($post_id) : '/wp-content/themes/doga/assets/images/washer-pump-thumbnail-img.png';
                            $washer_system_product_shrot_description = get_field('washer_system_product_shrot_description');
                            ?>
                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                                <div class="common-product-card">
                                    <div class="image-content">
                                        <div class="img-container">
                                            <figure>
                                                <img src="<?php echo $thumbnail_image; ?>" alt="<?php echo $post_title; ?>">
                                            </figure>
                                        </div>
                                    </div>
                                    <div class="text-content">
                                        <div class="product-detail">
                                            <div class="product-name">
                                                <h6>
                                                    <?php echo $post_title; ?>
                                                </h6>
                                            </div>
                                            <?php if ($washer_system_product_shrot_description): ?>
                                                <div class="detail">
                                                    <div class="desc descriptoin">
                                                        <?php
                                                        echo $washer_system_product_shrot_description;
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="btn">
                                            <a href="#contact-us" class="more-info-btn">
                                                <?php echo ($wsd_more_information_title) ? $wsd_more_information_title : 'More Information'; ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        wp_reset_postdata();
    endif;
    return ob_get_clean();
}
add_shortcode("types_of_accessories", "get_types_of_accessories");
/* ************** Types Of Accessories Filter Shortcode ************** */
/* ************** Get Search Term Shortcode ************** */
function rb_get_search_term_shortcode()
{
    if (is_search()) {
        return get_search_query();
    }
    return '';
}
add_shortcode('search_term', 'rb_get_search_term_shortcode');
/* ************** Get Search Term Shortcode ************** */

/* ************** Drive System Category Products Listing Shortcode ************** */
function get_drive_sytem_category_product_linting_shortcode() {
    $get_category_object = get_queried_object();
    $term_id = $get_category_object->term_id;
    $default_img = get_stylesheet_directory_uri() . '/assets/images/child-category-slider-default-img.jpg';

    // child terms
    $get_child_terms = get_terms(array(
        'taxonomy' => 'product-category',
        'hide_empty' => false,
        'parent' => $term_id,
    ));

    $paged = get_query_var('paged') ? get_query_var('paged') : 1;

    // Options Page Values
    $pdp_filter_title = get_field('pdp_filter_title', 'options');
    $pdp_search_name_title = get_field('pdp_search_name_title', 'options');
    $speed_filter_title = get_field('speed_filter_title', 'options');
    $speed_filter_data = get_field('speed_filter_data', 'options');
    $torque_filter_title = get_field('torque_filter_title', 'options');
    $torque_filter_data = get_field('torque_filter_data', 'options');
    $voltage_filter_title = get_field('voltage_filter_title', 'options');
    $voltage_filter_data = get_field('voltage_filter_data', 'options');
    $weight_filter_title = get_field('weight_filter_title', 'options');
    $weight_filter_data = get_field('weight_filter_data', 'options');
    $pdp_see_product_title = get_field('pdp_see_product_title', 'options');
    $pdp_speed_title = get_field('pdp_speed_title', 'options');
    $pdp_torque_title = get_field('pdp_torque_title', 'options');
    $pdp_voltage_title = get_field('pdp_voltage_title', 'options');
    $pdp_weight_title = get_field('pdp_weight_title', 'options');
    $pdp_nominal_torque_title = get_field('pdp_nominal_torque_title', 'options');

    // Query base
    $query_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'order' => 'ASC',
        'orderby' => 'title',
        'posts_per_page' => get_option('posts_per_page'),
        'paged' => $paged,
        'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => 'product-market-sector',
                'field' => 'slug',
                'terms' => 'drive-system',
            ],
            [
                'taxonomy' => 'product-category',
                'field' => 'term_id',
                'terms' => $term_id,
            ],
        ],
    );

    $taxonomy_query = new WP_Query($query_args);

    // Capturamos filtros activos desde la URL
    $active_filters = array();
    if (!empty($_GET['speed'])) {
        $active_filters['Speed'] = array_map('sanitize_text_field', (array) $_GET['speed']);
    }
    if (!empty($_GET['torque'])) {
        $active_filters['Torque'] = array_map('sanitize_text_field', (array) $_GET['torque']);
    }
    if (!empty($_GET['voltage'])) {
        $active_filters['Voltage'] = array_map('sanitize_text_field', (array) $_GET['voltage']);
    }
    if (!empty($_GET['weight'])) {
        $active_filters['Weight'] = array_map('sanitize_text_field', (array) $_GET['weight']);
    }

    if ($taxonomy_query->have_posts()):
        ?>
        <?php if (!empty($get_child_terms)): ?>
            <div class="product-category-slider">
                <div class="product-category-slider-content">
                    <div class="swiper productCategorySlider" id="">
                        <div class="swiper-wrapper category-page-child-category-slider">
                            <?php foreach ($get_child_terms as $child_term): ?>
                                <?php
                                $child_term_id = $child_term->term_id;
                                $child_term_name = $child_term->name;
                                $thumbnail_image = get_field('pc_thumbnail_image', 'product-category_' . $child_term_id);
                                ?>
                                <div class="swiper-slide">
                                    <div class="product-link-card" data-term-id="<?php echo $child_term_id; ?>">
                                        <div class="product-link-card-bg"
                                            style="background:url('<?php echo ($thumbnail_image) ? $thumbnail_image['url'] : $default_img; ?>') no-repeat center; background-size: cover">
                                        </div>
                                        <div class="title">
                                            <h6><?php echo $child_term_name; ?></h6>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="product-listing-with-filter" style="margin:0;">
            <?php if ($speed_filter_data || $torque_filter_data || $voltage_filter_data || $weight_filter_data || $pdp_search_name_title): ?>
                <div class="filter-wrapper">
                    <?php if ($pdp_filter_title): ?>
                        <div class="heading">
                            <h5><?php echo $pdp_filter_title; ?></h5>
                        </div>
                    <?php endif; ?>
                    <div class="filter-container">
                        <div class="filter-left-block">
                            <div class="accordion" id="filterAcco">
                                <div class="filter-button-container products-meta-data-filter">
                                    
                                    <!-- Speed Filter -->
                                    <?php if (!empty($speed_filter_data)): ?>
                                        <div class="filter-button">
                                            <?php if ($speed_filter_title): ?>
                                                <h6 class="title collapsed" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                                    <?php echo $speed_filter_title; ?>
                                                </h6>
                                            <?php endif; ?>
                                            <div id="collapseOne" class="accordion-collapse collapse speed-filter">
                                                <div class="accordion-body">
                                                    <div class="filter-item-parent product-cat-speed-meta-data">
                                                        <?php foreach ($speed_filter_data as $speed_filter_data_item): ?>
                                                            <?php
                                                            $speed_filter_value = $speed_filter_data_item['value'];
                                                            $speed_filter_label = $speed_filter_data_item['label'];
                                                            ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="speed[]"
                                                                    id="speed-filter-<?php echo $speed_filter_value; ?>"
                                                                    value="<?php echo $speed_filter_value; ?>">
                                                                <label class="form-check-label"
                                                                    for="speed-filter-<?php echo $speed_filter_value; ?>">
                                                                    <?php echo $speed_filter_label; ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Torque Filter -->
                                    <?php if (!empty($torque_filter_data)): ?>
                                        <div class="filter-button">
                                            <?php if ($torque_filter_title): ?>
                                                <h6 class="title collapsed" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                                    <?php echo $torque_filter_title; ?>
                                                </h6>
                                            <?php endif; ?>
                                            <div id="collapseTwo" class="accordion-collapse collapse torque-fitler">
                                                <div class="accordion-body">
                                                    <div class="filter-item-parent product-cat-torque-meta-data">
                                                        <?php foreach ($torque_filter_data as $torque_filter_item): ?>
                                                            <?php
                                                            $torque_filter_value = $torque_filter_item['value'];
                                                            $torque_filter_label = $torque_filter_item['label'];
                                                            ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input"
                                                                    name="torque[]"
                                                                    id="torque-filter-<?php echo $torque_filter_value; ?>"
                                                                    type="checkbox"
                                                                    value="<?php echo $torque_filter_value; ?>">
                                                                <label class="form-check-label"
                                                                    for="torque-filter-<?php echo $torque_filter_value; ?>">
                                                                    <?php echo $torque_filter_label; ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Voltage Filter -->
                                    <?php if (!empty($voltage_filter_data)): ?>
                                        <div class="filter-button">
                                            <?php if ($voltage_filter_title): ?>
                                                <h6 class="title collapsed" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                                    <?php echo $voltage_filter_title; ?>
                                                </h6>
                                            <?php endif; ?>
                                            <div id="collapseThree" class="accordion-collapse collapse voltage-filter">
                                                <div class="accordion-body">
                                                    <div class="filter-item-parent product-cat-voltage-meta-data">
                                                        <?php foreach ($voltage_filter_data as $voltage_filter_data_item): ?>
                                                            <?php
                                                            $voltage_filter_value = $voltage_filter_data_item['value'];
                                                            $voltage_filter_label = $voltage_filter_data_item['label'];
                                                            ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input"
                                                                    name="voltage[]"
                                                                    id="voltage-filter-<?php echo $voltage_filter_value; ?>"
                                                                    type="checkbox"
                                                                    value="<?php echo $voltage_filter_value; ?>">
                                                                <label class="form-check-label"
                                                                    for="voltage-filter-<?php echo $voltage_filter_value; ?>">
                                                                    <?php echo $voltage_filter_label; ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Weight Filter -->
                                    <?php if (!empty($weight_filter_data)): ?>
                                        <div class="filter-button">
                                            <?php if ($weight_filter_title): ?>
                                                <h6 class="title collapsed" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                                    <?php echo $weight_filter_title; ?>
                                                </h6>
                                            <?php endif; ?>
                                            <div id="collapseFour" class="accordion-collapse collapse weight-filter">
                                                <div class="accordion-body">
                                                    <div class="filter-item-parent product-cat-weight-meta-data">
                                                        <?php foreach ($weight_filter_data as $weight_filter_data_item): ?>
                                                            <?php
                                                            $weight_filter_value = $weight_filter_data_item['value'];
                                                            $weight_filter_label = $weight_filter_data_item['label'];
                                                            ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input"
                                                                    name="weight[]"
                                                                    id="weight-filter-<?php echo $weight_filter_value; ?>"
                                                                    type="checkbox"
                                                                    value="<?php echo $weight_filter_value; ?>">
                                                                <label class="form-check-label"
                                                                    for="weight-filter-<?php echo $weight_filter_value; ?>">
                                                                    <?php echo $weight_filter_label; ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="filter-right-block">
                            <div class="search-container category-product-search-block">
                                <input type="text"
                                    placeholder="<?php echo ($pdp_search_name_title) ? $pdp_search_name_title : 'Search by name or code'; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Active Filters Section -->
            <?php if (!empty($active_filters)): ?>
                <div class="active-filters">
                    <h6>Active Filters:</h6>
                    <ul>
                        <?php foreach ($active_filters as $filter_name => $values): ?>
                            <?php foreach ($values as $value): ?>
                                <li class="active-filter-item">
                                    <span class="filter-name"><?php echo esc_html($filter_name); ?>:</span>
                                    <span class="filter-value"><?php echo esc_html($value); ?></span>
                                    <a href="<?php echo esc_url(remove_query_arg(strtolower($filter_name))); ?>" class="remove-filter">✕</a>
                                </li>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </ul>
                    <div class="clear-all-filters">
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="btn-clear">Clear All</a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Products Listing -->
            <div class="products-wrapper product-category-product-listing-block" data-term-id="<?php echo $term_id; ?>">
                <?php while ($taxonomy_query->have_posts()): $taxonomy_query->the_post(); ?>
                    <?php
                    $post_id = get_the_ID();
                    $post_url = get_the_permalink($post_id);
                    $post_title = get_the_title();
                    $pd_product_slider_images = get_field('pd_product_slider_images', $post_id);
                    $pd_minimum_speed = get_field('pd_minimum_speed', $post_id);
                    $pd_maximum_speed = get_field('pd_maximum_speed', $post_id);
                    $pd_torque = get_field('pd_torque', $post_id);
                    $pd_weight = get_field('pd_weight', $post_id);
                    $pd_voltage_details = get_field('pd_voltage_details', $post_id);
                    ?>
                    <div class="product-row">
                        <?php if ($pd_product_slider_images): ?>
                            <div class="product-image-slider">
                                <div class="swiper product-listing-slider" id="productListingSlider">
                                    <div class="swiper-wrapper">
                                        <?php foreach ($pd_product_slider_images as $product_image): ?>
                                            <?php if ($product_image['pdi_image']): ?>
                                                <div class="swiper-slide">
                                                    <div class="product-slider-card">
                                                        <figure>
                                                            <img src="<?php echo $product_image['pdi_image']['url']; ?>" alt="<?php echo $product_image['pdi_image']['alt']; ?>">
                                                        </figure>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="product-info-card">
                            <div class="heading">
                                <h4><a href="<?php echo $post_url; ?>"><?php echo $post_title; ?></a></h4>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php
    endif;
    wp_reset_postdata();
}
add_shortcode('drive_sytem_category_product_linting_shortcode', 'get_drive_sytem_category_product_linting_shortcode');

/* ************** Drive System Category Products Listing Shortcode ************** */
/* ************** Save Repeater Field Data ************** */
function save_number_index_field($post_id)
{
    // Avoid running on autosave or ACF revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (wp_is_post_revision($post_id))
        return;
    if (get_post_type($post_id) !== 'product')
        return;
    // Ensure ACF repeater rows are available
    if (!have_rows('pd_voltage_details', $post_id))
        return;
    $numbers = [];
    while (have_rows('pd_voltage_details', $post_id)) {
        the_row();
        $number = get_sub_field('voltage');
        if ($number !== null && $number !== '') {
            $numbers[] = $number;
        }
    }
    // Save comma-separated string with leading/trailing commas
    if (!empty($numbers)) {
        update_post_meta($post_id, 'number_index', ',' . implode(',', $numbers) . ',');
    } else {
        delete_post_meta($post_id, 'number_index');
    }
}
add_action('acf/save_post', 'save_number_index_field', 20); // Priority 20 is key
/* ************** Save Repeater Field Data ************** */
/* ************** Drive System Category's Market Sector Archive Page  ************** */
function get_drive_system_market_sector_archive_page_shortcode()
{
    ob_start();
    $get_market_term_object = get_queried_object();
    $current_market_term_id = $get_market_term_object->term_id;
    // Get market top level terms
    $get_top_level_terms = get_terms(
        array(
            'taxonomy' => 'market-sector',
            'hide_empty' => true,
            'parent' => $current_market_term_id,
        ),
    );
    ?>
    <div class="drive-system-market-inner">
        <!-- Market Top Level Terms Start -->
        <?php if (!empty($get_top_level_terms)): ?>
            <div class="ds-market-filter market-top-level-filter-block">
                <div class="ds-market-filter-block">
                    <div class="row">
                        <?php $top_level_term_data_counter = 1; ?>
                        <?php foreach ($get_top_level_terms as $top_level_term): ?>
                            <?php
                            $top_level_term_id = $top_level_term->term_id;
                            $top_level_term_name = $top_level_term->name;
                            $single_cat = count($get_top_level_terms) == 1;
                            ?>
                            <div class="col-12 col-md-6 col-lg-4" <?php echo $single_cat ? 'style="display:none;"' : ''; ?>>
                                <div class="ds-market-filter-link <?php echo ($top_level_term_data_counter == 1) ? 'show' : ''; ?> "
                                    data-term-id=<?php echo $top_level_term_id; ?>>
                                    <a href="javascript:;" class="ds-market-filter-btn">
                                        <span>
                                            <?php echo mb_strtoupper($top_level_term_name); ?>
                                        </span>
                                    </a>
                                    <div class="filter-marker">
                                        <div class="filter-marker-fill"></div>
                                    </div>
                                </div>
                            </div>
                            <?php $top_level_term_data_counter++; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!-- Market Top Level Terms End -->
        <!-- Market 2nd Level child Terms Start -->
        <div class="ds-market-filter-cards hide-second-level-market-terms">
            <div class="ds-market-filter-cards-block">
                <div class="row market-second-level-child-terms-block">
                    <!-- Second Level Terms -->
                </div>
            </div>
        </div>
        <!-- Market 2nd Level child Terms End -->
        <!-- Market 3rd Level Child Terms Start -->
        <div class="ds-market-products market-third-terms-block">
            <!-- 3rd Level Child Market DAta -->
        </div>
        <!-- Market 3rd Level Child Terms End  -->
    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('drive_system_market_sector_archive_page_shortcode', 'get_drive_system_market_sector_archive_page_shortcode');
/* ************** Drive System Category's Market Sector Archive Page  ************** */
/* ************** Drive System Landing Markets Listing  ************** */
function get_drive_system_market_category_listing()
{
    ob_start();
    $default_img = get_stylesheet_directory_uri() . '/assets/images/product-line-default-img.png';
    $drive_system_market_ID = get_field('select_taxonomy_for_drive_system_landing_page', 'options');
    /*$get_drive_system_markets = get_terms(
        array(
            'taxonomy'      => 'market-sector',
            'hide_empty'    => false,
            'parent'        => $drive_system_market_ID,
            'orderby'    => 'term_id',
            'order'      => 'ASC',     // o 'DESC'
        ),
    );*/
    $priority_ids = array(143, 145, 146, 147, 148,149,150,151,152,153,154,155);
    $priority_terms = get_terms(array(
        'taxonomy' => 'market-sector',
        'hide_empty' => false,
        'parent' => $drive_system_market_ID,
        'include' => $priority_ids,
        'orderby' => 'include', // respeta el orden de $priority_ids
    ));
    $priority_found_ids = wp_list_pluck($priority_terms, 'term_id');
    $other_terms = get_terms(array(
        'taxonomy' => 'market-sector',
        'hide_empty' => false,
        'parent' => $drive_system_market_ID,
        'exclude' => $priority_found_ids,
        'orderby' => 'term_id', // o 'name', 'count', etc.
        'order' => 'ASC',
    ));
    // 3) Une: primero los prioritarios encontrados, luego el resto
    $get_drive_system_markets = array_merge($priority_terms, $other_terms);
    if (!empty($get_drive_system_markets)):
        ?>
        <div class="custom-market-parent-block">
            <div class="row">
                <?php foreach ($get_drive_system_markets as $drive_system_market): ?>
                    <?php
                    $market_term_id = $drive_system_market->term_id;
                    $market_term_name = $drive_system_market->name;
                    $market_term_url = get_term_link($market_term_id);
                    $external_url = get_field('url_externa', 'market-sector_' . $market_term_id);
                    if ($external_url != '')
                        $market_term_url = $external_url;
                    // echo 'External: '. $external_url.'.EnD.';
                    $market_term_thumbail = get_field('thumbnail_image', 'market-sector_' . $market_term_id);
                    ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="<?php echo $market_term_url; ?>" <?= (($external_url != '') ? 'target="_BLANK"' : '') ?>
                            class="market-card">
                            <div class="market-card-bg"
                                style="background:url('<?php echo ($market_term_thumbail) ? $market_term_thumbail['url'] : $default_img; ?>') no-repeat center; background-size: cover">
                            </div>
                            <div class="title">
                                <p>
                                    <?php echo strtoupper($market_term_name); ?>
                                </p>
                            </div>
                            <div class="btn-container">
                                <div class="market-link" href="javascript:;">
                                    <figure>
                                        <img src="/wp-content/themes/doga/assets/images/market-grid-arrow.svg" alt="arrow">
                                    </figure>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    endif;
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('drive_system_market_category_listing', 'get_drive_system_market_category_listing');
/* ************** Drive System Landing Markets Listing  ************** */

/* ************** Wiper System Landing Markets Listing  ************** */
function get_wiper_system_market_category_listing()
{
    ob_start();
    $default_img = get_stylesheet_directory_uri() . '/assets/images/product-line-default-img.png';
    $wiper_system_ID = get_field('select_taxonomy_for_wiper_system_landing_page', 'option');
    /*$get_wiper_system_market_terms = get_terms(
        array(
            'taxonomy' => 'market-sector',
            'hide_empty' => false,
            'parent' => $wiper_system_ID,
        ),
    );*/
    

    $priority_ids = array(74,73,77,218,21,75,78,220,76); 
    
    $priority_terms = get_terms(array(
        'taxonomy' => 'market-sector',
        'hide_empty' => false,
        'parent' => $wiper_system_ID,
        'include' => $priority_ids,
        'orderby' => 'include', // respeta el orden de $priority_ids
    ));
    
    $priority_found_ids = wp_list_pluck($priority_terms, 'term_id');
    $other_terms = get_terms(array(
        'taxonomy' => 'market-sector',
        'hide_empty' => false,
        'parent' => $wiper_system_ID,
        'exclude' => $priority_found_ids,
        'orderby' => 'term_id', // o 'name', 'count', etc.
        'order' => 'ASC',
    ));
    
    // Une: primero los prioritarios encontrados, luego el resto
    $get_wiper_system_market_terms = array_merge($priority_terms, $other_terms);
    if (!empty($get_wiper_system_market_terms)):
        ?>
        <div class="custom-market-parent-block">
            <div class="row">
                <?php foreach ($get_wiper_system_market_terms as $wiper_system_market): ?>
                    <?php
                    $market_term_id = $wiper_system_market->term_id;
                    $market_term_name = $wiper_system_market->name;
                    $market_term_url = get_term_link($market_term_id);
                    $external_url = get_field('url_externa', 'market-sector_' . $market_term_id);
                    if ($external_url != '')
                        $market_term_url = $external_url;
                    $market_term_thumbail = get_field('thumbnail_image', 'market-sector_' . $market_term_id);
                    ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="<?php echo $market_term_url; ?>" <?= (($external_url != '') ? 'target="_BLANK"' : '') ?>
                            class="market-card">
                            <div class="market-card-bg"
                                style="background:url('<?php echo ($market_term_thumbail) ? $market_term_thumbail['url'] : $default_img; ?>') no-repeat center; background-size: cover">
                            </div>
                            <div class="title">
                                <p>
                                    <?php echo strtoupper($market_term_name); ?>
                                </p>
                            </div>
                            <div class="btn-container">
                                <div class="market-link" href="javascript:;">
                                    <figure>
                                        <img src="/wp-content/themes/doga/assets/images/market-grid-arrow.svg" alt="arrow">
                                    </figure>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    endif;
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('wiper_system_market_category_listing', 'get_wiper_system_market_category_listing');
/* ************** Wiper System Landing Markets Listing  ************** */
/* ************** Wiper System Related Markets Listing  ************** */
function get_wiper_system_related_markets()
{
    $default_img = get_stylesheet_directory_uri() . '/assets/images/product-line-default-img.png';
    $get_current_wiper_system_market_object = get_queried_object();
    $current_term_id = $get_current_wiper_system_market_object->term_id;
    $current_term_parent = $get_current_wiper_system_market_object->parent;
    $related_market_terms = get_terms(
        array(
            'taxonomy' => 'market-sector',
            'hide_empty' => false,
            'parent' => $current_term_parent,
            'exclude' => [$current_term_id],
        ),
    );
    ob_start();
    if (!empty($related_market_terms)):
        ?>
        <div class="custom-market-parent-block">
            <div class="row">
                <?php foreach ($related_market_terms as $related_wiper_system_market): ?>
                    <?php
                    $market_term_id = $related_wiper_system_market->term_id;
                    $market_term_name = $related_wiper_system_market->name;
                    $market_term_url = get_term_link($market_term_id);
                    $market_term_thumbail = get_field('thumbnail_image', 'market-sector_' . $market_term_id);
                    ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="<?php echo $market_term_url; ?>" class="market-card">
                            <div class="market-card-bg"
                                style="background:url('<?php echo ($market_term_thumbail) ? $market_term_thumbail['url'] : $default_img; ?>') no-repeat center; background-size: cover">
                            </div>
                            <div class="title">
                                <p>
                                    <?php echo strtoupper($market_term_name); ?>
                                </p>
                            </div>
                            <div class="btn-container">
                                <div class="market-link" href="javascript:;">
                                    <figure>
                                        <img src="/wp-content/themes/doga/assets/images/market-grid-arrow.svg" alt="arrow">
                                    </figure>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    endif;
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('wiper_system_related_markets', 'get_wiper_system_related_markets');
/* ************** Wiper System Related Markets Listing  ************** */
/* ************** Drive System Product Page Related Market Shortcode ************** */
function get_related_markets()
{
    $default_img = get_stylesheet_directory_uri() . '/assets/images/product-line-default-img.png';
    $terms = get_the_terms(get_the_ID(), 'market-sector');
    ob_start();
    if (!empty($terms)):
        ?>
        <div class="custom-market-parent-block">
            <div class="row">
                <?php
                foreach ($terms as $term):
                    if ($term->parent == 0)
                        continue;
                    $parent_term = get_term($term->parent, 'market-sector');
                    if ($parent_term->parent != 0)
                        continue;
                    $term_id = $term->term_id;
                    $parent_url = get_term_link($term->parent);
                    $term_name = $term->name;
                    $term_link = get_term_link($term_id);
                    $term_img = get_field('thumbnail_image', 'market-sector_' . $term_id);
                    $thumbnail_img = $term_img ? $term_img['url'] : $default_img;
                    ?>
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        <a href="<?php echo esc_url($term_link); ?>" class="market-card">
                            <div class="market-card-bg"
                                style="background:url('<?php echo esc_url($thumbnail_img); ?>') no-repeat center; background-size: cover">
                            </div>
                            <div class="title">
                                <p><?php echo esc_html($term_name); ?></p>
                            </div>
                            <div class="btn-container">
                                <div class="market-link">
                                    <figure>
                                        <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/market-grid-arrow.svg"
                                            alt="arrow">
                                    </figure>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php
                endforeach;
                ?>
            </div>
        </div>
        <script>
            jQuery(document).ready(function ($) {
                $('.hide-drive-system-single-product-related-market').addClass('show');
            });
        </script>
        <?php
    endif;
    return ob_get_clean();
}
add_shortcode("related_markets", "get_related_markets");
/* ************** Drive System Product Page Related Market Shortcode ************** */
/* ************** Wiper System Product Detail Page Banner Section Shortcode ************** */
/*
function get_wpier_sys_product_details()
{
    ob_start();
    $default_img = get_stylesheet_directory_uri() . '/assets/images/wiper-system-product-page-default-img.jpg';
    $small_slider_default_img = get_stylesheet_directory_uri() . '/assets/images/wiper-system-product-page-small-slider-default-img.jpg';
    $product_title = get_the_title();
    $pd_product_slider_images = get_field('pd_product_slider_images');
    $download_sheet_files = get_field('download_sheet_files');
    $wiper_system_details = get_the_content();
    $more_information_button = get_field('more_information_button', 'options');
    ?>
    <div class="product-inner-page-card">
        <?php if (!empty($pd_product_slider_images) || $default_img): ?>
            <div class="product-slider-container">
                <?php
                $small_slider_img = '';
                $slider_img = '';
                if (!empty($pd_product_slider_images)):
                    foreach ($pd_product_slider_images as $slider_image_data):
                        $pdi_image = $slider_image_data['pdi_image'];
                        if ($pdi_image):
                            $url = $pdi_image['url'];
                            $alt = $pdi_image['alt'];
                            $ext = pathinfo($url, PATHINFO_EXTENSION);
                            if (strtolower($ext) === 'mp4') {
                                $small_slider_img .= '<div class="swiper-slide">
                                                        <div class="img-container">
                                                            <figure>
                                                                <video src="' . $url . '" muted autoplay loop playsinline></video>
                                                            </figure>
                                                        </div>
                                                    </div>';
                                $slider_img .= '<div class="swiper-slide">
                                                    <a href="' . $url . '" class="img-container" data-fancybox="gallery" data-type="video">
                                                        <figure>
                                                            <video src="' . $url . '" controls playsinline></video>
                                                        </figure>
                                                    </a>
                                                </div>';
                            } else {
                                $small_slider_img .= '<div class="swiper-slide">
                                                        <div class="img-container">
                                                            <figure>
                                                                <img src="' . $url . '" alt="' . $alt . '">
                                                            </figure>
                                                        </div>
                                                    </div>';
                                $slider_img .= '<div class="swiper-slide">
                                                    <a href="' . $url . '" class="img-container" data-fancybox="gallery">
                                                        <figure>
                                                            <img src="' . $url . '" alt="' . $alt . '">
                                                        </figure>
                                                    </a>
                                                </div>';
                            }
                        endif;
                    endforeach;
                endif;
                ?>
                <?php if ($small_slider_img || $small_slider_default_img): ?>
                    <div class="slider-container slider-container-1">
                        <div class="swiper slider-thumb">
                            <div class="swiper-wrapper">
                                <?php
                                if ($small_slider_img):
                                    echo $small_slider_img;
                                else:
                                ?>
                                    <div class="swiper-slide">
                                        <div class="img-container">
                                            <figure>
                                                <img src="<?php echo $small_slider_default_img; ?>" alt="<?php echo $product_title; ?>">
                                            </figure>
                                        </div>
                                    </div>
                                <?php
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($slider_img || $default_img): ?>
                    <div class="slider-container slider-container-2">
                        <div class="search-container">
                            <div class="search-icon">
                                <figure>
                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/search-icon.svg"
                                        alt="search-icon">
                                </figure>
                            </div>
                        </div>
                        <div class="swiper slider-main">
                            <div class="swiper-wrapper">
                                <?php
                                if ($slider_img):
                                    echo $slider_img;
                                else:
                                ?>
                                    <div class="swiper-slide">
                                        <a href="<?php echo $default_img; ?>" class="img-container" data-fancybox="gallery">
                                            <figure>
                                                <img src="<?php echo $default_img; ?>" alt="<?php echo $product_title; ?>">
                                            </figure>
                                        </a>
                                    </div>
                                <?php
                                endif;
                                ?>
                            </div>
                        </div>
                        <div class="swiper-btn-container">
                            <div class="swiper-button-prev">
                                <figure>
                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/wiper-slider-prev-arrow.svg"
                                        alt="prev-arrow-icon">
                                </figure>
                            </div>
                            <div class="swiper-button-next">
                                <figure>
                                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/wiper-slider-next-arrow.svg"
                                        alt="next-arrow-icon">
                                </figure>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="product-details-container">
            <?php if ($product_title): ?>
                <div class="heading">
                    <div class="title">
                        <h5><?php echo $product_title; ?></h5>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($wiper_system_details): ?>
                <div class="typography">
                    <div class="typography-block">
                        <?php echo apply_filters('the_content', $wiper_system_details); ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($download_sheet_files)): ?>
                <div class="product-details-download">
                    <div class="product-details-download-block">
                        <?php foreach ($download_sheet_files as $file_data):
                            $download_button_title = $file_data['download_button_title'];
                            $upload_file = $file_data['upload_file'];
                            if ($upload_file): ?>
                                <div class="product-sheet-link-block">
                                    <div class="product-sheet-link">
                                        <a href="<?php echo $upload_file['url']; ?>" download target="_blank" rel="noopener noreferrer">
                                            <?php echo $download_button_title; ?>
                                        </a>
                                    </div>
                                </div>
                        <?php endif;
                        endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php
            $post_id = get_the_ID();
            // Recupera SOLO los atributos asignados a este post:
            $product_attribute = get_the_terms($post_id, 'product-attribute');
            ?>
            <?php if (
                (! empty($product_attribute) && ! is_wp_error($product_attribute))
                || ! empty($more_information_button)
            ): ?>
                <div class="product-details-footer">
                    <div class="product-details-footer-block">
                        <!-- Sólo mostramos tags si las hay -->
                        <?php if (! empty($product_attribute) && ! is_wp_error($product_attribute)): ?>
                            <div class="tags-container">
                                <?php foreach ($product_attribute as $attribute): ?>
                                    <div class="tag">
                                        <p><?php echo esc_html($attribute->name); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <!-- Mostramos el botón siempre que $more_information_button exista -->
                        <?php if (! empty($more_information_button)): ?>
                            <div class="more-info-btn-container">
                                <a href="<?php echo esc_url($more_information_button['url']); ?>"
                                    class="more-info-btn">
                                    <span><?php echo esc_html($more_information_button['title']); ?></span>
                                    <span>
                                        <img decoding="async"
                                            src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/location-dis-doga-arrow.png'); ?>"
                                            alt="flecha">
                                    </span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode("wpier_sys_product_details_shortcode", "get_wpier_sys_product_details");
/* ************** Wiper System Product Detail Page Banner Section Shortcode ************** */
/* ************** Wiper System Product Detail Page Banner Section Shortcode ************** */
function get_wpier_sys_product_details()
{
    ob_start();
    $default_img = get_stylesheet_directory_uri() . '/assets/images/wiper-system-product-page-default-img.jpg';
    $small_slider_default_img = get_stylesheet_directory_uri() . '/assets/images/wiper-system-product-page-small-slider-default-img.jpg';
    $product_title = get_the_title();
    $pd_product_slider_images = (array) get_field('pd_product_slider_images');
    $download_sheet_files = (array) get_field('download_sheet_files');
    $wiper_system_details = get_the_content();
    $more_information_button = get_field('more_information_button', 'options');
    // Unique ID per shortcode instance (avoids Swiper collisions)
    $uid = 'wsps_' . wp_generate_uuid4();
    // Build slides
    $small_slider_html = '';
    $main_slider_html = '';
    if (!empty($pd_product_slider_images)) {
        foreach ($pd_product_slider_images as $slider_image_data) {
            $pdi_image = isset($slider_image_data['pdi_image']) ? $slider_image_data['pdi_image'] : null;
            if (!$pdi_image || empty($pdi_image['url'])) {
                continue;
            }
            $url = esc_url($pdi_image['url']);
            $alt = esc_attr($pdi_image['alt'] ?? $product_title);
            $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
            if ($ext === 'mp4') {
                $small_slider_html .= '<div class="swiper-slide"><div class="img-container"><figure><video src="' . $url . '" muted autoplay loop playsinline></video></figure></div></div>';
                $main_slider_html .= '<div class="swiper-slide"><a href="' . $url . '" class="img-container" data-fancybox="gallery" data-type="video"><figure><video src="' . $url . '" controls playsinline></video></figure></a></div>';
            } else {
                $small_slider_html .= '<div class="swiper-slide"><div class="img-container"><figure><img src="' . $url . '" alt="' . $alt . '"></figure></div></div>';
                $main_slider_html .= '<div class="swiper-slide"><a href="' . $url . '" class="img-container" data-fancybox="gallery"><figure><img src="' . $url . '" alt="' . $alt . '"></figure></a></div>';
            }
        }
    }
    ?>
    <div class="product-inner-page-card" id="<?php echo esc_attr($uid); ?>">
        <?php if ($small_slider_html || $small_slider_default_img || $main_slider_html || $default_img): ?>
            <div class="product-slider-container">
                <?php if ($small_slider_html || $small_slider_default_img): ?>
                    <div class="slider-container slider-container-1">
                        <div class="swiper slider-thumb" id="<?php echo esc_attr($uid); ?>__thumbs">
                            <div class="swiper-wrapper">
                                <?php
                                if ($small_slider_html) {
                                    echo $small_slider_html; // safe: built above with esc_*
                                } else {
                                    ?>
                                    <div class="swiper-slide">
                                        <div class="img-container">
                                            <figure>
                                                <img src="<?php echo esc_url($small_slider_default_img); ?>"
                                                    alt="<?php echo esc_attr($product_title); ?>">
                                            </figure>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($main_slider_html || $default_img): ?>
                    <div class="slider-container slider-container-2">
                        <div class="search-container">
                            <div class="search-icon">
                                <figure>
                                    <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/search-icon.svg'); ?>"
                                        alt="search-icon">
                                </figure>
                            </div>
                        </div>
                        <div class="swiper slider-main" id="<?php echo esc_attr($uid); ?>__main">
                            <div class="swiper-wrapper">
                                <?php
                                if ($main_slider_html) {
                                    echo $main_slider_html; // safe: built above with esc_*
                                } else {
                                    ?>
                                    <div class="swiper-slide">
                                        <a href="<?php echo esc_url($default_img); ?>" class="img-container" data-fancybox="gallery">
                                            <figure>
                                                <img src="<?php echo esc_url($default_img); ?>"
                                                    alt="<?php echo esc_attr($product_title); ?>">
                                            </figure>
                                        </a>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <div class="swiper-btn-container">
                            <button type="button" class="swiper-button-prev" aria-label="Previous slide">
                                <figure>
                                    <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/wiper-slider-prev-arrow.svg'); ?>"
                                        alt="prev-arrow-icon">
                                </figure>
                            </button>
                            <button type="button" class="swiper-button-next" aria-label="Next slide">
                                <figure>
                                    <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/wiper-slider-next-arrow.svg'); ?>"
                                        alt="next-arrow-icon">
                                </figure>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="product-details-container">
            <?php if ($product_title): ?>
                <div class="heading">
                    <div class="title">
                        <h5><?php echo esc_html($product_title); ?></h5>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($wiper_system_details): ?>
                <div class="typography" style="padding:30px">
                    <div class="typography-block">
                        <?php echo apply_filters('the_content', $wiper_system_details); ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($download_sheet_files)): ?>
                <div class="product-details-download">
                    <div class="product-details-download-block">
                        <?php foreach ($download_sheet_files as $file_data):
                            $download_button_title = $file_data['download_button_title'] ?? '';
                            $upload_file = $file_data['upload_file'] ?? null;
                            if (!empty($upload_file['url'])): ?>
                                <div class="product-sheet-link-block">
                                    <div class="product-sheet-link">
                                        <a href="<?php echo esc_url($upload_file['url']); ?>" download target="_blank"
                                            rel="noopener noreferrer">
                                            <?php echo esc_html($download_button_title ?: __('Download', 'textdomain')); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif;
                        endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php
            $post_id = get_the_ID();
            $product_attribute = get_the_terms($post_id, 'product-attribute');
            ?>
            <?php if ((!empty($product_attribute) && !is_wp_error($product_attribute)) || !empty($more_information_button)): ?>
                <div class="product-details-footer">
                    <div class="product-details-footer-block">
                        <?php if (!empty($product_attribute) && !is_wp_error($product_attribute)): ?>
                            <div class="tags-container">
                                <?php foreach ($product_attribute as $attribute): ?>
                                    <div class="tag">
                                        <p><?php echo esc_html($attribute->name); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php
                        $external_url = get_field('external_url');
                        if (!empty($external_url)): ?>
                            <div class="more-info-btn-container">
                                <a href="<?php echo esc_url($external_url); ?>" class="more-info-btn" target="_blank"
                                    rel="noopener noreferrer">
                                    <span>Más información</span>
                                    <span>
                                        <img decoding="async"
                                            src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/location-dis-doga-arrow.png'); ?>"
                                            alt="flecha">
                                    </span>
                                </a>
                            </div>
                        <?php elseif (!empty($more_information_button)): ?>
                            <div class="more-info-btn-container">
                                <a href="<?php echo esc_url($more_information_button['url']); ?>" class="more-info-btn"
                                    target="<?php echo !empty($more_information_button['target']) ? esc_attr($more_information_button['target']) : '_self'; ?>">
                                    <span><?php echo esc_html($more_information_button['title']); ?></span>
                                    <span>
                                        <img decoding="async"
                                            src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/location-dis-doga-arrow.png'); ?>"
                                            alt="flecha">
                                    </span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode("wpier_sys_product_details_shortcode", "get_wpier_sys_product_details");
/* ************** Wiper System Product Detail Page Banner Section Shortcode ************** */
?>