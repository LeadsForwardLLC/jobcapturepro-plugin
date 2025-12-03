const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');

module.exports = {
    entry: {
        'js/map': './src/js/map.js',
        'js/gallery': './src/js/gallery.js',
        'js/checkins/load-more': './src/js/checkins/load-more.js',
        'js/checkins/masonry-grid': './src/js/checkins/masonry-grid.js',
        'css/tailwind': './src/css/tailwind.css',
        'css/checkins-grid': './src/css/checkins-grid.css',
        'css/company-info': './src/css/company-info.css',
        'css/map': './src/css/map.css',
        'css/single-checkin': './src/css/single-checkin.css'

    },
    output: {
        filename: '[name].min.js',
        path: path.resolve(__dirname, 'dist'),
        clean: true
    },
    module: {
        rules: [
            {
                test: /\.?js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env'],
                    }
                },
            },
            {
                test: /\.css$/i,
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'postcss-loader'],
            },
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].min.css',
        }),
        new RemoveEmptyScriptsPlugin(),
    ],
}