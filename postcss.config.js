module.exports = ( { env } ) => ( {
  plugins: {
    'postcss-import': {},
    'postcss-preset-env': {
      browsers: 'last 2 versions',
			features: {
				'nesting-rules': true,
			},
    }
  },
} );
