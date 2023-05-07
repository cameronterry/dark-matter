// node module that let's us do file system stuffs...
const ESLintPlugin = require( 'eslint-webpack-plugin' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const path = require( 'path' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

module.exports = {
	entry: {
		'domain-mapping': path.resolve( process.cwd(), './assets/js/DomainMapping.js' ),
	},
	output: {
		// where we want our built file to go to and be named
		// I name it index.build.js so I keep index files separate
		filename: '[name].js',
		// we're going to put our built file in a './build/' folder
		path: path.resolve( process.cwd(), 'dist' )
	},
	externals: {
		jquery: 'jQuery',
	},
	module: {
		rules: [
			{
				// basically tells webpack to use babel with the correct presets
				test: /\.js$/,
				loader: 'babel-loader',
			},
			{
				test: /\.css$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader,
					},
					{
						loader: 'css-loader',
						options: {
							importLoaders: 1
						}
					},
					{
						loader: 'postcss-loader',
					}
				]
			}
		]
	},
	plugins: [
		new DependencyExtractionWebpackPlugin(),
		new ESLintPlugin( {
			fix: true,
		} ),
		/**
		 * Extract CSS to a separate file.
		 */
		new MiniCssExtractPlugin( {
			filename: '[name].css',
			chunkFilename: '[id].css',
		} ),
	]
};
