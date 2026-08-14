const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const FileManagerPlugin = require('filemanager-webpack-plugin');

module.exports = (env, argv) => {
  const config = {
    entry: {
      'order.payments': './src/index.js',
      'orderpayments.grid': './src/orderpayments.grid.js',
      'orderinvoices.grid': './src/orderinvoices.grid.js',
    },
    output: {
      path: path.resolve(__dirname, 'dist'),
      filename: '[name].js',
      clean: true,
    },
    module: {
      rules: [
        {
          test: /\.js$/,
          use: 'babel-loader',
          exclude: /node_modules/
        },
        {
          test: /\.css$/,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader'
          ]
        },
        {
          test: /\.vue$/,
          loader: 'vue-loader'
        },
        {
          test: /\.s[ac]ss$/i,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            {
              loader: 'sass-loader',
              options: {
                sassOptions: {
                  indentedSyntax: true,
                },
              },
            },
          ],
        },
      ]
    },
    resolve: {
      extensions: ['.js', '.vue'],
      alias: {
        'vue$': 'vue/dist/vue.esm-bundler.js',
        '@scss': path.resolve(__dirname, 'src/assets/scss')
      }
    },
    plugins: [
      new VueLoaderPlugin(),
      new MiniCssExtractPlugin({
        filename: '[name].css'
      }),
      new FileManagerPlugin({
        events: {
          onEnd: {
            copy: [
              { source: './dist/*.js', destination: '../views/js' },
              { source: './dist/*.css', destination: '../views/css' },
            ]
          }
        }
      })
    ]
  };

  if (argv.mode === 'development') {
    config.devtool = 'eval-source-map';
  }

  return config;
};
