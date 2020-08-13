const path = require('path');
const glob = require('glob');
const webpack = require('webpack');
require('dotenv').config();

// dotenv will read .env file and return an object with a 'parsed' key
const isWordpressUrlDefined =
  Boolean(process.env.WORDPRESS_URL) || process.env.WORDPRESS_URL;

if (!isWordpressUrlDefined) {
  console.log(
    'Required WORDPRESS_URL is not defined.\nYou should define it in .env file',
  );
  process.exit(1);
}
const envKeys = process.env
  ? Object.keys(process.env).reduce((acc, current) => {
      acc[`process.env.${current}`] = JSON.stringify(process.env[current]);
      return acc;
    }, {})
  : {};

module.exports = {
  webpack: config => {
    config.module.rules.push(
      {
        test: /\.(css|scss)/,
        loader: 'emit-file-loader',
        options: {
          name: 'dist/[path][name].[ext]',
        },
      },
      {
        test: /\.css$/,
        use: ['babel-loader', 'raw-loader', 'postcss-loader'],
      },
      {
        test: /\.s(a|c)ss$/,
        use: [
          'babel-loader',
          'raw-loader',
          'postcss-loader',
          {
            loader: 'sass-loader',
            options: {
              includePaths: ['styles', 'node_modules']
                .map(d => path.join(__dirname, d))
                .map(g => glob.sync(g))
                .reduce((a, c) => a.concat(c), []),
            },
          },
        ],
      },
    );

    config.plugins.push(new webpack.DefinePlugin(envKeys));

    return config;
  },
};
