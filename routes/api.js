const log = require('another-logger');
const apiApp = require('polka')();
const superagent = require('superagent');
const db = require('../util/db');
const parse = require('../themes/parser');

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
	const user = await request.json();
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
	const category = await request.json();
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
	let category = await request.json();
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
		return response.json(401, {error: 'You must be an admin to delete categories (did you mean to hide the category instead?)'});
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
	const req = await request.json();
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

apiApp.post('/themes/delete', async (request, response) => {
	if (!await request.authenticate({level: 4})) {
		return response.json(401, {error: 'You must be an admin to delete themes'});
	}
	const req = await request.json();
	try {
		const promise = new Promise((resolve, reject) => {
			try {
				db.deleteThemes(req.themeType);
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		promise.then(() => {
			response.json(201);
		});
	} catch (error) {
		response.error(error);
	}
});

module.exports = apiApp;
