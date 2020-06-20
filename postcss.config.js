module.exports = ( { env } ) => ( {
  plugins: {
    'postcss-import': {},
    'postcss-preset-env': {
      autoprefixer: {
        grid: true
      },
      browsers: 'last 2 versions',
      preserve: false,
      stage: 0
    },
    'cssnano':
      'production' === env
        ? {}
        : false
    ,
  },
} );
