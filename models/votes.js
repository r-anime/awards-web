module.exports = (sequelize, types) => sequelize.define('nominations', {
	reddit_user: {
		type: types.STRING,
		allowNull: false,
	},
	user_id: types.INTEGER,
	category_id: {
		type: types.INTEGER,
		allowNull: false,
	},
	entry_id: types.INTEGER,
	anilist_id: types.INTEGER,
	theme_name: types.STRING,
});
