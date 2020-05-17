module.exports = ( { env } ) => ( {
  plugins: {
    'postcss-import': {},
    'postcss-preset-env': {
      stage: 0
    },
    'cssnano':
      'production' === env
        ? {}
        : false
    ,
  },
} );
