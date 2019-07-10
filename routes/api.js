const log = require('another-logger');
const apiApp = require('polka')();
const superagent = require('superagent');
const db = require('../util/db');

apiApp.get('/me', async (request, response) => {
	if (!request.session.redditAccessToken) {
		return response.json(401);
	}
	let redditorInfo;
	try {
		redditorInfo = (await request.reddit().get('/api/v1/me')).body;
	} catch (error) {
		log.error(`Error while retrieving Reddit account info:\n${error}`);
		return response.json(500, error);
	}
	const userInfo = db.getUser(redditorInfo.name);
	log.info(userInfo);
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
	response.json(db.allUsers());
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
	log.info('adding user');
	db.insertUser({reddit: user.reddit, flags: 0});
	log.info('responding');
	response.json(user);
});

apiApp.delete('/user/:reddit', async (request, response) => {
	const redditName = request.params.reddit;
	if (!redditName) {
		return response.json(400, {error: 'Missing reddit name of user to delete'});
	}
	const userInfo = db.getUser(redditName);
	if (!userInfo) {
		return response.json(400, {error: 'The specified user does not exist'});
	}
	if (!await request.authenticate({level: userInfo.level + 1})) {
		return response.json(401, {error: 'You can only remove users of lower level than yourself'});
	}
	db.deleteUser(redditName);
	response.empty();
});

module.exports = apiApp;
