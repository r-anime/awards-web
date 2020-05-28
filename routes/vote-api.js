/* eslint-disable no-await-in-loop */
const apiApp = require('polka')();
const sequelize = require('../models').sequelize;

// Sequelize models to avoid redundancy
const Votes = sequelize.model('votes');
const Categories = sequelize.model('categories');

apiApp.get('/summary', async (request, response) => {
	if (!await request.authenticate({level: 2, lock: 'hostResults'})) {
		return response.json(401, {error: 'You must be a host to view vote summary.'});
	}
	try {
		const allVotes = await Votes.findAll();
		// eslint-disable-next-line no-unused-vars
		const [allUsers, userMeta] = await sequelize.query('SELECT COUNT(DISTINCT `reddit_user`) as `count` FROM `votes`');

		// console.log(allUsers);

		const voteSummary = {
			votes: allVotes.length,
			users: allUsers[0].count,
			allVotes: [],
		};
		// eslint-disable-next-line multiline-comment-style
		/* for (const vote of allVotes) {
			voteSummary.votes += 1;
			if (!allUsers[vote.reddit_user]) {
				allUsers[vote.reddit_user] = true;
				voteSummary.users += 1;
			}
			voteSummary.allVotes.push({
				id: vote.id,
				categoryId: vote.category_id,
				entryId: vote.entry_id,
				anilistId: vote.anilist_id,
				themeName: vote.theme_name,
			});
		}*/
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

apiApp.get('/dashboard/get', async (request, response) => {
	if (!await request.authenticate({level: 2, lock: 'hostResults'})) {
		response.json(401, {error: 'You must be a host to see vote totals.'});
	}
	try {
		// eslint-disable-next-line no-unused-vars
		const [res, metadata] = await sequelize.query('SELECT COUNT(*) as `vote_count`, `votes`.`category_id`, `votes`.`entry_id`, `votes`.`anilist_id`, `votes`.`theme_name` FROM `votes` WHERE `votes`.`anilist_id` IS NOT NULL AND `votes`.`theme_name` IS NULL GROUP BY `votes`.`category_id`, `votes`.`anilist_id` ORDER BY `votes`.`category_id` ASC, `vote_count` DESC');
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
	const categories = await Categories.findAll({where: {active: 1}});
	const userVotes = await Votes.findAll({where: {reddit_user: userName}});
	// This entire loop needs to be a promise
	const promise = new Promise(async (resolve, reject) => {
		try {
			for (const [id, entries] of req) {
				if (entries.length === 0) {
					continue;
				}
				// eslint-disable-next-line eqeqeq
				const category = categories.find(cat => cat.id == id); // The eqeq is very important
				for (const entry of entries) {
					if (entry == null) continue;
					if (category.entryType === 'themes') {
						await Votes.findOrCreate({
							where: {
								entry_id: entry.id,
							},
							defaults: {
								reddit_user: userName,
								category_id: category.id,
								theme_name: entry.title,
								anilist_id: entry.anilistID,
							},
						});
					} else {
						await Votes.findOrCreate({
							where: {
								entry_id: entry.id,
							},
							defaults: {
								reddit_user: userName,
								category_id: category.id,
							},
						});
					}
				}
				const filteredVotes = userVotes.filter(vote => vote.category_id === category.id);
				for (const vote of filteredVotes) {
					const found = entries.find(entry => entry.id === vote.entry_id);
					if (!found) {
						await Votes.destroy({
							where: {entry_id: vote.entry_id},
						});
					}
				}
			}
			resolve();
		} catch (err) {
			reject(err);
		}
	});
	promise.then(() => response.empty());
});

apiApp.get('/get', async (request, response) => {
	const userName = (await request.reddit().get('/api/v1/me')).body.name;
	if (!await request.authenticate({name: userName, oldEnough: true, lock: 'voting'})) {
		return response.json(401, {error: 'Invalid user. Your account may be too new.'});
	}
	try {
		response.json(await Votes.findAll({where: {reddit_user: userName}}));
	} catch (error) {
		response.error(error);
	}
});

module.exports = apiApp;
