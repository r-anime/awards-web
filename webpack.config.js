const {VueLoaderPlugin} = require('vue-loader');
const config = require('./config');
const path = require('path');

module.exports = {
	mode: 'development', // prod mode when deploying apparently does good things
	entry: [
		'./frontend/main.js',
	],
	output: {
		path: config.publicDir,
		filename: 'bundle.js',
	},
	/* We need to do this at some point too
	optimization: {
		splitChunks: {
			chunks: 'all',
		},
	},
	*/
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
			},
			{
				test: /\.(png|svg|jpg|gif)$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: '[name][hash].[ext]',
						},
					},
				],
			},
		],
	},
	plugins: [
		new VueLoaderPlugin(),
	],
};
