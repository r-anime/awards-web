const log = require('another-logger');
const apiApp = require('polka')();
const sequelize = require('../models').sequelize;
const parse = require('../themes/parser');

// Sequelize models to avoid redundancy
const Themes = sequelize.model('themes');

apiApp.post('/create', async (request, response) => {
	if (!await request.authenticate({level: 4})) {
		return response.json(401, {error: 'You must be an admin to modify themes'});
	}
	let req;
	try {
		req = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}
	const themes = await parse.readThemes(`./themes/${req.themeType.toUpperCase()}.csv`);
	log.success(themes);
	try {
		const promise = new Promise(async (resolve, reject) => {
			try {
				for (const theme of themes) {
					// eslint-disable-next-line no-await-in-loop
					await Themes.create(theme);
				}
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		promise.then(async () => {
			response.json(await Themes.findAll());
		});
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/', async (request, response) => {
	try {
		response.json(await Themes.findAll());
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/delete/:themeType', async (request, response) => {
	if (!await request.authenticate({level: 4})) {
		return response.json(401, {error: 'You must be an admin to delete themes'});
	}
	try {
		const promise = new Promise(async (resolve, reject) => {
			try {
				await Themes.destroy({where: {themeType: request.params.themeType}});
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		promise.then(() => {
			response.empty();
		});
	} catch (error) {
		response.error(error);
	}
});

module.exports = apiApp;
