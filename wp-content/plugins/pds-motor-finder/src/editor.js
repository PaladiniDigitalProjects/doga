import { registerBlockType } from '@wordpress/blocks';
import { useSelect }         from '@wordpress/data';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

registerBlockType( metadata.name, {

    edit( { attributes, setAttributes } ) {

        const { postType, taxonomy } = attributes;

        // Obtener post types registrados (públicos)
        const postTypes = useSelect( select => {
            const types = select( 'core' ).getPostTypes( { per_page: -1 } );
            if ( ! types ) return [];
            return types
                .filter( t => t.viewable && t.slug !== 'attachment' )
                .map( t => ( { label: t.name, value: t.slug } ) );
        }, [] );

        // Obtener taxonomías del post type seleccionado
        const taxonomies = useSelect( select => {
            const types = select( 'core' ).getPostTypes( { per_page: -1 } );
            if ( ! types ) return [];
            const found = types.find( t => t.slug === postType );
            if ( ! found ) return [];
            return ( found.taxonomies || [] ).map( slug => ( { label: slug, value: slug } ) );
        }, [ postType ] );

        return (
            <>
                <InspectorControls>
                    <PanelBody title={ __( 'Configuración', 'pds-motor-finder' ) }>
                        <SelectControl
                            label={ __( 'Post Type', 'pds-motor-finder' ) }
                            value={ postType }
                            options={ postTypes }
                            onChange={ val => setAttributes( { postType: val, taxonomy: '' } ) }
                        />
                        <SelectControl
                            label={ __( 'Taxonomía', 'pds-motor-finder' ) }
                            value={ taxonomy }
                            options={ taxonomies }
                            onChange={ val => setAttributes( { taxonomy: val } ) }
                            disabled={ ! taxonomies.length }
                            help={ ! taxonomies.length ? __( 'Selecciona primero un Post Type.', 'pds-motor-finder' ) : '' }
                        />
                    </PanelBody>
                </InspectorControls>

                <Placeholder
                    icon="filter"
                    label={ __( 'Motor Finder', 'pds-motor-finder' ) }
                    instructions={ `Post Type: ${postType || '—'} · Taxonomía: ${taxonomy || '—'}` }
                >
                    <p style={ { fontSize: '12px', color: '#757575' } }>
                        { __( 'El filtro se renderiza en el frontend.', 'pds-motor-finder' ) }
                    </p>
                </Placeholder>
            </>
        );
    },

    // Server-side render — sin save
    save: () => null,
} );
