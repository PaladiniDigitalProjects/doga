( function( blocks, element, serverSideRender, data ) {
    var el = element.createElement;
    var ServerSideRender = serverSideRender;

    // Solo los atributos registrados en el schema PHP — evita que plugins
    // de terceros (gutenberghub) inyecten sus atributos extra y rompan la API.
    function ownAttrs( attributes, keys ) {
        var out = {};
        keys.forEach( function( k ) { out[ k ] = attributes[ k ]; } );
        return out;
    }

    // ID del post que se está editando. En el editor, ServerSideRender renderiza
    // el bloque de forma aislada (sin el context['postId'] que aporta un bloque
    // padre en el frontend), así que hay que enviar el post_id explícitamente para
    // que el endpoint REST block-renderer configure el global $post y el callback
    // pueda resolver get_the_ID(). Sin esto, el bloque sale en blanco en el editor.
    function currentPostId() {
        try {
            var id = data.select( 'core/editor' ).getCurrentPostId();
            return id ? id : 0;
        } catch ( e ) {
            return 0;
        }
    }

    function ssr( blockName, attributes ) {
        var postId = currentPostId();
        var config = {
            block: blockName,
            attributes: attributes,
        };
        if ( postId ) {
            config.urlQueryArgs = { post_id: postId };
        }
        return el( ServerSideRender, config );
    }

    blocks.registerBlockType( 'apdb/acf-product-details', {
        title: 'Detalles de producto (ACF)',
        icon: 'products',
        category: 'widgets',
        attributes: {
            title: { type: 'string', default: 'Detalles del producto' },
        },
        edit: function( props ) {
            return ssr( 'apdb/acf-product-details', ownAttrs( props.attributes, [ 'title' ] ) );
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
            return ssr( 'apdb/product-slider-gallery', ownAttrs( props.attributes, [ 'title' ] ) );
        },
        save: function() { return null; },
    } );
} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.serverSideRender,
    window.wp.data
);
