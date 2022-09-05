const path = require('path'),
      NotifierPlugin = require('webpack-notifier'),
      EsLintPlugin = require('eslint-webpack-plugin'),
      WebpackbarPlugin = require('webpackbar'),
      CleanTerminalPlugin = require('clean-terminal-webpack-plugin'),
      {CleanWebpackPlugin} = require('clean-webpack-plugin'),
      CopyPlugin = require('copy-webpack-plugin');

const paths = {
  base: './resources',
  target: './dist',
};

const absBasePath = path.resolve(__dirname, paths.base),
      absTargetPath = path.resolve(__dirname, paths.target),

      js_entry = p => `./js/${p}`;

module.exports = (env, argv) => {
  const isProd = argv.mode === 'production',
        isDev = argv.mode === 'development',
        isWatch = typeof argv.watch !== 'undefined' && argv.watch === true;

  return {

    entry: {
      scorm_player: [js_entry('scorm_player.js')]
    },

    output: {
      filename: 'js/[name].js',
      path: absTargetPath,
      publicPath: paths.pub,
    },

    context: absBasePath,

    devtool: isDev ? 'source-map' : false,

    watchOptions: {
      ignored: ['node_modules/**']
    },

    performance: {
      hints: false
    },

    plugins: [
      new CleanWebpackPlugin({
        cleanStaleWebpackAssets: !isWatch
      }),
      new EsLintPlugin({
        overrideConfig: {
          rules: {
            "no-console": isProd ? 2 : 1,
            "no-debugger": isProd ? 2 : 1,
            "no-empty": isProd ? 2 : 1,
            "no-unused-vars": isProd ? 2 : 1,
            "no-constant-condition": isProd ? 2 : 1,
          }
        }
      }),
      new NotifierPlugin(),
      new WebpackbarPlugin(),
      new CleanTerminalPlugin(),
    ],

    externals: {
    },

    module: {
      rules: [
        {
          test: /\.js$/i,
          include: absBasePath,
          use: [
            'babel-loader'
          ]
        },
      ],
    },

  };
};
