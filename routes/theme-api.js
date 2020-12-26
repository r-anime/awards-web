const apiApp = require('polka')();
const sequelize = require('../models').sequelize;
const parse = require('../themes/parser');

// Sequelize models to avoid redundancy
const Themes = sequelize.model('themes');

const {yuuko} = require('../bot/index');
const config = require('../config');

apiApp.get('/create', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to modify themes'});
	}
	const themes = await parse.readThemes('./themes/theme-data.csv');
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
					title: 'Themes imported',
					description: `Themes were imported by **${auth}**.`,
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
		response.json(await Themes.findAll({
			attributes: ['id', 'anime', 'title', 'themeType', 'anilistID', 'themeNo', 'link'],
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/delete', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to delete themes'});
	}
	try {
		const promise = new Promise(async (resolve, reject) => {
			try {
				await Themes.destroy({truncate: true, restartIdentity: true});
				await sequelize.query("DELETE FROM sqlite_sequence WHERE name = 'themes'");
				resolve();
			} catch (error) {
				response.error(error);
				reject(error);
			}
		});
		promise.then(() => {
			yuuko.createMessage(config.discord.auditChannel, {
				embed: {
					title: 'Themes deleted',
					description: `Themes were deleted by **${auth}**.`,
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
