module.exports = (sequelize, types) => sequelize.define('finalvotes', {
	reddit_user: {
		type: types.STRING,
		allowNull: false,
	},
	category_id: {
		type: types.INTEGER,
		allowNull: false,
	},
	nom_id: types.INTEGER,
	anilist_id: types.INTEGER,
	theme_name: types.STRING,
});
