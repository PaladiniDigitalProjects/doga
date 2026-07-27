<?php
/**
 * Template: Detalles de producto (ACF).
 *
 * Variables esperadas:
 * - $apdb_post_id  (int)
 * - $apdb_fields   (array) campos ACF del grupo
 * - $apdb_title    (string) título del bloque
 */

if ( ! isset( $apdb_post_id, $apdb_fields ) || ! is_array( $apdb_fields ) ) {
    return;
}
?>
<section class="apdb-product-details">
    <dl class="apdb-product-details__list">
        <?php
        foreach ( $apdb_fields as $field ) {

            // Campos combinados ya llevan el valor precalculado en '_value'.
            if ( isset( $field['_value'] ) ) {
                $formatted = esc_html( $field['_value'] );
            } else {
                $value = get_field( $field['name'], $apdb_post_id );

                // Saltar si está vacío (incluye arrays vacíos de repeaters).
                if ( ! isset( $value ) || $value === '' || $value === null || $value === false || ( is_array( $value ) && empty( $value ) ) ) {
                    continue;
                }
                // Campos WYSIWYG: preservar el HTML (listas, negritas, enlaces…)
                // en vez de aplanarlo con apdb_format_acf_value (wp_strip_all_tags).
                if ( 'wysiwyg' === ( $field['type'] ?? '' ) ) {
                    $formatted = wp_kses_post( $value );
                } else {
                    $formatted = apdb_format_acf_value( $value );
                }
                if ( trim( wp_strip_all_tags( (string) $formatted ) ) === '' ) continue;
            }
            ?>
            <div class="apdb-product-details__row">
                <dt class="apdb-product-details__label">
                    <?php echo esc_html( $field['label'] ); ?>
                </dt>
                <dd class="apdb-product-details__value">
                    <?php echo $formatted; ?>
                </dd>
            </div>
            <?php
        }
        ?>
    </dl>
</section>
