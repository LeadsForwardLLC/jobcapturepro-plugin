const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');

module.exports = {
    entry: {
        // WordPress plugin bundles (paths consumed by wp_enqueue_* — keep stable)
        'js/map': './src/wp/map.js',
        'js/checkins/checkins-pagination': './src/wp/checkins-pagination.js',
        'js/checkins/slider': './src/wp/checkins-slider.js',
        'css/tailwind': './src/css/tailwind.css',
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
