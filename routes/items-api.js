const apiApp = require('polka')();
const sequelize = require('../models').sequelize;
const {Op, QueryTypes} = require('sequelize');

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
	} catch (error) {
		response.error(error);
	}
});


apiApp.post('/update/parents', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be an host to modify items'});
	}

	let data;
	try {
		data = await request.json();
	} catch (error) {
		response.error(error);
	}
	try {
		if (data.old && data.new){
			await Items.update({parentID: data.new}, {where: {parentID: data.old}});
			response.json(await Items.findAll());
		} else {
			response.error("Invalid Fields.");
		}
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
		response.empty();
		// response.json(await Items.findAll());
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/update/bulk', async (request, response) => {
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

		for (const item of items){
			promiseArr.push(new Promise(async (resolve, reject) => {
				try {
					// console.log(item.id);
					await Items.update(item, {where: {id: item.id}});
					resolve();
				} catch (error) {
					// console.log(error);
					// response.error(error);
					reject(error);
				}
			}));
		}
		Promise.all(promiseArr).then(async ()=>{
			response.empty();
		});
	} catch (error) {
		response.error(error);
	}
});


apiApp.get('/', async (request, response) => {
	try {
		response.json(await Items.findAll({include: [{
				model: Items,
				as: 'parent',
				where: {
					type: 'anime',
				},
				include: [
					{
						model: Items,
						as: 'parent',
						where: {
							type: 'char',
						},
					},
				],
			},],
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/page/:page', async (request, response) => {
	try {
		const page = request.params.page;
		const offset = 1000*page;
		
		/*
		(SELECT va.id           AS `id`,
			va.anilistid    AS `anilistID`,
			va.english      AS `english`,
			va.romanji      AS `romanji`,
			va.year         AS `year`,
			va.image        AS `image`,
			va.type         AS `type`,
			va.parentid     AS `parentID`,
			va.internal     AS `internal`,
			va.mediatype    AS `mediatype`,
			va.names        AS `names`,
			va.idmal        AS `idMal`,
			CHAR.id         AS `parent.id`,
			CHAR.anilistid  AS `parent.anilistID`,
			CHAR.english    AS `parent.english`,
			CHAR.romanji    AS `parent.romanji`,
			CHAR.type       AS `parent.type`,
			CHAR.mediatype  AS `parent.mediatype`,
			CHAR.internal   AS `parent.internal`,
			CHAR.names      AS `parent.names`,
			ANIME.id         AS `parent.parent.id`,
			ANIME.anilistid  AS `parent.parent.anilistID`,
			ANIME.english    AS `parent.parent.english`,
			ANIME.romanji    AS `parent.parent.romanji`,
			ANIME.type       AS `parent.parent.type`,
			ANIME.mediatype  AS `parent.parent.mediatype`,
			ANIME.internal   AS `parent.parent.internal`
			ANIME.names      AS `parent.parent.names`
		FROM   items AS va
			LEFT JOIN items AS CHAR
					ON va.parentid = CHAR.anilistid
						AND CHAR.type = "char"
			LEFT JOIN items AS ANIME
					ON CHAR.parentid = ANIME.anilistid
						AND ANIME.type = "anime"
		WHERE  va.type = "va"
		UNION
		SELECT CHAR.id         AS `id`,
			CHAR.anilistid  AS `anilistID`,
			CHAR.english    AS `english`,
			CHAR.romanji    AS `romanji`,
			CHAR.year       AS `year`,
			CHAR.image      AS `image`,
			CHAR.type       AS `type`,
			CHAR.parentid   AS `parentID`,
			CHAR.internal   AS `internal`,
			CHAR.mediatype  AS `mediatype`,
			CHAR.names      AS `names`,
			CHAR.idmal      AS `idMal`,
			ANIME.id         AS `parent.id`,
			ANIME.anilistid  AS `parent.anilistID`,
			ANIME.english    AS `parent.english`,
			ANIME.romanji    AS `parent.romanji`,
			ANIME.type       AS `parent.type`,
			ANIME.mediatype  AS `parent.mediatype`,
			ANIME.internal   AS `parent.internal`,
			ANIME.names      AS `parent.names`,
			NULL             AS `parent.parent.id`,
			NULL				AS `parent.parent.anilistID`,
			NULL			    AS `parent.parent.english`,
			NULL  			AS `parent.parent.romanji`,
			NULL       		AS `parent.parent.type`,
			NULL 			AS `parent.parent.mediatype`,
			NULL 			AS `parent.parent.internal`
			NULL 			AS `parent.parent.names`
		FROM   items AS CHAR
			LEFT JOIN items AS `ANIME`
					ON CHAR.parentid = ANIME.anilistid
						AND ANIME.type = "anime"
		WHERE  CHAR.type = "char"
		UNION
		SELECT ANIME.id         AS `id`,
			ANIME.anilistid  AS `anilistID`,
			ANIME.english    AS `english`,
			ANIME.romanji    AS `romanji`,
			ANIME.year       AS `year`,
			ANIME.image      AS `image`,
			ANIME.type       AS `type`,
			ANIME.parentid   AS `parentID`,
			ANIME.internal   AS `internal`,
			ANIME.mediatype  AS `mediatype`,
			ANIME.names      AS `names`,
			ANIME.idmal      AS `idMal`,
			NULL   		    AS `parent.id`,
			NULL				AS `parent.anilistID`,
			NULL  			AS `parent.english`,
			NULL			    AS `parent.romanji`,
			NULL  		    AS `parent.type`,
			NULL			    AS `parent.mediatype`,
			NULL 			AS `parent.internal`,
			NULL 			AS `parent.names`,
			NULL             AS `parent.parent.id`,
			NULL				AS `parent.parent.anilistID`,
			NULL			    AS `parent.parent.english`,
			NULL  			AS `parent.parent.romanji`,
			NULL       		AS `parent.parent.type`,
			NULL 			AS `parent.parent.mediatype`,
			NULL 			AS `parent.parent.internal`
			NULL 			AS `parent.parent.names`
		FROM   items AS ANIME
		WHERE  ANIME.type = "anime")

		*/

		const [results, metadata] = await sequelize.query(
			'SELECT * FROM (SELECT va.id AS `id`, va.anilistid AS `anilistID`, va.english AS `english`, va.romanji AS `romanji`, va.year AS `year`, va.image AS `image`, va.type AS `type`, va.parentid AS `parentID`, va.internal AS `internal`, va.mediatype AS `mediatype`, va.names AS `names`, va.idmal AS `idMal`, CHAR.id AS `parent.id`, CHAR.anilistid AS `parent.anilistID`, CHAR.english AS `parent.english`, CHAR.romanji AS `parent.romanji`, CHAR.type AS `parent.type`, CHAR.mediatype AS `parent.mediatype`, CHAR.internal AS `parent.internal`, CHAR.names AS `parent.names`, ANIME.id AS `parent.parent.id`, ANIME.anilistid AS `parent.parent.anilistID`, ANIME.english AS `parent.parent.english`, ANIME.romanji AS `parent.parent.romanji`, ANIME.type AS `parent.parent.type`, ANIME.mediatype AS `parent.parent.mediatype`, ANIME.internal AS `parent.parent.internal`, ANIME.names AS `parent.parent.names` FROM items AS va LEFT JOIN items AS CHAR ON va.parentid = CHAR.anilistid AND CHAR.type = "char" LEFT JOIN items AS ANIME ON CHAR.parentid = ANIME.anilistid AND ANIME.type = "anime" WHERE va.type = "va" UNION SELECT CHAR.id AS `id`, CHAR.anilistid AS `anilistID`, CHAR.english AS `english`, CHAR.romanji AS `romanji`, CHAR.year AS `year`, CHAR.image AS `image`, CHAR.type AS `type`, CHAR.parentid AS `parentID`, CHAR.internal AS `internal`, CHAR.mediatype AS `mediatype`, CHAR.names AS `names`, CHAR.idmal AS `idMal`, ANIME.id AS `parent.id`, ANIME.anilistid AS `parent.anilistID`, ANIME.english AS `parent.english`, ANIME.romanji AS `parent.romanji`, ANIME.type AS `parent.type`, ANIME.mediatype AS `parent.mediatype`, ANIME.internal AS `parent.internal`, ANIME.names AS `parent.names`, NULL AS `parent.parent.id`, NULL AS `parent.parent.anilistID`, NULL AS `parent.parent.english`, NULL AS `parent.parent.romanji`, NULL AS `parent.parent.type`, NULL AS `parent.parent.mediatype`, NULL AS `parent.parent.internal`, NULL AS `parent.parent.names` FROM items AS CHAR LEFT JOIN items AS `ANIME` ON CHAR.parentid = ANIME.anilistid AND ANIME.type = "anime" WHERE CHAR.type = "char" UNION SELECT ANIME.id AS `id`, ANIME.anilistid AS `anilistID`, ANIME.english AS `english`, ANIME.romanji AS `romanji`, ANIME.year AS `year`, ANIME.image AS `image`, ANIME.type AS `type`, ANIME.parentid AS `parentID`, ANIME.internal AS `internal`, ANIME.mediatype AS `mediatype`, ANIME.names AS `names`, ANIME.idmal AS `idMal`, NULL AS `parent.id`, NULL AS `parent.anilistID`, NULL AS `parent.english`, NULL AS `parent.romanji`, NULL AS `parent.type`, NULL AS `parent.mediatype`, NULL AS `parent.internal`, NULL AS `parent.names`, NULL AS `parent.parent.id`, NULL AS `parent.parent.anilistID`, NULL AS `parent.parent.english`, NULL AS `parent.parent.romanji`, NULL AS `parent.parent.type`, NULL AS `parent.parent.mediatype`, NULL AS `parent.parent.internal`, NULL AS `parent.parent.names` FROM items AS ANIME WHERE ANIME.type = "anime") ORDER BY id LIMIT $limit OFFSET $page',
			{
				bind: { limit: 1000, page: offset},
				type: QueryTypes.select
			}
		);

		const count = await Items.count();

		response.json({
			rows: results,
			count: count
		});
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/delete/imported/char', async (request, response) => {
	const auth = await request.authenticate({level: 2});
	if (!auth) {
		return response.json(401, {error: 'You must be an host to delete items'});
	}
	try {
		await Items.destroy({where: {
			anilistID: {
				[Op.ne]: -1,
			},
			type: {
				[Op.or]: ['va', 'char'],
			}
		}});
		response.json(await Items.findAll());
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
