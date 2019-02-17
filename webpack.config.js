// node module that let's us do file system stuffs...
const path = require( 'path' );
const UglifyJsPlugin = require( 'uglifyjs-webpack-plugin' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const OptimizeCSSAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );

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
          test: /\.s?[ac]ss$/,
          use: [
              MiniCssExtractPlugin.loader,
              {
                loader: 'css-loader',
                options: {
                  url: false,
                  sourceMap: true
                }
              },
              {
                loader: 'sass-loader',
                options: {
                  sourceMap: true,
                }
              }
          ],
        }
      ]
    },
    mode: argv.mode,
    plugins: [
      new MiniCssExtractPlugin( {
        filename: 'style' + ( 'production' === argv.mode ? '.min' : '' ) + '.css'
      } )
    ]
  };

  if ( 'production' === argv.mode ) {
    config.optimization = {
      minimizer: [
        new UglifyJsPlugin( {
          cache: true,
          parallel: true,
          sourceMap: true // set to true if you want JS source maps
        } ),
        new OptimizeCSSAssetsPlugin( {} )
      ]
    };
  }

  return config;
};
