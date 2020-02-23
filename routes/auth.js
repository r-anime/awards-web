const log = require('another-logger');
const superagent = require('superagent');
const authApp = require('polka')();
const config = require('../config');
const db = require('../util/db');

function query (obj) {
	return Object.keys(obj)
		.map(k => `${encodeURIComponent(k)}=${encodeURIComponent(obj[k])}`)
		.join('&');
}


authApp.get('/reddit', (request, response) => {
	const queryString = query({
		client_id: config.reddit.clientId,
		response_type: 'code',
		state: 'test state', // TODO
		redirect_uri: `${config.host}/auth/reddit/callback`,
		duration: 'permanent',
		scope: 'identity',
	});
	response.redirect(`https://old.reddit.com/api/v1/authorize?${queryString}`);
});
authApp.get('/reddit/callback', async (request, response) => {
	const {error, state, code} = request.query;
	if (error) return response.end(error);
	if (state !== 'test state') return response.end('Invalid state');
	const data = await superagent.post('https://www.reddit.com/api/v1/access_token')
		.auth(config.reddit.clientId, config.reddit.clientSecret)
		.query({
			grant_type: 'authorization_code',
			code,
			redirect_uri: `${config.host}/auth/reddit/callback`,
		})
		.then(tokenResponse => tokenResponse.body);
	if (data.error) return response.end(data.error);
	request.session.redditAccessToken = data.access_token;
	request.session.redditRefreshToken = data.refresh_token;

	// Now that we stored the tokens, we need to see who we are and if we're
	// already in the database or not
	let name;
	try {
		name = (await request.reddit().get('/api/v1/me')).body.name;
		log.info(name);
	} catch (res) {
		log.error('Error getting reddit user info:', res.status, res.body);
	}
	const user = db.getUser(name);
	if (!user) {
		db.insertUser({
			reddit: name,
			level: 0,
			flags: 0,
		});
	}
	response.redirect('/login');
});
// debug stuff
authApp.get('/reddit/debug', (request, response) => {
	request.session.redditAccessToken = null;
	response.end('Yeeted');
});

// Deletes session
authApp.get('/logout', (request, response) => {
	request.session.destroy(() => {
		response.redirect('/login');
	});
});

module.exports = authApp;
