/* eslint-disable multiline-comment-style */
const log = require('another-logger');
const apiApp = require('polka')();
const superagent = require('superagent');
const db = require('../util/db');
const parse = require('../themes/parser');
// eslint-disable-next-line no-unused-vars
const voteHelpers = require('../util/voteHelpers');

apiApp.get('/me', async (request, response) => {
	if (!request.session.redditAccessToken) {
		return response.json(401);
	}
	let redditorInfo;
	try {
		redditorInfo = (await request.reddit().get('/api/v1/me')).body;
	} catch (error) {
		return response.error(error);
	}
	let userInfo;
	try {
		userInfo = db.getUser(redditorInfo.name);
	} catch (error) {
		return response.error(error);
	}
	if (!userInfo) {
		return response.json(404, null);
	}
	response.json({
		reddit: {
			name: redditorInfo.name,
			avatar: redditorInfo.subreddit.icon_img,
			created: redditorInfo.created_utc,
		},
		level: userInfo.level,
		flags: userInfo.flags,
	});
});

apiApp.get('/users', (request, response) => {
	response.json(db.getAllUsers());
});

apiApp.post('/user', async (request, response) => {
	let user;
	try {
		user = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}
	if (!await request.authenticate({level: user.level + 1})) {
		return response.json(401, {error: 'You can only set users to levels below your own'});
	}
	log.info(user);
	if (db.getUser(user.reddit)) {
		log.info('user already present');
		return response.json(400, {error: 'That user is already present'});
	}
	let redditResponse;
	try {
		redditResponse = await superagent.get(`https://www.reddit.com/user/${user.reddit}/about.json`);
	} catch (error) {
		return response.json(400, {error: 'That user does not have a Reddit account'});
	}
	// replace the name with the one from reddit in case the capitalization is different
	user.reddit = redditResponse.body.data.name;
	try {
		db.insertUser(user);
		response.json(user);
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/user/:reddit', async (request, response) => {
	const redditName = request.params.reddit;
	if (!redditName) {
		return response.json(400, {error: 'Missing reddit name of user to delete'});
	}
	let userInfo;
	try {
		userInfo = db.getUser(redditName);
	} catch (error) {
		return response.error(error);
	}
	if (!userInfo) {
		return response.json(400, {error: 'The specified user does not exist'});
	}
	if (!await request.authenticate({level: userInfo.level + 1})) {
		return response.json(401, {error: 'You can only remove users of lower level than yourself'});
	}
	try {
		db.deleteUser(redditName);
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/categories', (request, response) => {
	try {
		response.json(db.getAllCategories());
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/category/:id', (request, response) => {
	try {
		response.json(db.getCategory(request.params.id));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/category', async (request, response) => {
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
		const {lastInsertRowid} = db.insertCategory(category);
		response.json(db.getCategoryByRowid(lastInsertRowid));
	} catch (error) {
		response.error(error);
	}
});

apiApp.patch('/category/:id', async (request, response) => {
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
	category = Object.assign({}, db.getCategory(category.id), category);
	try {
		db.updateCategory(category);
		response.json(db.getCategory(category.id));
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/category/:id', async (request, response) => {
	if (!await request.authenticate({level: 4})) {
		return response.json(401, {error: 'You must be an admin to delete categories'});
	}
	try {
		db.deleteCategory(request.params.id);
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/category/:id/nominations', (request, response) => {
	try {
		response.json(db.getNominationsByCategory(request.params.id));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/category/:id/nominations', async (request, response) => {
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
		const promise = new Promise((resolve, reject) => {
			try {
				for (const nom of nominations) {
					// log.success(nom);
					db.insertNomination({
						altName: nom.altName,
						categoryID: request.params.id,
						anilistID: nom.anilistID,
						themeID: nom.themeID,
						entryType: nom.entryType,
						active: 1,
						writeup: nom.writeup,
						juryRank: nom.juryRank,
						publicVotes: nom.publicVotes,
						characterID: nom.characterID,
						publicSupport: nom.publicSupport,
						staff: nom.staff,
					});
				}
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		promise.then(() => {
			response.json(db.getNominationsByCategory(request.params.id));
		});
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/category/:id/nominations', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to delete nominations'});
	}
	try {
		await db.deactivateNominationsByCategory(request.params.id);
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.patch('/category/:id/nominations', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to modify nominations'});
	}
	let req;
	try {
		req = await request.json();
	} catch (error) {
		response.error(error);
	}
	try {
		await db.toggleActiveNominationsById({
			id: req.id,
			active: req.active,
		});
		response.json(await db.getNominationsByCategory(req.id));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/categories/nominations', (request, response) => {
	try {
		response.json(db.getAllNominations());
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/category/:id/jurors', (request, response) => {
	try {
		response.json(db.getJurorsByCategory(request.params.id));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/category/:id/jurors', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to add jurors'});
	}
	let jurors;
	try {
		jurors = await request.json();
		log.success(jurors);
	} catch (error) {
		response.error(error);
	}
	try {
		const promise = new Promise((resolve, reject) => {
			try {
				for (const juror of jurors) {
					// log.success(nom);
					db.insertJuror({
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
		promise.then(() => {
			response.json(db.getJurorsByCategory(request.params.id));
		});
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/category/:id/jurors', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to delete jurors'});
	}
	try {
		await db.deactivateJurorsByCategory(request.params.id);
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/category/:id/hms', (request, response) => {
	try {
		response.json(db.getHMsByCategory(request.params.id));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/category/:id/hms', async (request, response) => {
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
		const promise = new Promise((resolve, reject) => {
			try {
				for (const hm of hms) {
					// log.success(nom);
					db.insertHM({
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
		promise.then(() => {
			response.json(db.getHMsByCategory(request.params.id));
		});
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/category/:id/hms', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to delete honorable mentions.'});
	}
	try {
		await db.deactivateHMsByCategory(request.params.id);
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/themes/create', async (request, response) => {
	if (!await request.authenticate({level: 4})) {
		return response.json(401, {error: 'You must be an admin to modify themes'});
	}
	let req;
	try {
		req = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}
	const themes = await parse.readThemes(`./themes/${req.themeType.toUpperCase()}.csv`);
	try {
		const promise = new Promise((resolve, reject) => {
			try {
				themes.forEach(theme => {
					db.insertThemes(theme);
				});
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		promise.then(() => {
			response.json(db.getAllThemes());
		});
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/themes', async (request, response) => {
	try {
		response.json(await db.getAllThemes());
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/themes/delete/:themeType', async (request, response) => {
	if (!await request.authenticate({level: 4})) {
		return response.json(401, {error: 'You must be an admin to delete themes'});
	}
	try {
		const promise = new Promise((resolve, reject) => {
			try {
				log.success(request.params.themeType);
				db.deleteThemes(request.params.themeType);
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		promise.then(() => {
			response.empty();
		});
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/categories/:group', async (request, response) => {
	try {
		response.json(await db.getCategoryByGroup(request.params.group));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/deleteaccount', async (request, response) => {
	const name = (await request.reddit().get('/api/v1/me')).body.name;
	try {
		db.deleteUser(name);
		db.deleteAllVotesFromUser(name);
		request.session.destroy(() => {
			response.empty();
		});
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/voteSummary', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You must be a host to view vote summary.'});
	}
	try {
		const allVotes = await db.getAllVotes();
		const allUsers = await db.getVoteUserCount();

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

apiApp.get('/votes/all/get', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		response.json(401, {error: 'You must be an host to see vote totals.'});
	}
	try {
		response.json(await db.getVoteTotals());
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/votes/dashboard/get', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		response.json(401, {error: 'You must be an host to see vote totals.'});
	}
	try {
		response.json(await db.getDashboardTotals());
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/votes/oped/get', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		response.json(401, {error: 'You must be an host to see vote totals.'});
	}
	try {
		response.json(await db.getOPEDTotals());
	} catch (error) {
		response.error(error);
	}
});


apiApp.get('/votes/all/delete', async (request, response) => {
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

module.exports = apiApp;
