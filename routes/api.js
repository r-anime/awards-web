/* eslint-disable no-await-in-loop */
/* eslint-disable multiline-comment-style */
const log = require('another-logger');
const apiApp = require('polka')();
const superagent = require('superagent');
const sequelize = require('../models').sequelize;
const parse = require('../themes/parser');
// eslint-disable-next-line no-unused-vars
const voteHelpers = require('../util/voteHelpers');


// Sequelize models to avoid redundancy
const Users = sequelize.model('users');
const Categories = sequelize.model('categories');
const Noms = sequelize.model('nominations');
const Jurors = sequelize.model('jurors');
const HMs = sequelize.model('honorable_mentions');
const Themes = sequelize.model('themes');
const Votes = sequelize.model('votes');

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
					avatar: redditorInfo.subreddit.icon_img,
					created: redditorInfo.created_utc,
				},
				level: user.level,
				flags: user.flags,
			});
		});
	} catch (error) {
		return response.error(error);
	}
});

apiApp.get('/users', (request, response) => {
	Users.findAll().then(users => {
		response.json(users);
	});
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
		redditResponse = await superagent.get(`https://www.reddit.com/user/${user.reddit}/about.json`);
	} catch (error) {
		return response.json(400, {error: 'That user does not have a Reddit account'});
	}
	// replace the name with the one from reddit in case the capitalization is different
	user.reddit = redditResponse.body.data.name;
	try {
		await Users.create(user);
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
	if (!await request.authenticate({level: userInfo.level + 1})) {
		return response.json(401, {error: 'You can only remove users of lower level than yourself'});
	}
	try {
		await Users.destroy({
			where: {
				reddit: redditName,
			},
		});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/categories', async (request, response) => {
	try {
		response.json(await Categories.findAll({where: {active: 1}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/category/:id', async (request, response) => {
	try {
		response.json(await Categories.findOne({
			where: {
				id: request.params.id,
			},
		}));
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
		response.json(await Categories.create(category));
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
	category = Object.assign({}, await Categories.findOne({
		where: {
			id: category.id,
		},
	}));
	try {
		const res = await Categories.update(category, {where: {id: category.id}});
		response.json(res);
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/category/:id', async (request, response) => {
	if (!await request.authenticate({level: 4})) {
		return response.json(401, {error: 'You must be an admin to delete categories'});
	}
	try {
		await Categories.destroy({
			where: {
				id: request.params.id,
			},
		});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/category/:id/nominations', async (request, response) => {
	try {
		response.json(await Noms.findAll({
			where: {
				categoryId: request.params.id,
				active: 1,
			},
			include: {
				model: Categories,
			},
		}));
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
		const promise = new Promise(async (resolve, reject) => {
			try {
				for (const nom of nominations) {
					if (nom.themeId === -1) nom.themeId = null;
					await Noms.create({
						alt_name: nom.alt_name,
						alt_img: nom.alt_img,
						categoryId: request.params.id,
						anilist_id: nom.anilist_id,
						themeId: nom.themeId,
						writeup: nom.writeup,
						rank: nom.rank,
						votes: nom.votes,
						character_id: nom.character_id,
						finished: nom.finished,
						staff: nom.staff,
					});
				}
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		promise.then(async () => {
			response.json(await Noms.findAll({
				where: {
					categoryId: request.params.id,
				},
				include: {
					model: Categories,
				},
			}));
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
		await Noms.destroy({where: {categoryId: request.params.id}});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/categories/nominations', async (request, response) => {
	try {
		response.json(await Noms.findAll({where: {active: true}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/category/:id/jurors', async (request, response) => {
	try {
		response.json(await Jurors.findAll({
			where: {
				categoryId: request.params.id,
				active: true,
			},
		}));
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
	} catch (error) {
		response.error(error);
	}
	try {
		const promise = new Promise(async (resolve, reject) => {
			try {
				for (const juror of jurors) {
					// log.success(nom);
					await Jurors.create({
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
		promise.then(async () => {
			response.json(await Jurors.findAll({
				where: {
					categoryId: request.params.id,
					active: true,
				},
			}));
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
		await Jurors.destroy({where: {categoryId: request.params.id}});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/categories/jurors', async (request, response) => {
	try {
		response.json(await Jurors.findAll({where: {active: true}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/category/:id/hms', async (request, response) => {
	try {
		response.json(await HMs.findAll({
			where: {
				categoryId: request.params.id,
				active: true,
			},
		}));
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
		const promise = new Promise(async (resolve, reject) => {
			try {
				for (const hm of hms) {
					// log.success(nom);
					await HMs.create({
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
		promise.then(async () => {
			response.json(await HMs.findAll({where: {active: true}}));
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
		await HMs.destroy({where: {categoryId: request.params.id}});
		response.empty();
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/categories/hms', async (request, response) => {
	try {
		response.json(await HMs.findAll({where: {active: true}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.delete('/categories/wipeNominations', async (request, response) => {
	if (!await request.authenticate({level: 4})) {
		return response.json(401, {error: 'You must be an admin to delete nominations data.'});
	}
	Promise.all([Noms.destroy({truncate: true}), Jurors.destroy({truncate: true}), HMs.destroy({truncate: true})]).then(() => response.empty(), error => response.error(error));
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
				themes.forEach(async theme => {
					await Themes.create(theme);
				});
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		promise.then(async () => {
			response.json(await Themes.findAll());
		});
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/themes', async (request, response) => {
	try {
		response.json(await Themes.findAll());
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
				Themes.destroy({where: {themeType: request.params.themeType}});
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
		response.json(await Categories.findAll({where: {active: true, awardsGroup: request.params.group}}));
	} catch (error) {
		response.error(error);
	}
});

apiApp.post('/deleteaccount', async (request, response) => {
	const name = (await request.reddit().get('/api/v1/me')).body.name;
	try {
		Users.destroy({where: {reddit: name}});
		Votes.destroy({where: {reddit_user: name}});
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
		const allVotes = await Votes.findAll();
		// eslint-disable-next-line no-unused-vars
		const [allUsers, userMeta] = await sequelize.query('SELECT COUNT(DISTINCT `reddit_user`) as `count` FROM `votes`');

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
		response.json(await sequelize.query('SELECT COUNT(*) as `vote_count`, `votes`.`category_id`, `votes`.`entry_id`, `votes`.`anilist_id`, `votes`.`theme_name` FROM `votes` GROUP BY `votes`.`category_id`, `votes`.`entry_id`, `votes`.`anilist_id`, `votes`.`theme_name` ORDER BY `votes`.`category_id` ASC, `vote_count` DESC'));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/votes/dashboard/get', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		response.json(401, {error: 'You must be an host to see vote totals.'});
	}
	try {
		response.json(await sequelize.query('SELECT COUNT(*) as `vote_count`, `votes`.`category_id`, `votes`.`entry_id`, `votes`.`anilist_id`, `votes`.`theme_name` FROM `votes` WHERE `votes`.`anilist_id` IS NOT NULL AND `votes`.`theme_name` IS NULL GROUP BY `votes`.`category_id`, `votes`.`anilist_id` ORDER BY `votes`.`category_id` ASC, `vote_count` DESC'));
	} catch (error) {
		response.error(error);
	}
});

apiApp.get('/votes/oped/get', async (request, response) => {
	if (!await request.authenticate({level: 2})) {
		response.json(401, {error: 'You must be an host to see vote totals.'});
	}
	try {
		response.json(await sequelize.query('SELECT COUNT(*) as `vote_count`, `votes`.`category_id`, `votes`.`entry_id`, `votes`.`anilist_id`, `votes`.`theme_name` FROM `votes` WHERE `votes`.`theme_name` IS NOT NULL GROUP BY `votes`.`category_id`, `votes`.`theme_name` ORDER BY `votes`.`category_id` ASC, `vote_count` DESC'));
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
