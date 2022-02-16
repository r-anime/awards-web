const apiApp = require('polka')();
const sequelize = require('../models').sequelize;

const FinalVotes = sequelize.model('finalvotes');
const WatchVotes = sequelize.model('WatchVotes');

apiApp.get('/get', async (request, response) => {
	const userName = (await request.reddit().get('/api/v1/me')).body.name;
	if (!await request.authenticate({name: userName, oldEnough: true, lock: 'fv-genre'})) {
		return response.json(401, {error: 'Your account may be too new or voting may be closed.'});
	}
	try {
		const _votes = await FinalVotes.findAll({where: {reddit_user: userName}});
		response.json(_votes);
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/submit', async (request, response) => {
	const userName = await request.authenticate({name: request.session.reddit_name, oldEnough: true, lock: 'fv-genre'});
	if (!userName) {
		return response.json(401, {error: 'Invalid user. Your account may be too new or voting may be closed.'});
	}
	let req;
	try {
		req = await request.json();
	} catch (error) {
		return response.json(400, {error: 'Invalid JSON'});
	}

	const _values = {
		reddit_user: userName,
		category_id: req.category_id,
		nom_id: req.nom_id,
		anilist_id: req.anilist_id,
	};

	FinalVotes.findOne({where: {reddit_user: userName, category_id: req.category_id}})
		.then(async obj => {
		// update
			if (obj) {
				await obj.update(_values);
			} else {
				await FinalVotes.create(_values);
			}
		}).finally(async () => {
			try {
				const _votes = await FinalVotes.findAll({where: {reddit_user: userName}});
				// console.log(_votes);
				response.json(_votes);
			} catch (error) {
				return response.error(error);
			}
		});
});

apiApp.get('/survey/get', async (request, response) => {
	const userName = (await request.reddit().get('/api/v1/me')).body.name;
	if (!await request.authenticate({name: userName, oldEnough: true, lock: 'fv-genre'})) {
		return response.json(401, {error: 'Your account may be too new or voting may be closed.'});
	}
	try {
		const _votes = await WatchVotes.findAll({where: {reddit_user: userName}});
		response.json(_votes);
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/survey/submit', async (request, response) => {
	const userName = await request.authenticate({name: request.session.reddit_name, oldEnough: true, lock: 'fv-genre'});
	if (!userName) {
		return response.json(401, {error: 'Invalid user. Your account may be too new or voting may be closed.'});
	}
	let req;
	try {
		req = await request.json();
	} catch (error) {
		return response.json(400, {error: 'Invalid JSON'});
	}

	const _values = {
		reddit_user: userName,
		anilist_id: req.anilist_id,
		name: req.name,
    	status: req.status
	};

	WatchVotes.findOne({where: {reddit_user: userName, anilist_id: req.anilist_id, name: req.name}})
		.then(async obj => {
			// update
			if (obj) {
				await obj.update(_values);
			} else {
				await WatchVotes.create(_values);
			}
		}).finally(async () => {
			try {
				const _votes = await WatchVotes.findAll({where: {reddit_user: userName}});
				// console.log(_votes);
				response.json(_votes);
			} catch (error) {
				return response.error(error);
			}
		});
});

apiApp.get('/summary', async (request, response) => {
	if (!await request.authenticate({level: 2, lock: 'fv-results'})) {
		return response.json(401, {error: 'You must be a host to view vote summary.'});
	}
	try {
		const allVotes = await FinalVotes.findAll();
		// eslint-disable-next-line no-unused-vars
		const [allUsers, userMeta] = await sequelize.query('SELECT COUNT(DISTINCT `reddit_user`) as `count` FROM `finalvotes`');

		const voteSummary = {
			votes: allVotes.length,
			users: allUsers[0].count,
			allVotes: [],
		};
		response.json(voteSummary);
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/totals', async (request, response) => {
	if (!await request.authenticate({level: 2, lock: 'fv-results'})) {
		return response.json(401, {error: 'You must be a host to view vote summary.'});
	}
	try {
		const [res] = await sequelize.query('SELECT COUNT(*) as `vote_count`, `finalvotes`.`category_id`, `finalvotes`.`nom_id`, `finalvotes`.`anilist_id` FROM `finalvotes` GROUP BY `finalvotes`.`category_id`, `finalvotes`.`nom_id`, `finalvotes`.`anilist_id` ORDER BY `finalvotes`.`category_id` ASC, `vote_count` DESC');
		response.json(res);
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/totals', async (request, response) => {
	if (!await request.authenticate({level: 2, lock: 'fv-results'})) {
		return response.json(401, {error: 'You must be a host to view vote summary.'});
	}
	try {
		const [res] = await sequelize.query('SELECT COUNT(*) as `vote_count`, `finalvotes`.`category_id`, `finalvotes`.`nom_id`, `finalvotes`.`anilist_id` FROM `finalvotes` GROUP BY `finalvotes`.`category_id`, `finalvotes`.`nom_id`, `finalvotes`.`anilist_id` ORDER BY `finalvotes`.`category_id` ASC, `vote_count` DESC');
		response.json(res);
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/totals', async (request, response) => {
	if (!await request.authenticate({level: 2, lock: 'fv-results'})) {
		return response.json(401, {error: 'You must be a host to view vote summary.'});
	}
	try {
		const [res] = await sequelize.query('SELECT COUNT(*) as `vote_count`, `finalvotes`.`category_id`, `finalvotes`.`nom_id`, `finalvotes`.`anilist_id` FROM `finalvotes` GROUP BY `finalvotes`.`category_id`, `finalvotes`.`nom_id`, `finalvotes`.`anilist_id` ORDER BY `finalvotes`.`category_id` ASC, `vote_count` DESC');
		response.json(res);
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/watched', async (request, response) => {
	if (!await request.authenticate({level: 2, lock: 'fv-results'})) {
		return response.json(401, {error: 'You must be a host to view vote summary.'});
	}
	try {
		const [res] = await sequelize.query('SELECT COUNT(*) as `vote_count`, `finalvotes`.`category_id`, `finalvotes`.`nom_id`, `finalvotes`.`anilist_id` FROM `finalvotes` INNER JOIN `WatchVotes` ON `finalvotes`.`reddit_user` = `WatchVotes`.`reddit_user` AND `finalvotes`.`anilist_id` = `WatchVotes`.`anilist_id` WHERE `WatchVotes`.`status` = 1 GROUP BY `finalvotes`.`category_id`, `finalvotes`.`nom_id`, `finalvotes`.`anilist_id` ORDER BY `finalvotes`.`category_id` ASC, `vote_count` DESC');
		response.json(res);
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/watchstats', async (request, response) => {
	if (!await request.authenticate({level: 2, lock: 'fv-results'})) {
		return response.json(401, {error: 'You must be a host to view vote summary.'});
	}
	try {
		const [res] = await sequelize.query('SELECT COUNT(*) as `vote_count`, `anilist_id`, `name` FROM `WatchVotes` WHERE `status`=1 GROUP BY `anilist_id`, `name` ORDER BY `vote_count` DESC');
		response.json(res);
	} catch (error) {
		response.error(error);
	}
});



module.exports = apiApp;
