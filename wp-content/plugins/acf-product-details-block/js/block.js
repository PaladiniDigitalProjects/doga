( function( blocks, element, serverSideRender ) {
    var el = element.createElement;
    var ServerSideRender = serverSideRender;

    // Solo los atributos registrados en el schema PHP — evita que plugins
    // de terceros (gutenberghub) inyecten sus atributos extra y rompan la API.
    function ownAttrs( attributes, keys ) {
        var out = {};
        keys.forEach( function( k ) { out[ k ] = attributes[ k ]; } );
        return out;
    }

    blocks.registerBlockType( 'apdb/acf-product-details', {
        title: 'Detalles de producto (ACF)',
        icon: 'products',
        category: 'widgets',
        attributes: {
            title: { type: 'string', default: 'Detalles del producto' },
        },
        edit: function( props ) {
            return el( ServerSideRender, {
                block: 'apdb/acf-product-details',
                attributes: ownAttrs( props.attributes, [ 'title' ] ),
            } );
        },
        save: function() { return null; },
    } );

    blocks.registerBlockType( 'apdb/product-slider-gallery', {
        title: 'Galería de producto (ACF)',
        icon: 'images-alt2',
        category: 'widgets',
        attributes: {
            title: { type: 'string', default: 'Galería de producto' },
        },
        edit: function( props ) {
            return el( ServerSideRender, {
                block: 'apdb/product-slider-gallery',
                attributes: ownAttrs( props.attributes, [ 'title' ] ),
            } );
        },
        save: function() { return null; },
    } );
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.serverSideRender
);
