const log = require('another-logger');
const apiApp = require('polka')();
const superagent = require('superagent');
const db = require('../util/db');
const parse = require('../themes/parser');
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
		log.success(db.getAllCategories());
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

apiApp.post('/votes/submit', async (request, response) => {
	const userName = (await request.reddit().get('/api/v1/me')).body.name;
	if (!await request.authenticate({name: userName})) {
		return response.json(401, {error: 'Invalid user.'});
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
					if (voteHelpers.isOPED(category)) {
						db.pushUserThemeVotes({
							redditUser: userName,
							categoryId: category.id,
							entryId: entry.id,
							themeName: entry.title,
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
	if (!await request.authenticate({name: userName})) {
		return response.json(401, {error: 'Invalid user.'});
	}
	try {
		response.json(db.getAllUserVotes(userName));
	} catch (error) {
		response.error(error);
	}
});

module.exports = apiApp;
