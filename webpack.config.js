const {VueLoaderPlugin} = require('vue-loader');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');
const config = require('./config');

module.exports = {
	mode: 'development', // prod mode when deploying apparently does good things
	entry: [
		'./frontend/main.js',
	],
	output: {
		path: config.publicDir,
		filename: 'bundle.js',
	},
	optimization: {
		minimizer: [new UglifyJsPlugin()],
		splitChunks: {
			cacheGroups: {
				commons: {
					test: /[\\/]node_modules[\\/]/,
					name: 'vendors',
					chunks: 'all',
				},
			},
		},
		runtimeChunk: {
			name: 'manifest',
		},
	},
	resolve: {
		extensions: [
			'.js',
			'.vue',
		],
	},
	target: 'web',
	module: {
		rules: [
			{
				test: /\.bundle\.js$/,
				loader: 'bundle-loader',
				options: {
					lazy: true,
				},
			},
			{
				test: /\.vue$/,
				loader: 'vue-loader',
			},
			{
				test: /\.css$/,
				use: [
					'vue-style-loader',
					'css-loader',
				],
			},
			{
				test: /\.scss$/,
				use: [
					'vue-style-loader',
					'css-loader',
					{
						loader: 'sass-loader',
						options: {
							data: `
								@import "frontend/styles/main";
							`,
						},
					},
				],
			},
			{
				test: /\.js$/,
				use: {
					loader: 'babel-loader',
					options: {
						plugins: [
							'@babel/plugin-syntax-dynamic-import',
						], // Need presets for safari, edge and IE but that's insanely hard for some reason
					},
				},
				exclude: /node_modules/,
			},
			{
				test: /\.(png|svg|jpg|gif)$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: '[name][hash].[ext]',
							publicPath: '/',
						},
					},
				],
			},
		],
	},
	plugins: [
		new VueLoaderPlugin(),
		new HtmlWebpackPlugin({
			template: './public/template.html',
			title: '/r/anime Awards 2019',
		}),
		new CompressionPlugin({
			filename: '[path].gz[query]',
			algorithm: 'gzip',
			test: /\.js$|\.css$|\.html$/,
			threshold: 10240,
			minRatio: 0.8,
		}),
	],
};
