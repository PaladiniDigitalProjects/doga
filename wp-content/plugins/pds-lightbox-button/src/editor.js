/**
 * PDS Lightbox Button – Editor JSX source
 * Compile: npm run build
 */
import { registerBlockType } from '@wordpress/blocks';
import {
	useBlockProps,
	RichText,
	InnerBlocks,
} from '@wordpress/block-editor';
import { Button, Modal } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

const CloseIcon = () => (
	<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
		fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
		<line x1="18" y1="6" x2="6" y2="18" />
		<line x1="6" y1="6" x2="18" y2="18" />
	</svg>
);

registerBlockType( 'pds-lightbox/button', {
	title: __( 'Lightbox Button', 'pds-lightbox-button' ),
	description: __( 'Un botón que abre un lightbox con cualquier contenido dentro (texto, imagen, formulario…).', 'pds-lightbox-button' ),
	category: 'widgets',
	icon: 'lightbulb',
	supports: {
		className: true,
		align: [ 'left', 'center', 'right' ],
	},

	edit( { attributes, setAttributes, clientId } ) {
		const { label } = attributes;
		const blockProps = useBlockProps( { className: 'pds-lb-wrapper-editor' } );
		const [ isModalOpen, setModalOpen ] = useState( false );

		const innerBlocksCount = useSelect(
			( select ) => {
				const block = select( 'core/block-editor' ).getBlock( clientId );
				return block ? block.innerBlocks.length : 0;
			},
			[ clientId ]
		);

		return (
			<>
				<div { ...blockProps }>
					<RichText
						tagName="span"
						className="wp-block-button__link pds-lb-trigger"
						value={ label }
						onChange={ ( val ) => setAttributes( { label: val } ) }
						allowedFormats={ [] }
						placeholder={ __( 'Texto del botón…', 'pds-lightbox-button' ) }
					/>

					<Button
						variant="secondary"
						className="pds-lb-edit-content-btn"
						onClick={ () => setModalOpen( true ) }
					>
						{ innerBlocksCount > 0
							? __( 'Editar contenido del lightbox', 'pds-lightbox-button' )
							: __( 'Añadir contenido al lightbox', 'pds-lightbox-button' ) }
					</Button>
				</div>

				{ isModalOpen && (
					<Modal
						title={ __( 'Contenido del lightbox', 'pds-lightbox-button' ) }
						onRequestClose={ () => setModalOpen( false ) }
						className="pds-lb-edit-modal"
						shouldCloseOnClickOutside={ false }
					>
						<InnerBlocks templateLock={ false } />
						<Button variant="primary" onClick={ () => setModalOpen( false ) }>
							{ __( 'Listo', 'pds-lightbox-button' ) }
						</Button>
					</Modal>
				) }
			</>
		);
	},

	save( { attributes } ) {
		const { label } = attributes;
		const blockProps = useBlockProps.save( { className: 'pds-lb-wrapper' } );

		return (
			<div { ...blockProps }>
				<button type="button" className="wp-block-button__link pds-lb-trigger" aria-haspopup="dialog">
					<RichText.Content tagName="span" value={ label } />
				</button>

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
			</div>
		);
	},
} );
