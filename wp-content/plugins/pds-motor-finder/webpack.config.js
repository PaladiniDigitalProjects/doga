const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path           = require( 'path' );

module.exports = {
    ...defaultConfig,
    entry: {
        editor:   './src/editor.js',
        frontend: './src/frontend.js',
    },
    output: {
        ...defaultConfig.output,
        path: path.resolve( __dirname, 'assets/js' ),
    },
};
