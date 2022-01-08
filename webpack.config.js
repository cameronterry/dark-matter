// node module that let's us do file system stuffs...
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const path = require( 'path' );
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const TerserPlugin = require('terser-webpack-plugin'); // eslint-disable-line import/no-extraneous-dependencies
const webpack = require( 'webpack' );
const WebpackBar = require('webpackbar');

const env = process.env.NODE_ENV;

// Webpack expects an exported object with all the configurations, so we export an object here
module.exports = () => {
  let config = {
    name: 'domain-mapping',
    entry: {
      'domain-mapping': path.resolve( process.cwd(), './domain-mapping/ui/index.js' ),
      'domain-mapping-style': path.resolve( process.cwd(), './domain-mapping/ui/App.css' ),
    },
    output: {
      // where we want our built file to go to and be named
      // I name it index.build.js so I keep index files separate
      filename: '[name]' + ( 'production' === env ? '.min' : '' ) + '.js',
      // we're going to put our built file in a './build/' folder
      path: path.resolve( process.cwd(), 'domain-mapping/build' )
    },
    externals: {
      jquery: 'jQuery',
    },
    module: {
      rules: [
        {
          test: /\.js$/,
          enforce: 'pre',
          loader: 'eslint-loader',
          options: {
            fix: true,
          }
        },
        {
          // basically tells webpack to use babel with the correct presets
          test: /\.js$/,
          loader: 'babel-loader',
        },
        {
          test: /\.css$/,
          include: path.resolve( process.cwd(), './domain-mapping/ui/' ),
          use: [
            {
              loader: MiniCssExtractPlugin.loader,
            },
            {
              loader: 'css-loader',
              options: {
                sourceMap: ! ( 'production' === env ),
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
    mode: env,
    plugins: [
      new RemoveEmptyScriptsPlugin(),
      /**
       * Extract CSS to a separate file.
       */
      new MiniCssExtractPlugin( {
        filename: '[name]' + ( 'production' === env ? '.min' : '' ) + '.css',
        chunkFilename: '[id].css',
      } ),
      new WebpackBar(),
      new webpack.EnvironmentPlugin( [ 'NODE_ENV' ] )
    ]
  };

  if ( 'production' === env ) {
    config.optimization = {
      minimize: true,
      minimizer: [
        new TerserPlugin( {
          parallel: true,
          terserOptions: {
            parse: {
              /**
               * We want terser to parse ecma 8 code. However, we don't want it to apply any minfication steps that
               * turns valid ecma 5 code into invalid ecma 5 code. This is why the 'compress' and 'output' sections only
               * apply transformations that are ecma 5 safe: https://github.com/facebook/create-react-app/pull/4234
               */
              ecma: 8,
            },
            compress: {
              ecma: 5,
              warnings: false,
              /**
               * Disabled because of an issue with Uglify breaking seemingly valid code: https://github.com/facebook/create-react-app/issues/2376
               *
               * Pending further investigation: https://github.com/mishoo/UglifyJS2/issues/2011
               */
              comparisons: false,
              /**
               * Disabled because of an issue with Terser breaking valid code: https://github.com/facebook/create-react-app/issues/5250
               *
               * Pending futher investigation: https://github.com/terser-js/terser/issues/120
               */
              inline: 2,
            },
          },
        } ),
      ]
    };
  }

  return config;
};
