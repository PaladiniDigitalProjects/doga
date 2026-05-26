<?php
/**
 * index.asset.php — Dependencias del script del editor.
 *
 * WordPress busca este archivo automáticamente cuando registra
 * el bloque con register_block_type(). Sin él, el script no
 * se carga y el bloque no aparece en el editor.
 *
 * Cada entrada corresponde al handle de un script de WordPress
 * que debe estar cargado ANTES que nuestro index.js.
 */
return [
    'dependencies' => [
        'wp-blocks',        // registerBlockType
        'wp-element',       // createElement, useState, useEffect
        'wp-block-editor',  // useBlockProps, InspectorControls, BlockControls, AlignmentControl
        'wp-components',    // PanelBody, SelectControl, TextControl, Spinner, Notice...
        'wp-data',          // useSelect
        'wp-api-fetch',     // apiFetch
        'wp-i18n',          // __()
        'wp-primitives',    // Iconos SVG
    ],
    'version' => '2.0.0',
];
