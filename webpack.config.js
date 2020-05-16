// node module that let's us do file system stuffs...
const path = require( 'path' );
const TerserPlugin = require('terser-webpack-plugin'); // eslint-disable-line import/no-extraneous-dependencies
const WebpackBar = require('webpackbar');

// Webpack expects an exported object with all the configurations, so we export an object here
module.exports = ( env, argv ) => {
  let config = {
    name: 'domain-mapping',
    entry: './domain-mapping/ui/index.js', // Where to find our main js
    output: {
      // where we want our built file to go to and be named
      // I name it index.build.js so I keep index files separate
      filename: 'build' + ( 'production' === argv.mode ? '.min' : '' ) + '.js',
      // we're going to put our built file in a './build/' folder
      path: path.resolve( __dirname, 'domain-mapping/build' )
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
          query: {
            presets: ['@babel/preset-env', '@babel/preset-react']
          }
        },
        {
          test: /\.css$/,
          use: [
            'style-loader',
            {
              loader: 'css-loader',
              options: {
                importLoaders: 1
              }
            },
            'postcss-loader'
          ]
        }
      ]
    },
    mode: argv.mode,
    plugins: [
      new WebpackBar(),
    ]
  };

  if ( 'production' === argv.mode ) {
    config.optimization = {
      minimizer: [
        new TerserPlugin( {
          cache: true,
          parallel: true,
          sourceMap: false,
          terserOptions: {
            parse: {
              /**
               * We want terser to parse ecma 8 code. However, we don't want it
               * to apply any minfication steps that turns valid ecma 5 code
               * into invalid ecma 5 code. This is why the 'compress' and 'output'
               * sections only apply transformations that are ecma 5 safe
               * https://github.com/facebook/create-react-app/pull/4234
               */
              ecma: 8,
            },
            compress: {
              ecma: 5,
              warnings: false,
              /**
               * Disabled because of an issue with Uglify breaking seemingly valid code:
               * https://github.com/facebook/create-react-app/issues/2376
               * Pending further investigation:
               * https://github.com/mishoo/UglifyJS2/issues/2011
               */
              comparisons: false,
              /**
               * Disabled because of an issue with Terser breaking valid code:
               * https://github.com/facebook/create-react-app/issues/5250
               * Pending futher investigation:
               * https://github.com/terser-js/terser/issues/120
               */
              inline: 2,
            },
            output: {
              ecma: 5,
              comments: false,
            },
            ie8: false,
          },
        } ),
      ]
    };
  }

  return config;
};
