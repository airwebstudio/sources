const HtmlWebPackPlugin = require("html-webpack-plugin")
const Dotenv = require('dotenv-webpack')
const path = require('path');
const CompressionPlugin = require("compression-webpack-plugin");

module.exports = {
    entry: './src/index.js',
    output: {
        path: path.resolve(__dirname, 'dist'),
        filename: '[name].js',
        publicPath: '/',
    },
    devServer: {
   historyApiFallback: true,
   contentBase: './',
   hot: true,
   port: 3000,
},
    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: "babel-loader"
                }
            },
            {
                test: /\.html$/,
                use: [
                    {
                        loader: "html-loader"
                    }
                ]
            },
            {
                test: /\.css$/,
                use: [
                    { loader: 'style-loader' },
                    { loader: 'css-loader' }
                ],
            },
            {
                test: /\.scss$/,
                use: [
                    {
                        loader: "style-loader"
                    }, // creates style nodes from JS strings
                    {
                        loader: "css-loader", // translates CSS into CommonJS
                        options: {
                            modules: true
                        }
                    },
                    {
                        loader: "sass-loader", // compiles Sass to CSS, using Node Sass by default
                    }
               ],
           },
       ]
   },
   plugins: [
        new HtmlWebPackPlugin({
            template: "./public/index.html",
            filename: "./index.html"
        }),
        new Dotenv(),
        new CompressionPlugin({
          test: /\.js(\?.*)?$/i,
        }),
    ]
}
