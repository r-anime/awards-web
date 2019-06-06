const log = require('another-logger');
const apiApp = require('polka')();
const r = require('../util/db');

// this entire file is TODO because I have to implement actual API calls again

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
	const userInfo = (await r.table('users').filter({reddit: redditorInfo.name}).run())[0];
	log.info(userInfo);
	response.json({
		reddit: {
			name: redditorInfo.name,
			avatar: redditorInfo.subreddit.icon_img,
		},
		// Should come from a Discord API OAuth request (if I ever get to that)
		discord: {
			name: 'Geo☆1088',
			discriminator: 2272,
			avatar: 'https://cdn.discordapp.com/avatars/122902150291390469/2877cf6c8d2d0dca3c6602c89cbfbbc6.png?size=64',
		},
		// Should come from reading the database (compare users by reddit name)
		level: userInfo.level, // 3 for mod, 2 for host, 1 for juror, 0 for all others
		// other stuff?
	});
});

apiApp.get('/users', async (request, response) => {
	response.json(await r.table('users'));
});

apiApp.post('/user', async (request, response) => {
	const user = await request.json();
	if (!await request.authenticate({level: user.level})) {
		return response.json(401, {error: 'You can only set users to levels below your own'});
	}
	log.info(user);
	const users = await r.table('users').filter({reddit: user.reddit}).run();
	if (users.length) {
		log.info('user already present');
		return response.json(400, {error: 'That user is already present'});
	}
	log.info('adding user');
	await r.table('users').insert(user);
	log.info('responding');
	response.json(user);
});

module.exports = apiApp;
