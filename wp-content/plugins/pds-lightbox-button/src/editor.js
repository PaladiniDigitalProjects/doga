/**
 * PDS Lightbox Button – Editor JSX source
 * Compile: npm run build
 */
import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	RichText,
	InnerBlocks,
	BlockControls,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	ToolbarGroup,
	ToolbarButton,
	PanelBody,
	ToggleControl,
	TextControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const WIDTHS = [ 25, 50, 75, 100 ];

const CloseIcon = () => (
	<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
		fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
		<line x1="18" y1="6" x2="6" y2="18" />
		<line x1="6" y1="6" x2="18" y2="18" />
	</svg>
);

// Estructura del lightbox (overlay + modal + cierre + body), compartida por save() y deprecated.
const Lightbox = () => (
	<div className="pds-lb-overlay" role="presentation">
		<div className="pds-lb-modal shadow20" role="dialog" aria-modal="true">
			<button type="button" className="pds-lb-close" aria-label={ __( 'Cerrar', 'pds-lightbox-button' ) }>
				<CloseIcon />
			</button>
			<div className="pds-lb-body">
				<InnerBlocks.Content />
			</div>
		</div>
	</div>
);

const ATTRIBUTES = {
	label: { type: 'string', default: 'Más información' },
	width: { type: 'number' },
	// URL del recurso (PDF, etc.) → se renderiza como href del botón y el frontend la inyecta
	// en el campo oculto del WPForms (clase .pds-lb-download) al abrir el lightbox.
	downloadUrl: { type: 'string' },
};

const SUPPORTS = {
	className: true,
	align: [ 'left', 'center', 'right' ],
	color: { background: true, text: true, gradients: true },
	typography: {
		fontSize: true,
		lineHeight: true,
		__experimentalFontFamily: true,
		__experimentalFontWeight: true,
		__experimentalFontStyle: true,
		__experimentalTextTransform: true,
		__experimentalTextDecoration: true,
		__experimentalLetterSpacing: true,
		__experimentalDefaultControls: {
			fontSize: true,
			fontAppearance: true,
			letterSpacing: true,
		},
	},
	spacing: {
		padding: true,
		__experimentalDefaultControls: { padding: true },
	},
	// OJO: la clave del soporte de borde es `__experimentalBorder` (lo que lee WP / usa
	// core/button), NO `border` — con `border` el panel de borde no se renderiza.
	__experimentalBorder: {
		color: true,
		radius: true,
		style: true,
		width: true,
		__experimentalDefaultControls: {
			radius: true,
			color: true,
			width: true,
		},
	},
};

/**
 * Reparte lo que genera useBlockProps entre el WRAPPER y el BOTÓN (<a>):
 *  - wrapper: clase de bloque, is-style-*, align*, pds-lb-wrapper, y el ancho (width%).
 *  - botón:   las clases `has-*` (color/borde/fuente) + el `style` inline de los supports.
 * Así los estilos del botón se aplican al botón (no al contenedor), pero el `is-style-*`
 * sigue en el wrapper para que casen los selectores `.is-style-… > a` del tema (iconos/tag).
 */
function splitProps( blockProps, width ) {
	const { style, className = '', ...rest } = blockProps;
	const classes = className.split( ' ' ).filter( Boolean );
	const aClasses = classes.filter( ( c ) => c.startsWith( 'has-' ) );
	const wrapperClass = classes.filter( ( c ) => ! c.startsWith( 'has-' ) ).join( ' ' );
	return {
		wrapper: {
			...rest,
			className: wrapperClass,
			style: width ? { width: `${ width }%` } : undefined,
		},
		btnClassName: [ 'wp-block-button__link', 'pds-lb-trigger', ...aClasses ].join( ' ' ),
		btnStyle: style,
	};
}

registerBlockType( 'pds-lightbox/button', {
	apiVersion: 3,
	title: __( 'Lightbox Button', 'pds-lightbox-button' ),
	description: __( 'Un botón que abre un lightbox con cualquier contenido dentro (texto, imagen, formulario…).', 'pds-lightbox-button' ),
	category: 'widgets',
	icon: 'lightbulb',
	supports: SUPPORTS,

	edit( { attributes, setAttributes } ) {
		const { label, width, downloadUrl } = attributes;
		const [ showContent, setShowContent ] = useState( true );

		const { wrapper, btnClassName, btnStyle } = splitProps(
			useBlockProps( { className: 'pds-lb-wrapper' } ),
			width
		);

		return (
			<>
				<BlockControls>
					<ToolbarGroup>
						{ WIDTHS.map( ( w ) => (
							<ToolbarButton
								key={ w }
								isPressed={ width === w }
								onClick={ () =>
									setAttributes( { width: width === w ? undefined : w } )
								}
							>
								{ w }%
							</ToolbarButton>
						) ) }
					</ToolbarGroup>
				</BlockControls>

				<InspectorControls>
					<PanelBody title={ __( 'Lightbox', 'pds-lightbox-button' ) }>
						<TextControl
							type="url"
							label={ __( 'URL del recurso (descarga)', 'pds-lightbox-button' ) }
							help={ __(
								'Enlace al PDF/recurso. Se inyecta en el campo oculto del formulario (clase CSS "pds-lb-download") para enviarlo por email al rellenarlo.',
								'pds-lightbox-button'
							) }
							value={ downloadUrl || '' }
							onChange={ ( val ) => setAttributes( { downloadUrl: val || undefined } ) }
							placeholder="https://…/recurso.pdf"
						/>
						<ToggleControl
							label={ __( 'Mostrar contenido en el editor', 'pds-lightbox-button' ) }
							help={ __(
								'Muestra u oculta el contenedor editable del lightbox. Solo afecta al editor, no a la web.',
								'pds-lightbox-button'
							) }
							checked={ showContent }
							onChange={ setShowContent }
						/>
					</PanelBody>
				</InspectorControls>

				{ /* Botón (preview): los estilos van en el propio <a>, no en el wrapper */ }
				<div { ...wrapper }>
					<RichText
						tagName="a"
						className={ btnClassName }
						style={ btnStyle }
						value={ label }
						onChange={ ( val ) => setAttributes( { label: val } ) }
						allowedFormats={ [] }
						placeholder={ __( 'Texto del botón…', 'pds-lightbox-button' ) }
					/>
				</div>

				{ /* Contenedor del lightbox, editable inline */ }
				<div
					className={ `pds-lb-editor-content${ showContent ? '' : ' is-collapsed' }` }
				>
					<span className="pds-lb-editor-content__label">
						{ __( 'Contenido del lightbox', 'pds-lightbox-button' ) }
					</span>
					<InnerBlocks templateLock={ false } />
				</div>
			</>
		);
	},

	save( { attributes } ) {
		const { label, width, downloadUrl } = attributes;
		const { wrapper, btnClassName, btnStyle } = splitProps(
			useBlockProps.save( { className: 'pds-lb-wrapper' } ),
			width
		);

		return (
			<div { ...wrapper }>
				<a className={ btnClassName } style={ btnStyle } href={ downloadUrl || undefined } role="button" tabIndex={ 0 } aria-haspopup="dialog">
					<RichText.Content tagName="span" value={ label } />
				</a>
				<Lightbox />
			</div>
		);
	},

	deprecated: [
		// v1.2: supports + is-style en el wrapper, botón <a> sin estilos propios.
		{
			attributes: ATTRIBUTES,
			supports: SUPPORTS,
			save( { attributes } ) {
				const { label, width } = attributes;
				const blockProps = useBlockProps.save( {
					className: 'pds-lb-wrapper',
					style: width ? { width: `${ width }%` } : undefined,
				} );
				return (
					<div { ...blockProps }>
						<a className="wp-block-button__link pds-lb-trigger" role="button" tabIndex={ 0 } aria-haspopup="dialog">
							<RichText.Content tagName="span" value={ label } />
						</a>
						<Lightbox />
					</div>
				);
			},
		},
		// v1.0: el trigger era un <button>.
		{
			attributes: ATTRIBUTES,
			supports: SUPPORTS,
			save( { attributes } ) {
				const { label, width } = attributes;
				const blockProps = useBlockProps.save( {
					className: 'pds-lb-wrapper',
					style: width ? { width: `${ width }%` } : undefined,
				} );
				return (
					<div { ...blockProps }>
						<button type="button" className="wp-block-button__link pds-lb-trigger" aria-haspopup="dialog">
							<RichText.Content tagName="span" value={ label } />
						</button>
						<Lightbox />
					</div>
				);
			},
		},
	],
} );
