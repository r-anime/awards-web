const log = require('another-logger');
const apiApp = require('polka')();
const superagent = require('superagent');
const sequelize = require('../models').sequelize;
const uuid = require('uuid');

// Sequelize models to avoid redundancy
const Users = sequelize.model('users');
const Votes = sequelize.model('votes');
const Applicants = sequelize.model('applicants');
const Answers = sequelize.model('answers');

const {yuuko} = require('../bot/index');
const config = require('../config');

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
	try {
		Users.findOne({
			where: {
				reddit: redditorInfo.name,
			},
		}).then(user => {
			if (!user) {
				return response.json(404, null);
			}
			response.json({
				reddit: {
					name: redditorInfo.name,
					avatar: redditorInfo.subreddit.icon_img.split('?')[0],
					created: redditorInfo.created_utc,
				},
				level: user.level,
				flags: user.flags,
				uuid: user.uuid,
			});
		});
	} catch (error) {
		return response.error(error);
	}
});

apiApp.get('/all', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		return response.json(401, {error: 'You do not have permission to retrieve the user list'});
	}
	try {
		response.json(await Users.findAll({
			order: [['level', 'DESC']]
		}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/', async (request, response) => {
	let user;
	try {
		user = await request.json();
	} catch (error) {
		return response.json({error: 'Invalid JSON'});
	}
	const auth = await request.authenticate({level: user.level + 1});
	if (!auth) {
		return response.json(401, {error: 'You can only set users to levels below your own'});
	}
	const userInfo = await Users.findOne({
		where: {
			reddit: user.reddit,
		},
	});
	if (userInfo) {
		log.info('user already present');
		return response.json(400, {error: 'That user is already present'});
	}
	let redditResponse;
	try {
		redditResponse = await superagent.get(`https://www.reddit.com/user/${encodeURI(user.reddit)}/about.json`).set('User-Agent', config.reddit.userAgent);
	} catch (error) {
		console.log(error);
		return response.json(400, {error: 'That user does not have a Reddit account'});
	}
	// replace the name with the one from reddit in case the capitalization is different
	user.reddit = redditResponse.body.data.name;
	try {
		await Users.create(user);
		yuuko.createMessage(config.discord.auditChannel, {
			embed: {
				title: 'User Added',
				description: `User ${user.reddit} was created or added by **${auth}** with level **${user.level}**.`,
				color: 8302335,
			},
		});
		response.json(user);
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/:reddit', async (request, response) => {
	const redditName = request.params.reddit;
	if (!redditName) {
		return response.json(400, {error: 'Missing reddit name of user to delete'});
	}
	let userInfo;
	try {
		userInfo = await Users.findOne({
			where: {
				reddit: redditName,
			},
		});
	} catch (error) {
		return response.error(error);
	}
	if (!userInfo) {
		return response.json(400, {error: 'The specified user does not exist'});
	}
	const auth = await request.authenticate({level: userInfo.level + 1});
	if (!auth) {
		return response.json(401, {error: 'You can only remove users of lower level than yourself'});
	}
	try {
		await Users.destroy({
			where: {
				reddit: redditName,
			},
		});
		yuuko.createMessage(config.discord.auditChannel, {
			embed: {
				title: 'User Removed',
				description: `User ${userInfo.reddit} was removed by **${auth}**.`,
				color: 8302335,
			},
		});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/deleteaccount', async (request, response) => {
	const name = (await request.reddit().get('/api/v1/me')).body.name;
	try {
		// Don't allow people to do this lest they delete their app from the records. TODO: Only delete the app that's open
		// const user = await Users.findOne({
		// where: {
		// reddit: name,
		// },
		// });
		// const applicant = await Applicants.findOne({
		// where: {
		// user_id: user.id,
		// },
		// });
		// await Answers.destroy({
		// where: {
		// applicant_id: applicant.id,
		// },
		// });
		// await Applicants.destroy({
		// where: {
		// id: applicant.id,
		// },
		// });
		await Users.destroy({where: {reddit: name}});
		await Votes.destroy({where: {reddit_user: name}});
		request.session.destroy(() => {
			response.empty();
		});
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/create-uuid', async (request, response) => {
	if (!request.session.redditAccessToken) {
		return response.json(401);
	}
	let redditorInfo;
	try {
		redditorInfo = (await request.reddit().get('/api/v1/me')).body;
	} catch (error) {
		return response.error(error);
	}
	try {
		Users.findOne({
			where: {
				reddit: redditorInfo.name,
			},
		}).then(async (user) => {
			if (user.uuid){
				return response.json({
					uuid: user.uuid,
					message: "Unique ID already exists for this user."
				})
			} else {
				const newuuid = uuid.v4();
				await user.update({
					uuid: newuuid,
				});
				return response.json({
					uuid: newuuid,
					message: "Unique ID created."
				})
			}
		});
	} catch (error) {
		return response.error(error);
	}
});

module.exports = apiApp;
