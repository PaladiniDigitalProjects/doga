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

// Estructura del lightbox (overlay + modal + cierre + body), compartida por save() y el deprecated.
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
};

registerBlockType( 'pds-lightbox/button', {
	title: __( 'Lightbox Button', 'pds-lightbox-button' ),
	description: __( 'Un botón que abre un lightbox con cualquier contenido dentro (texto, imagen, formulario…).', 'pds-lightbox-button' ),
	category: 'widgets',
	icon: 'lightbulb',
	// Opciones estándar de botón (igual que core/button): color, tipografía, borde, padding.
	supports: {
		className: true,
		align: [ 'left', 'center', 'right' ],
		color: {
			background: true,
			text: true,
			gradients: true,
		},
		typography: {
			fontSize: true,
			lineHeight: true,
		},
		spacing: {
			padding: true,
		},
		border: {
			color: true,
			radius: true,
			style: true,
			width: true,
		},
	},

	edit( { attributes, setAttributes } ) {
		const { label, width } = attributes;

		// Estado solo-editor: mostrar/ocultar el contenedor del lightbox.
		// No es un atributo → no ensucia el post ni afecta a la web.
		const [ showContent, setShowContent ] = useState( true );

		// El wrapper recibe los estilos de los supports (color, borde, tipografía, padding) y la
		// clase is-style-* del estilo elegido; el botón (<a>) los hereda vía CSS y, al ser hijo
		// directo, le aplican los estilos de icono/tag del tema (`.is-style-… > a`).
		const blockProps = useBlockProps( {
			className: 'pds-lb-wrapper',
			style: width ? { width: `${ width }%` } : undefined,
		} );

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

				{ /* Botón (preview con los estilos estándar aplicados) */ }
				<div { ...blockProps }>
					<RichText
						tagName="a"
						className="wp-block-button__link pds-lb-trigger"
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

	// v1: el trigger era un <button>. Reproducir aquí para migrar las instancias guardadas.
	deprecated: [
		{
			attributes: ATTRIBUTES,
			supports: {
				className: true,
				align: [ 'left', 'center', 'right' ],
				color: { background: true, text: true, gradients: true },
				typography: { fontSize: true, lineHeight: true },
				spacing: { padding: true },
				border: { color: true, radius: true, style: true, width: true },
			},
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
