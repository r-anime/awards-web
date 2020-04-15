module.exports = (sequelize, types) => sequelize.define('themes', {
	anime: {
		type: types.STRING,
		allowNull: false,
		defaultValue: '',
	},
	title: {
		type: types.STRING,
	},
	themeType: {
		type: types.STRING,
		defaultValue: '',
		allowNull: false,
	},
	anilistID: {
		type: types.INTEGER,
		allowNull: false,
	},
	themeNo: types.INTEGER,
	link: {
		type: types.STRING,
		defaultValue: '',
	},
});
