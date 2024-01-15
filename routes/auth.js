const log = require('another-logger');
const superagent = require('superagent');
const authApp = require('polka')();
const config = require('../config');
const sequelize = require('../models').sequelize;
const jwt = require('jsonwebtoken');

function query (obj) {
	return Object.keys(obj)
		.map(k => `${encodeURIComponent(k)}=${encodeURIComponent(obj[k])}`)
		.join('&');
}

authApp.get('/reddit/callback', async (request, response) => {
	const {error, state, code} = request.query;
	if (error) return response.end(error);
	if (state !== request.session.redditState) return response.end('Invalid state');
	const data = await superagent.post('https://www.reddit.com/api/v1/access_token')
		.auth(config.reddit.clientId, config.reddit.clientSecret)
		.set('User-Agent', config.reddit.userAgent)
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
		request.session.reddit_name = name;
	} catch (res) {
		response.error(error);
		return;
	}

	try {
		const [user] = await sequelize.model('users').findOrCreate({
			where: {
				reddit: name,
			},
			defaults: {
				level: 0,
				flags: 0,
			},
		});
		const {next} = JSON.parse(state);
		if (next === 'apps') {
			const lock = await sequelize.model('locks').findOne({
				where: {
					name: 'apps-open',
				},
			});
			if (lock.flag || user.level > lock.level) {
				const apps = await sequelize.model('applications').findAll({
					limit: 1,
					where: {active: true},
					order: [['year', 'DESC']],
				});
				await sequelize.model('applicants').findOrCreate({
					where: {
						user_id: user.id,
						active: true,
						app_id: apps[0].id,
					},
				});
			}
		}
		response.redirect(`/login/redirect/${next}`);
	} catch (responseError) {
		response.error(responseError);
	}
});

// debug stuff
authApp.get('/reddit/debug', (request, response) => {
	request.session.redditAccessToken = null;
	response.end('Yeeted');
});

// Deletes session
authApp.get('/logout/:next', (request, response) => {
	request.session.destroy(() => {
		if (request.params.next === 'home') {
			response.redirect('/');
			return;
		}
		response.redirect(`/${request.params.next}`);
	});
});

// Keep this at the bottom to avoid conflicts with other routes
authApp.get('/reddit/:next', (request, response) => {
	const state = JSON.stringify({
		next: request.params.next || '/',
		unique: `${Math.random()}`, // TODO: this should be secure
	});
	const queryString = query({
		client_id: config.reddit.clientId,
		response_type: 'code',
		state,
		redirect_uri: `${config.host}/auth/reddit/callback`,
		duration: 'permanent',
		scope: 'identity',
	});
	request.session.redditState = state;
	response.redirect(`https://reddit.com/api/v1/authorize?${queryString}`);
});

module.exports = authApp;
