<?php
/**
 * Plugin Name: PDS ACF Product Details Block
 * Description: Bloque de Gutenberg que muestra automáticamente los campos ACF del grupo "product details" en productos.
 * Author: Daniel Paladini
 * Version: 1.1.0
 * Text Domain: acf-product-details-block
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registro del bloque de Gutenberg.
 */
function apdb_register_acf_product_details_block() {

    // JS del editor (bloques).
    wp_register_script(
        'apdb-editor-script',
        plugins_url( 'js/block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-server-side-render', 'wp-data' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'js/block.js' )
    );

    // JS de frontend (slider + lightbox) compartido por los bloques.
    wp_register_script(
        'apdb-frontend-script',
        plugins_url( 'js/slider.js', __FILE__ ),
        array(),
        filemtime( plugin_dir_path( __FILE__ ) . 'js/slider.js' ),
        true
    );

    // CSS (front + editor).
    wp_register_style(
        'apdb-design',
        plugins_url( 'css/design.css', __FILE__ ),
        array(),
        filemtime( plugin_dir_path( __FILE__ ) . 'css/design.css' )
    );

    wp_register_style(
        'apdb-style',
        plugins_url( 'css/style.css', __FILE__ ),
        array( 'apdb-design' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'css/style.css' )
    );

    // Bloque 1: detalles de producto (campos de texto, select, etc.).
    register_block_type(
        'apdb/acf-product-details',
        array(
            'editor_script'   => 'apdb-editor-script',
            'script'          => 'apdb-frontend-script',
            'style'           => array( 'apdb-design', 'apdb-style' ),
            'render_callback' => 'apdb_render_acf_product_details_block',
            'uses_context'    => array( 'postId', 'postType' ),
            'attributes'      => array(
                'title'             => array( 'type' => 'string',  'default' => 'Detalles del producto' ),
                'ghubLinkToProduct' => array( 'type' => 'boolean', 'default' => false ),
            ),
            'post_types'      => array( 'product' ),
        )
    );

    // Bloque 2: galería/slider de imágenes de producto a partir de ACF.
    register_block_type(
        'apdb/product-slider-gallery',
        array(
            'editor_script'   => 'apdb-editor-script',
            'script'          => 'apdb-frontend-script',
            'style'           => array( 'apdb-design', 'apdb-style' ),
            'render_callback' => 'apdb_render_product_slider_gallery_block',
            'uses_context'    => array( 'postId', 'postType' ),
            'attributes'      => array(
                'title'             => array( 'type' => 'string',  'default' => 'Galería de producto' ),
                'ghubLinkToProduct' => array( 'type' => 'boolean', 'default' => false ),
            ),
            'post_types'      => array( 'product' ),
        )
    );
}
add_action( 'init', 'apdb_register_acf_product_details_block' );

/**
 * Render dinámico del bloque: muestra los campos ACF del grupo "product details".
 */
function apdb_render_acf_product_details_block( $attributes, $content = '', $block = null ) {

    // ACF es obligatorio.
    if ( ! function_exists( 'get_field' ) || ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
        return '';
    }

    // Obtener el ID del post: contexto del bloque (frontend/template) > global $post (REST con post_id).
    $post_id = null;

    if ( $block && ! empty( $block->context['postId'] ) ) {
        $post_id = (int) $block->context['postId'];
    }
    if ( ! $post_id ) {
        $post_id = get_the_ID() ?: null;
    }
    if ( ! $post_id ) {
        global $post;
        $post_id = isset( $post->ID ) ? (int) $post->ID : null;
    }

    if ( ! $post_id ) {
        return '';
    }

    /**
     * Obtener grupos ACF asignados a este post y localizar el que
     * tenga en el título "product details" (case-insensitive).
     */
    $groups = acf_get_field_groups(
        array(
            'post_id' => $post_id,
        )
    );

    if ( empty( $groups ) ) {
        if ( is_admin() ) {
            return '<p><em>No se han encontrado grupos de campos ACF asignados a este producto.</em></p>';
        }

        return '';
    }

    $target_group = null;

    foreach ( $groups as $group ) {
        // "product detail" (singular) hace match tanto con "Product Detail"
        // como con "Product Details" — el grupo real se llama "Product Detail".
        if ( ! empty( $group['title'] ) && false !== stripos( $group['title'], 'product detail' ) ) {
            $target_group = $group;
            break;
        }
    }

    // Si no encontramos uno cuyo título contenga "product detail",
    // usamos el primero como fallback. IMPORTANTE: no imprimir aquí (echo) —
    // un render_callback debe DEVOLVER string; hacer echo rompe las cabeceras
    // del admin ("headers already sent") y saca el aviso al inicio de la página.
    if ( ! $target_group ) {
        $target_group = reset( $groups );
    }

    $group_key = $target_group['key'];
    $fields    = acf_get_fields( $group_key );

    if ( empty( $fields ) ) {
        if ( is_admin() ) {
            return '<p><em>El grupo ACF seleccionado no contiene campos.</em></p>';
        }

        return '';
    }

    // Aplanar repeater pd_voltage_details → campo "Voltage (V): 12 / 24".
    // Debe ir ANTES del filtro de tipos para que el repeater no sea eliminado.
    foreach ( $fields as $i => $f ) {
        if ( $f['name'] === 'pd_voltage_details' ) {
            $rows = get_field( 'pd_voltage_details', $post_id );
            if ( ! empty( $rows ) && is_array( $rows ) ) {
                $values = array_filter( array_map( function( $row ) {
                    return isset( $row['voltage'] ) && $row['voltage'] !== '' ? $row['voltage'] : null;
                }, $rows ) );
                if ( ! empty( $values ) ) {
                    $fields[ $i ] = array(
                        'name'   => '_voltage',
                        'label'  => 'Voltage (V)',
                        'type'   => '_combined',
                        '_value' => implode( ' / ', $values ),
                    );
                } else {
                    unset( $fields[ $i ] );
                }
            } else {
                unset( $fields[ $i ] );
            }
            break;
        }
    }
    $fields = array_values( $fields );

    // Excluir tipos que se gestionan con bloques dedicados (slider, descarga, etc.).
    $excluded_types = array( 'file', 'image', 'gallery', 'repeater', 'flexible_content', 'tab', 'message', 'accordion' );
    // Excluir campos concretos que no se quieren mostrar.
    $excluded_names = array( 'pd_minimum_nominal_speed', 'pd_maximum_nominal_speed' );
    // En la plantilla de productos Wipers ("wipers-single") no aplica el
    // campo "Level Sensor" (espd_level_sensor) → se oculta solo ahí.
    if ( 'wipers-single' === get_page_template_slug( $post_id ) ) {
        $excluded_names[] = 'espd_level_sensor';
    }
    $fields = array_values( array_filter( $fields, function( $f ) use ( $excluded_types, $excluded_names ) {
        return ! in_array( $f['type'] ?? '', $excluded_types, true )
            && ! in_array( $f['name'] ?? '', $excluded_names, true );
    } ) );

    // Renombrar labels con unidades.
    $label_overrides = array(
        'pd_weight'         => 'Weight (Kg)',
        'pd_torque'         => 'Power (W)',
    );
    foreach ( $fields as &$f ) {
        if ( isset( $label_overrides[ $f['name'] ] ) ) {
            $f['label'] = $label_overrides[ $f['name'] ];
        }
    }
    unset( $f );

    // Pares de campos min/max que se muestran como rango "min–max".
    $range_pairs = array(
        array( 'min' => 'pd_minimum_nominal_torque', 'max' => 'pd_maximum_nominal_torque', 'label' => 'Nominal Torque (Nm)' ),
        array( 'min' => 'pd_minimum_speed',          'max' => 'pd_maximum_speed',          'label' => 'Nominal Speed (rpm)' ),
    );
    foreach ( $range_pairs as $pair ) {
        $min_idx = null;
        $max_idx = null;
        foreach ( $fields as $i => $f ) {
            if ( $f['name'] === $pair['min'] ) $min_idx = $i;
            if ( $f['name'] === $pair['max'] ) $max_idx = $i;
        }
        if ( $min_idx === null || $max_idx === null ) continue;
        $val_min = get_field( $pair['min'], $post_id );
        $val_max = get_field( $pair['max'], $post_id );
        if ( $val_min !== null && $val_min !== '' && $val_max !== null && $val_max !== '' ) {
            $first  = min( $min_idx, $max_idx );
            $second = max( $min_idx, $max_idx );
            $fields[ $first ] = array(
                'name'   => '_range_' . $pair['label'],
                'label'  => $pair['label'],
                'type'   => '_combined',
                '_value' => $val_min . ' – ' . $val_max,
            );
            array_splice( $fields, $second, 1 );
        }
    }
    $fields = array_values( $fields );

    // Datos para la plantilla.
    $apdb_post_id = $post_id;
    $apdb_fields  = $fields;
    $apdb_title   = ! empty( $attributes['title'] ) ? $attributes['title'] : '';

    ob_start();
    include plugin_dir_path( __FILE__ ) . 'templates/block-product-details.php';
    return ob_get_clean();
}

/**
 * Bloque de galería de producto basado en ACF.
 *
 * Según tu descripción:
 * - "Product Slider Images" es un campo REPEATER que, por cada fila,
 *   contiene al menos un subcampo de imagen (que se usa en el slider).
 * - "Product Thumbnail Slider Section" es un campo TAB (solo organiza UI).
 *
 * Este bloque:
 * - Lee el repeater "Product Slider Images" (asumimos field name: product_slider_images).
 * - En cada fila intenta localizar automáticamente un subcampo de imagen:
 *   - Si encuentra un array con clave "url" → lo usa como imagen.
 *   - Si encuentra un ID numérico → lo convierte a URL de imagen.
 * - Pinta una imagen por fila, como si fuera una galería/slider.
 */
function apdb_render_product_slider_gallery_block( $attributes, $content = '', $block = null ) {

    if ( ! function_exists( 'get_field' ) ) {
        return '';
    }

    // Obtener el ID del post: contexto del bloque (frontend/template) > global $post (REST con post_id).
    $post_id = null;

    if ( $block && ! empty( $block->context['postId'] ) ) {
        $post_id = (int) $block->context['postId'];
    }
    if ( ! $post_id ) {
        $post_id = get_the_ID() ?: null;
    }
    if ( ! $post_id ) {
        global $post;
        $post_id = isset( $post->ID ) ? (int) $post->ID : null;
    }

    if ( ! $post_id ) {
        return '';
    }

    /**
     * Localizar el repeater "Product Slider Images" por LABEL,
     * sin depender del field name exacto.
     */
    $repeater_field_name = 'product_slider_images';

    if ( function_exists( 'acf_get_field_groups' ) && function_exists( 'acf_get_fields' ) ) {
        $groups = acf_get_field_groups(
            array(
                'post_id' => $post_id,
            )
        );

        if ( ! empty( $groups ) ) {
            foreach ( $groups as $group ) {
                $fields = acf_get_fields( $group['key'] );
                if ( empty( $fields ) ) {
                    continue;
                }

                foreach ( $fields as $field ) {
                    if ( ! empty( $field['label'] ) && false !== stripos( $field['label'], 'Product Slider Images' ) ) {
                        $repeater_field_name = $field['name'];
                        break 2;
                    }
                }
            }
        }
    }

    // Leemos el repeater usando el field name detectado.
    $rows = get_field( $repeater_field_name, $post_id );

    if ( empty( $rows ) || ! is_array( $rows ) ) {
        if ( is_admin() ) {
            return '<p><em>No se han encontrado filas en el repeater ACF cuyo label contenga "Product Slider Images" para este producto.</em></p>';
        }

        return '';
    }

    // Normalizar todas las filas a un array de imágenes (url, alt, caption opcional).
    $images = array();

    foreach ( $rows as $row ) {
        if ( ! is_array( $row ) ) {
            continue;
        }

        $image_data = null;

        foreach ( $row as $sub_value ) {
            // Caso array con 'url' (formato típico de imagen ACF).
            if ( is_array( $sub_value ) && ! empty( $sub_value['url'] ) ) {
                $image_data = $sub_value;
                break;
            }

            // Caso ID de adjunto.
            if ( is_int( $sub_value ) || ( is_string( $sub_value ) && ctype_digit( $sub_value ) ) ) {
                $attachment_id = (int) $sub_value;
                $url           = wp_get_attachment_image_url( $attachment_id, 'large' );
                if ( $url ) {
                    $image_data = array(
                        'url'     => $url,
                        'alt'     => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
                        'caption' => get_post_field( 'post_excerpt', $attachment_id ),
                    );
                    break;
                }
            }
        }

        if ( $image_data && ! empty( $image_data['url'] ) ) {
            $images[] = $image_data;
        }
    }

    if ( empty( $images ) ) {
        return '';
    }

    $apdb_slider_images = $images;
    $apdb_slider_title  = ! empty( $attributes['title'] ) ? $attributes['title'] : '';

    ob_start();
    include plugin_dir_path( __FILE__ ) . 'templates/block-product-slider-gallery.php';
    return ob_get_clean();
}

/**
 * Limpia HTML y caracteres no deseados de un valor de texto.
 * Útil para campos que vienen con HTML pegado desde Excel/Sheets.
 */
function apdb_clean_text_value( $value ) {
    if ( ! is_string( $value ) ) {
        return $value;
    }

    // Eliminar HTML tags
    $value = wp_strip_all_tags( $value );

    // Limpiar entidades HTML que puedan quedar
    $value = html_entity_decode( $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

    // Limpiar múltiples espacios/saltos de línea
    $value = preg_replace( '/\s+/', ' ', $value );

    // Limpiar caracteres especiales problemáticos
    $value = str_replace( array( '&nbsp;', '\n', '\r', '\t' ), ' ', $value );

    // Trim final
    $value = trim( $value );

    return $value;
}

/**
 * Formateo genérico de valores ACF para evitar mostrar "Array".
 *
 * - Arrays simples: "valor1, valor2, valor3".
 * - Repeater / arrays de arrays: lista <ul> con cada fila.
 * - Arrays asociativos simples: "valor1 – valor2".
 */
function apdb_format_acf_value( $value ) {

    if ( is_array( $value ) ) {

        if ( $value === array() ) {
            return '';
        }

        $keys       = array_keys( $value );
        $is_numeric = $keys === range( 0, count( $value ) - 1 );

        // Caso: array indexado.
        if ( $is_numeric ) {
            $first = reset( $value );

            // Caso repeater: array de arrays (cada fila tiene subcampos).
            if ( is_array( $first ) ) {
                $rows_html = '<ul class="apdb-product-details__nested-list">';

                foreach ( $value as $row ) {
                    if ( ! is_array( $row ) ) {
                        continue;
                    }

                    $parts = array();

                    foreach ( $row as $sub_value ) {
                        if ( $sub_value === '' || $sub_value === null ) {
                            continue;
                        }
                        if ( is_array( $sub_value ) ) {
                            continue;
                        }

                        // Limpiar HTML antes de escapar
                        $cleaned = apdb_clean_text_value( $sub_value );
                        if ( $cleaned !== '' ) {
                            $parts[] = esc_html( $cleaned );
                        }
                    }

                    if ( ! empty( $parts ) ) {
                        $rows_html .= '<li>' . implode( ' – ', $parts ) . '</li>';
                    }
                }

                $rows_html .= '</ul>';

                return $rows_html;
            }

            // Array simple de valores escalares.
            $escaped = array_map(
                function( $item ) {
                    $cleaned = apdb_clean_text_value( $item );
                    return esc_html( (string) $cleaned );
                },
                $value
            );

            // Filtrar valores vacíos después de la limpieza
            $escaped = array_filter( $escaped, function( $item ) {
                return $item !== '';
            } );

            return implode( ', ', $escaped );
        }

        // Caso: array asociativo simple (por ejemplo campo group).
        $parts = array();

        foreach ( $value as $sub_value ) {
            if ( $sub_value === '' || $sub_value === null ) {
                continue;
            }
            if ( is_array( $sub_value ) ) {
                continue;
            }

            // Limpiar HTML antes de escapar
            $cleaned = apdb_clean_text_value( $sub_value );
            if ( $cleaned !== '' ) {
                $parts[] = esc_html( $cleaned );
            }
        }

        return implode( ' – ', $parts );
    }

    // Escalar: limpiar HTML y luego escapar
    $cleaned = apdb_clean_text_value( $value );
    return esc_html( (string) $cleaned );
}

