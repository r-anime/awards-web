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
		level: 3, // 3 for mod, 2 for host, 1 for juror, 0 for all others
		// other stuff?
	});
});

apiApp.get('/users', async (request, response) => {
	response.json(await r.table('users'));
});

module.exports = apiApp;
