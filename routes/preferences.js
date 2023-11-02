const apiApp = require('polka')();
const sequelize = require('../models').sequelize;

const Answers = sequelize.model('answers');
const Applicants = sequelize.model('applicants');
const Scores = sequelize.model('scores');
const Users = sequelize.model('users');
const Categories = sequelize.model('categories');
const Jurors = sequelize.model('jurors');




async function fetchUserMap() {
	const userKey = await Applicants.findAll({
		where: {
			app_id: 4,
			active: true,
		},
		attributes: [
			'user_id', 'id'
		],
		include: [
			{
				model: Users,
				as: 'user',
				attributes: ['reddit'],
			},
		],
		order: [
			'user_id'
		],
	});

	const userMap = new Map();
	userKey.forEach(obj => {
		if (obj.user_id)
			userMap.set(obj.user_id, { reddit: obj.user.reddit, applicationId: obj.id });
	});
	return userMap
}

async function fetchPreferences() {
	return await Applicants.findAll({
		where: {
			active: true,
		},
		attributes: [
			'user_id'
		],
		include: [
			{
				model: Answers,
				as: 'answers',
				attributes: ['answer'],
				where: { 'question_id': 42 }
			},
		],
		order: [
			'user_id'
		],
	})
}

async function fetchNumberOfCats() {
    return await Applicants.findAll({
		where: {
			active: true,
		},
		attributes: [
			'user_id'
		],
		include: [
			{
				model: Answers,
				as: 'answers',
				attributes: ['answer'],
				where: { 'question_id': 41 }
			},
		],
		order: [
			'user_id'
		],
	})
}


async function fetchCategoryJurors() {
	return await Jurors.findAll({
		where: {
			active: true,
		},
		attributes: [
			'name', 'score', 'preference', 'categoryId'
		],
		include: [
			{
				model: Users,
				as: 'user',
				attributes: ['id']
			},
		],
		order: [
			'name'
		],
	})
}

async function fetchCategoryMap() {
    
	const catMap = new Map();
	const categories = await Categories.findAll({
		where: {
			active: true,
		},
		attributes: [
			'id', 'name'
		],
	})
	categories.forEach(cat => {
		catMap.set(cat.id, cat.name);
	});
	return catMap;
}

async function fetchScores() {
	return await Scores.findAll({
		attributes: ['score', 'subgrade'],
		include: [
			{
				model: Answers,
				as: 'answer',
				required: true,
				attributes: ['id'],
				include: [
					{
						model: Applicants,
						as: 'applicant',
						required: true,
						attributes: ['user_id', ],
						where: { 'app_id': 4 }
					}
				],
			}
		],
	});
}

apiApp.get('/', async (request, response) => {
    const auth = await request.authenticate({ level: 2 });
	if (!auth) {
		return response.json(401, { error: 'You must be an host to fetch preferences.' });
	}

	try {
		const prefTable = await fetchUserMap();
		const preferences = await fetchPreferences();
        const numberOfCats = await fetchNumberOfCats();
		const categoryJurors = await fetchCategoryJurors();
		const catMap = await fetchCategoryMap();
		const scores = await fetchScores();
		// console.log(prefTable.get(12595));

        //Initialize no cats as juror, scores 0, no prefs for each category, 
		prefTable.forEach((user, userId) => {
			user['jurorIn'] = [];
			user['scores'] = { 'genre': 0, 'visual': 0, 'oped': 0, 'char': 0, 'main': 0 }
			catMap.forEach((catName, catId) => {
				user[catName] = 999
			});
		});

        // Add number of cats they wish to be in
        numberOfCats.forEach(obj => {
            if (obj['user_id']!=null) {
                prefTable.get(obj['user_id']).numberOfCats=obj.answers[0].answer;
            }
        })

		// console.log(prefTable.get(12595));

        // Add prefs
		preferences.forEach(pref => {
			if (pref['user_id'] != null) {
				let prefVal = 0;
				JSON.parse(pref.answers[0].answer).forEach(catId => {
					const catName = catMap.get(catId);
					prefTable.get(pref['user_id'])[catName] = prefVal;
					prefVal++;
				});
			}
		});
		// console.log(prefTable.get(12595));

        // Add cats they are a juror in
		categoryJurors.forEach(juror => {
			prefTable.get(juror.user.id).jurorIn.push(catMap.get(juror.categoryId));
		});

        // Add scores
		scores.forEach(obj => {
			if (obj.answer.applicant['user_id'] != null) {
				const user = prefTable.get(obj.answer.applicant['user_id']);
				user.scores[obj.subgrade] += (obj.score / 3);
				if (obj.subgrade != 'oped')
					user.scores['main'] += (obj.score / 9);
			}
		});

		// console.log(prefTable.get(12595));

        // Convert to an array and send
		const data = [];
		prefTable.forEach(obj => data.push(obj));
		response.json(data);

	} catch (error) {
		response.error(error);
	}
});

module.exports = apiApp;