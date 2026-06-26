<?php
/**
 * Plugin Name: PDS Lightbox Button
 * Description: Bloque tipo "botón" que al hacer clic abre un lightbox con cualquier contenido dentro (texto, imagen, formulario WPForms, etc.).
 * Version:     1.2.0
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

	// Editor script — deps + version desde el asset generado por el build
	// (imprescindible: incluye `react-jsx-runtime`, requerido por @wordpress/scripts v30;
	//  hardcodear las deps dejaba fuera esa dependencia → el bloque no se registraba en WP 7.0).
	$editor_asset = require PDS_LB_DIR . 'build/editor.asset.php';
	wp_register_script(
		'pds-lb-editor',
		PDS_LB_URL . 'build/editor.js',
		$editor_asset['dependencies'],
		$editor_asset['version'],
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

	// Estilos de botón (mismos que core/button en este sitio). Los `name` coinciden con los
	// que el tema registra para `core/button`, así que reutilizan su CSS (`.is-style-…`).
	// Fill = look por defecto; Outline lo refuerza el SCSS del plugin; Icon*/Tag vienen del tema.
	$button_styles = [
		[ 'name' => 'fill',                      'label' => __( 'Fill', 'pds-lightbox-button' ),              'is_default' => true ],
		[ 'name' => 'outline',                   'label' => __( 'Outline', 'pds-lightbox-button' ) ],
		[ 'name' => 'button-icon-right',         'label' => __( 'Icon Right', 'pds-lightbox-button' ) ],
		[ 'name' => 'button-icon-right-bottom',  'label' => __( 'Icon Right Bottom', 'pds-lightbox-button' ) ],
		[ 'name' => 'button-icon-left',          'label' => __( 'Icon Left', 'pds-lightbox-button' ) ],
		[ 'name' => 'button-tag',                'label' => __( 'Tag', 'pds-lightbox-button' ) ],
	];
	foreach ( $button_styles as $style ) {
		register_block_style( 'pds-lightbox/button', $style );
	}
}
