const {VueLoaderPlugin} = require('vue-loader');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');

// npm package wrapper around the canonical dart implementation of Sass
// (required for newly implemented @use stuff)
const dartSass = require('sass');

const config = require('./config');

module.exports = {
	mode: 'development', // prod mode when deploying apparently does good things
	entry: {
		main: './frontend/results/main.js',
		host: './frontend/host/main.js',
		vote: './frontend/voting/main.js',
		jurorApps: './frontend/jurorApps/main.js',
		finalVote: './frontend/final-vote/main.js',
	},
	output: {
		path: config.publicDir,
		filename: '[name].bundle.js',
		publicPath: '/',
	},
	optimization: {
		splitChunks: {
			cacheGroups: {
				commons: {
					test: /[\\/]node_modules[\\/]/,
					name: 'vendors',
					chunks: 'all',
				},
			},
		},
		runtimeChunk: 'single',
	},
	resolve: {
		extensions: [
			'.js',
			'.vue',
		],
	},
	watchOptions: {
		aggregateTimeout: 200,
		poll: 2000,
		ignored: /node_modules/,
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
					{
						loader: 'css-loader',
						options: {
							esModule: false,
						},
					},
				],
			},
			{
				test: /\.scss$/,
				use: [
					'vue-style-loader',
					{
						loader: 'css-loader',
						options: {
							esModule: false,
						},
					},
					{
						loader: 'sass-loader',
						options: {
							implementation: dartSass,
						},
					},
				],
			},
			{
				test: /\.js$/,
				use: {
					loader: 'babel-loader',
				},
				exclude: /node_modules/,
			},
			{
				test: /\.(png|svg|jpg|gif|pdf)$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: '[name].[hash].[ext]',
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
			filename: 'index.html',
			excludeChunks: ['host', 'vote', 'jurorApps', 'finalVote'],
		}),
		new HtmlWebpackPlugin({
			template: './public/template.html',
			title: '/r/anime Awards 2019',
			filename: 'host.html',
			excludeChunks: ['main', 'vote', 'jurorApps', 'finalVote'],
		}),
		new HtmlWebpackPlugin({
			template: './public/template.html',
			title: '/r/anime Awards 2019',
			filename: 'vote.html',
			excludeChunks: ['main', 'host', 'jurorApps', 'finalVote'],
		}),
		new HtmlWebpackPlugin({
			template: './public/template.html',
			title: '/r/anime Awards 2019',
			filename: 'jurorApps.html',
			excludeChunks: ['main', 'host', 'vote', 'finalVote'],
		}),
		new HtmlWebpackPlugin({
			template: './public/template.html',
			title: '/r/anime Awards 2019',
			filename: 'final-vote.html',
			excludeChunks: ['main', 'host', 'vote', 'jurorApps'],
		}),
		new CompressionPlugin({
			filename: '[path].br[query]',
			algorithm: 'brotliCompress',
			test: /\.(js|css|html|svg)$/,
			compressionOptions: {
				level: 11,
			},
			threshold: 10240,
			minRatio: 0.8,
		}),
	],
};
