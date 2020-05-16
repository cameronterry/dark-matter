module.exports = ( { env } ) => ( {
  plugins: {
    'postcss-import': {},
    'postcss-preset-env': {
      browsers: 'last 2 versions',
    },
    'cssnano':
      'production' === env
        ? {}
        : false
    ,
  },
} );