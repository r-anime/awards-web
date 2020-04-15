module.exports = (sequelize, types) => sequelize.define('votes', {
	reddit_user: {
		type: types.STRING,
		allowNull: false,
	},
	category_id: {
		type: types.INTEGER,
		allowNull: false,
	},
	entry_id: types.INTEGER,
	anilist_id: types.INTEGER,
	theme_name: types.STRING,
});
