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
const {yuuko} = require('../bot/index');
const config = require('../config');

apiApp.get('/all', async (request, response) => {
	try {
		response.json(await Categories.findAll({where: {active: 1}, order: [['order', 'ASC']]}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.patch('/sort', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be a host to modify categories'});
	}
	let categories;
	try {
		categories = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}
	const promiseArr = [];
	// const t = await sequelize.transaction();
	for (let i = 0; i < categories.length; i++) {
		const category = categories[i];
		promiseArr.push(new Promise(async (resolve, reject) => {
			try {
				// console.log(category.id, i);
				await Categories.update({order: i}, {where: {id: category.id}});
				resolve();
			} catch (error) {
				response.error(error);
				reject(error);
			}
		}));
	}
	Promise.all(promiseArr).then(async () => {
		// await t.commit();
		// yuuko.createMessage(config.discord.auditChannel, {
		// 	embed: {
		// 		title: 'Category Order',
		// 		description: `Category order changed by **${auth}**.`,
		// 		color: 8302335,
		// 	},
		// });
		response.json(await Categories.findAll({where: {active: 1}, order: [['order', 'ASC']]}));
	}, error => response.error(error));
});

apiApp.delete('/wipeEverything', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to wipe everything.'});
	}
	const t = await sequelize.transaction();
	// WHY DOES THIS NOT WORK JGAPGJAGJEAGAELGK:KEAJL:GJMEAKL
	Promise.all([Entries.destroy({truncate: true, restartIdentity: true, transaction: t}), Noms.destroy({truncate: true, restartIdentity: true, transaction: t}), Jurors.destroy({truncate: true, restartIdentity: true, transaction: t}), HMs.destroy({truncate: true, restartIdentity: true, transaction: t})]).then(async () => {
		await t.commit();
		await sequelize.query("DELETE FROM sqlite_sequence WHERE name = 'entries'");
		yuuko.createMessage(config.discord.auditChannel, {
			embed: {
				title: 'Full Category Data Wipe',
				description: `There was a full category data wipe by **${auth}**.`,
				color: 8302335,
			},
		});
		response.empty();
	}, error => response.error(error));
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
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be a host to create categories'});
	}
	let category;
	try {
		category = await request.json();
		yuuko.createMessage(config.discord.auditChannel, {
			embed: {
				title: 'Category Created',
				description: `A category called **${category.name}** was created by **${auth}**.`,
				color: 8302335,
			},
		});
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
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be a host to modify categories'});
	}
	let category;
	try {
		category = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}
	yuuko.createMessage(config.discord.auditChannel, {
		embed: {
			title: 'Category Modified',
			description: `A category called **${category.name}** was modified by **${auth}**. It may or may not have been a different name previously.`,
			color: 8302335,
		},
	});
	try {
		await Categories.update(category, {where: {id: category.id}});
		response.json(await Categories.findOne({where: {id: category.id}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/:id', async (request, response) => {
	const auth = await request.authenticate({level: 4});
	if (!auth) {
		return response.json(401, {error: 'You must be an admin to delete categories'});
	}
	const category = await Categories.findOne({
		where: {
			id: request.params.id,
		},
	});
	try {
		Promise.all([Categories.destroy({
			where: {
				id: request.params.id,
			},
		}),
		Entries.destroy({where: {categoryId: request.params.id}}),
		Noms.destroy({where: {categoryId: request.params.id}}),
		HMs.destroy({where: {categoryId: request.params.id}}),
		Jurors.destroy({where: {categoryId: request.params.id}})]).then(() => {
			yuuko.createMessage(config.discord.auditChannel, {
				embed: {
					title: 'Category Deleted',
					description: `A category called **${category.name}** was deleted by **${auth}**.`,
					color: 8302335,
				},
			});
			response.empty();
		}, error => response.error(error));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/entries/all', async (request, response) => {
	try {
		response.json(await Entries.findAll({
			attributes: ['id', 'anilist_id', 'character_id', 'themeId', 'categoryId', 'search'],
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/entries/vote', async (request, response) => {
	try {
		response.json(await Entries.findAll({
			where: {
				character_id: null,
			},
			attributes: ['id', 'anilist_id', 'character_id', 'themeId', 'categoryId', 'search'],
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/:id/entries/copy/:copyid', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be a host to modify entries'});
	}
	
	const ogEntries = await Entries.findAll({
		where: {
			categoryId: request.params.copyid,
		},
	});

	let promiseArr = [];

	const t = await sequelize.transaction();
	try {
		for (const entry of ogEntries) {
			promiseArr.push(new Promise(async (resolve, reject) => {
				try {
					await Entries.create({
						anilist_id: entry.anilist_id,
						character_id: entry.character_id,
						themeId: entry.themeId,
						categoryId: request.params.id,
						search: entry.search ? entry.search : null,
					},
					{
						transaction: t,
					});
					resolve();
				} catch (error) {
					response.error(error);
					reject(error);
				}
			}));
		}

		Promise.all(promiseArr).then(async () => {
			await t.commit();
			const category = await Categories.findOne({where: {id: request.params.id}});
			yuuko.createMessage(config.discord.auditChannel, {
				embed: {
					title: 'Entries Modified',
					description: `Entries of a category called **${category.name}** were modified by **${auth}**.`,
					color: 8302335,
				},
			});
			response.json(await Entries.findAll({
				attributes: ['id', 'anilist_id', 'character_id', 'themeId', 'categoryId', 'search'],
			}));
		}, error => response.error(error));
		
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/:id/entries', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be a host to modify entries'});
	}
	let entries;
	try {
		entries = await request.json();
		console.log(entries);
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}
	let promiseArr = [];
	const ogEntries = await Entries.findAll({
		where: {
			categoryId: request.params.id,
		},
	});
	const t = await sequelize.transaction();
	try {
		for (const entry of entries) {
			promiseArr.push(new Promise(async (resolve, reject) => {
				try {
					await Entries.findOrCreate({
						where: {
							anilist_id: entry.anilist_id,
							character_id: entry.character_id,
							themeId: entry.themeId,
							categoryId: request.params.id,
						},
						defaults: {
							anilist_id: entry.anilist_id,
							character_id: entry.character_id,
							themeId: entry.themeId,
							categoryId: request.params.id,
							search: entry.search ? entry.search : null,
						},
						transaction: t,
					});
					resolve();
				} catch (error) {
					response.error(error);
					reject(error);
				}
			}));
		}

		Promise.all(promiseArr).then(async () => {
			await t.commit();
			const t2 = await sequelize.transaction();
			promiseArr = [];
			for (const entry of ogEntries) {
				const found = entries.find(element => element.anilist_id === entry.anilist_id && element.character_id === entry.character_id && element.themeId === entry.themeId);
				if (!found) {
					promiseArr.push(new Promise(async (resolve, reject) => {
						try {
							await Entries.destroy({
								where: {
									id: entry.id,
								},
								transaction: t2,
							});
							resolve();
						} catch (error) {
							response.error(error);
							reject(error);
						}
					}));
				}
			}
			Promise.all(promiseArr).then(async () => {
				await t2.commit();
				const category = await Categories.findOne({where: {id: request.params.id}});
				yuuko.createMessage(config.discord.auditChannel, {
					embed: {
						title: 'Entries Modified',
						description: `Entries of a category called **${category.name}** were modified by **${auth}**.`,
						color: 8302335,
					},
				});
				response.json(await Entries.findAll({
					attributes: ['id', 'anilist_id', 'character_id', 'themeId', 'categoryId', 'search'],
				}));
			}, error => response.error(error));
		});
	} catch (error) {
		response.error(error);
	}
});


apiApp.get('/:id/nominations', async (request, response) => {
	try {
		response.json(await Noms.findAll({
			where: {
				categoryId: request.params.id,
				active: true,
			},
			include: [{
				model: Categories,
				as: 'category',
			}],
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/:id/nominations', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be a host to add nominations'});
	}

	let nominations;
	try {
		nominations = await request.json();
	} catch (error) {
		response.error(error);
	}
	try {
		const ogNoms = await Noms.findAll({
			where: {
				categoryId: request.params.id,
				active: true,
			},
			attributes: ['id'],
		});
		const promise = new Promise(async (resolve, reject) => {
			try {
				for (const nom of nominations) {
					if (nom.themeId === -1) nom.themeId = null;
					if (nom.id) {
						await Noms.update({
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
						}, {
							where: {
								id: nom.id,
							},
						});
					} else {
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
				}
				resolve();
			} catch (error) {
				response.error(error);
				reject(error);
			}
		});
		promise.then(async () => {
			for (const nom of ogNoms) {
				const found = nominations.filter(aNom => aNom.id).find(aNom => aNom.id === nom.id);
				if (!found) {
					await Noms.update({active: false}, {
						where: {
							id: nom.id,
						}
					});
				}
			}
			const category = await Categories.findOne({where: {id: request.params.id}});
			yuuko.createMessage(config.discord.auditChannel, {
				embed: {
					title: 'Nominations Modified',
					description: `Nominations of a category called **${category.name}** were modified by **${auth}**.`,
					color: 8302335,
				},
			});
			response.json(await Noms.findAll({
				where: {
					categoryId: request.params.id,
					active: true,
				},
				include: [{
					model: Categories,
					as: 'category',
				}],
			}));
		}, error => response.error(error));
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

apiApp.post('/:id/jurors', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
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
						score: parseFloat(juror.score),
						preference: juror.preference,
					});
				}
				resolve();
			} catch (error) {
				response.error(error);
				reject(error);
			}
		});
		promise.then(async () => {
			const category = await Categories.findOne({where: {id: request.params.id}});
			yuuko.createMessage(config.discord.auditChannel, {
				embed: {
					title: 'Jurors Modified',
					description: `Jurors of a category called **${category.name}** were modified by **${auth}**.`,
					color: 8302335,
				},
			});
			response.json(await Jurors.findAll({
				where: {
					categoryId: request.params.id,
					active: true,
				},
			}));
		}, error => response.error(error));
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
		response.json(await Jurors.findAll({
			where: {active: true},
			include: [{
				model: Categories,
				as: 'category',
			}],
		}));
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
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be a host to add honorable mentions'});
	}
	let hms;
	try {
		hms = await request.json();
	} catch (error) {
		response.error(error);
	}
	try {
		const promise = new Promise(async (resolve, reject) => {
			try {
				for (const hm of hms) {
					await HMs.create({
						categoryId: request.params.id,
						name: hm.name,
						writeup: hm.writeup,
					});
				}
				resolve();
			} catch (error) {
				response.error(error);
				reject(error);
			}
		});
		promise.then(async () => {
			const category = await Categories.findOne({where: {id: request.params.id}});
			yuuko.createMessage(config.discord.auditChannel, {
				embed: {
					title: 'HMs Modified',
					description: `Honourable Mentions of a category called **${category.name}** were modified by **${auth}**.`,
					color: 8302335,
				},
			});
			response.json(await HMs.findAll({where: {active: true}}));
		}, error => response.error(error));
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

module.exports = apiApp;
