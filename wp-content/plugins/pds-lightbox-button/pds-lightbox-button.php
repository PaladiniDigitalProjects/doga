<?php
/**
 * Plugin Name: PDS Lightbox Button
 * Description: Bloque tipo "botón" que al hacer clic abre un lightbox con cualquier contenido dentro (texto, imagen, formulario WPForms, etc.).
 * Version:     1.0.0
 * Requires at least: 6.1
 * Requires PHP: 7.4
 * Author:      Ricard Paladini Digital Solutions
 * Text Domain: pds-lightbox-button
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PDS_LB_DIR', plugin_dir_path( __FILE__ ) );
define( 'PDS_LB_URL', plugin_dir_url( __FILE__ ) );

add_action( 'init', 'pds_lb_register_block' );

function pds_lb_register_block() {

	// Editor script
	wp_register_script(
		'pds-lb-editor',
		PDS_LB_URL . 'build/editor.js',
		[ 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n', 'wp-data' ],
		filemtime( PDS_LB_DIR . 'build/editor.js' ),
		true
	);

	// Frontend + editor stylesheet
	wp_register_style(
		'pds-lb-style',
		PDS_LB_URL . 'build/style-style.css',
		[],
		filemtime( PDS_LB_DIR . 'build/style-style.css' )
	);

	// Frontend script (vanilla JS, sin dependencias)
	wp_register_script(
		'pds-lb-frontend',
		PDS_LB_URL . 'build/frontend.js',
		[],
		filemtime( PDS_LB_DIR . 'build/frontend.js' ),
		true
	);

	register_block_type( 'pds-lightbox/button', [
		'editor_script' => 'pds-lb-editor',
		'style'         => 'pds-lb-style',
		'script'        => 'pds-lb-frontend',
		'attributes'    => [
			'label' => [ 'type' => 'string', 'default' => 'Más información' ],
			'width' => [ 'type' => 'number' ],
		],
		'supports'      => [
			'className' => true,
			'align'     => [ 'left', 'center', 'right' ],
			'color'     => [
				'background' => true,
				'text'       => true,
				'gradients'  => true,
			],
			'typography' => [
				'fontSize'   => true,
				'lineHeight' => true,
			],
			'spacing' => [
				'padding' => true,
			],
			'border' => [
				'color'  => true,
				'radius' => true,
				'style'  => true,
				'width'  => true,
			],
		],
	] );
}
