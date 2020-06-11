/* eslint-disable no-await-in-loop */
const apiApp = require('polka')();
const sequelize = require('../models').sequelize;

const Locks = sequelize.model('locks');

const {yuuko} = require('../bot/index');
const config = require('../config');

apiApp.get('/all', async (request, response) => {
	try {
		response.json(await Locks.findAll());
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to modify locks.'});
	}

	let newLocks;
	try {
		newLocks = await request.json();
	} catch (error) {
		response.error('Invalid request.');
	}
	try {
		for (const lock of newLocks) {
			await Locks.update(lock, {where: {name: lock.name}});
		}
		yuuko.createMessage(config.discord.auditChannel, {
			embed: {
				title: 'Locks Changed',
				description: `Locks were changed by **${auth}**.`,
				color: 8302335,
			},
		});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

module.exports = apiApp;
