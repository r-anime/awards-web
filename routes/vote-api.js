const apiApp = require('polka')();
const sequelize = require('../models').sequelize;

// Sequelize models to avoid redundancy
const Votes = sequelize.model('votes');

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
	if (!await request.authenticate({level: 2})) {
		response.json(401, {error: 'You must be an host to see vote totals.'});
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
	if (!await request.authenticate({level: 2})) {
		response.json(401, {error: 'You must be an host to see vote totals.'});
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
	if (!await request.authenticate({level: 2})) {
		response.json(401, {error: 'You must be an host to see vote totals.'});
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

// all of this needs to be rewritten with sequelize's syntax

// eslint-disable-next-line multiline-comment-style
/*
const voteHelpers = require('../util/voteHelpers');
apiApp.post('/votes/submit', async (request, response) => {
	const userName = (await request.reddit().get('/api/v1/me')).body.name;
	if (!await request.authenticate({name: userName, oldEnough: true})) {
		return response.json(401, {error: 'Invalid user. Your account may be too new.'});
	}
	await db.deleteAllVotesFromUser(userName);
	let req;
	try {
		req = Object.entries(await request.json());
	} catch (error) {
		return response.json(400, {error: 'Invalid JSON'});
	}
	const categories = await db.getAllCategories();
	// This entire loop needs to be a promise
	const promise = new Promise((resolve, reject) => {
		try {
			for (const [id, entries] of req) {
				if (entries.length === 0) {
					continue;
				}
				const category = categories.find(cat => cat.id == id); // The eqeq is very important
				for (const entry of entries) {
					if (entry == null) continue;
					if (voteHelpers.isOPED(category)) {
						db.pushUserThemeVotes({
							redditUser: userName,
							categoryId: category.id,
							entryId: entry.id,
							themeName: entry.title,
							anilistId: entry.anilistID,
						});
					} else if (voteHelpers.isDashboard(category)) {
						db.pushUserDashboardVotes({
							redditUser: userName,
							categoryId: category.id,
							entryId: entries.indexOf(entry),
							anilistId: entry.id,
						});
					} else {
						db.pushUserVotes({
							redditUser: userName,
							categoryId: category.id,
							entryId: entry.id,
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

apiApp.get('/votes/get', async (request, response) => {
	const userName = (await request.reddit().get('/api/v1/me')).body.name;
	if (!await request.authenticate({name: userName, oldEnough: true})) {
		return response.json(401, {error: 'Invalid user. Your account may be too new.'});
	}
	try {
		response.json(await db.getAllUserVotes(userName));
	} catch (error) {
		response.error(error);
	}
});

*/

module.exports = apiApp;
