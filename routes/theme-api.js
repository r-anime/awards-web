const apiApp = require('polka')();
const sequelize = require('../models').sequelize;
const parse = require('../themes/parser');

// Sequelize models to avoid redundancy
const Themes = sequelize.model('themes');

const {yuuko} = require('../bot/index');
const config = require('../config');

apiApp.post('/create', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to modify themes'});
	}
	let req;
	try {
		req = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}
	const themes = await parse.readThemes(`./themes/${req.themeType.toUpperCase()}.csv`);
	try {
		const promise = new Promise(async (resolve, reject) => {
			try {
				for (const theme of themes) {
					// eslint-disable-next-line no-await-in-loop
					await Themes.create(theme);
				}
				resolve();
			} catch (error) {
				response.error(error);
				reject(error);
			}
		});
		promise.then(async () => {
			yuuko.createMessage(config.discord.auditChannel, {
				embed: {
					title: `${req.themeType.toUpperCase()}s imported`,
					description: `${req.themeType.toUpperCase()}s were imported by **${auth}**.`,
					color: 8302335,
				},
			});
			response.json(await Themes.findAll());
		}, error => response.error(error));
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
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to delete themes'});
	}
	try {
		const promise = new Promise(async (resolve, reject) => {
			try {
				await Themes.destroy({where: {themeType: request.params.themeType}});
				resolve();
			} catch (error) {
				response.error(error);
				reject(error);
			}
		});
		promise.then(() => {
			yuuko.createMessage(config.discord.auditChannel, {
				embed: {
					title: `${request.params.themeType.toUpperCase()}s deleted`,
					description: `${request.params.themeType.toUpperCase()}s were deleted by **${auth}**.`,
					color: 8302335,
				},
			});
			response.empty();
		}, error => response.error(error));
	} catch (error) {
		response.error(error);
	}
});

module.exports = apiApp;
