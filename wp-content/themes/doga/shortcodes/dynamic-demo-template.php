<?php

/* ************** Custom Market Shortcode ************** */
function get_custom_markets() {
    ob_start();

    if(is_user_logged_in()):
    ?>
        <div class="custom-market-parent-block">
            <div class="row">
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="javascript:void;" class="market-card">
                        <div class="market-card-bg" style="background:url('/wp-content/themes/doga/assets/images/m-image.png') no-repeat center; background-size: cover">
                        </div>
                        <div class="title">
                            <p>PRECISION AGRICULTURE</p>
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
            </div>
        </div>
    <?php
    endif;
    return ob_get_clean();
}
add_shortcode("custom_markets","get_custom_markets");
/* ************** Custom Market Shortcode ************** */



/* ************** Washer System Product Line Tank Capacity Shortcode ************** */
function washer_system_washer_tank_capacity_filter() {
    ob_start();
    ?>
        <!-- Write your code here -->
    <?php
    return ob_get_clean();
}
add_shortcode("washer_tank_capacity_filter","washer_system_washer_tank_capacity_filter");
/* ************** Washer System Product Line Tank Capacity Shortcode ************** */


?>