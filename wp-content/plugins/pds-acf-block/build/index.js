/**
 * PDS ACF Field Block v2 — Editor Script
 *
 * IMPORTANTE: Requiere index.asset.php en la misma carpeta.
 * Usa wp.* globals cargados por WordPress (sin compilar con npm).
 */
( function ( blocks, element, blockEditor, components, apiFetch, i18n ) {
    'use strict';

    var registerBlockType   = blocks.registerBlockType;
    var createElement       = element.createElement;
    var useState            = element.useState;
    var useEffect           = element.useEffect;
    var Fragment            = element.Fragment;

    var useBlockProps       = blockEditor.useBlockProps;
    var InspectorControls   = blockEditor.InspectorControls;
    var BlockControls       = blockEditor.BlockControls;
    var AlignmentControl    = blockEditor.AlignmentControl;

    var PanelBody           = components.PanelBody;
    var SelectControl       = components.SelectControl;
    var TextControl         = components.TextControl;
    var Spinner             = components.Spinner;
    var Notice              = components.Notice;

    var __                  = i18n.__;
    var e                   = createElement;

    var HTML_TAGS = [
        { label: 'Párrafo <p>',           value: 'p'          },
        { label: 'Contenedor <div>',      value: 'div'        },
        { label: 'En línea <span>',       value: 'span'       },
        { label: 'Título H1',             value: 'h1'         },
        { label: 'Título H2',             value: 'h2'         },
        { label: 'Título H3',             value: 'h3'         },
        { label: 'Título H4',             value: 'h4'         },
        { label: 'Título H5',             value: 'h5'         },
        { label: 'Título H6',             value: 'h6'         },
        { label: 'Negrita <strong>',      value: 'strong'     },
        { label: 'Cursiva <em>',          value: 'em'         },
        { label: 'Elemento lista <li>',   value: 'li'         },
        { label: 'Cita <blockquote>',     value: 'blockquote' },
    ];

    var IMAGE_SIZES = [
        { label: 'Original (full)',       value: 'full'      },
        { label: 'Grande (large)',        value: 'large'     },
        { label: 'Mediano (medium)',      value: 'medium'    },
        { label: 'Miniatura (thumbnail)', value: 'thumbnail' },
    ];

    var IMAGE_FIT = [
        { label: 'Cubrir (cover)',        value: 'cover'   },
        { label: 'Contener (contain)',    value: 'contain' },
        { label: 'Estirar (fill)',        value: 'fill'    },
        { label: 'Sin ajuste (none)',     value: 'none'    },
    ];

    var DISPLAY_TYPES = [
        { label: 'Texto',                 value: 'text'  },
        { label: 'HTML / WYSIWYG',        value: 'html'  },
        { label: 'Imagen',                value: 'image' },
        { label: 'Enlace / URL',          value: 'link'  },
    ];

    var ACF_TYPE_MAP = {
        image:   { display: 'image', tag: 'p'   },
        gallery: { display: 'image', tag: 'p'   },
        link:    { display: 'link',  tag: 'p'   },
        url:     { display: 'link',  tag: 'p'   },
        email:   { display: 'link',  tag: 'p'   },
        file:    { display: 'link',  tag: 'p'   },
        wysiwyg: { display: 'html',  tag: 'div' },
        textarea:{ display: 'html',  tag: 'div' },
        number:  { display: 'text',  tag: 'span'},
        range:   { display: 'text',  tag: 'span'},
    };

    function EditBlock( props ) {
        var attributes    = props.attributes;
        var setAttributes = props.setAttributes;
        var context       = props.context;

        var fieldName   = attributes.fieldName   || '';
        var displayType = attributes.displayType || 'text';
        var htmlTag     = attributes.htmlTag     || 'p';
        var prefix      = attributes.prefix      || '';
        var suffix      = attributes.suffix      || '';
        var fallback    = attributes.fallback     || '';
        var imageSize   = attributes.imageSize   || 'full';
        var imageFit    = attributes.imageFit    || 'cover';
        var imageWidth  = attributes.imageWidth  || '';
        var imageHeight = attributes.imageHeight || '';
        var linkText    = attributes.linkText    || '';
        var linkTarget  = attributes.linkTarget  || '_self';
        var textAlign   = attributes.textAlign   || '';

        var s1 = useState( [] );    var acfFields = s1[0]; var setAcfFields = s1[1];
        var s2 = useState( true );  var loading   = s2[0]; var setLoading   = s2[1];
        var s3 = useState( null );  var error     = s3[0]; var setError     = s3[1];

        var postId = ( context && context['postId'] ) ? context['postId'] : null;

        useEffect( function () {
            apiFetch( { path: '/pds-acf-field-block/v1/fields' } )
                .then( function ( fields ) { setAcfFields( fields ); setLoading( false ); } )
                .catch( function ( err )   { setError( err && err.message ? err.message : 'Error REST' ); setLoading( false ); } );
        }, [] );

        var fieldOptions = [ { label: '— Selecciona un campo —', value: '' } ].concat(
            acfFields.map( function (f) { return { label: f.label + '  (' + f.type + ')', value: f.value }; } )
        );

        var selectedField = acfFields.find( function (f) { return f.value === fieldName; } );

        function handleFieldChange( val ) {
            var field   = acfFields.find( function (f) { return f.value === val; } );
            var autoMap = ( field && ACF_TYPE_MAP[ field.type ] ) ? ACF_TYPE_MAP[ field.type ] : null;
            setAttributes({ fieldName: val, displayType: autoMap ? autoMap.display : 'text', htmlTag: autoMap ? autoMap.tag : htmlTag });
        }

        var extraClass = 'acf-field-block'
            + ( !fieldName  ? ' acf-field-block--empty' : '' )
            + ( textAlign   ? ' has-text-align-' + textAlign : '' );

        var blockProps = useBlockProps({ className: extraClass });

        /* Canvas */
        var canvasContent;
        if ( !fieldName ) {
            canvasContent = e( 'span', { className: 'acf-field-block__empty-label' },
                '🗂  PDS Campo ACF — selecciona un campo en la barra lateral →'
            );
        } else {
            var icon = displayType === 'image' ? '🖼' : displayType === 'link' ? '🔗' : displayType === 'html' ? '🌐' : '📋';
            var meta = ( selectedField ? selectedField.type : displayType ) + '  ·  <' + htmlTag + '>';
            if ( displayType === 'image' ) meta = 'imagen · ' + imageSize + ' · ' + imageFit;
            if ( displayType === 'link'  ) meta = 'enlace · ' + htmlTag + ' · ' + linkTarget;

            canvasContent = e( 'div', { className: 'acf-field-block__preview' },
                e( 'span', { className: 'acf-field-block__preview-icon' }, icon ),
                e( 'div',  { className: 'acf-field-block__preview-meta' },
                    e( 'strong', null, selectedField ? selectedField.label : fieldName ),
                    e( 'small',  null, meta ),
                    postId ? e( 'small', { style: { opacity: .55 } }, 'Post #' + postId ) : null
                )
            );
        }

        /* Inspector */
        var inspector = e( InspectorControls, null,

            e( PanelBody, { title: 'Campo ACF', initialOpen: true },
                loading
                    ? e( 'div', { style: { display:'flex', gap:'8px', alignItems:'center', padding:'8px 0' } }, e( Spinner ), 'Cargando…' )
                    : error
                        ? e( Notice, { status:'error', isDismissible: false }, error )
                        : e( Fragment, null,
                              e( SelectControl, { label:'Campo', value: fieldName, options: fieldOptions, onChange: handleFieldChange, __nextHasNoMarginBottom: true } ),
                              selectedField && e( 'p', { style:{ margin:'4px 0 0', fontSize:'11px', color:'#999', fontFamily:'monospace' } },
                                  'Tipo: ' + selectedField.type + '  |  name: ' + selectedField.value )
                          )
            ),

            e( PanelBody, { title: 'Visualización', initialOpen: true },
                e( SelectControl, { label:'Mostrar como', value: displayType, options: DISPLAY_TYPES,
                    onChange: function(v){ setAttributes({ displayType: v }); }, __nextHasNoMarginBottom: true } ),

                displayType !== 'image' && e( Fragment, null,
                    e( 'div', { style:{ marginTop:'12px' } } ),
                    e( SelectControl, { label:'Etiqueta HTML', value: htmlTag, options: HTML_TAGS,
                        onChange: function(v){ setAttributes({ htmlTag: v }); }, __nextHasNoMarginBottom: true } )
                ),

                displayType === 'image' && e( Fragment, null,
                    e( 'div', { style:{ marginTop:'12px' } } ),
                    e( SelectControl, { label:'Tamaño', value: imageSize, options: IMAGE_SIZES,
                        onChange: function(v){ setAttributes({ imageSize: v }); }, __nextHasNoMarginBottom: true } ),
                    e( 'div', { style:{ marginTop:'8px' } } ),
                    e( SelectControl, { label:'object-fit', value: imageFit, options: IMAGE_FIT,
                        onChange: function(v){ setAttributes({ imageFit: v }); }, __nextHasNoMarginBottom: true } ),
                    e( 'div', { style:{ marginTop:'8px' } } ),
                    e( TextControl, { label:'Ancho (ej: 300px)', value: imageWidth,
                        onChange: function(v){ setAttributes({ imageWidth: v }); }, __nextHasNoMarginBottom: true } ),
                    e( 'div', { style:{ marginTop:'8px' } } ),
                    e( TextControl, { label:'Alto (ej: 200px)', value: imageHeight,
                        onChange: function(v){ setAttributes({ imageHeight: v }); }, __nextHasNoMarginBottom: true } )
                ),

                displayType === 'link' && e( Fragment, null,
                    e( 'div', { style:{ marginTop:'12px' } } ),
                    e( TextControl, { label:'Texto del enlace', value: linkText,
                        onChange: function(v){ setAttributes({ linkText: v }); }, __nextHasNoMarginBottom: true } ),
                    e( 'div', { style:{ marginTop:'8px' } } ),
                    e( SelectControl, { label:'Abrir en', value: linkTarget,
                        options: [ { label:'Misma pestaña', value:'_self' }, { label:'Nueva pestaña', value:'_blank' } ],
                        onChange: function(v){ setAttributes({ linkTarget: v }); }, __nextHasNoMarginBottom: true } )
                )
            ),

            e( PanelBody, { title: 'Contenido adicional', initialOpen: false },
                e( TextControl, { label:'Prefijo', value: prefix, placeholder:'Texto antes',
                    onChange: function(v){ setAttributes({ prefix: v }); }, __nextHasNoMarginBottom: true } ),
                e( 'div', { style:{ marginTop:'8px' } } ),
                e( TextControl, { label:'Sufijo', value: suffix, placeholder:'Texto después',
                    onChange: function(v){ setAttributes({ suffix: v }); }, __nextHasNoMarginBottom: true } ),
                e( 'div', { style:{ marginTop:'8px' } } ),
                e( TextControl, { label:'Fallback (campo vacío)', value: fallback, placeholder:'Texto alternativo',
                    onChange: function(v){ setAttributes({ fallback: v }); }, __nextHasNoMarginBottom: true } )
            )
        );

        /* Block Controls: alineación */
        var blockControls = ( displayType !== 'image' && AlignmentControl )
            ? e( BlockControls, { group: 'block' },
                  e( AlignmentControl, { value: textAlign, onChange: function(v){ setAttributes({ textAlign: v||'' }); } } )
              )
            : null;

        return e( Fragment, null, inspector, blockControls, e( 'div', blockProps, canvasContent ) );
    }

    registerBlockType( 'pds-acf-field-block/field', {
        edit: EditBlock,
        save: function () { return null; },
    } );

}(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.apiFetch,
    window.wp.i18n
) );
