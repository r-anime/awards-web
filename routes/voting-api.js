const log = require('another-logger');
const apiApp = require('polka')();
const db = require('../util/db');
const voteHelpers = require('../util/voteHelpers');

// all of this needs to be rewritten with sequelize's syntax

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
