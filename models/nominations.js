/* eslint-disable new-cap */
module.exports = (sequelize, types) => sequelize.define('nominations', {
	category_id: {
		type: types.INTEGER,
		allowNull: false,
	},
	anilist_id: types.INTEGER,
	theme_id: types.INTEGER,
	character_id: types.INTEGER,
	entry_type: {
		type: types.STRING,
		allowNull: false,
		defaultValue: 'shows',
	},
	active: {
		type: types.BOOLEAN,
		allowNull: false,
		defaultValue: true,
	},
	writeup: {
		type: types.STRING(10000),
		defaultValue: '',
	},
	rank: types.INTEGER,
	votes: types.INTEGER,
	finished: {
		type: types.INTEGER,
		defaultValue: 0,
	},
	alt_name: {
		type: types.STRING(255),
		defaultValue: '',
	},
	staff: {
		type: types.STRING(1028),
		defaultValue: '',
	},
	alt_img: {
		type: types.STRING,
		defaultValue: '',
	},
});
