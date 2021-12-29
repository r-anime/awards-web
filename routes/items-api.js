const apiApp = require('polka')();
const sequelize = require('../models').sequelize;
const {Op} = require('sequelize');

// Sequelize models to avoid redundancy
const Items = sequelize.model('items');

const {yuuko} = require('../bot/index');
const config = require('../config');

apiApp.post('/add', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be an host to modify items'});
	}

	let items;
	try {
		items = await request.json();
	} catch (error) {
		response.error(error);
	}
	try {
		const promiseArr = new Array();
		/*for (const item of items){
			promiseArr.push(new Promise(async (resolve, reject) => {
				try {
					await Items.create(item);
					console.log(item.anilistID);
					resolve();
				} catch (error) {
					// response.error(error);
					reject(error);
				}
			}));
		}*/

		promiseArr.push(new Promise(async (resolve, reject) => {
			try {
				await Items.bulkCreate(items);
				resolve();
			} catch (error) {
				// response.error(error);
				reject(error);
			}
		}));

		Promise.all(promiseArr).then(async ()=>{
			response.empty();
		});
		/*
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
		*/
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/update', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be an host to modify items'});
	}

	let item;
	try {
		item = await request.json();
	} catch (error) {
		response.error(error);
	}
	try {
		await Items.update(item, {where: {id: item.id}});
		response.json(await Items.findAll());
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/', async (request, response) => {
	try {
		response.json(await Items.findAll({include: [
			{
				model: Items,
				as: 'parent',
				include: [
					{
						model: Items,
						as: 'parent',
					},
				],
			},
		]}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/delete/imported', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be an host to delete items'});
	}
	try {
		await Items.destroy({where: {
			anilistID: {
				[Op.ne]: -1,
			}
		}});
		response.json(await Items.findAll());
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/delete', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be an host to delete items'});
	}
	let item;
	try {
		item = Number(await request.json());
	} catch (error) {
		response.error(error);
	}
	try {
		await Items.destroy({where: {
			id: item,
		}});
		response.json(await Items.findAll());
	} catch (error) {
		response.error(error);
	}
});

module.exports = apiApp;
