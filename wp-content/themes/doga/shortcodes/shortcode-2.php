<?php

/* ************** Washer Systemn Pump Filter ************** */
function get_washer_system_type_pump()
{
    ob_start();

    if (is_user_logged_in()):
        $current_term = get_queried_object();

        if ($current_term):
            $child_terms = get_terms(array(
                'taxonomy' => 'product-line',
                'hide_empty' => false,
                'parent' => $current_term->term_id,
            ));

            if (!empty($child_terms)):
                echo '<div class="washer-system-filter" data-first-term="' . esc_attr($child_terms[0]->term_id) . '">';

                foreach ($child_terms as $term):
                    $term_id = $term->term_id;
                    $term_name = $term->name;
                    $image_url = get_field('pl_thumbnail_image', 'product-line_' . $term_id);

                    echo '<div class="washer-filter-button" data-term="' . $term_id . '">';

                    echo '</div>';
                endforeach;

                echo '</div>';
                echo '<div id="washer-filter-posts"></div>';
            endif;
        endif;
    endif;

    return ob_get_clean();
}
add_shortcode('washer_system_pumps_filter', 'get_washer_system_type_pump');

// AJAX handler
add_action('wp_ajax_get_washer_system_posts', 'get_washer_system_posts_callback');
add_action('wp_ajax_nopriv_get_washer_system_posts', 'get_washer_system_posts_callback');

function get_washer_system_posts_callback()
{

    $term_id = $_POST['term_id'];

    $posts = get_posts(array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'orderby' => 'ID',
        'posts_per_page' => 6,
        'tax_query' => array(
            array(
                'taxonomy' => 'product-line',
                'field' => 'term_id',
                'terms' => $term_id,
            ),
        ),
    ));

    if ($posts) {
        echo '<ul class="child-term-posts">';
        foreach ($posts as $post) {
            setup_postdata($post);

            $title = get_the_title($post);
            $permalink = get_permalink($post);

            $pd_minimum_speed = get_field('pd_minimum_speed', $post->ID);
            $pd_maximum_speed = get_field('pd_maximum_speed', $post->ID);
            $pd_torque = get_field('pd_torque', $post->ID);
            $pd_weight = get_field('pd_weight', $post->ID);
            $pd_voltage_details = get_field('pd_voltage_details', $post->ID);
            $pd_minimum_nominal_torque = get_field('pd_minimum_nominal_torque', $post->ID);
            $pd_maximum_nominal_torque = get_field('pd_maximum_nominal_torque', $post->ID);
        }
        wp_reset_postdata();
        echo '</ul>';
    } else {
        echo '<p>No posts found for this category.</p>';
    }

    wp_die();
}
/* ************** Washer Systemn Pump Filter ************** */


/* ************** Washer Tank Capacity Filter ************** */
function get_washer_tank_capacity_filter()
{
    ob_start();

    if (is_user_logged_in()) {
        $posts = get_posts(array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'orderby' => 'ID',
            'posts_per_page' => 6,
            'meta_query' => array(
                array(
                    'key' => 'espd_ability',
                    'value' => '',
                    'compare' => '!=',
                )
            )
        ));

        if ($posts) {
            echo '<ul class="child-term-posts">';
            foreach ($posts as $post) {
                setup_postdata($post);

                $title = get_the_title($post);
                $permalink = get_permalink($post);
                $thumbnail_url = get_the_post_thumbnail_url($post->ID);

                $espd_ability = get_field('espd_ability', $post->ID);
                $espd_type_of_plug = get_field('espd_type_of_plug', $post->ID);
                $espd_number_of_bombs = get_field('espd_number_of_bombs', $post->ID);
                $espd_level_sensor = get_field('espd_level_sensor', $post->ID);
                $pd_voltage_details = get_field('pd_voltage_details', $post->ID);

                echo '<li class="product-item">';

                if ($thumbnail_url) {
                    echo '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr($title) . '">';
                }

                echo '<a href="' . esc_url($permalink) . '"><strong>' . esc_html($title) . '</strong></a>';

                echo '<ul class="acf-details">';
                if ($espd_ability) {
                    echo '<li>' . esc_html($espd_ability) . '</li>';
                }
                if ($espd_type_of_plug) {
                    echo '<li>' . esc_html($espd_type_of_plug) . '</li>';
                }
                if ($espd_number_of_bombs) {
                    echo '<li>' . esc_html($espd_number_of_bombs) . '</li>';
                }
                if ($espd_level_sensor) {
                    echo '<li>' . esc_html($espd_level_sensor) . '</li>';
                }
                if ($pd_voltage_details) {
                    echo '<li>';
                    foreach ($pd_voltage_details as $voltage_detail) {
                        echo esc_html($voltage_detail['voltage']) . ' / ';
                    }
                    echo '</li>';
                }
                echo '</ul>';

                $terms = get_the_terms($post->ID, 'product-attribute');
                if (!empty($terms)) {
                    $term_names = array();
                    foreach ($terms as $term) {
                        $term_names[] = $term->name;
                    }
                    echo '<ul class="product-attributes"><li>' . esc_html(implode(', ', $term_names)) . '</li></ul>';
                }

                echo '</li>';
            }
            wp_reset_postdata();
            echo '</ul>';
        }
    }

    return ob_get_clean();
}

add_shortcode('washer_tank_capacity_liter_filter', 'get_washer_tank_capacity_filter');
/* ************** Washer Tank Capacity Filter ************** */


/* ************** Drive System Market Single Page Product Filter Shortcode ************** */
function drive_system_market_single_page_product_filter()
{
    ob_start();
?>

    <?php
    $current_term = get_queried_object();

    $child_terms = get_terms([
        'taxonomy' => 'market-sector',
        'hide_empty' => false,
        'parent' => $current_term->term_id,
    ]);

    $default_img = get_stylesheet_directory_uri() . '/assets/images/product-line-default-img.png';
    ?>

    <div class="drive-system-market-inner">
        <?php if (!empty($child_terms)): ?>
            <div class="ds-market-filter">
                <div class="ds-market-filter-block">
                    <div class="row">
                        <?php $term_counter = 1;
                        foreach ($child_terms as $term):
                            $child_term_id = $term->term_id;
                            $child_term_name = $term->name;
                        ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="ds-market-filter-link <?php echo ($term_counter === 1) ? 'show' : ''; ?>">
                                    <a href="javascript:;" class="ds-market-filter-btn"
                                        data-term-id="<?php echo $child_term_id; ?>">
                                        <span><?php echo $child_term_name; ?></span>
                                    </a>
                                    <div class="filter-marker">
                                        <div class="filter-marker-fill"></div>
                                    </div>
                                </div>
                            </div>
                        <?php $term_counter++;
                        endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="ds-market-filter-cards">
            <div class="ds-market-filter-cards-block">
                <div class="row">
                    <?php
                    if (!empty($child_terms)):
                        $first_term_id = $child_terms[0]->term_id;
                        $default_terms = get_terms(array(
                            'taxonomy' => 'market-sector',
                            'hide_empty' => false,
                            'parent' => $first_term_id,
                        ));

                        foreach ($default_terms as $term):
                            $default_terms_id = $term->term_id;
                            $default_terms_name = $term->name;
                            $thumbnail_image = get_field('thumbnail_image', 'market-sector_' . $default_terms_id);
                            $image_url = !empty($thumbnail_image['url']) ? $thumbnail_image['url'] : $default_img;
                    ?>
                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                                <a href="javascript:;" class="ds-market-filter-card"
                                    data-term-id="<?php echo $default_terms_id; ?>">
                                    <div class="market-card-bg"
                                        style="background:url('<?php echo $image_url; ?>') no-repeat center; background-size: cover;">
                                    </div>
                                    <div class="title">
                                        <p><?php echo $default_terms_name; ?></p>
                                    </div>
                                </a>
                            </div>
                    <?php endforeach;
                    endif;
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- DS Market Products Start -->

    <div class="ds-market-products"> </div>

    <!-- DS Market Products End -->

<?php
    return ob_get_clean();
}
add_shortcode("drive_system_market_product_filter", "drive_system_market_single_page_product_filter");
/* ************** Drive System Market Single Page Product Filter Shortcode ************** */


/* ************** Product Detail Page Banner Section Shortcode ************** */
function get_product_details()
{
    ob_start();
?>
    <?php
    $product_title = get_the_title();
    $pdp_speed_title = get_field('pdp_speed_title', 'option');
    $pdp_torque_title = get_field('pdp_torque_title', 'option');
    $pdp_voltage_title = get_field('pdp_voltage_title', 'option');
    $pdp_weight_title = get_field('pdp_weight_title', 'option');
    $pdp_nominal_torque_title = get_field('pdp_nominal_torque_title', 'option');
    $pd_product_slider_images = get_field('pd_product_slider_images');
    $pd_minimum_speed = get_field('pd_minimum_speed');
    $pd_maximum_speed = get_field('pd_maximum_speed');
    $pd_torque = get_field('pd_torque');
    $pd_weight = get_field('pd_weight');
    $pd_voltage_details = get_field('pd_voltage_details');
    $pd_minimum_nominal_torque = get_field('pd_minimum_nominal_torque');
    $pd_maximum_nominal_torque = get_field('pd_maximum_nominal_torque');
    $download_sheet_files = get_field('download_sheet_files');
    $more_information_button = get_field('more_information_button', 'option');
    $wiper_system_details = get_the_content();
    ?>
    <div class="product-inner-page-card">
        <?php if (!empty($pd_product_slider_images)): ?>
            <div class="product-slider-container">
                <?php
                $small_slider_img = '';
                $slider_img = '';

                if (!empty($pd_product_slider_images)):
                    foreach ($pd_product_slider_images as $slider_image_data):
                        $pdi_image = $slider_image_data['pdi_image'];

                        if ($pdi_image):
                            $small_slider_img .= '<div class="swiper-slide">
                                                    <div class="img-container">
                                                        <figure>
                                                            <img src="' . $pdi_image['url'] . '" alt="' . $pdi_image['alt'] . '">
                                                        </figure>
                                                    </div>
                                                </div>';

                            $slider_img .= '<div class="swiper-slide">
                                                <a href="' . $pdi_image['url'] . '" class="img-container" data-fancybox="gallery">
                                                    <figure>
                                                        <img src="' . $pdi_image['url'] . '" alt="' . $pdi_image['alt'] . '">
                                                    </figure>
                                                </a>
                                            </div>';
                        endif;
                    endforeach;
                endif;
                ?>

                <?php if ($small_slider_img): ?>
                    <div class="slider-container slider-container-1">
                        <div class="swiper slider-thumb">
                            <div class="swiper-wrapper">
                                <?php echo $small_slider_img; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="slider-container slider-container-2">
                    <div class="search-container">
                        <div class="search-icon">
                            <figure>
                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/search-icon.svg"
                                    alt="search-icon">
                            </figure>
                        </div>
                    </div>
                    <?php if ($slider_img): ?>
                        <div class="swiper slider-main">
                            <div class="swiper-wrapper">
                                <?php echo $slider_img; ?>
                            </div>
                        </div>
                    <?php endif; ?>
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
            </div>
        <?php endif; ?>
        <div class="product-details-container">
            <?php if ($product_title): ?>
                <div class="heading">
                    <div class="title">
                        <h5>
                            <?php
                            echo esc_html($product_title);
                            /*if (preg_match('/^\d+$/', $product_title)) {
                                echo esc_html($product_title . ' Motor');
                            } else {
                                echo esc_html($product_title);
                            }*/ ?>
                        </h5>
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

            <?php if ($pd_minimum_speed || $pd_minimum_speed || $pd_torque || $pd_weight || $pd_minimum_nominal_torque || $pd_maximum_nominal_torque || !empty($pd_voltage_details)): ?>
                <div class="product-details-main">
                    <div class="product-detail-block">
                        <?php if ($pd_maximum_speed || $pd_minimum_speed): ?>
                            <div class="product-detail">
                                <?php if ($pdp_speed_title): ?>
                                    <div class="title">
                                        <p><?php echo $pdp_speed_title; ?></p>
                                    </div>
                                <?php endif; ?>
                                <div class="desc">
                                    <?php if ($pd_minimum_speed): ?>
                                        <p><?php echo esc_html($pd_minimum_speed); ?></p>
                                    <?php endif; ?>
                                    <?php if ($pd_minimum_speed && $pd_maximum_speed): ?>
                                        <span>-</span>
                                    <?php endif; ?>
                                    <?php if ($pd_maximum_speed): ?>
                                        <p><?php echo esc_html($pd_maximum_speed); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($pd_torque): ?>
                            <div class="product-detail">
                                <?php if ($pdp_torque_title): ?>
                                    <div class="title">
                                        <p><?php echo $pdp_torque_title; ?></p>
                                    </div>
                                <?php endif; ?>
                                <div class="desc">
                                    <span>
                                        </span>
                                            <p><?php echo $pd_torque; ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($pd_voltage_details)): ?>
                            <div class="product-detail">
                                <?php if ($pdp_voltage_title): ?>
                                    <div class="title">
                                        <p><?php echo $pdp_voltage_title; ?></p>
                                    </div>
                                <?php endif; ?>
                                <div class="desc">
                                    <?php
                                    $voltages = [];

                                    foreach ($pd_voltage_details as $voltage_detail):
                                        if (!empty($voltage_detail['voltage'])):
                                            $voltages[] = $voltage_detail['voltage'];
                                        endif;
                                    endforeach;

                                    if (!empty($voltages)):
                                        echo '<p>' . implode(' / ', $voltages) . '</p>';
                                    endif;
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($pd_weight): ?>
                            <div class="product-detail">
                                <?php if ($pdp_weight_title): ?>
                                    <div class="title">
                                        <p><?php echo $pdp_weight_title; ?></p>
                                    </div>
                                <?php endif; ?>
                                <div class="desc">
                                    <p><?php echo $pd_weight; ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if ($pd_minimum_nominal_torque || $pd_maximum_nominal_torque): ?>
                            <div class="product-detail">
                                <?php if ($pdp_nominal_torque_title): ?>
                                    <div class="title">
                                        <p><?php echo $pdp_nominal_torque_title; ?></p>
                                    </div>
                                <?php endif; ?>
                                <div class="desc">
                                    <?php echo $pd_minimum_nominal_torque ? '<p>' . $pd_minimum_nominal_torque . '</p>' : ''; ?>
                                    <?php if ($pd_minimum_nominal_torque && $pd_maximum_nominal_torque): ?>
                                        <span>-</span>
                                    <?php endif; ?>
                                    <?php echo $pd_maximum_nominal_torque ? '<p>' . $pd_maximum_nominal_torque . '</p>' : ''; ?>
                                </div>
                            </div>
                        <?php endif; ?>
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



            // ALEX: Antiguo. Lo he modificado
            // $product_attribute = get_terms(array(
            //     'taxonomy' => 'product-attribute',
            //     'hide_empty' => false,
            // ));

            /*
            ?>

            <div class="product-details-footer">
                <div class="product-details-footer-block">
                    <?php if (!empty($product_attribute)): ?>
                        <div class="tags-container">
                            <?php foreach ($product_attribute as $attribute): ?>
                                <div class="tag">
                                    <p><?php echo $attribute->name; ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($more_information_button): ?>
                        <div class="more-info-btn-container">
                            <a href="<?php echo $more_information_button['url']; ?>"
                                target="<?php echo $more_information_button['target']; ?>" class="more-info-btn">
                                <span><?php echo $more_information_button['title']; ?></span>
                                <span>
                                    <img decoding="async"
                                        src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/location-dis-doga-arrow.png"
                                        alt="location-dis-doga-arrow">
                                </span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            */
            ?>

            <?php
            $post_id = get_the_ID();
            $product_attributes = get_the_terms($post_id, 'product-attribute');
            ?>
            <?php if (
                (! empty($product_attributes) && ! is_wp_error($product_attributes))
                || ! empty($more_information_button)
            ): ?>
                <div class="product-details-footer">
                    <div class="product-details-footer-block">

                        <?php if (! empty($product_attributes) && ! is_wp_error($product_attributes)): ?>
                            <div class="tags-container">
                                <?php foreach ($product_attributes as $attribute): ?>
                                    <div class="tag">
                                        <p><?php echo esc_html($attribute->name); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                         <?php
                        $external_url = get_field('external_url');

                        if (!empty($external_url)) : ?>
                            <div class="more-info-btn-container">
                                <a href="<?php echo esc_url($external_url); ?>" class="more-info-btn" target="_blank" rel="noopener noreferrer">
                                    <span>Más información</span>
                                    <span>
                                        <img decoding="async" src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/location-dis-doga-arrow.png'); ?>" alt="flecha">
                                    </span>
                                </a>
                            </div>
                        <?php elseif (!empty($more_information_button)) : ?>
                            <div class="more-info-btn-container">
                                <a href="<?php echo esc_url($more_information_button['url']); ?>" class="more-info-btn" target="<?php echo !empty($more_information_button['target']) ? esc_attr($more_information_button['target']) : '_self'; ?>">
                                    <span><?php echo esc_html($more_information_button['title']); ?></span>
                                    <span>
                                        <img decoding="async" src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/location-dis-doga-arrow.png'); ?>" alt="flecha">
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
add_shortcode("product_details_shortcode", "get_product_details");
/* ************** Product Detail Page Banner Section Shortcode ************** */
