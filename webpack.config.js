const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const TerserPlugin = require('terser-webpack-plugin');
const webpack = require('webpack');
const fs = require('fs');

// Swiper's MIT banner, verbatim from the installed package (so the version in
// it never drifts). Webpack erases the original: swiper's entry module is
// re-export-only, so module concatenation drops its whole source — comment
// included — before terser ever sees it. BannerPlugin below re-attaches it to
// the chunks that bundle Swiper, and format.comments keeps it through terser.
const swiperBanner = fs
    .readFileSync(path.resolve(__dirname, 'node_modules/swiper/swiper.mjs'), 'utf8')
    .match(/^\/\*\*[\s\S]*?\*\//)[0];

module.exports = {
    entry: {
        // WordPress plugin bundles (paths consumed by wp_enqueue_* — keep stable)
        'js/map': './src/wp/map.js',
        'js/checkins/checkins-pagination': './src/wp/checkins-pagination.js',
        'js/checkins/slider': './src/wp/checkins-slider.js',
        'css/tailwind': './src/css/tailwind.css',
        // Self-contained embed for non-WordPress sites (Wix etc.)
        'embed/jcp-embed': './src/embed/index.js',
    },
    output: {
        filename: '[name].min.js',
        path: path.resolve(__dirname, 'dist'),
        clean: true
    },
    optimization: {
        // Setting `minimizer` replaces webpack's default Terser instance, so the
        // terserOptions mirror webpack's own default (compress.passes: 2). The
        // rest keeps third-party license attribution inline in the bundles —
        // the embed ships as a single .js file, so a sibling .LICENSE.txt would
        // never be deployed: extractComments: false stops the extraction (and
        // the banner pointing at the missing file), and format.comments keeps
        // the license comments in place instead.
        minimizer: [
            new TerserPlugin({
                extractComments: false,
                terserOptions: {
                    compress: { passes: 2 },
                    format: {
                        // Strict superset of terser's default "some" filter:
                        // the same regex, plus the plain /** MIT banners that
                        // Swiper and ssr-window ship without an @license
                        // marker (which "some" would therefore drop).
                        comments: (astNode, comment) =>
                            (comment.type === 'comment1' || comment.type === 'comment2') &&
                            (/@preserve|@copyright|@lic|@cc_on|^\**!/i.test(comment.value) ||
                                /Released under the MIT License|Licensed under MIT/.test(comment.value)),
                    },
                },
            }),
        ],
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
                // CSS imported with ?inline-css resolves to the compiled CSS
                // as a string, which the embed injects into a <style> tag.
                test: /\.css$/i,
                resourceQuery: /inline-css/,
                type: 'asset/source',
                use: ['postcss-loader'],
            },
            {
                test: /\.css$/i,
                resourceQuery: { not: [/inline-css/] },
                use: [MiniCssExtractPlugin.loader, 'css-loader', 'postcss-loader'],
            },
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].min.css',
        }),
        new RemoveEmptyScriptsPlugin(),
        new webpack.BannerPlugin({
            banner: swiperBanner,
            raw: true,
            entryOnly: true,
            include: [/jcp-embed/, /checkins\/slider/],
        }),
    ],
}
