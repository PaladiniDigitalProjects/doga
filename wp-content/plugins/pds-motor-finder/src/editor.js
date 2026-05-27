import { registerBlockType } from '@wordpress/blocks';
import { useSelect }         from '@wordpress/data';
import { InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Placeholder, ToggleControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from '../block.json';

registerBlockType( metadata.name, {

    edit( { attributes, setAttributes } ) {

        const { postType, taxonomy, title, description, pagination, postsPerPage } = attributes;

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
                    <PanelBody title={ __( 'Configuración del filtro', 'pds-motor-finder' ) }>
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
                        <ToggleControl
                            label={ __( 'Activar paginación', 'pds-motor-finder' ) }
                            checked={ pagination }
                            onChange={ val => setAttributes( { pagination: val } ) }
                        />
                        { pagination && (
                            <TextControl
                                label={ __( 'Items por página', 'pds-motor-finder' ) }
                                type="number"
                                value={ String( postsPerPage ) }
                                min={ 1 }
                                max={ 100 }
                                onChange={ val => setAttributes( { postsPerPage: parseInt( val, 10 ) || 12 } ) }
                            />
                        ) }
                    </PanelBody>
                </InspectorControls>

                <div className="pds-mf-editor-preview">
                    <RichText
                        tagName="h2"
                        placeholder={ __( 'Título (opcional)', 'pds-motor-finder' ) }
                        value={ title }
                        onChange={ val => setAttributes( { title: val } ) }
                        className="pds-mf-editor-preview__title"
                    />
                    <RichText
                        tagName="p"
                        placeholder={ __( 'Descripción (opcional)', 'pds-motor-finder' ) }
                        value={ description }
                        onChange={ val => setAttributes( { description: val } ) }
                        className="pds-mf-editor-preview__desc"
                    />
                    <Placeholder
                        icon="filter"
                        label={ __( 'Product Filter', 'pds-motor-finder' ) }
                        instructions={ `Post Type: ${postType || '—'} · Taxonomía: ${taxonomy || '—'}` }
                    >
                        <p style={ { fontSize: '12px', color: '#757575' } }>
                            { __( 'El filtro se renderiza en el frontend.', 'pds-motor-finder' ) }
                        </p>
                    </Placeholder>
                </div>
            </>
        );
    },

    // Server-side render — sin save
    save: () => null,
} );
