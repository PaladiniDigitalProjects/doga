<?php
/**
 * Plugin Name: PDS Lightbox Button
 * Description: Bloque tipo "botón" que al hacer clic abre un lightbox con cualquier contenido dentro (texto, imagen, formulario WPForms, etc.).
 * Version:     1.6.0
 * Requires at least: 6.1
 * Requires PHP: 7.4
 * Author:      Daniel Paladini
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
		'api_version'   => 3,
		'editor_script' => 'pds-lb-editor',
		'style'         => 'pds-lb-style',
		// El frontend (script + estilo en el front) se encola vía render_block, NO con 'script':
		// la detección de bloques de WP no ve este bloque cuando está dentro de un bloque
		// reutilizable (ve core/block) → no cargaría el JS y el <a href> navegaría. Ver más abajo.
		'attributes'    => [
			'label'       => [ 'type' => 'string', 'default' => 'Más información' ],
			'width'       => [ 'type' => 'number' ],
			'downloadUrl' => [ 'type' => 'string' ],
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
				'fontSize'                    => true,
				'lineHeight'                  => true,
				'__experimentalFontFamily'    => true,
				'__experimentalFontWeight'    => true,
				'__experimentalFontStyle'     => true,
				'__experimentalTextTransform' => true,
				'__experimentalTextDecoration'=> true,
				'__experimentalLetterSpacing' => true,
				'__experimentalDefaultControls' => [
					'fontSize'       => true,
					'fontAppearance' => true,
					'letterSpacing'  => true,
				],
			],
			'spacing' => [
				'padding' => true,
				'__experimentalDefaultControls' => [ 'padding' => true ],
			],
			// La clave del soporte de borde es `__experimentalBorder` (no `border`); con `border`
			// el panel no se renderiza. Igual que core/button.
			'__experimentalBorder' => [
				'color'  => true,
				'radius' => true,
				'style'  => true,
				'width'  => true,
				'__experimentalDefaultControls' => [
					'radius' => true,
					'color'  => true,
					'width'  => true,
				],
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

/**
 * Encola el JS + CSS del frontend cuando el bloque se renderiza, en lugar de depender de la
 * detección de bloques de WP (que NO ve este bloque dentro de un bloque reutilizable / patrón
 * sincronizado → no cargaría el frontend.js y el botón <a href> navegaría en vez de abrir el
 * lightbox). `render_block` SÍ se dispara para bloques anidados dentro de core/block.
 */
add_filter( 'render_block', 'pds_lb_enqueue_on_render', 10, 2 );
function pds_lb_enqueue_on_render( $content, $block ) {
	if ( isset( $block['blockName'] ) && 'pds-lightbox/button' === $block['blockName'] ) {
		wp_enqueue_script( 'pds-lb-frontend' );
		wp_enqueue_style( 'pds-lb-style' );

		// A prueba de balas frente a navegación: en el render movemos el href del botón a
		// `data-pds-download` y le quitamos el href. Sin href, el <a> NO es navegable → ni Luge
		// (que solo intercepta enlaces con href), ni el navegador, ni ningún otro script pueden
		// "saltar" de página. La URL la lee nuestro frontend.js del data-attr. Además marcamos
		// data-lg-transition="disabled" por si acaso. Se hace en el render (no en save()) para no
		// invalidar el contenido ya guardado del bloque/reutilizable.
		if ( false !== strpos( $content, 'pds-lb-trigger' ) ) {
			// 1) href="X"  →  data-pds-download="X" (solo en el <a> del trigger)
			$content = preg_replace(
				'/(<a\b(?=[^>]*\bpds-lb-trigger\b)[^>]*?)\shref=(["\'])(.*?)\2/',
				'$1 data-pds-download=$2$3$2',
				$content,
				1
			);
			// 2) marca de exclusión de Luge (cinturón y tirantes)
			$content = preg_replace(
				'/(<a\b)((?=[^>]*\bpds-lb-trigger\b)[^>]*>)/',
				'$1 data-lg-transition="disabled"$2',
				$content,
				1
			);
		}
	}
	return $content;
}
