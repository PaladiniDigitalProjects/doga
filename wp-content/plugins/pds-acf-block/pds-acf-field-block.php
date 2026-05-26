<?php
/**
 * Plugin Name:      PDS ACF Field Block
 * Plugin URI:       https://github.com/
 * Description:      Muestra cualquier campo ACF como bloque Gutenberg editable con fuente, colores, márgenes y más.
 * Version:          2.0.0
 * Requires at least: 6.3
 * Requires PHP:     7.4
 * Author:           Ricard PDS
 * License:          GPL-2.0-or-later
 * Text Domain:      pds-acf-field-block
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registra el bloque y sus assets
 */
function pds_acf_field_block_init() {
    if ( ! function_exists( 'get_field' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>PDS ACF Field Block</strong>: ';
            esc_html_e( 'Requiere que Advanced Custom Fields esté instalado y activo.', 'pds-acf-field-block' );
            echo '</p></div>';
        } );
        return;
    }

    register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'pds_acf_field_block_init' );

/**
 * Endpoint REST: devuelve todos los campos ACF registrados.
 *
 * URL: /wp-json/pds-acf-field-block/v1/fields
 *
 * Importante: register_rest_route() debe llamarse desde rest_api_init,
 * no desde init, para que WordPress haya cargado el servidor REST.
 */
function pds_acf_field_block_get_fields( WP_REST_Request $request ) {
    if ( ! function_exists( 'acf_get_field_groups' ) ) {
        return new WP_Error(
            'acf_missing',
            __( 'ACF no está activo o no tiene campos registrados.', 'pds-acf-field-block' ),
            [ 'status' => 500 ]
        );
    }

    $groups = acf_get_field_groups();

    if ( empty( $groups ) ) {
        return rest_ensure_response( [] );
    }

    $result = [];

    foreach ( $groups as $group ) {
        $fields = acf_get_fields( $group['key'] );
        if ( empty( $fields ) ) {
            continue;
        }
        foreach ( $fields as $field ) {
            $result[] = [
                'value' => $field['name'],
                'label' => $group['title'] . ' › ' . $field['label'],
                'type'  => $field['type'],
                'key'   => $field['key'],
            ];
        }
    }

    return rest_ensure_response( $result );
}

function pds_acf_field_block_register_rest() {
    register_rest_route(
        'pds-acf-field-block/v1',
        '/fields',
        [
            'methods'             => WP_REST_Server::READABLE, // GET
            'callback'            => 'pds_acf_field_block_get_fields',
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        ]
    );
}
add_action( 'rest_api_init', 'pds_acf_field_block_register_rest' );
