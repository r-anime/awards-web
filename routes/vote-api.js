/* eslint-disable multiline-comment-style */
const apiApp = require('polka')();
const sequelize = require('../models').sequelize;
const Fuse = require('fuse.js');

// Sequelize models to avoid redundancy
const Votes = sequelize.model('votes');
const Entries = sequelize.model('entries');

apiApp.post('/character-search', async (request, response) => {
	let req;
	try {
		req = await request.json();
	} catch (error) {
		return response.json(400, {error: 'Invalid JSON'});
	}
	const entries = await Entries.findAll({
		where: {
			categoryId: req.categoryId,
		},
	});
	const fuse = new Fuse(entries, {
		keys: ['searchSchema.character', 'searchSchema.anime', 'searchSchema.va', 'searchSchema.synonyms'],
		minMatchCharLength: 3,
		shouldSort: true,
		ignoreLocation: true,
		threshold: 0.3,
		location: 0,
		distance: 70,
		maxPatternLength: 64,
	});
	response.json(fuse.search(req.search));
});

apiApp.post('/submit', async (request, response) => {
	const userName = await request.authenticate({name: request.session.reddit_name, oldEnough: true, lock: 'voting'});
	if (!userName) {
		return response.json(401, {error: 'Invalid user. Your account may be too new or voting may be closed.'});
	}
	let req;
	try {
		req = await request.json();
	} catch (error) {
		return response.json(400, {error: 'Invalid JSON'});
	}
	await Votes.create({
		reddit_user: userName,
		category_id: req.category_id,
		entry_id: req.entry_id,
		anilist_id: req.anilist_id,
		theme_name: req.theme_name,
	});
	response.empty();
});

apiApp.post('/delete', async (request, response) => {
	const userName = await request.authenticate({name: request.session.reddit_name, oldEnough: true, lock: 'voting'});
	if (!userName) {
		return response.json(401, {error: 'Invalid user. Your account may be too new or voting may be closed.'});
	}
	let req;
	try {
		req = await request.json();
	} catch (error) {
		return response.json(400, {error: 'Invalid JSON'});
	}
	await Votes.destroy({
		where: {
			reddit_user: userName,
			category_id: req.category_id,
			entry_id: req.entry_id,
			anilist_id: req.anilist_id,
			theme_name: req.theme_name,
		},
	});
	response.empty();
});

apiApp.get('/summary', async (request, response) => {
	if (!await request.authenticate({level: 2, lock: 'hostResults'})) {
		return response.json(401, {error: 'You must be a host to view vote summary.'});
	}
	try {
		const allVotes = await Votes.findAll();
		// eslint-disable-next-line no-unused-vars
		const [allUsers, userMeta] = await sequelize.query('SELECT COUNT(DISTINCT `reddit_user`) as `count` FROM `votes`');

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

apiApp.get('/all/get', async (request, response) => {
	if (!await request.authenticate({level: 2, lock: 'hostResults'})) {
		response.json(401, {error: 'You must be a host to see vote totals.'});
	}
	try {
		// eslint-disable-next-line no-unused-vars
		const [res, metadata] = await sequelize.query('SELECT COUNT(*) as `vote_count`, `votes`.`category_id`, `votes`.`entry_id`, `votes`.`anilist_id`, `votes`.`theme_name` FROM `votes` GROUP BY `votes`.`category_id`, `votes`.`entry_id`, `votes`.`anilist_id`, `votes`.`theme_name` ORDER BY `votes`.`category_id` ASC, `vote_count` DESC');
		response.json(res);
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/oped/get', async (request, response) => {
	if (!await request.authenticate({level: 2, lock: 'hostResults'})) {
		response.json(401, {error: 'You must be a host to see vote totals.'});
	}
	try {
		// eslint-disable-next-line no-unused-vars
		const [res, metadata] = await sequelize.query('SELECT COUNT(*) as `vote_count`, `votes`.`category_id`, `votes`.`entry_id`, `votes`.`anilist_id`, `votes`.`theme_name` FROM `votes` WHERE `votes`.`theme_name` IS NOT NULL GROUP BY `votes`.`category_id`, `votes`.`theme_name` ORDER BY `votes`.`category_id` ASC, `vote_count` DESC');
		response.json(res);
	} catch (error) {
		response.error(error);
	}
});


apiApp.get('/all/delete', async (request, response) => {
	if (!await request.authenticate({level: 4})) {
		return response.json(401, {error: 'You must be an admin to delete all votes.'});
	}
	return response.json(400, {error: "I don't think you want to do that"});

	// try {
	// 	db.deleteAllVotes();
	// 	response.empty();
	// } catch (error) {
	// 	response.error(error);
	// }
});

apiApp.get('/get', async (request, response) => {
	const userName = (await request.reddit().get('/api/v1/me')).body.name;
	if (!await request.authenticate({name: userName, oldEnough: true, lock: 'voting'})) {
		return response.json(401, {error: 'Your account may be too new or voting may be closed.'});
	}
	try {
		response.json(await Votes.findAll({where: {reddit_user: userName}}));
	} catch (error) {
		response.error(error);
	}
});

// Old /submit route
/*
apiApp.post('/submit', async (request, response) => {
	const userName = (await request.reddit().get('/api/v1/me')).body.name;
	if (!await request.authenticate({name: userName, oldEnough: true, lock: 'voting'})) {
		return response.json(401, {error: 'Invalid user. Your account may be too new.'});
	}
	let req;
	try {
		req = Object.entries(await request.json());
	} catch (error) {
		return response.json(400, {error: 'Invalid JSON'});
	}
	const t = await sequelize.transaction();
	Promise.all([Categories.findAll({where: {active: 1}}), Votes.findAll({where: {reddit_user: userName}})]).then(data => {
		const categories = data[0];
		const userVotes = data[1];
		let promiseArr = [];

		for (const [id, entries] of req) {
			if (entries.length === 0) {
				continue;
			}
			// eslint-disable-next-line eqeqeq
			const category = categories.find(cat => cat.id == id); // The eqeq is very important
			for (const entry of entries) {
				if (entry == null) continue;
				if (category.entryType === 'themes') {
					promiseArr.push(Votes.findOrCreate({
						where: {
							entry_id: entry.id,
							reddit_user: userName,
							category_id: category.id,
							theme_name: entry.title,
							anilist_id: entry.anilistID,
						},
						defaults: {
							entry_id: entry.id,
							reddit_user: userName,
							category_id: category.id,
							theme_name: entry.title,
							anilist_id: entry.anilistID,
						},
						transaction: t,
					}));
				} else {
					promiseArr.push(Votes.findOrCreate({
						where: {
							entry_id: entry.id,
							reddit_user: userName,
							category_id: category.id,
						},
						defaults: {
							entry_id: entry.id,
							reddit_user: userName,
							category_id: category.id,
						},
						transaction: t,
					}));
				}
			}
			// eslint-disable-next-line no-loop-func
			Promise.all(promiseArr).then(async () => {
				await t.commit();
				promiseArr = [];
				const t2 = await sequelize.transaction();
				const filteredVotes = userVotes.filter(vote => vote.category_id === category.id);
				for (const vote of filteredVotes) {
					const found = entries.find(entry => entry.id === vote.entry_id);
					if (!found) {
						promiseArr.push(Votes.destroy({
							where: {entry_id: vote.entry_id},
							transaction: t2,
						}));
					}
				}
				Promise.all(promiseArr).then(async () => {
					await t2.commit();
					response.empty();
				}, error => response.error(error));
			}, error => response.error(error));
		}
	}, error => response.error(error));
});
*/

module.exports = apiApp;
