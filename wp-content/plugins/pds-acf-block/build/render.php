<?php
/**
 * render.php — Renderizado en servidor con soporte completo de estilos Gutenberg.
 *
 * @var array    $attributes  Atributos del bloque
 * @var string   $content     Contenido interior (vacío)
 * @var WP_Block $block       Objeto bloque con contexto
 */

if ( ! function_exists( 'get_field' ) ) {
    return;
}

// ── Contexto & campo ──────────────────────────────────────────────────────────
$post_id    = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
$field_name = isset( $attributes['fieldName'] ) ? sanitize_text_field( $attributes['fieldName'] ) : '';

if ( empty( $field_name ) || ! $post_id ) {
    // Placeholder solo en el editor
    if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        $wrapper_attrs = get_block_wrapper_attributes( [ 'class' => 'acf-field-block acf-field-block--empty' ] );
        printf(
            '<div %s><span class="acf-field-block__placeholder">📋 Campo ACF — configura el campo en la barra lateral</span></div>',
            $wrapper_attrs
        );
    }
    return;
}

// ── Atributos ─────────────────────────────────────────────────────────────────
$display_type = $attributes['displayType'] ?? 'text';
$html_tag     = in_array(
    $attributes['htmlTag'] ?? 'p',
    [ 'p', 'span', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'em', 'li', 'dd', 'dt', 'blockquote' ],
    true
) ? $attributes['htmlTag'] : 'p';

$prefix      = $attributes['prefix']      ?? '';
$suffix      = $attributes['suffix']      ?? '';
$fallback    = $attributes['fallback']    ?? '';
$image_size  = $attributes['imageSize']  ?? 'full';
$image_fit   = $attributes['imageFit']   ?? 'cover';
$image_width = $attributes['imageWidth'] ?? '';
$image_height= $attributes['imageHeight']?? '';
$link_text   = $attributes['linkText']   ?? '';
$link_target = in_array( $attributes['linkTarget'] ?? '_self', [ '_self', '_blank' ], true )
               ? $attributes['linkTarget'] : '_self';
$text_align  = $attributes['textAlign']  ?? '';

// ── Valor del campo ────────────────────────────────────────────────────────────
$value = get_field( $field_name, $post_id );

if ( ( $value === null || $value === '' || $value === false || $value === [] ) ) {
    if ( $fallback !== '' ) {
        $value = $fallback;
    } else {
        return;
    }
}

// ── Clase de alineación de texto ──────────────────────────────────────────────
$extra_class = 'pds-acf-field-block';
if ( $text_align ) {
    $extra_class .= ' has-text-align-' . sanitize_html_class( $text_align );
}

// ── Wrapper attributes (incluye estilos de color, tipografía, spacing, border) ──
$wrapper_attrs = get_block_wrapper_attributes( [ 'class' => $extra_class ] );

// ── Render por tipo ───────────────────────────────────────────────────────────
switch ( $display_type ) {

    // ── Imagen ────────────────────────────────────────────────────────────────
    case 'image':
        $img_url = $img_alt = $img_width_attr = $img_height_attr = '';
        $img_w = $img_h = 0;

        if ( is_array( $value ) && isset( $value['url'] ) ) {
            // ACF retorna array (image field, return format: array)
            $img_url = $value['url'];
            $img_alt = $value['alt'] ?? '';
            $img_w   = $value['width'] ?? 0;
            $img_h   = $value['height'] ?? 0;
        } elseif ( is_numeric( $value ) ) {
            // ACF retorna ID
            $src_data = wp_get_attachment_image_src( (int) $value, $image_size );
            if ( $src_data ) {
                $img_url = $src_data[0];
                $img_w   = $src_data[1];
                $img_h   = $src_data[2];
            }
            $img_alt = get_post_meta( (int) $value, '_wp_attachment_image_alt', true );
        } else {
            $img_url = esc_url( $value );
        }

        if ( ! $img_url ) {
            return;
        }

        // Estilos inline para dimensiones personalizadas
        $img_style_parts = [];
        if ( $image_width )  $img_style_parts[] = 'width:'  . esc_attr( $image_width );
        if ( $image_height ) $img_style_parts[] = 'height:' . esc_attr( $image_height );
        if ( $image_fit !== 'cover' || $image_width || $image_height ) {
            $img_style_parts[] = 'object-fit:' . esc_attr( $image_fit );
        }
        $img_style = $img_style_parts ? ' style="' . implode( ';', $img_style_parts ) . '"' : '';

        if ( $img_w && $img_h && ! $image_width && ! $image_height ) {
            $img_width_attr  = ' width="'  . (int) $img_w . '"';
            $img_height_attr = ' height="' . (int) $img_h . '"';
        }

        printf(
            '<figure %s><img src="%s" alt="%s" loading="lazy"%s%s%s /></figure>',
            $wrapper_attrs,
            esc_url( $img_url ),
            esc_attr( $img_alt ),
            $img_width_attr,
            $img_height_attr,
            $img_style
        );
        break;

    // ── Enlace ────────────────────────────────────────────────────────────────
    case 'link':
        $href = $label = '';
        $rel  = $link_target === '_blank' ? ' rel="noopener noreferrer"' : '';

        // Helper: extraer href + label de un valor simple
        $resolve_link = function( $val ) use ( $link_text ) {
            if ( is_array( $val ) && isset( $val['url'] ) ) {
                return [
                    'href'  => $val['url'],
                    'label' => $link_text ?: ( $val['title'] ?? $val['filename'] ?? $val['url'] ),
                ];
            }
            if ( is_numeric( $val ) ) {
                $att_url = wp_get_attachment_url( (int) $val );
                return $att_url ? [
                    'href'  => $att_url,
                    'label' => $link_text ?: get_the_title( (int) $val ),
                ] : null;
            }
            if ( is_string( $val ) && $val !== '' ) {
                return [ 'href' => $val, 'label' => $link_text ?: $val ];
            }
            return null;
        };

        // Repeater: array de filas [ [...], [...] ]
        if ( is_array( $value ) && isset( $value[0] ) && is_array( $value[0] ) ) {
            $links_html = '';
            foreach ( $value as $row ) {
                $row_href = $row_label = '';
                foreach ( $row as $sub_val ) {
                    if ( is_array( $sub_val ) && isset( $sub_val['url'] ) && ! $row_href ) {
                        $row_href = $sub_val['url'];
                    } elseif ( is_numeric( $sub_val ) && ! $row_href ) {
                        $att = wp_get_attachment_url( (int) $sub_val );
                        if ( $att ) $row_href = $att;
                    } elseif ( is_string( $sub_val ) && $sub_val && ! $row_label ) {
                        $row_label = $sub_val;
                    }
                }
                if ( ! $row_href ) continue;
                $links_html .= sprintf(
                    '<a href="%s" target="%s"%s>%s</a>',
                    esc_url( $row_href ),
                    esc_attr( $link_target ),
                    $rel,
                    esc_html( $link_text ?: $row_label ?: basename( $row_href ) )
                );
            }
            if ( ! $links_html ) return;
            printf(
                '<%1$s %2$s>%3$s%4$s%5$s</%1$s>',
                $html_tag,
                $wrapper_attrs,
                esc_html( $prefix ),
                $links_html,
                esc_html( $suffix )
            );
            break;
        }

        // Valor simple (link field, file field, string, ID)
        $resolved = $resolve_link( $value );
        if ( ! $resolved ) return;

        printf(
            '<%1$s %2$s>%3$s<a href="%4$s" target="%5$s"%6$s>%7$s</a>%8$s</%1$s>',
            $html_tag,
            $wrapper_attrs,
            esc_html( $prefix ),
            esc_url( $resolved['href'] ),
            esc_attr( $link_target ),
            $rel,
            esc_html( $resolved['label'] ),
            esc_html( $suffix )
        );
        break;

    // ── HTML / WYSIWYG ────────────────────────────────────────────────────────
    case 'html':
        printf(
            '<div %s>%s%s%s</div>',
            $wrapper_attrs,
            $prefix ? '<span class="acf-field-block__prefix">' . esc_html( $prefix ) . '</span>' : '',
            wp_kses_post( $value ),
            $suffix ? '<span class="acf-field-block__suffix">' . esc_html( $suffix ) . '</span>' : ''
        );
        break;

    // ── Texto (por defecto) ───────────────────────────────────────────────────
    default:
        // Soportar arrays (select múltiple, checkbox…). Ignorar elementos
        // que sean a su vez arrays (relationship, repeater…).
        if ( is_array( $value ) ) {
            $value = implode( ', ', array_map(
                function( $v ) { return is_scalar( $v ) ? wp_strip_all_tags( (string) $v ) : ''; },
                $value
            ) );
        }

        printf(
            '<%1$s %2$s>%3$s%4$s%5$s</%1$s>',
            $html_tag,
            $wrapper_attrs,
            $prefix ? '<span class="acf-field-block__prefix">' . esc_html( $prefix ) . '</span>' : '',
            esc_html( (string) $value ),
            $suffix ? '<span class="acf-field-block__suffix">' . esc_html( $suffix ) . '</span>' : ''
        );
        break;
}
