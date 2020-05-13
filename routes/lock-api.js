const apiApp = require('polka')();
const sequelize = require('../models').sequelize;

const Locks = sequelize.model('locks');

apiApp.get('/all', async (request, response) => {
	try {
		response.json(await Locks.findAll());
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/:name', async (request, response) => {
	if (!await request.authenticate({level: 4})) {
		return response.json(401, {error: 'You must be an admin to modify locks.'});
	}

	let newLock;
	try {
		newLock = await request.json();
	} catch (error) {
		response.error('Invalid request.');
	}
	try {
		await Locks.update(newLock, {where: {name: newLock.name}});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

module.exports = apiApp;
