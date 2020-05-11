/* eslint-disable no-await-in-loop */
const apiApp = require('polka')();
const sequelize = require('../models').sequelize;

// Sequelize models to avoid redundancy
const Categories = sequelize.model('categories');
const Noms = sequelize.model('nominations');
const Jurors = sequelize.model('jurors');
const HMs = sequelize.model('honorable_mentions');
const Entries = sequelize.model('entries');

// eslint-disable-next-line no-unused-vars
const log = require('another-logger');

apiApp.get('/all', async (request, response) => {
	try {
		response.json(await Categories.findAll({where: {active: 1}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/:id', async (request, response) => {
	try {
		response.json(await Categories.findOne({
			where: {
				id: request.params.id,
			},
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to create categories'});
	}
	let category;
	try {
		category = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}

	try {
		response.json(await Categories.create(category));
	} catch (error) {
		response.error(error);
	}
});

apiApp.patch('/:id', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to modify categories'});
	}
	let category;
	try {
		category = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}
	// HACK there's gotta be a better way to merge things than this wow
	category = Object.assign({}, await Categories.findOne({
		where: {
			id: category.id,
		},
	}));
	try {
		const res = await Categories.update(category, {where: {id: category.id}});
		response.json(res);
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/:id', async (request, response) => {
	if (!await request.authenticate({level: 4})) {
		return response.json(401, {error: 'You must be an admin to delete categories'});
	}
	try {
		await Categories.destroy({
			where: {
				id: request.params.id,
			},
		});
		await Entries.destroy({where: {categoryId: request.params.id}});
		await Noms.destroy({where: {categoryId: request.params.id}});
		await HMs.destroy({where: {categoryId: request.params.id}});
		await Jurors.destroy({where: {categoryId: request.params.id}});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/:group', async (request, response) => {
	try {
		response.json(await Categories.findAll({where: {active: true, awardsGroup: request.params.group}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/:id/entries', async (request, response) => {
	try {
		response.json(await Entries.findAll({where: {categoryId: request.params.id}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/:id/entries', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to modify entries'});
	}
	let entries;
	try {
		entries = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}
	try {
		for (const entry of entries) {
			await Entries.findOrCreate({
				where: {
					anilist_id: entry.anilist_id,
					character_id: entry.character_id,
					themeId: entry.themeId,
				},
				defaults: {
					categoryId: request.params.id,
				},
			});
		}

		const ogEntries = await Entries.findAll({
			where: {
				categoryId: request.params.id,
			},
		});

		for (const entry of ogEntries) {
			const found = entries.find(element => element.anilist_id === entry.anilist_id && element.character_id === entry.character_id && element.themeId === entry.themeId);
			if (!found) {
				await Entries.destroy({
					where: {
						id: entry.id,
					},
				});
			}
		}
		response.json(await Entries.findAll({
			where: {
				categoryId: request.params.id,
			},
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/:id/nominations', async (request, response) => {
	try {
		response.json(await Noms.findAll({
			where: {
				categoryId: request.params.id,
				active: 1,
			},
			include: {
				model: Categories,
			},
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/:id/nominations', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to add nominations'});
	}

	let nominations;
	try {
		nominations = await request.json();
		// log.success(nominations);
	} catch (error) {
		response.error(error);
	}
	try {
		const promise = new Promise(async (resolve, reject) => {
			try {
				for (const nom of nominations) {
					if (nom.themeId === -1) nom.themeId = null;
					await Noms.create({
						alt_name: nom.alt_name,
						alt_img: nom.alt_img,
						categoryId: request.params.id,
						anilist_id: nom.anilist_id,
						themeId: nom.themeId,
						writeup: nom.writeup,
						rank: nom.rank,
						votes: nom.votes,
						character_id: nom.character_id,
						finished: nom.finished,
						staff: nom.staff,
					});
				}
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		promise.then(async () => {
			response.json(await Noms.findAll({
				where: {
					categoryId: request.params.id,
				},
				include: {
					model: Categories,
				},
			}));
		});
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/:id/nominations', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to delete nominations'});
	}
	try {
		await Noms.destroy({where: {categoryId: request.params.id}});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/nominations/all', async (request, response) => {
	try {
		response.json(await Noms.findAll({where: {active: true}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/:id/jurors', async (request, response) => {
	try {
		response.json(await Jurors.findAll({
			where: {
				categoryId: request.params.id,
				active: true,
			},
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/:id/jurors', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to add jurors'});
	}
	let jurors;
	try {
		jurors = await request.json();
	} catch (error) {
		response.error(error);
	}
	try {
		const promise = new Promise(async (resolve, reject) => {
			try {
				for (const juror of jurors) {
					// log.success(nom);
					await Jurors.create({
						categoryId: request.params.id,
						name: juror.name,
						link: juror.link,
					});
				}
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		promise.then(async () => {
			response.json(await Jurors.findAll({
				where: {
					categoryId: request.params.id,
					active: true,
				},
			}));
		});
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/:id/jurors', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to delete jurors'});
	}
	try {
		await Jurors.destroy({where: {categoryId: request.params.id}});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/jurors/all', async (request, response) => {
	try {
		response.json(await Jurors.findAll({where: {active: true}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/:id/hms', async (request, response) => {
	try {
		response.json(await HMs.findAll({
			where: {
				categoryId: request.params.id,
				active: true,
			},
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/:id/hms', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to add honorable mentions'});
	}
	let hms;
	try {
		hms = await request.json();
		// log.success(nominations);
	} catch (error) {
		response.error(error);
	}
	try {
		const promise = new Promise(async (resolve, reject) => {
			try {
				for (const hm of hms) {
					// log.success(nom);
					await HMs.create({
						categoryId: request.params.id,
						name: hm.name,
						writeup: hm.writeup,
					});
				}
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		promise.then(async () => {
			response.json(await HMs.findAll({where: {active: true}}));
		});
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/:id/hms', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to delete honorable mentions.'});
	}
	try {
		await HMs.destroy({where: {categoryId: request.params.id}});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/hms/all', async (request, response) => {
	try {
		response.json(await HMs.findAll({where: {active: true}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/wipeEverything', async (request, response) => {
	if (!await request.authenticate({level: 4})) {
		return response.json(401, {error: 'You must be an admin to wipe everything.'});
	}
	Promise.all([Entries.destroy({truncate: true, restartIdentity: true}), Noms.destroy({truncate: true, restartIdentity: true}), Jurors.destroy({truncate: true, restartIdentity: true}), HMs.destroy({truncate: true, restartIdentity: true})]).then(() => response.empty(), error => response.error(error));
});

module.exports = apiApp;
