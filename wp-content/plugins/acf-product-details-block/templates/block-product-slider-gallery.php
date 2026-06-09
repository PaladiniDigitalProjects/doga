<?php
/**
 * Template: Galería / slider de producto (ACF) con scroll horizontal.
 *
 * Variables esperadas:
 * - $apdb_slider_images (array) lista de imágenes normalizadas (url, alt, caption)
 * - $apdb_slider_title  (string) título del bloque
 */

if ( empty( $apdb_slider_images ) || ! is_array( $apdb_slider_images ) ) {
    return;
}

$images = $apdb_slider_images;
$title  = ! empty( $apdb_slider_title ) ? $apdb_slider_title : '';
$unique_id = 'apdb-slider-' . uniqid();
?>
<section class="apdb-product-slider" data-apdb-slider data-slider-id="<?php echo esc_attr( $unique_id ); ?>" style="position:relative">
    <button class="apdb-product-slider__zoom" type="button" aria-label="<?php esc_attr_e( 'Ampliar', 'acf-product-details-block' ); ?>" data-action="zoom">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
    </button>

    <div class="apdb-product-slider__viewport">
        <?php if ( count( $images ) > 1 ) : ?>
            <button class="apdb-product-slider__arrow apdb-product-slider__arrow--prev" type="button" aria-label="<?php esc_attr_e( 'Imagen anterior', 'acf-product-details-block' ); ?>" data-action="prev"></button>
        <?php endif; ?>

        <div class="apdb-product-slider__slides" data-slides-container>
            <?php foreach ( $images as $index => $image ) : ?>
                <?php
                $img_url   = $image['url'];
                $img_alt   = isset( $image['alt'] ) ? $image['alt'] : '';
                $caption   = isset( $image['caption'] ) ? $image['caption'] : '';
                $use_lazy  = $index > 2;
                ?>
                <figure class="apdb-product-slider__slide" data-index="<?php echo esc_attr( $index ); ?>">
                    <?php if ( $use_lazy ) : ?>
                        <img data-src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" loading="lazy" />
                    <?php else : ?>
                        <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" />
                    <?php endif; ?>
                    <?php if ( $caption ) : ?>
                        <figcaption class="apdb-product-slider__caption">
                            <?php echo esc_html( $caption ); ?>
                        </figcaption>
                    <?php endif; ?>
                </figure>
            <?php endforeach; ?>
        </div>

        <?php if ( count( $images ) > 1 ) : ?>
            <button class="apdb-product-slider__arrow apdb-product-slider__arrow--next" type="button" aria-label="<?php esc_attr_e( 'Imagen siguiente', 'acf-product-details-block' ); ?>" data-action="next"></button>
        <?php endif; ?>
    </div>

    <?php if ( count( $images ) > 1 ) : ?>
        <div class="apdb-product-slider__thumbs" data-thumbs-container>
            <?php foreach ( $images as $index => $image ) : ?>
                <?php
                $thumb_url = $image['url'];
                $thumb_alt = isset( $image['alt'] ) ? $image['alt'] : '';
                $is_active = 0 === $index ? ' is-active' : '';
                ?>
                <button class="apdb-product-slider__thumb<?php echo esc_attr( $is_active ); ?>" type="button" data-index="<?php echo esc_attr( $index ); ?>">
                    <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $thumb_alt ); ?>" />
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Lightbox mejorado con navegación horizontal -->
    <div class="apdb-lightbox" aria-hidden="true" data-lightbox>
        <div class="apdb-lightbox__backdrop" data-apdb-lightbox-close></div>
        <div class="apdb-lightbox__content">
            <button class="apdb-lightbox__close" type="button" aria-label="<?php esc_attr_e( 'Cerrar', 'acf-product-details-block' ); ?>" data-apdb-lightbox-close>&times;</button>

            <?php if ( count( $images ) > 1 ) : ?>
                <button class="apdb-lightbox__arrow apdb-lightbox__arrow--prev" type="button" aria-label="<?php esc_attr_e( 'Imagen anterior', 'acf-product-details-block' ); ?>" data-lightbox-action="prev"></button>
                <button class="apdb-lightbox__arrow apdb-lightbox__arrow--next" type="button" aria-label="<?php esc_attr_e( 'Imagen siguiente', 'acf-product-details-block' ); ?>" data-lightbox-action="next"></button>
            <?php endif; ?>

            <div class="apdb-lightbox__slides" data-lightbox-slides>
                <?php foreach ( $images as $index => $image ) : ?>
                    <?php
                    $img_url = $image['url'];
                    $img_alt = isset( $image['alt'] ) ? $image['alt'] : '';
                    $caption = isset( $image['caption'] ) ? $image['caption'] : '';
                    ?>
                    <figure class="apdb-lightbox__slide" data-lightbox-index="<?php echo esc_attr( $index ); ?>">
                        <img class="apdb-lightbox__image" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" />
                        <?php if ( $caption ) : ?>
                            <figcaption class="apdb-lightbox__caption">
                                <?php echo esc_html( $caption ); ?>
                            </figcaption>
                        <?php endif; ?>
                    </figure>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
